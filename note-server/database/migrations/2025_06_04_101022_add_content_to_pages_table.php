<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('content')->nullable()->after('uuid'); // json type for structured data
        });
    }

    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
