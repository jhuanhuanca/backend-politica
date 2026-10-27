<?php

namespace App\Models;

use App\Enums\AdSlot;
use Database\Factories\AdFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    /** @use HasFactory<AdFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slot',
        'title',
        'image_path',
        'link_url',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => AdSlot::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Ad>  $query
     * @return Builder<Ad>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
