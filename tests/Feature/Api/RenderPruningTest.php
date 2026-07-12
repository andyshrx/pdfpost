<?php

use App\Models\Render;
use Illuminate\Support\Facades\Storage;

it('prunes old renders and their artifacts', function () {
    Storage::fake('local');
    Storage::disk('local')->put('renders/old.pdf', 'old');
    Storage::disk('local')->put('renders/fresh.pdf', 'fresh');

    $old = Render::factory()->succeeded()->create([
        'artifact_path' => 'renders/old.pdf',
        'completed_at' => now()->subDays(10),
    ]);

    $fresh = Render::factory()->succeeded()->create([
        'artifact_path' => 'renders/fresh.pdf',
        'completed_at' => now()->subDay(),
    ]);

    $this->artisan('model:prune', ['--model' => [Render::class]])->assertSuccessful();

    expect(Render::count())->toBe(1)
        ->and(Render::first()->id)->toBe($fresh->id);

    Storage::disk('local')->assertMissing('renders/old.pdf');
    Storage::disk('local')->assertExists('renders/fresh.pdf');
});

it('never prunes renders that are still queued or processing', function () {
    Render::factory()->create([
        'status' => 'queued',
        'created_at' => now()->subDays(30),
    ]);

    $this->artisan('model:prune', ['--model' => [Render::class]])->assertSuccessful();

    expect(Render::count())->toBe(1);
});

it('schedules daily pruning', function () {
    $events = collect(app(Illuminate\Console\Scheduling\Schedule::class)->events());

    expect($events->contains(fn ($event) => str_contains($event->command ?? '', 'model:prune')))->toBeTrue();
});
