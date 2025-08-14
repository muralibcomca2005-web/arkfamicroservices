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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Enrollment info
            $table->timestamp('enrolled_at')->nullable();
            $table->enum('status', ['pending', 'enrolled'])->default('pending');
            $table->enum('completion_status', ['in_progress', 'dropped', 'completed'])->nullable();

            // Timestamps
            $table->timestamps();

            // Soft deletes
            $table->softDeletes();

            // Indexes for faster lookup (optional but recommended)
            $table->index('student_id');
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
