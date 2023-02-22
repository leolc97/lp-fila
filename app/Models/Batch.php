<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'ticket_batch',
        'price',
        'status',
        'batch_type',
        'limit_date',
        'limit_tickets',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
