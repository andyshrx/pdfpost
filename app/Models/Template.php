<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TemplateVersion::class)->orderByDesc('version');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(TemplateVersion::class, 'current_version_id');
    }

    /**
     * Versions are append only. Publishing stores a new version and points
     * the template at it, older versions stay untouched for history.
     */
    public function publishNewVersion(string $liquidSource, ?array $sampleData = null): TemplateVersion
    {
        $version = $this->versions()->create([
            'version' => ((int) $this->versions()->max('version')) + 1,
            'liquid_source' => $liquidSource,
            'sample_data' => $sampleData,
        ]);

        $this->forceFill(['current_version_id' => $version->id])->save();

        return $version;
    }
}
