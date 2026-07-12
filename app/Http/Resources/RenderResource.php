<?php

namespace App\Http\Resources;

use App\Models\Render;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin Render
 */
class RenderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status,
            'format' => $this->format,
            'artifact_url' => $this->when($this->status === 'succeeded', fn () => URL::temporarySignedRoute(
                'api.renders.artifact',
                now()->addMinutes(30),
                ['render' => $this->uuid],
            )),
            'error' => $this->when($this->status === 'failed', $this->error),
            'duration_ms' => $this->duration_ms,
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
        ];
    }
}
