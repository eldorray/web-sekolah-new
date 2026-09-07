<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id', 64)->index();
            $table->string('ip', 45)->index();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('path', 500);
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
