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
		Schema::create('traspasos', function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_origen_id');
            $table->unsignedInteger('sucursal_destino_id');
            $table->unsignedBigInteger('empleado_id');
            $table->integer('tipo');
            $table->text('comentarios')->nullable();
			$table->integer('estatus')->default(1);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('sucursal_origen_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('sucursal_destino_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

			$table->engine = 'InnoDB';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('traspasos');
	}
};
