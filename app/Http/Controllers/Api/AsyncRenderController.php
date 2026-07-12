<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenderRequest;
use App\Http\Resources\RenderResource;
use App\Jobs\ProcessRender;
use App\Models\Render;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AsyncRenderController extends Controller
{
    public function store(RenderRequest $request): JsonResponse
    {
        $versionId = null;

        if ($slug = $request->validated('template')) {
            $version = Template::whereSlug($slug)->first()?->currentVersion;

            if ($version === null) {
                throw ValidationException::withMessages([
                    'template' => 'This template has no published version.',
                ]);
            }

            $versionId = $version->id;
        }

        $render = Render::create([
            'status' => 'queued',
            'template_version_id' => $versionId,
            'format' => $request->validated('format') ?? 'pdf',
            'html' => $request->validated('html'),
            'payload' => $request->validated('data'),
            'options' => $request->validated('options'),
            'webhook_url' => $request->validated('webhook_url'),
        ]);

        ProcessRender::dispatch($render);

        return (new RenderResource($render))->response()->setStatusCode(202);
    }

    public function show(Render $render): RenderResource
    {
        return new RenderResource($render);
    }
}
