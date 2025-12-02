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
            $table->text('name'); // Encrypted
            $table->integer('age');
            $table->text('email'); // Encrypted
            $table->string('email_hash')->index(); // Blind Index
            $table->text('phone'); // Encrypted
            $table->string('gender');
            $table->text('company_name')->nullable(); // Encrypted
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('role');
            $table->integer('years_of_experience');
            $table->text('background')->nullable(); // Encrypted
            $table->string('tshirt_size');
            $table->text('dietary_restrictions')->nullable(); // Encrypted
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
