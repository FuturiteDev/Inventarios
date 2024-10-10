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
		Schema::create('productos', function(Blueprint $table) {
            $table->increments('id');
			$table->bigInteger('categoria_id')->nullable();
			$table->bigInteger('subcategoria_id')->nullable();
			$table->string('nombre');
			$table->text('descripcion')->nullable();
			$table->string('sku')->nullable();
            $table->decimal('precio',9,2)->default(0);
			$table->json('caracteristicas_json')->nullable();
			$table->json('extras_json')->nullable();
			$table->bigInteger('visitas')->default(0);
			$table->integer('estatus')->default(1);
			
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
		Schema::drop('productos');
	}
};
