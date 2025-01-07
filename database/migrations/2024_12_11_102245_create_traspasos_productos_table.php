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
		Schema::create('traspasos_productos', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('traspaso_id');
			$table->unsignedInteger('producto_id');
            $table->float('cantidad');
            $table->float('cantidad_recibida');
            $table->string('foto')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('traspaso_id')->references('id')->on('traspasos')->onDelete('restrict');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');

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
		Schema::drop('traspasos_productos');
	}
};
