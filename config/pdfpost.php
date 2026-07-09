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

];
