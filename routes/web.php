<?php

use App\Models\Template;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('dashboard', function () {
    $templates = Template::with('currentVersion')->latest()->get()->map(fn ($template) => [
        'slug' => $template->slug,
        'name' => $template->name,
        'data' => $template->currentVersion?->sample_data ?: (object) [],
    ]);

    return view('dashboard', compact('templates'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Volt::route('templates', 'templates.index')->name('templates.index');
    Volt::route('templates/create', 'templates.editor')->name('templates.create');
    Volt::route('templates/{template}/edit', 'templates.editor')->name('templates.edit');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
