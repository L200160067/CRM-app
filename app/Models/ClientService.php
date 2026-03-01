<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientService extends Model
{
    /** @use HasFactory<\Database\Factories\ClientServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'domain_name',
        'status',
        'billing_cycle',
        'recurring_price',
        'started_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ServiceStatus::class,
            'billing_cycle' => \App\Enums\ServiceBillingCycle::class,
            'recurring_price' => 'decimal:2',
            'started_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
