<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_notes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('impacts', function (Blueprint $table) {
            $table->id();
            $table->string('impact');
            $table->timestamps();
        });
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('color')->default('#000000');
            $table->timestamps();
        });
        Schema::create('substatuses', function (Blueprint $table) {
            $table->id();
            $table->string('substatus');
            $table->string('color')->default('#000000');
            $table->timestamps();
        });
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('priority');
            $table->string('color')->default('#000000');
            $table->timestamps();
        });
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->string('sprint');
            $table->timestamps();
        });
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag');
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('index');
            $table->string('title');
            $table->foreignId('status_id')->constrained('statuses')->default(1)->onDelete('cascade');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('percentage_planned', 5, 2)->default(0);
            $table->decimal('percentage_progress', 5, 2)->default(0);
            $table->integer('days')->default(0);
            $table->longText('comments')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('real_end_date')->nullable();
            $table->date('restriction_start_date')->nullable();
            $table->date('restriction_end_date')->nullable();
            $table->integer('slack')->default(0);
            $table->timestamps();
        });
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_note_id')->constrained('type_notes')->onDelete('cascade');
            $table->foreignId('impact_id')->constrained('impacts')->onDelete('cascade');
            $table->text('content');
            $table->morphs('notable');
            $table->timestamps();
        });
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('statuses')->default(1)->onDelete('cascade');
            $table->foreignId('substatus_id')->nullable()->constrained('substatuses')->default(1)->onDelete('cascade');
            $table->string('title');
            $table->string('index');
            $table->integer('days')->default(0);
            $table->longText('comments')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('percentage_planned', 5, 2)->default(0);
            $table->decimal('percentage_progress', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('real_end_date')->nullable();
            $table->date('restriction_start_date')->nullable();
            $table->date('restriction_end_date')->nullable();
            $table->json('depend_me')->nullable();
            $table->json('i_depend')->nullable();
            $table->integer('slack')->default(0);
            $table->timestamps();
        });
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('phase_id')->constrained('phases')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('statuses')->default(1)->onDelete('cascade');
            $table->foreignId('substatus_id')->nullable()->constrained('substatuses')->default(1)->onDelete('cascade');
            $table->foreignId('priority_id')->constrained('priorities')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sprint_id')->constrained('sprints')->onDelete('cascade');
            $table->string('index');
            $table->string('title');
            $table->integer('days')->default(0);
            $table->longText('comments')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('percentage_planned', 5, 2)->default(0);
            $table->decimal('percentage_progress', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('real_end_date')->nullable();
            $table->date('restriction_start_date')->nullable();
            $table->date('restriction_end_date')->nullable();
            $table->json('depend_me')->nullable();
            $table->json('i_depend')->nullable();
            $table->integer('slack')->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('statuses')->default(1)->onDelete('cascade');
            $table->foreignId('substatus_id')->nullable()->constrained('substatuses')->default(1)->onDelete('cascade');
            $table->foreignId('priority_id')->constrained('priorities')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('index');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('percentage_planned', 5, 2)->default(0);
            $table->decimal('percentage_progress', 5, 2)->default(0);
            $table->integer('days')->default(0);
            $table->longText('comments')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('real_end_date')->nullable();
            $table->date('restriction_start_date')->nullable();
            $table->date('restriction_end_date')->nullable();
            $table->json('depend_me')->nullable();
            $table->json('i_depend')->nullable();
            $table->integer('slack')->default(0);
            $table->timestamps();
        });
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('stakeholder_id')->constrained('stakeholders')->onDelete('cascade');
            $table->string('description');
            $table->timestamps();
        });
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->morphs('uploadable');
            $table->string('path');
            $table->string('filename');
            $table->string('extension');
            $table->string('size');
            $table->string('mime_type');
            $table->string('disk')->default('local');
            $table->string('source');
            $table->timestamps();
        });
        Schema::create('delivery_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('delivery_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('requirement_id')->constrained('requirements')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('snap_projects',function(Blueprint $table){
            $table->id();
            $table->string('label')->nullable();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('percentage_planned', 5, 2)->default(0);
            $table->decimal('percentage_progress', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_requirement');
        Schema::dropIfExists('delivery_tag');
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('stakeholders');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('phases');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('priorities');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('impacts');
        Schema::dropIfExists('type_notes');
    }
};
