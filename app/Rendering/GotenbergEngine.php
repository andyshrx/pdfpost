<?php

namespace App\Rendering;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
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

    /**
     * Default screenshot size, the standard og-image dimensions.
     */
    private const OG_WIDTH = 1200;

    private const OG_HEIGHT = 630;

    public function render(string $html, array $options = []): string
    {
        $format = $options['format'] ?? 'pdf';

        $response = match ($format) {
            'pdf' => $this->convertToPdf($html, $options),
            'png' => $this->screenshot($html, $options),
            default => throw new InvalidArgumentException("Unsupported format [{$format}]."),
        };

        if ($response->failed()) {
            throw new RenderException(
                'Render engine returned HTTP '.$response->status().': '.Str::limit($response->body(), 500)
            );
        }

        return $response->body();
    }

    protected function convertToPdf(string $html, array $options): Response
    {
        $size = $options['paper_size'] ?? 'a4';

        if (! isset(self::PAPER_SIZES[$size])) {
            throw new InvalidArgumentException("Unsupported paper size [{$size}].");
        }

        return $this->send('/forms/chromium/convert/html', $html, self::PAPER_SIZES[$size]);
    }

    protected function screenshot(string $html, array $options): Response
    {
        return $this->send('/forms/chromium/screenshot/html', $html, [
            'format' => 'png',
            'width' => (string) ($options['width'] ?? self::OG_WIDTH),
            'height' => (string) ($options['height'] ?? self::OG_HEIGHT),
            'clip' => 'true',
        ]);
    }

    protected function send(string $route, string $html, array $form): Response
    {
        try {
            return $this->client()
                ->attach('files', $html, 'index.html')
                ->post($route, $form);
        } catch (ConnectionException $e) {
            throw new RenderException('Could not reach the render engine: '.$e->getMessage(), previous: $e);
        }
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl(config('pdfpost.gotenberg_url'))
            ->timeout(config('pdfpost.render_timeout'))
            ->connectTimeout(config('pdfpost.connect_timeout'));
    }
}
