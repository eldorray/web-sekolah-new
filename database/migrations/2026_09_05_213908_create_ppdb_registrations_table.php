<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_registrations', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->string('gender', 1);
            $table->string('birthplace');
            $table->date('birthdate');
            $table->string('previous_school');
            $table->text('address');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('parent_phone');
            $table->string('parent_email');
            $table->string('grade_target');
            $table->string('kk_file')->nullable();
            $table->string('birth_certificate_file')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_registrations');
    }
};
