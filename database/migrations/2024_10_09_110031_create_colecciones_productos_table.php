<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('colecciones_productos', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('coleccion_id');
            $table->integer('producto_id');
            $table->integer('estatus');
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
		Schema::drop('colecciones_productos');
	}
};
