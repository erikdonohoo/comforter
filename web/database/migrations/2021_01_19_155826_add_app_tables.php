<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('project_url');
            $table->integer('gitlab_project_id')->index();
            $table->string('primary_branch_name');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['gitlab_project_id', 'name']);
        });

        Schema::create('commits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->string('branch_name')->index();
            $table->string('sha')->index();
            $table->decimal('coverage', 8, 4, true);
            $table->integer('total_lines')->nullable();
            $table->integer('total_lines_covered')->nullable();
            $table->dateTime('created_at')->index();
            $table->dateTime('updated_at')->index();

            // Foreign
            $table->foreign('app_id')
             ->references('id')
             ->on('apps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commits');
        Schema::dropIfExists('apps');
    }
}
