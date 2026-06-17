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

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('Pending'); // string used for PostgreSQL compatibility (enum causes issues with ->change())
            $table->date('assigned_date');
            $table->date('completed_date')->nullable();
            $table->float('estimated_hours')->default(0);
            $table->float('actual_hours')->default(0);
            $table->timestamps();
       
        });
    }

    /**
     * Reverse the migrations.
    */

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
?>
