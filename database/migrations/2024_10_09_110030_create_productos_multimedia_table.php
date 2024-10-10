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
		Schema::create('productos_multimedia', function(Blueprint $table) {
            $table->increments('id');
			$table->bigInteger('producto_id');
			$table->string('file_name');
			$table->string('path');
			$table->string('url');
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
		Schema::drop('productos_multimedia');
	}
};
