<?php

namespace App\Rendering;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

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
        $form = self::PAPER_SIZES[$options['paper_size'] ?? 'a4'];
        $url = rtrim(config('pdfpost.gotenberg_url'), '/').'/forms/chromium/convert/html';

        try {
            $response = Http::timeout(config('pdfpost.render_timeout'))
                ->attach('files', $html, 'index.html')
                ->post($url, $form);
        } catch (ConnectionException $e) {
            throw new RenderException('Could not reach the render engine: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RenderException(
                'Render engine returned HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500)
            );
        }

        return $response->body();
    }
}
