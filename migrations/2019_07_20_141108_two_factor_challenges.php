<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TwoFactorChallenges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('two_factor_challenges', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->unsigned();
            $table->string('method');
            $table->string('purpose');
            $table->timestamp('challenged_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('state')->nullable();
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
        Schema::dropIfExists('two_factor_challenges');
    }
}
