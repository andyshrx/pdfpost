<?php

use App\Models\Template;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('redirects guests to login', function () {
    auth()->logout();

    $this->get('/templates')->assertRedirect('/login');
});

it('lists templates', function () {
    $template = Template::factory()->create(['name' => 'Monthly Invoice']);
    $template->publishNewVersion('<p>x</p>');

    $this->get('/templates')
        ->assertOk()
        ->assertSee('Monthly Invoice')
        ->assertSee('v1');
});

it('shows an empty state', function () {
    $this->get('/templates')->assertSee('No templates yet');
});

it('deletes a template', function () {
    $template = Template::factory()->create();

    Volt::test('templates.index')
        ->call('delete', $template->slug)
        ->assertHasNoErrors();

    expect(Template::count())->toBe(0);
});
