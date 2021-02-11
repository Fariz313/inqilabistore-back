<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Store extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('store_name');
            $table->integer('kode_provinsi');
            $table->integer('kode_kota');
            $table->integer('kode_kecamatan');
            $table->integer('kode_desa');
            $table->integer('kode_pos');    
            $table->text('address');
            $table->text('description')->nullable();
            $table->string('contact')->nullable();
            $table->string('photo')->nullable();
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
        Schema::dropIfExists('store');
    }
}
