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
		Schema::create('productos_pendientes_traspaso', function(Blueprint $table) {
            $table->increments('id');

			$table->unsignedInteger('sucursal_id')->nullable();
            $table->unsignedInteger('producto_id')->nullable();
            $table->integer('cantidad')->nullable();
			$table->integer('estatus')->default(1);
            $table->softDeletes();
            $table->timestamps();
			$table->engine = 'InnoDB';

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('productos_pendientes_traspaso');
	}
};
