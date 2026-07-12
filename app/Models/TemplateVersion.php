<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVersion extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['version', 'liquid_source', 'sample_data'];

    protected function casts(): array
    {
        return [
            'sample_data' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
