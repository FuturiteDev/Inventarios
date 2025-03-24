<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLogRegistroInventariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('log_registro_inventarios');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('log_registro_inventarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->unsignedInteger('producto_id');
            $table->unsignedBigInteger('empleado_id');
            $table->integer('existencia_actual');
            $table->integer('existencia_real');
            $table->text('comentarios')->nullable();
            $table->timestamps(); 
			
			$table->engine = 'InnoDB';

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');
        });
    }
}
