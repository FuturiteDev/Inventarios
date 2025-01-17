<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductosPendientesTraspasoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos_pendientes_traspaso', function (Blueprint $table) {

            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');

            $table->unsignedInteger('sucursal_origen')->nullable()->after('id');
            $table->unsignedInteger('sucursal_destino')->nullable()->after('sucursal_origen');

            $table->foreign('sucursal_origen')->references('id')->on('sucursales')->onDelete('restrict');
            $table->foreign('sucursal_destino')->references('id')->on('sucursales')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos_pendientes_traspaso', function (Blueprint $table) {

            $table->unsignedInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->dropForeign(['sucursal_id']);

            $table->dropForeign(['sucursal_origen']);
            $table->dropForeign(['sucursal_destino']);

            $table->dropColumn('sucursal_origen');
            $table->dropColumn('sucursal_destino');
        });
    }
}
