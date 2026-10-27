<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nick',
        'department',
        'email',
        'email_hash',
        'password',
        'is_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (! $user->is_admin) {
                return;
            }

            $exists = static::query()
                ->where('is_admin', true)
                ->when($user->exists, fn ($query) => $query->whereKeyNot($user->id))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'is_admin' => ['Solo puede existir un administrador.'],
                ]);
            }
        });
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return array{id: int, nick: string, email: string, department: string|null, email_verified_at: mixed, is_admin: bool}
     */
    public function toAuthArray(): array
    {
        return [
            'id' => $this->id,
            'nick' => $this->nick,
            'email' => $this->email,
            'department' => $this->department,
            'email_verified_at' => $this->email_verified_at,
            'is_admin' => $this->isAdmin(),
        ];
    }

    public function debates(): HasMany
    {
        return $this->hasMany(Debate::class, 'author_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function voteTopics(): HasMany
    {
        return $this->hasMany(VoteTopic::class, 'author_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }
}
