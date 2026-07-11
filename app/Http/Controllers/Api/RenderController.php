<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenderRequest;
use App\Rendering\RenderEngine;
use App\Rendering\RenderException;
use Symfony\Component\HttpFoundation\Response;

class RenderController extends Controller
{
    public function __invoke(RenderRequest $request, RenderEngine $engine): Response
    {
        try {
            $pdf = $engine->render($request->input('html'), $request->input('options', []));
        } catch (RenderException $e) {
            return response()->json(['message' => 'Render failed: '.$e->getMessage()], 503);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="render.pdf"',
        ]);
    }
}
