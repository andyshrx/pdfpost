<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public array $abilities = ['render', 'templates'];
    public ?string $plainTextToken = null;

    public function createToken(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['in:render,templates'],
        ]);

        $token = Auth::user()->createToken($validated['name'], array_values($validated['abilities']));

        $this->plainTextToken = $token->plainTextToken;

        $this->reset('name');
    }

    public function revoke(int $id): void
    {
        Auth::user()->tokens()->where('id', $id)->delete();
    }

    public function dismissToken(): void
    {
        $this->plainTextToken = null;
    }

    public function with(): array
    {
        return [
            'tokens' => Auth::user()->tokens()->latest()->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">API tokens</flux:heading>
        <flux:subheading>Tokens let your apps call the render API. Revoke anything you no longer use.</flux:subheading>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <div class="w-full space-y-6 rounded-xl border border-zinc-200 p-6 lg:max-w-lg lg:shrink-0 dark:border-zinc-700">
            <form wire:submit="createToken" class="space-y-6">
                <flux:input
                    wire:model="name"
                    label="{{ __('Token name') }}"
                    type="text"
                    name="name"
                    placeholder="e.g. the app that will use it"
                />

                <flux:checkbox.group wire:model="abilities" label="{{ __('Abilities') }}">
                    <flux:checkbox value="render" label="render" description="Render PDFs and og-images" />
                    <flux:checkbox value="templates" label="templates" description="Create and edit templates" />
                </flux:checkbox.group>

                <flux:button variant="primary" type="submit">{{ __('Create token') }}</flux:button>
            </form>

            @if ($plainTextToken)
                <div class="space-y-2 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-500/40 dark:bg-amber-500/10" x-data="{ copied: false }">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        Save this token now, it won't be shown again.
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="min-w-0 flex-1 overflow-x-auto rounded bg-white px-2 py-1.5 font-mono text-xs dark:bg-zinc-900">{{ $plainTextToken }}</code>
                        <flux:button size="sm" x-on:click="pdfpostCopy({{ Js::from($plainTextToken) }}); copied = true; setTimeout(() => copied = false, 1500)">
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="dismissToken">
                            {{ __('Done') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>

        <div class="w-full space-y-4 lg:flex-1">
            <flux:heading size="lg">{{ __('Your tokens') }}</flux:heading>

            @if ($tokens->isEmpty())
                <flux:subheading>No tokens yet. The install wizard makes one, or use the form.</flux:subheading>
            @else
                <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    @foreach ($tokens as $token)
                        <div class="flex items-center justify-between gap-4 p-4">
                            <div class="min-w-0 space-y-1">
                                <p class="truncate font-medium">{{ $token->name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ implode(', ', $token->abilities) }}
                                    &middot; created {{ $token->created_at->diffForHumans() }}
                                    &middot; {{ $token->last_used_at ? 'last used '.$token->last_used_at->diffForHumans() : 'never used' }}
                                </p>
                            </div>
                            <flux:button
                                size="sm"
                                variant="danger"
                                wire:click="revoke({{ $token->id }})"
                                wire:confirm="Revoke this token? Anything still using it will stop working.">
                                {{ __('Revoke') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
