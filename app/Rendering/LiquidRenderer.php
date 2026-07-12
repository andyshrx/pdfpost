<?php

namespace App\Rendering;

use Keepsuit\Liquid\Environment;
use Keepsuit\Liquid\Exceptions\LiquidException;
use Keepsuit\Liquid\Extensions\StandardExtension;

class LiquidRenderer
{
    protected Environment $environment;

    public function __construct()
    {
        $this->environment = new Environment(extensions: [new StandardExtension]);
    }

    /**
     * Merge data into a Liquid template and return the resulting HTML.
     *
     * @throws TemplateSyntaxException
     */
    public function render(string $source, array $data = []): string
    {
        try {
            $template = $this->environment->parseString($source);

            return $template->render($this->environment->newRenderContext(data: $data));
        } catch (LiquidException $e) {
            throw new TemplateSyntaxException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Parse the source to prove it compiles, without rendering it.
     *
     * @throws TemplateSyntaxException
     */
    public function validate(string $source): void
    {
        try {
            $this->environment->parseString($source);
        } catch (LiquidException $e) {
            throw new TemplateSyntaxException($e->getMessage(), previous: $e);
        }
    }
}
