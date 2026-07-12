<?php

namespace Database\Factories;

use App\Models\Render;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Render>
 */
class RenderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'format' => 'pdf',
            'status' => 'queued',
            'html' => '<h1>Test</h1>',
        ];
    }

    public function succeeded(): static
    {
        return $this->state([
            'status' => 'succeeded',
            'artifact_disk' => 'local',
            'artifact_path' => 'renders/test.pdf',
            'completed_at' => now(),
        ]);
    }
}
