<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->integer('age');
            $table->string('email');
            $table->string('phone');
            $table->string('gender');
            $table->string('company_name')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('role');
            $table->integer('years_of_experience');
            $table->text('background')->nullable();
            $table->string('tshirt_size');
            $table->string('dietary_restrictions')->nullable();
            $table->boolean('mandatory_attendance_confirmed')->default(false);
            $table->boolean('looking_for_job')->default(false);
            $table->string('resume_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
