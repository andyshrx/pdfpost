<?php

use App\Jobs\ProcessRender;
use App\Models\Render;
use App\Models\User;
use App\Notifications\RenderFailuresDetected;
use App\Rendering\RenderException;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->operator = User::factory()->create();
});

function failRender(): Render
{
    $render = Render::factory()->create();
    (new ProcessRender($render))->failed(new RenderException('engine down'));

    return $render;
}

it('alerts the operator after three consecutive failures', function () {
    failRender();
    failRender();

    Notification::assertNothingSent();

    failRender();

    Notification::assertSentTo($this->operator, RenderFailuresDetected::class);
});

it('does not alert when a success breaks the streak', function () {
    failRender();
    failRender();
    Render::factory()->succeeded()->create(['completed_at' => now()]);
    failRender();

    Notification::assertNothingSent();
});

it('alerts at most once per hour', function () {
    failRender();
    failRender();
    failRender();
    failRender();
    failRender();

    Notification::assertSentToTimes($this->operator, RenderFailuresDetected::class, 1);
});
