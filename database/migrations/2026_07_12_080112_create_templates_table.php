<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            // no FK constraint here, circular reference with template_versions
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->timestamps();
        });

        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->longText('liquid_source');
            $table->json('sample_data')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['template_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_versions');
        Schema::dropIfExists('templates');
    }
};
