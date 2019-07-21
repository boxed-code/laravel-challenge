<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TwoFactorTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('two_factor_tokens', function(Blueprint $table) {
            $table->increments('id');
            $table->string('session_id')->index();
            $table->string('token');
            $table->integer('challengeable_id')->unsigned();
            $table->string('provider');
            $table->boolean('is_enrollment_token')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('two_factor_tokens');
    }
}
