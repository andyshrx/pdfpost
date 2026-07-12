<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Render;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RenderArtifactController extends Controller
{
    public function __invoke(Render $render): StreamedResponse
    {
        abort_unless($render->status === 'succeeded' && $render->artifact_path, 404);

        return Storage::disk($render->artifact_disk)->response(
            $render->artifact_path,
            'render-'.$render->uuid.'.'.$render->fileExtension(),
        );
    }
}
