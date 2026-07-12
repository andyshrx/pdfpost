<?php

use App\Models\Template;

it('creates a template with its first version', function () {
    $response = $this->postJson('/api/v1/templates', [
        'name' => 'Monthly Invoice',
        'liquid_source' => '<h1>Invoice for {{ customer }}</h1>',
        'sample_data' => ['customer' => 'Acme Co'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'monthly-invoice')
        ->assertJsonPath('data.version', 1);

    expect(Template::whereSlug('monthly-invoice')->first()->currentVersion->liquid_source)
        ->toBe('<h1>Invoice for {{ customer }}</h1>');
});

it('rejects templates with broken liquid', function () {
    $this->postJson('/api/v1/templates', [
        'name' => 'Broken',
        'liquid_source' => '{% for x in %}',
    ])->assertUnprocessable();

    expect(Template::count())->toBe(0);
});

it('rejects a duplicate slug', function () {
    Template::factory()->create(['slug' => 'invoice']);

    $this->postJson('/api/v1/templates', [
        'name' => 'Another',
        'slug' => 'invoice',
        'liquid_source' => '<p>x</p>',
    ])->assertUnprocessable()->assertJsonValidationErrors(['slug']);
});

it('lists templates with their current version number', function () {
    Template::factory()->create()->publishNewVersion('<p>one</p>');

    $this->getJson('/api/v1/templates')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.version', 1);
});

it('shows a template by slug', function () {
    $template = Template::factory()->create(['slug' => 'receipt']);
    $template->publishNewVersion('<p>{{ total }}</p>', ['total' => '9.99']);

    $this->getJson('/api/v1/templates/receipt')
        ->assertOk()
        ->assertJsonPath('data.liquid_source', '<p>{{ total }}</p>')
        ->assertJsonPath('data.sample_data.total', '9.99');
});

it('publishes a new version when the source changes', function () {
    $template = Template::factory()->create(['slug' => 'invoice']);
    $template->publishNewVersion('v1');

    $this->putJson('/api/v1/templates/invoice', ['liquid_source' => 'v2'])
        ->assertOk()
        ->assertJsonPath('data.version', 2);

    expect($template->versions()->count())->toBe(2);
});

it('does not publish a new version for a metadata only update', function () {
    $template = Template::factory()->create(['slug' => 'invoice']);
    $template->publishNewVersion('v1');

    $this->putJson('/api/v1/templates/invoice', ['name' => 'Renamed'])
        ->assertOk()
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('data.name', 'Renamed');

    expect($template->versions()->count())->toBe(1);
});

it('deletes a template', function () {
    $template = Template::factory()->create(['slug' => 'gone']);
    $template->publishNewVersion('v1');

    $this->deleteJson('/api/v1/templates/gone')->assertNoContent();

    expect(Template::count())->toBe(0);
});

it('404s for an unknown slug', function () {
    $this->getJson('/api/v1/templates/nope')->assertNotFound();
});
