<?php

use App\Models\Template;
use App\Rendering\LiquidRenderer;
use App\Rendering\RenderEngine;
use App\Rendering\RenderException;
use App\Rendering\TemplateSyntaxException;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public ?Template $template = null;

    public string $name = '';

    public string $description = '';

    public string $liquidSource = '';

    public string $sampleJson = '';

    public string $previewHtml = '';

    public ?string $syntaxError = null;

    public ?string $jsonError = null;

    public function mount(?Template $template = null): void
    {
        // livewire hands us an empty model instead of null on the create route
        $this->template = $template?->exists ? $template : null;

        if ($this->template !== null) {
            $this->name = $this->template->name;
            $this->description = $this->template->description ?? '';
            $this->liquidSource = $this->template->currentVersion?->liquid_source ?? '';
            $this->sampleJson = $this->template->currentVersion?->sample_data
                ? json_encode($this->template->currentVersion->sample_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                : '';
        } else {
            $this->liquidSource = "<h1>Invoice for {{ customer }}</h1>\n<p>Total: {{ total }}</p>";
            $this->sampleJson = json_encode(['customer' => 'Acme Co', 'total' => '$99.00'], JSON_PRETTY_PRINT);
        }

        $this->refreshPreview();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['liquidSource', 'sampleJson'])) {
            $this->refreshPreview();
        }
    }

    public function refreshPreview(): void
    {
        $this->syntaxError = null;

        $data = $this->sampleData();

        if ($this->jsonError !== null) {
            return;
        }

        try {
            $this->previewHtml = app(LiquidRenderer::class)->render($this->liquidSource, $data);
        } catch (TemplateSyntaxException $e) {
            // keep the last good preview so the page does not go blank mid keystroke
            $this->syntaxError = $e->getMessage();
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            app(LiquidRenderer::class)->validate($this->liquidSource);
        } catch (TemplateSyntaxException $e) {
            $this->syntaxError = $e->getMessage();

            return;
        }

        $data = $this->sampleData();

        if ($this->jsonError !== null) {
            return;
        }

        if ($this->template === null) {
            $this->template = Template::create([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'description' => $this->description ?: null,
            ]);
            $this->template->publishNewVersion($this->liquidSource, $data ?: null);

            $this->redirectRoute('templates.edit', $this->template, navigate: true);

            return;
        }

        $this->template->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
        ]);

        $current = $this->template->currentVersion;

        if ($current?->liquid_source !== $this->liquidSource || $current?->sample_data !== ($data ?: null)) {
            $this->template->publishNewVersion($this->liquidSource, $data ?: null);
        }

        $this->template->refresh();
        $this->dispatch('template-saved');
    }

    public function downloadPdf(RenderEngine $engine)
    {
        $data = $this->sampleData();

        if ($this->jsonError !== null) {
            return null;
        }

        try {
            $html = app(LiquidRenderer::class)->render($this->liquidSource, $data);
            $pdf = $engine->render($html);
        } catch (TemplateSyntaxException $e) {
            $this->syntaxError = $e->getMessage();

            return null;
        } catch (RenderException) {
            $this->addError('pdf', 'The render engine is unavailable. Is Gotenberg running?');

            return null;
        }

        $filename = Str::slug($this->name ?: 'template').'.pdf';

        return response()->streamDownload(fn () => print $pdf, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function sampleData(): array
    {
        $this->jsonError = null;

        if (trim($this->sampleJson) === '') {
            return [];
        }

        $data = json_decode($this->sampleJson, true);

        if (! is_array($data)) {
            $this->jsonError = 'Sample data is not valid JSON.';

            return [];
        }

        return $data;
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $template ? 'Edit template' : 'New template' }}</flux:heading>
            @if ($template)
                <flux:subheading>
                    <code class="text-xs">{{ $template->slug }}</code>
                    &middot; v{{ $template->currentVersion?->version ?? 0 }}
                </flux:subheading>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <div x-data="{ saved: false }" x-on:template-saved.window="saved = true; setTimeout(() => saved = false, 2000)">
                <flux:badge x-show="saved" x-transition color="green">Saved</flux:badge>
            </div>
            <flux:button wire:click="downloadPdf">Download PDF</flux:button>
            <flux:button variant="primary" wire:click="save">Save</flux:button>
        </div>
    </div>

    @error('pdf')
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ $message }}" />
    @enderror

    <div class="grid flex-1 gap-4 lg:grid-cols-2">
        <div class="flex flex-col gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="name" label="Name" placeholder="Invoice" />
                <flux:input wire:model="description" label="Description" placeholder="Optional" />
            </div>

            <flux:field>
                <flux:label>Liquid template</flux:label>
                <div
                    wire:ignore
                    x-data
                    x-init="window.mountLiquidEditor($el, @js($liquidSource), (doc) => $wire.set('liquidSource', doc))"
                    class="min-h-[320px] overflow-hidden rounded-lg border border-zinc-200 text-sm dark:border-zinc-700 [&_.cm-editor]:min-h-[320px]"
                ></div>
                @if ($syntaxError)
                    <flux:error name="liquidSource">{{ $syntaxError }}</flux:error>
                @endif
            </flux:field>

            <flux:field>
                <flux:label>Sample data (JSON, used for the preview)</flux:label>
                <flux:textarea
                    wire:model.live.debounce.600ms="sampleJson"
                    rows="8"
                    class="font-mono text-sm"
                />
                @if ($jsonError)
                    <flux:error name="sampleJson">{{ $jsonError }}</flux:error>
                @endif
            </flux:field>
        </div>

        <div class="flex flex-col gap-2">
            <flux:label>Preview</flux:label>
            <iframe
                sandbox=""
                srcdoc="{{ $previewHtml }}"
                class="min-h-[480px] flex-1 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700"
                title="Template preview"
            ></iframe>
        </div>
    </div>
</div>
