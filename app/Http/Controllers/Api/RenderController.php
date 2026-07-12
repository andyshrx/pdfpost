<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenderRequest;
use App\Rendering\RenderEngine;
use Illuminate\Http\Response;

class RenderController extends Controller
{
    public function __invoke(RenderRequest $request, RenderEngine $engine): Response
    {
        $pdf = $engine->render($request->validated('html'), $request->validated('options') ?? []);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="render.pdf"',
        ]);
    }
}
