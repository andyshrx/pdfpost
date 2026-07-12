<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenderRequest;
use App\Models\Template;
use App\Rendering\LiquidRenderer;
use App\Rendering\RenderEngine;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class RenderController extends Controller
{
    public function __invoke(RenderRequest $request, RenderEngine $engine, LiquidRenderer $liquid): Response
    {
        $html = $request->validated('html') ?? $this->renderTemplate(
            $liquid,
            $request->validated('template'),
            $request->validated('data') ?? [],
        );

        $format = $request->validated('format') ?? 'pdf';

        $bytes = $engine->render($html, array_merge(
            $request->validated('options') ?? [],
            ['format' => $format],
        ));

        [$contentType, $extension] = $format === 'png'
            ? ['image/png', 'png']
            : ['application/pdf', 'pdf'];

        return response($bytes, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="render.'.$extension.'"',
        ]);
    }

    protected function renderTemplate(LiquidRenderer $liquid, string $slug, array $data): string
    {
        $version = Template::whereSlug($slug)->first()?->currentVersion;

        if ($version === null) {
            throw ValidationException::withMessages([
                'template' => 'This template has no published version.',
            ]);
        }

        return $liquid->render($version->liquid_source, $data);
    }
}
