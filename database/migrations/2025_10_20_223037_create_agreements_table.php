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
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('priority_id')->constrained('priorities')->onDelete('cascade');;
            $table->string('ac')->nullable();
            $table->string('responsible')->nullable();
            $table->date('agreed_date')->nullable();
            $table->foreignId('status_id')->constrained('statuses')->default(1)->onDelete('cascade');
            $table->foreignId('agreement_type_id')->constrained('agreement_types')->default(1)->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
