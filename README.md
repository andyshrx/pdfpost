# PDFPost

**POST JSON, get a pixel-perfect PDF back.** Self-hosted, MIT, no per-document pricing.

> Early days. The walking skeleton works: `POST /api/v1/render` with inline HTML
> returns a PDF, rendered by [Gotenberg](https://gotenberg.dev).

```bash
curl -X POST http://localhost:8000/api/v1/render \
  -H 'Content-Type: application/json' \
  -d '{"html":"<h1>Invoice #001</h1>"}' -o invoice.pdf
```

## Running it locally

```bash
docker run --rm -d -p 127.0.0.1:3000:3000 gotenberg/gotenberg:8
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

Templates, API keys, async renders + webhooks, and an og-image endpoint are on the way.
