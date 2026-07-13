<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-8 p-2">
        <div class="space-y-1">
            <flux:heading size="xl">Welcome to PDFPost</flux:heading>
            <flux:subheading>Design a template, then POST JSON to the API and get a PDF back.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('templates.create') }}" wire:navigate
               class="flex flex-col gap-2 rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600">
                <flux:icon.plus class="size-6 text-zinc-400" />
                <div class="space-y-1">
                    <flux:heading size="lg">Create a template</flux:heading>
                    <flux:subheading>Write Liquid, preview it live, save a version.</flux:subheading>
                </div>
            </a>

            <a href="{{ route('templates.index') }}" wire:navigate
               class="flex flex-col gap-2 rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600">
                <flux:icon.document-text class="size-6 text-zinc-400" />
                <div class="space-y-1">
                    <flux:heading size="lg">Your templates</flux:heading>
                    <flux:subheading>Browse, edit and version everything you've built.</flux:subheading>
                </div>
            </a>
        </div>

        <div class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <div class="space-y-1">
                <flux:heading size="lg">Call the API</flux:heading>
                <flux:subheading>
                    Mint a token with
                    <button type="button"
                        x-data="{ copied: false }"
                        @click="pdfpostCopy('php artisan pdfpost:token my-app'); copied = true; setTimeout(() => copied = false, 1500)"
                        class="inline-flex items-center gap-1 rounded bg-zinc-100 px-1.5 py-0.5 align-middle font-mono text-xs text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                        :title="copied ? 'Copied!' : 'Click to copy'">
                        php artisan pdfpost:token
                        <flux:icon.clipboard x-show="! copied" class="size-3 text-zinc-400" />
                        <flux:icon.check x-show="copied" x-cloak class="size-3 text-green-500" />
                    </button>,
                    then render a template with it. Drop it in where the request says
                    <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">$TOKEN</code>.
                </flux:subheading>
            </div>

            @if ($templates->isEmpty())
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Create a template, or run <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">php artisan db:seed</code>
                    for the sample gallery, to get a ready-to-run request here.
                </p>
            @else
                <div x-data="apiSnippet({ renderUrl: '{{ url('/api/v1/render') }}', templates: @js($templates) })" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Template</span>
                        <select x-model="selected"
                            class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                            <template x-for="template in templates" :key="template.slug">
                                <option :value="template.slug" x-text="template.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="relative">
                        <button type="button" @click="copy()"
                            class="absolute right-2 top-2 rounded-md border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-xs font-medium text-zinc-200 transition hover:bg-zinc-700">
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                        <pre class="overflow-x-auto rounded-lg bg-zinc-900 p-4 pr-16 text-xs leading-relaxed text-zinc-100"><code x-text="curl"></code></pre>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-auto pt-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
            Made with <span class="text-red-400">&hearts;</span> from Sydney, Australia
            &middot;
            <a href="https://github.com/andyshrx" target="_blank"
               class="hover:text-zinc-600 hover:underline dark:hover:text-zinc-300">github:andyshrx</a>
            &middot;
            <a href="https://buymeacoffee.com/andyshrx" target="_blank"
               class="hover:text-zinc-600 hover:underline dark:hover:text-zinc-300">Buy me a coffee</a>
        </div>
    </div>

    <script>
        // clipboard api needs a secure context, fall back for plain http
        function pdfpostCopy(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            } else {
                const area = document.createElement('textarea');
                area.value = text;
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                area.remove();
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('apiSnippet', (config) => ({
                renderUrl: config.renderUrl,
                templates: config.templates,
                selected: config.templates.length ? config.templates[0].slug : '',
                copied: false,
                get current() {
                    return this.templates.find((template) => template.slug === this.selected);
                },
                get curl() {
                    const body = JSON.stringify({
                        template: this.selected,
                        data: this.current ? this.current.data : {},
                    });

                    return `curl -X POST ${this.renderUrl} \\\n`
                        + `  -H "Authorization: Bearer $TOKEN" \\\n`
                        + `  -H 'Content-Type: application/json' \\\n`
                        + `  -d '${body}' -o output.pdf`;
                },
                copy() {
                    pdfpostCopy(this.curl);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 1500);
                },
            }));
        });
    </script>
</x-layouts.app>
