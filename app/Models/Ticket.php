<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'batch',
        'title',
        'description',
        'quantity',
        'status',
        'customer_limit',
        'gender',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
