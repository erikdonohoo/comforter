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
            $table->integer('gitlab_project_id')->index();
            $table->string('primary_branch_name');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('commits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->string('branch_name')->index();
            $table->string('sha')->index()->unique();
            $table->float('coverage');
            $table->dateTime('created_at')->index();
            $table->dateTime('updated_at');

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
