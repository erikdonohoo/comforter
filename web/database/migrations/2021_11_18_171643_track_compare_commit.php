<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TrackCompareCommit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->string('comparison_sha')->nullable()->after('sha');
            $table->foreign('comparison_sha')
             ->references('sha')
             ->on('commits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->dropForeign(['comparison_sha']);
            $table->dropColumn('comparison_sha');
        });
    }
}
