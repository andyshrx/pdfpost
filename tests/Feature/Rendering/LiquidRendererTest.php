<?php

use App\Rendering\LiquidRenderer;
use App\Rendering\TemplateSyntaxException;

it('merges data into a liquid template', function () {
    $html = app(LiquidRenderer::class)->render('<h1>Invoice for {{ customer }}</h1>', ['customer' => 'Acme Co']);

    expect($html)->toBe('<h1>Invoice for Acme Co</h1>');
});

it('supports loops and filters', function () {
    $source = '{% for item in items %}<li>{{ item.name }}: {{ item.price }}</li>{% endfor %}';

    $html = app(LiquidRenderer::class)->render($source, [
        'items' => [
            ['name' => 'Consulting', 'price' => '450.00'],
            ['name' => 'Hosting', 'price' => '12.00'],
        ],
    ]);

    expect($html)->toBe('<li>Consulting: 450.00</li><li>Hosting: 12.00</li>');
});

it('renders missing variables as empty instead of failing', function () {
    $html = app(LiquidRenderer::class)->render('<p>{{ not_provided }}</p>');

    expect($html)->toBe('<p></p>');
});

it('throws a template syntax exception for broken liquid', function () {
    app(LiquidRenderer::class)->render('{% for x in %}');
})->throws(TemplateSyntaxException::class);

it('validates source without rendering', function () {
    $renderer = app(LiquidRenderer::class);

    $renderer->validate('{{ fine }}');

    expect(fn () => $renderer->validate('{% endif %}'))->toThrow(TemplateSyntaxException::class);
});
