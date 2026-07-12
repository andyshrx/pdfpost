<?php

use App\Models\Template;
use App\Rendering\LiquidRenderer;
use Database\Seeders\TemplateSeeder;

it('seeds a gallery of valid templates', function () {
    $this->seed(TemplateSeeder::class);

    expect(Template::count())->toBe(5);

    Template::with('currentVersion')->get()->each(function (Template $template) {
        expect($template->currentVersion)->not->toBeNull();

        // every seeded template must compile and render with its own sample data
        $html = app(LiquidRenderer::class)->render(
            $template->currentVersion->liquid_source,
            $template->currentVersion->sample_data ?? [],
        );

        expect(strlen($html))->toBeGreaterThan(100);
    });
});

it('is safe to run twice', function () {
    $this->seed(TemplateSeeder::class);

    $template = Template::whereSlug('invoice')->first();
    $template->publishNewVersion('<p>customized</p>');

    $this->seed(TemplateSeeder::class);

    expect(Template::count())->toBe(5)
        ->and($template->fresh()->currentVersion->liquid_source)->toBe('<p>customized</p>');
});
