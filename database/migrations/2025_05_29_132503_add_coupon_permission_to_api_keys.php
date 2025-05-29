<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->tinyInteger('r_coupons')
                ->default(0)
                ->after('r_servers')
                ->comment('0 = None, 1 = Read, 2 = Write, 3 = Read+Write');
        });
    }

    public function down()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('r_coupons');
        });
    }
};
