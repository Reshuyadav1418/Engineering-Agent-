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
        // 1. Create teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('team_lead_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Create team_members table
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->timestamps();
        });

        // 3. Create task_members table
        Schema::create('task_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->float('assigned_hours')->default(0);
            $table->float('actual_hours')->default(0);
            $table->string('status')->default('Pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. Update tasks table: make employee_id nullable and add team_id
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->after('employee_id')->constrained('teams')->nullOnDelete();
        });

        // 5. Update productivity_scores table: add team_contribution
        Schema::table('productivity_scores', function (Blueprint $table) {
            $table->float('team_contribution')->default(0)->after('productivity_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productivity_scores', function (Blueprint $table) {
            $table->dropColumn('team_contribution');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            $table->dropForeign(['employee_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::dropIfExists('task_members');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
