<?php

use App\Models\Template;
use Livewire\Volt\Component;

new class extends Component {
    public function delete(string $slug): void
    {
        Template::whereSlug($slug)->firstOrFail()->delete();
    }

    public function with(): array
    {
        return [
            'templates' => Template::with('currentVersion')->latest()->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Templates</flux:heading>
            <flux:subheading>Design once, render from the API with your data.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('templates.create')" wire:navigate>
            New template
        </flux:button>
    </div>

    @if ($templates->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <flux:icon.document-text class="size-10 text-zinc-400" />
            <flux:heading size="lg">No templates yet</flux:heading>
            <flux:subheading>Create your first template to start rendering PDFs.</flux:subheading>
            <flux:button variant="primary" :href="route('templates.create')" wire:navigate>Create a template</flux:button>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Version</th>
                        <th class="px-4 py-3 font-medium">Updated</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700" wire:key="{{ $template->slug }}">
                            <td class="px-4 py-3 font-medium">{{ $template->name }}</td>
                            <td class="px-4 py-3"><code class="text-xs">{{ $template->slug }}</code></td>
                            <td class="px-4 py-3">v{{ $template->currentVersion?->version ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $template->updated_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" :href="route('templates.edit', $template)" wire:navigate>Edit</flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete('{{ $template->slug }}')"
                                    wire:confirm="Delete {{ $template->name }}? Renders that reference it will stop working."
                                >Delete</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
