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
		Schema::create('inventario_revision_productos', function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('inventario_revision_id');
            $table->unsignedInteger('producto_id');
            $table->integer('existencia_actual');
            $table->integer('existencia_real');
            $table->string('imagen')->nullable();
            $table->timestamps(); 
			
			$table->engine = 'InnoDB';

            $table->foreign('inventario_revision_id')->references('id')->on('inventario_revisiones')->onDelete('restrict');
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
		Schema::drop('inventario_revision_productos');
	}
};
