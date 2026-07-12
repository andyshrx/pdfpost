# PDFPost

**POST JSON, get a pixel-perfect PDF back.** Self-hosted, MIT, no per-document pricing.

Design a template once in [Liquid](https://shopify.github.io/liquid/), then render
documents from any app with a single authenticated API call. Rendering is done by
[Gotenberg](https://gotenberg.dev) (headless Chromium).

## Usage

Mint an API token, then talk to the API:

```bash
php artisan pdfpost:token my-app
```

Create a template (templates are versioned, every update keeps history):

```bash
curl -X POST http://localhost:8000/api/v1/templates \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"name":"Invoice","liquid_source":"<h1>Invoice for {{ customer }}</h1><p>Total: {{ total }}</p>"}'
```

Render it with your data:

```bash
curl -X POST http://localhost:8000/api/v1/render \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"template":"invoice","data":{"customer":"Acme Co","total":"$462.00"}}' -o invoice.pdf
```

One-off inline HTML works too:

```bash
curl -X POST http://localhost:8000/api/v1/render \
  -H "Authorization: Bearer $TOKEN" \
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

Async renders + webhooks, an og-image endpoint, a template editor UI, and one-command
docker compose are on the way.
