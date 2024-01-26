<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_telephone');
            $table->integer('operator_id');
            $table->longText('pbx_audio_url');
            $table->longText('telegram_audio_url');
            $table->string('event');
            $table->string('direction');
            $table->integer('call_duration');
            $table->integer('dialog_duration');
            $table->string('uuid');
            $table->integer('gateway');
            $table->integer('date');
            $table->boolean('sent_sms')->default(false);
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
        Schema::dropIfExists('calls');
    }
}
