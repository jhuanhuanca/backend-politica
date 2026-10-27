<?php

namespace App\Services\Admin;

use App\Enums\VoteTopicStatus;
use App\Enums\SuggestionStatus;
use App\Models\Debate;
use App\Models\Message;
use App\Models\Militant;
use App\Models\Signature;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\VoteTopic;
use App\Support\Bolivia;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminStatsService
{
    /** @var Collection<int, array<string, mixed>>|null */
    private ?Collection $rankedCache = null;

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return [
            'debates' => Debate::query()->count(),
            'users' => User::query()->where('is_admin', false)->count(),
            'militants' => Militant::query()->count(),
            'militants_pending' => Militant::query()->where('status', 'pending')->count(),
            'vote_topics' => VoteTopic::query()->count(),
            'signatures' => \App\Models\Signature::query()->count(),
            'suggestions_pending' => Suggestion::query()->where('status', SuggestionStatus::Pending)->count(),
            'stats' => $this->periodStats($monthStart, $monthEnd),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function periodStats(CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $mostCommented = Debate::query()
            ->withCount([
                'messages as messages_count' => fn (Builder $query) => $query
                    ->whereBetween('created_at', [$monthStart, $monthEnd]),
            ])
            ->orderByDesc('messages_count')
            ->orderByDesc('id')
            ->first();

        $topDebates = Debate::query()
            ->withCount([
                'messages as messages_count' => fn (Builder $query) => $query
                    ->whereBetween('created_at', [$monthStart, $monthEnd]),
            ])
            ->whereHas(
                'messages',
                fn (Builder $query) => $query->whereBetween('created_at', [$monthStart, $monthEnd])
            )
            ->orderByDesc('messages_count')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn (Debate $debate) => [
                'id' => $debate->id,
                'title' => $debate->title,
                'messages_count' => (int) $debate->messages_count,
            ])
            ->values()
            ->all();

        $reachedGoal = VoteTopic::query()
            ->where(function (Builder $query): void {
                $query->where('status', VoteTopicStatus::Achieved)
                    ->orWhere(function (Builder $inner): void {
                        $inner->where('target_signatures', '>', 0)
                            ->whereColumn('signatures_count', '>=', 'target_signatures');
                    });
            })
            ->orderByDesc('signatures_count')
            ->get(['id', 'title', 'signatures_count', 'target_signatures', 'status']);

        return [
            'month' => $monthStart->format('Y-m'),
            'month_label' => $this->monthLabel($monthStart),
            'comments_this_month' => Message::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'users_this_month' => User::query()
                ->where('is_admin', false)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'militants_this_month' => Militant::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'votes_reached_goal' => $reachedGoal->count(),
            'most_commented_debate' => $mostCommented && (int) $mostCommented->messages_count > 0
                ? [
                    'id' => $mostCommented->id,
                    'title' => $mostCommented->title,
                    'messages_count' => (int) $mostCommented->messages_count,
                ]
                : null,
            'top_debates' => $topDebates,
            'votes_reached' => $reachedGoal->map(fn (VoteTopic $topic) => [
                'id' => $topic->id,
                'title' => $topic->title,
                'signatures_count' => (int) $topic->signatures_count,
                'target_signatures' => (int) $topic->target_signatures,
            ])->values()->all(),
            'comments_by_month' => $this->monthlyCounts(Message::query()),
            'users_by_month' => $this->monthlyCounts(User::query()->where('is_admin', false)),
            'militants_by_month' => $this->monthlyCounts(Militant::query()),
            'from' => $this->statsFrom()->format('Y-m'),
            'from_label' => $this->monthLabel($this->statsFrom()),
            'leaders' => $this->possibleLeaders(),
            'departments' => $this->departmentAnalytics(),
            'unassigned_users' => User::query()->where('is_admin', false)->whereNull('department')->count(),
            'spectrum' => $this->politicalSpectrum(),
            'cleavages' => $this->politicalCleavages(),
        ];
    }

    /**
     * @param  Builder<*>  $query
     * @return list<array{month: string, label: string, total: int}>
     */
    private function monthlyCounts(Builder $query): array
    {
        $cursor = $this->statsFrom()->copy();
        $until = now()->startOfMonth();

        if ($until->lt($cursor)) {
            $until = $cursor->copy();
        }

        $series = [];

        while ($cursor->lte($until)) {
            $start = $cursor->copy()->startOfMonth();
            $end = $cursor->copy()->endOfMonth();

            $series[] = [
                'month' => $start->format('Y-m'),
                'label' => $this->monthLabel($start),
                'total' => (int) (clone $query)->whereBetween('created_at', [$start, $end])->count(),
            ];

            $cursor->addMonth();
        }

        return $series;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function possibleLeaders(): array
    {
        $real = $this->rankedPeople()->take(12)->values()->all();

        if ($real !== []) {
            return $real;
        }

        return $this->sampleLeaders();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function departmentAnalytics(): array
    {
        $people = $this->rankedPeople();
        if ($people->isEmpty()) {
            $people = collect($this->sampleLeaders());
        }
        $users = $this->countByDepartment(
            User::query()->where('is_admin', false),
            'department'
        );
        $militants = Militant::query()
            ->selectRaw('department, count(*) as total')
            ->groupBy('department')
            ->pluck('total', 'department');
        $comments = $this->countByDepartment(
            Message::query()->join('users', 'users.id', '=', 'messages.user_id')->where('users.is_admin', false),
            'users.department'
        );
        $signatures = $this->countByDepartment(
            Signature::query()->join('users', 'users.id', '=', 'signatures.user_id')->where('users.is_admin', false),
            'users.department'
        );
        $suggestions = $this->countByDepartment(
            Suggestion::query()->join('users', 'users.id', '=', 'suggestions.user_id')->where('users.is_admin', false),
            'users.department'
        );

        return array_map(function (string $name) use ($people, $users, $militants, $comments, $signatures, $suggestions): array {
            $leaders = $people
                ->filter(fn (array $row) => ($row['department'] ?? null) === $name)
                ->take(3)
                ->values()
                ->all();

            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'users' => (int) ($users[$name] ?? 0),
                'militants' => (int) ($militants[$name] ?? 0),
                'comments' => (int) ($comments[$name] ?? 0),
                'signatures' => (int) ($signatures[$name] ?? 0),
                'suggestions' => (int) ($suggestions[$name] ?? 0),
                'leaders' => $leaders,
            ];
        }, Bolivia::DEPARTMENTS);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function rankedPeople(): Collection
    {
        if ($this->rankedCache instanceof Collection) {
            return $this->rankedCache;
        }

        $this->rankedCache = User::query()
            ->where('is_admin', false)
            ->withCount([
                'messages',
                'signatures',
                'suggestions',
                'suggestions as accepted_suggestions_count' => fn (Builder $query) => $query
                    ->where('status', SuggestionStatus::Accepted),
            ])
            ->addSelect([
                'debates_touched_count' => Message::query()
                    ->selectRaw('count(distinct debate_id)')
                    ->whereColumn('user_id', 'users.id'),
            ])
            ->get()
            ->map(fn (User $user) => $this->leaderPayload($user))
            ->filter(fn (array $row) => $row['score'] > 0)
            ->sortByDesc('score')
            ->values();

        return $this->rankedCache;
    }

    /**
     * @param  Builder<*>  $query
     * @return array<string, int>
     */
    private function countByDepartment(Builder $query, string $column): array
    {
        return $query
            ->selectRaw($column.' as department, count(*) as total')
            ->groupBy($column)
            ->pluck('total', 'department')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function leaderPayload(User $user): array
    {
        $messages = (int) $user->messages_count;
        $signatures = (int) $user->signatures_count;
        $suggestions = (int) ($user->suggestions_count ?? 0);
        $accepted = (int) ($user->accepted_suggestions_count ?? 0);
        $touched = (int) ($user->debates_touched_count ?? 0);

        return [
            'id' => $user->id,
            'nick' => $user->nick,
            'name' => $user->name,
            'department' => $user->department,
            'messages_count' => $messages,
            'signatures_count' => $signatures,
            'suggestions_count' => $suggestions,
            'accepted_suggestions_count' => $accepted,
            'debates_touched' => $touched,
            'score' => ($messages * 2) + ($touched * 4) + ($signatures * 3) + ($suggestions * 5) + ($accepted * 8),
            'side' => $this->sideForNick((string) $user->nick),
            'sample' => false,
        ];
    }

    /**
     * @return list<array{key: string, label: string, percent: int, people: int, sample: bool}>
     */
    private function politicalSpectrum(): array
    {
        $weights = ['izquierda' => 34, 'centro' => 29, 'derecha' => 37];
        $people = ['izquierda' => 0, 'centro' => 0, 'derecha' => 0];

        foreach ($this->possibleLeaders() as $leader) {
            $side = $leader['side'];
            if (! isset($weights[$side])) {
                continue;
            }
            $people[$side]++;
            $weights[$side] += (int) $leader['score'];
        }

        $total = max(array_sum($weights), 1);
        $izquierda = (int) round(($weights['izquierda'] / $total) * 100);
        $centro = (int) round(($weights['centro'] / $total) * 100);
        $derecha = max(0, 100 - $izquierda - $centro);

        return [
            $this->spectrumRow('izquierda', 'Izquierda', $izquierda, $people['izquierda']),
            $this->spectrumRow('centro', 'Centro', $centro, $people['centro']),
            $this->spectrumRow('derecha', 'Derecha', $derecha, $people['derecha']),
        ];
    }

    /**
     * @return array{key: string, label: string, percent: int, people: int, sample: bool}
     */
    private function spectrumRow(string $key, string $label, int $percent, int $people): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'percent' => $percent,
            'people' => $people,
            'sample' => true,
        ];
    }

    /**
     * @return list<array{key: string, label: string, left: string, right: string, left_percent: int, right_percent: int, sample: bool}>
     */
    private function politicalCleavages(): array
    {
        return [
            [
                'key' => 'recambio',
                'label' => 'Recambio generacional',
                'left' => 'Recambio',
                'right' => 'Continuidad',
                'left_percent' => 61,
                'right_percent' => 39,
                'sample' => true,
            ],
            [
                'key' => 'estado',
                'label' => 'Rol del Estado',
                'left' => 'Más Estado',
                'right' => 'Más mercado',
                'left_percent' => 46,
                'right_percent' => 54,
                'sample' => true,
            ],
            [
                'key' => 'sindical',
                'label' => 'Representación',
                'left' => 'Base sindical',
                'right' => 'Ciudadanía abierta',
                'left_percent' => 42,
                'right_percent' => 58,
                'sample' => true,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sampleLeaders(): array
    {
        return [
            $this->sampleLeader('sindical_lpz', 'Sindical LPZ', 'La Paz', 21, 8, 2, 1, 6, 'izquierda'),
            $this->sampleLeader('joven_scr', 'Joven SCR', 'Chuquisaca', 18, 6, 3, 1, 5, 'izquierda'),
            $this->sampleLeader('docente_oruro', 'Docente Oruro', 'Oruro', 14, 5, 1, 0, 4, 'centro'),
            $this->sampleLeader('crucena_prod', 'Cruceña Prod', 'Santa Cruz', 16, 9, 2, 1, 5, 'derecha'),
            $this->sampleLeader('agro_beni', 'Agro Beni', 'Beni', 12, 7, 1, 0, 3, 'derecha'),
            $this->sampleLeader('uni_cbba', 'Uni Cbba', 'Cochabamba', 11, 4, 2, 0, 4, 'centro'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleLeader(
        string $nick,
        string $name,
        string $department,
        int $messages,
        int $signatures,
        int $suggestions,
        int $accepted,
        int $touched,
        string $side,
    ): array {
        return [
            'id' => null,
            'nick' => $nick,
            'name' => $name,
            'department' => $department,
            'messages_count' => $messages,
            'signatures_count' => $signatures,
            'suggestions_count' => $suggestions,
            'accepted_suggestions_count' => $accepted,
            'debates_touched' => $touched,
            'score' => ($messages * 2) + ($touched * 4) + ($signatures * 3) + ($suggestions * 5) + ($accepted * 8),
            'side' => $side,
            'sample' => true,
        ];
    }

    private function sideForNick(string $nick): string
    {
        $map = [
            'sindical_lpz' => 'izquierda',
            'madre_elalto' => 'izquierda',
            'joven_scr' => 'izquierda',
            'yungas_verde' => 'izquierda',
            'mineria_potosi' => 'izquierda',
            'docente_oruro' => 'centro',
            'uni_cbba' => 'centro',
            'redaccion' => 'centro',
            'paceno_sur' => 'centro',
            'vecino_lpz' => 'centro',
            'crucena_prod' => 'derecha',
            'chofer_scz' => 'derecha',
            'agro_beni' => 'derecha',
            'comerciante_tz' => 'derecha',
        ];

        if (isset($map[$nick])) {
            return $map[$nick];
        }

        $sides = ['izquierda', 'centro', 'derecha'];

        return $sides[abs(crc32($nick)) % 3];
    }

    private function statsFrom(): Carbon
    {
        return Carbon::parse((string) config('admin.stats_from', '2026-08-01'))->startOfMonth();
    }

    private function monthLabel(CarbonInterface $date): string
    {
        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        return $months[(int) $date->month].' '.$date->year;
    }
}
