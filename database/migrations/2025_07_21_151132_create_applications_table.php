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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('university_name');
            $table->string('country');
            $table->string('program');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected', 'approved'])->default('pending');
            $table->enum('priority_level', ['low', 'medium', 'high'])->default('medium');
            $table->date('estimated_completion_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
