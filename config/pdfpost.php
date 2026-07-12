<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Render Engine
    |--------------------------------------------------------------------------
    |
    | PDFPost delegates HTML to PDF conversion to a Gotenberg instance.
    | Point this at wherever Gotenberg is running. The timeout is the
    | maximum number of seconds to wait for a single render.
    |
    */

    'gotenberg_url' => env('PDFPOST_GOTENBERG_URL', 'http://localhost:3000'),

    'render_timeout' => (int) env('PDFPOST_RENDER_TIMEOUT', 30),

    'connect_timeout' => (int) env('PDFPOST_CONNECT_TIMEOUT', 2),

    /*
    |--------------------------------------------------------------------------
    | Artifacts
    |--------------------------------------------------------------------------
    |
    | Async renders store their output on this filesystem disk. Use s3 (or
    | any configured disk) in production if you want durable storage.
    |
    */

    'artifact_disk' => env('PDFPOST_ARTIFACT_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Webhook payloads are signed with HMAC-SHA256 in the X-PDFPost-Signature
    | header. Set an explicit secret to share with receivers, otherwise a
    | secret derived from APP_KEY is used.
    |
    */

    'webhook_secret' => env('PDFPOST_WEBHOOK_SECRET'),

];
