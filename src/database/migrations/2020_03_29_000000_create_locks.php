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
        Schema::connection(config('atom.locks.connection'))->create('locks', function (Blueprint $table) {
            $table->string('sha1')->unique();
            $table->timestamp('updated_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('atom.locks.connection'))->dropIfExists('locks');
    }
};
