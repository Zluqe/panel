<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->unsignedTinyInteger('r_server_subusers')
                ->default(0)
                ->after('r_server_databases') // Optional: place after existing permission column
                ->comment('Controls read/write access to server subusers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('r_server_subusers');
        });
    }
};
