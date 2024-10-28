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
		Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->unsignedInteger('producto_id')->nullable();;
            $table->float('cantidad_total')->default(1);
            $table->float('cantidad_disponible')->default(1);
            $table->dateTime('fecha_caducidad')->nullable();
            $table->integer('estatus')->default(1);
            
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
		Schema::drop('inventario');
	}
};
