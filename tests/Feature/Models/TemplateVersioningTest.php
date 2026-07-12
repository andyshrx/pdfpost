<?php

use App\Models\Template;
use App\Models\TemplateVersion;

it('publishes a first version and points the template at it', function () {
    $template = Template::factory()->create();

    $version = $template->publishNewVersion('<h1>{{ title }}</h1>', ['title' => 'Hello']);

    expect($version->version)->toBe(1)
        ->and($template->fresh()->current_version_id)->toBe($version->id)
        ->and($version->sample_data)->toBe(['title' => 'Hello']);
});

it('keeps old versions when publishing a new one', function () {
    $template = Template::factory()->create();

    $first = $template->publishNewVersion('v1');
    $second = $template->publishNewVersion('v2');

    expect($second->version)->toBe(2)
        ->and($template->fresh()->current_version_id)->toBe($second->id)
        ->and($template->versions()->count())->toBe(2)
        ->and($first->fresh()->liquid_source)->toBe('v1');
});

it('deletes versions with the template', function () {
    $template = Template::factory()->create();
    $template->publishNewVersion('v1');

    $template->delete();

    expect(TemplateVersion::count())->toBe(0);
});

it('resolves templates by slug in routes', function () {
    expect((new Template)->getRouteKeyName())->toBe('slug');
});
