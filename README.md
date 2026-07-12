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

### Async renders + webhooks

Queue a render and get called back when it is done (run a worker: `php artisan queue:work`):

```bash
curl -X POST http://localhost:8000/api/v1/renders \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"template":"invoice","data":{"customer":"Acme Co"},"webhook_url":"https://your-app.test/hooks/pdfpost"}'
```

You get a `202` with a render id. Poll `GET /api/v1/renders/{id}`, or wait for the webhook:
the payload carries the status and an expiring signed `artifact_url`, and every request is
signed with HMAC-SHA256 in the `X-PDFPost-Signature` header so receivers can verify it
(secret: `PDFPOST_WEBHOOK_SECRET`, or a key derived from `APP_KEY` by default).

### Social / og-images

Same API, `"format": "png"` renders a 1200x630 screenshot instead of a PDF:

```bash
curl -X POST http://localhost:8000/api/v1/render \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"html":"<h1 style=\"font-size:80px\">My Post Title</h1>","format":"png"}' -o og.png
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

Retention pruning, failure notifications, and one-command docker compose are on the way.
