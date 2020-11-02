<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_inputs', function (Blueprint $table) {
            $table->id();
            $table->integer('description_id')->unsigned();
            $table->decimal('amount', 8, 2);
            $table->date('payment_date');
            $table->timestamps();


            //$table->foreign('description_id')
                //->references('id')->on('description_release')
                //->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_inputs');
    }
}
