<?php

use App\Models\Template;
use App\Models\User;
use App\Rendering\RenderEngine;
use App\Rendering\RenderException;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('mounts an existing template into the editor', function () {
    $template = Template::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
    $template->publishNewVersion('<p>{{ total }}</p>', ['total' => '9.99']);

    Volt::test('templates.editor', ['template' => $template])
        ->assertSet('name', 'Invoice')
        ->assertSet('liquidSource', '<p>{{ total }}</p>')
        ->assertSee('v1');
});

it('renders the preview with sample data merged', function () {
    Volt::test('templates.editor')
        ->set('liquidSource', '<h1>Hello {{ name }}</h1>')
        ->set('sampleJson', '{"name": "Andy"}')
        ->assertSet('previewHtml', '<h1>Hello Andy</h1>');
});

it('keeps the last good preview when the liquid breaks', function () {
    $component = Volt::test('templates.editor')
        ->set('liquidSource', '<p>fine</p>')
        ->set('sampleJson', '')
        ->assertSet('previewHtml', '<p>fine</p>');

    $component->set('liquidSource', '{% for x in %}')
        ->assertSet('previewHtml', '<p>fine</p>');

    expect($component->get('syntaxError'))->not->toBeNull();
});

it('flags invalid sample json', function () {
    $component = Volt::test('templates.editor')->set('sampleJson', '{nope');

    expect($component->get('jsonError'))->toBe('Sample data is not valid JSON.');
});

it('creates a template on first save and redirects to the edit page', function () {
    Volt::test('templates.editor')
        ->set('name', 'Fresh Invoice')
        ->set('liquidSource', '<p>{{ total }}</p>')
        ->set('sampleJson', '{"total": "1.00"}')
        ->call('save')
        ->assertRedirect(route('templates.edit', 'fresh-invoice'));

    $template = Template::whereSlug('fresh-invoice')->first();

    expect($template->currentVersion->version)->toBe(1)
        ->and($template->currentVersion->sample_data)->toBe(['total' => '1.00']);
});

it('publishes a new version only when the source changed', function () {
    $template = Template::factory()->create(['name' => 'Invoice']);
    $template->publishNewVersion('<p>v1</p>');

    $component = Volt::test('templates.editor', ['template' => $template->fresh()]);

    $component->call('save');
    expect($template->versions()->count())->toBe(1);

    $component->set('liquidSource', '<p>v2</p>')->call('save');
    expect($template->versions()->count())->toBe(2);
});

it('refuses to save broken liquid', function () {
    Volt::test('templates.editor')
        ->set('name', 'Broken')
        ->set('liquidSource', '{% for x in %}')
        ->call('save');

    expect(Template::count())->toBe(0);
});

it('downloads a pdf of the current editor content', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->andReturn('%PDF-editor');

    Volt::test('templates.editor')
        ->set('name', 'Invoice')
        ->set('liquidSource', '<p>x</p>')
        ->set('sampleJson', '')
        ->call('downloadPdf')
        ->assertFileDownloaded('invoice.pdf');
});

it('shows a friendly error when the engine is down instead of crashing', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->andThrow(new RenderException('down'));

    Volt::test('templates.editor')
        ->set('liquidSource', '<p>x</p>')
        ->set('sampleJson', '')
        ->call('downloadPdf')
        ->assertHasErrors('pdf');
});
