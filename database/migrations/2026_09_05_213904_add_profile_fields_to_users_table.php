<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('guru')->after('email');
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'role', 'phone', 'position', 'bio', 'photo',
                'instagram', 'facebook', 'is_active',
            ]);
        });
    }
};
