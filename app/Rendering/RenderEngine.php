<?php

namespace App\Rendering;

interface RenderEngine
{
    /**
     * Render HTML to a document.
     *
     * @return string raw PDF bytes
     *
     * @throws RenderException
     */
    public function render(string $html, array $options = []): string;
}
