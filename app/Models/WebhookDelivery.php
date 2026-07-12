<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = ['attempt', 'response_status', 'response_excerpt', 'delivered_at'];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
        ];
    }

    public function render(): BelongsTo
    {
        return $this->belongsTo(Render::class);
    }
}
