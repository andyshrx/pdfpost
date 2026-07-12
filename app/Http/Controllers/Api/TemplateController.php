<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Models\Template;
use App\Rendering\LiquidRenderer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TemplateResource::collection(
            Template::with('currentVersion')->latest()->get()
        );
    }

    public function store(StoreTemplateRequest $request, LiquidRenderer $liquid): TemplateResource
    {
        $liquid->validate($request->validated('liquid_source'));

        $template = Template::create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug') ?? Str::slug($request->validated('name')),
            'description' => $request->validated('description'),
        ]);

        $template->publishNewVersion(
            $request->validated('liquid_source'),
            $request->validated('sample_data'),
        );

        return new TemplateResource($template->load('currentVersion'));
    }

    public function show(Template $template): TemplateResource
    {
        return new TemplateResource($template->load('currentVersion'));
    }

    public function update(UpdateTemplateRequest $request, Template $template, LiquidRenderer $liquid): TemplateResource
    {
        $template->update($request->safe()->only(['name', 'description']));

        if ($request->has('liquid_source')) {
            $liquid->validate($request->validated('liquid_source'));

            $template->publishNewVersion(
                $request->validated('liquid_source'),
                $request->validated('sample_data') ?? $template->currentVersion?->sample_data,
            );
        }

        return new TemplateResource($template->fresh()->load('currentVersion'));
    }

    public function destroy(Template $template): Response
    {
        $template->delete();

        return response()->noContent();
    }
}
