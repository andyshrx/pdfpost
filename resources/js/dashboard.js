// clipboard api needs a secure context, fall back for plain http
window.pdfpostCopy = function (text) {
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
};

// Registered here instead of an inline script on the dashboard because the
// page is usually reached through wire:navigate, and alpine:init only fires
// on a full page load. Inline registration left the template picker empty.
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
