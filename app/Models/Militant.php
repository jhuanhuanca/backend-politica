<?php

namespace App\Models;

use App\Enums\MilitantStatus;
use Database\Factories\MilitantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Militant extends Model
{
    /** @use HasFactory<MilitantFactory> */
    use HasFactory;

    protected $connection = 'militancia';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'ci',
        'email',
        'phone',
        'city',
        'department',
        'birth_year',
        'consent',
        'card_number',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'ci',
        'email',
        'phone',
        'full_name',
        'birth_year',
        'city',
        'department',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'birth_year' => 'integer',
            'status' => MilitantStatus::class,
        ];
    }
}
