<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('atom.locks.connection'))->create(config('atom.locks.table'), function (Blueprint $table)
        {
            $table->string('sha1', 40)->unique();
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
        Schema::connection(config('atom.locks.connection'))->dropIfExists(config('atom.locks.table'));
    }
}
