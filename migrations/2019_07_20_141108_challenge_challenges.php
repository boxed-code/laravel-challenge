<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChallengeChallenges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_challenges', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->unsigned();
            $table->string('method');
            $table->string('purpose');
            $table->timestamp('challenged_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('state')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'method', 'purpose'], 'unique_challenge');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_challenges');
    }
}
