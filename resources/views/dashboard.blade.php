<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">Welcome to PDFPost</flux:heading>
            <flux:subheading>Design a template, then POST JSON to the API and get a PDF back.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('templates.create') }}" wire:navigate
               class="rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600">
                <flux:icon.plus class="mb-3 size-6 text-zinc-400" />
                <flux:heading size="lg">Create a template</flux:heading>
                <flux:subheading>Write Liquid, preview it live, save a version.</flux:subheading>
            </a>

            <a href="{{ route('templates.index') }}" wire:navigate
               class="rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600">
                <flux:icon.document-text class="mb-3 size-6 text-zinc-400" />
                <flux:heading size="lg">Your templates</flux:heading>
                <flux:subheading>Browse, edit and version everything you've built.</flux:subheading>
            </a>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Call the API</flux:heading>
            <flux:subheading class="mb-3">Mint a token, then render from anywhere.</flux:subheading>
            <pre class="overflow-x-auto rounded-lg bg-zinc-900 p-4 text-xs text-zinc-100"><code>php artisan pdfpost:token my-app

curl -X POST {{ url('/api/v1/render') }} \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"template":"invoice","data":{"customer":"Acme Co"}}' -o invoice.pdf</code></pre>
        </div>
    </div>
</x-layouts.app>
