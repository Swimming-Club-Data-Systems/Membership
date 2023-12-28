<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competition_files', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->foreignIdFor(\App\Models\Tenant\Competition::class);
            $table->string('path', 512);
            $table->string('disk');
            $table->string('original_name');
            $table->string('public_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->boolean('public');
            $table->integer('sequence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_files');
    }
};
