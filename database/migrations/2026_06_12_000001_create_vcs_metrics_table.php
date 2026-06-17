<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vcs_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('provider'); // 'github', 'gitlab', 'bitbucket'
            $table->string('git_username');
            $table->integer('commits')->default(0);
            $table->integer('pull_requests')->default(0);
            $table->integer('reviews')->default(0);
            $table->integer('bugs_fixed')->default(0);
            $table->integer('deployments')->default(0);
            $table->float('deployment_frequency')->default(0.0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vcs_metrics');
    }
};
