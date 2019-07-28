<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChallengeEnrolments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_enrolments', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('method');
            $table->text('state')->nullable();
            $table->timestamp('setup_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'method'], 'unique_enrolment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_enrolments');
    }
}
