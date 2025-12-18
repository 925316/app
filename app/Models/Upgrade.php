<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'license_from',
        'license_to',
        'notes',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function licenseFrom(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_from');
    }

    public function licenseTo(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_to');
    }
}