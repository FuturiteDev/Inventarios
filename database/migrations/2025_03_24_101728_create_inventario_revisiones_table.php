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
		Schema::create('inventario_revisiones', function(Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('sucursal_id');
            $table->unsignedBigInteger('empleado_id')->nullable();
			$table->tinyInteger('estatus')->default(0);
            $table->timestamps();
			
			$table->engine = 'InnoDB';

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventario_revisiones');
	}
};
