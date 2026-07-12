<?php

namespace App\Rendering;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GotenbergEngine implements RenderEngine
{
    /**
     * Page dimensions in inches, keyed by paper size.
     */
    private const PAPER_SIZES = [
        'a4' => ['paperWidth' => '8.27', 'paperHeight' => '11.7'],
        'letter' => ['paperWidth' => '8.5', 'paperHeight' => '11'],
    ];

    public function render(string $html, array $options = []): string
    {
        $size = $options['paper_size'] ?? 'a4';

        if (! isset(self::PAPER_SIZES[$size])) {
            throw new InvalidArgumentException("Unsupported paper size [{$size}].");
        }

        try {
            $response = Http::baseUrl(config('pdfpost.gotenberg_url'))
                ->timeout(config('pdfpost.render_timeout'))
                ->connectTimeout(config('pdfpost.connect_timeout'))
                ->attach('files', $html, 'index.html')
                ->post('/forms/chromium/convert/html', self::PAPER_SIZES[$size]);
        } catch (ConnectionException $e) {
            throw new RenderException('Could not reach the render engine: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RenderException(
                'Render engine returned HTTP '.$response->status().': '.Str::limit($response->body(), 500)
            );
        }

        return $response->body();
    }
}
