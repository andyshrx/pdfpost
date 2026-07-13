<?php

namespace Database\Factories;

use App\Models\Template;
use App\Models\TemplateVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateVersion>
 */
class TemplateVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'template_id' => Template::factory(),
            'version' => 1,
            'liquid_source' => '<h1>{{ title }}</h1>',
            'sample_data' => ['title' => 'Sample'],
        ];
    }
}
