<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('template_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format', 8)->default('pdf');
            $table->string('status', 16)->default('queued');
            $table->longText('html')->nullable();
            $table->json('payload')->nullable();
            $table->json('options')->nullable();
            $table->string('artifact_disk')->nullable();
            $table->string('artifact_path')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('webhook_url', 2048)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('render_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->string('response_excerpt', 500)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('renders');
    }
};
