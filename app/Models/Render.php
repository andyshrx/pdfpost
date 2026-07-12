<?php

namespace App\Models;

use Database\Factories\RenderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Render extends Model
{
    /** @use HasFactory<RenderFactory> */
    use HasFactory;

    protected $fillable = [
        'template_version_id', 'format', 'status', 'html', 'payload',
        'options', 'webhook_url',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'options' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Render $render) {
            $render->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(TemplateVersion::class);
    }

    public function webhookDeliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function fileExtension(): string
    {
        return $this->format === 'png' ? 'png' : 'pdf';
    }

    public function markSucceeded(string $disk, string $path, int $durationMs): void
    {
        // forceFill, these are internal state transitions not user input
        $this->forceFill([
            'status' => 'succeeded',
            'artifact_disk' => $disk,
            'artifact_path' => $path,
            'duration_ms' => $durationMs,
            'error' => null,
            'completed_at' => now(),
        ])->save();
    }

    public function markFailed(string $error): void
    {
        $this->forceFill([
            'status' => 'failed',
            'error' => Str::limit($error, 1000),
            'completed_at' => now(),
        ])->save();
    }
}
