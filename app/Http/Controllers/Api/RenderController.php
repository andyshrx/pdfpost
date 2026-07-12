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

        $pdf = $engine->render($html, $request->validated('options') ?? []);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="render.pdf"',
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
