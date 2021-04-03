<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Order extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->integer('store_id');
            $table->integer('user_id');
            $table->integer('address_id');
            $table->integer('total');
            $table->integer('ongkir');
            $table->string('resi')->nullable();
            $table->string('url')->nullable();
            $table->string('payment_method');
            $table->enum('status',['pending','process','sending','success','failed']);
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
        Schema::dropIfExists('order');
    }   
}
