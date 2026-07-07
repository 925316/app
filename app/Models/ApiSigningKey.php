<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiSigningKey extends Model
{
    /** @use HasFactory<\Database\Factories\ApiSigningKeyFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key_id',
        'algorithm',
        'public_key',
        'public_key_fingerprint',
        'private_key_path',
        'is_active',
        'activated_at',
        'rotated_at',
        'retired_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'rotated_at' => 'datetime',
            'retired_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRetired(Builder $query): Builder
    {
        return $query->whereNotNull('retired_at');
    }

    public function scopeOrderedForAdmin(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_active')
            ->orderByDesc('activated_at')
            ->orderByDesc('created_at');
    }
}
