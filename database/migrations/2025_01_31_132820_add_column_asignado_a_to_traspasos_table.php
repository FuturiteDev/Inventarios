<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAsignadoAToTraspasosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('traspasos', function (Blueprint $table) {
            $table->unsignedBigInteger('asignado_a')->nullable()->after('empleado_id');
            $table->foreign('asignado_a')->references('id')->on('empleados')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('traspasos', function (Blueprint $table) {
            $table->dropForeign(['asignado_a']);
            $table->dropColumn('asignado_a');
        });
    }
}
