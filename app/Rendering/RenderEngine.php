<?php

namespace App\Rendering;

interface RenderEngine
{
    /**
     * Render HTML to a document.
     *
     * @return string raw PDF bytes
     *
     * @throws RenderException when the engine is unreachable or fails to render
     * @throws \InvalidArgumentException when an option is not supported
     */
    public function render(string $html, array $options = []): string;
}
