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
		Schema::create('log_registro_inventarios', function(Blueprint $table) {
			$table->id();
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('empleado_id');
            $table->integer('existencia_actual');
            $table->integer('existencia_real');
            $table->text('comentarios')->nullable();
            $table->timestamps(); 
			
			$table->engine = 'InnoDB';

            // Agregar las claves foráneas
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
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
		Schema::drop('log_registro_inventarios');
	}
};
