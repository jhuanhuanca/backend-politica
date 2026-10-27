<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSuggestionRequest;
use App\Http\Resources\Suggestion\SuggestionResource;
use App\Models\Suggestion;
use App\Services\Suggestion\SuggestionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SuggestionController extends Controller
{
    public function __construct(private readonly SuggestionService $suggestions) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = addcslashes($request->string('search')->toString(), '%_\\');
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $suggestions = Suggestion::query()
            ->with('user:id,nick')
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                })
            )
            ->when(
                in_array($status, ['pending', 'accepted', 'rejected'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                in_array($type, ['debate', 'vote'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return SuggestionResource::collection($suggestions);
    }

    public function reject(UpdateSuggestionRequest $request, Suggestion $suggestion): SuggestionResource
    {
        $updated = $this->suggestions->reject(
            $suggestion,
            $request->validated('admin_note') ?? null,
        );

        return new SuggestionResource($updated->loadMissing('user:id,nick'));
    }

    public function publish(Suggestion $suggestion): SuggestionResource
    {
        $updated = $this->suggestions->publish($suggestion);

        return new SuggestionResource($updated);
    }
}
