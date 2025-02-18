<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFechaCaducidadToProductosPendientesTraspasoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos_pendientes_traspaso', function (Blueprint $table) {
            $table->dateTime('fecha_caducidad')->nullable()->after('cantidad');
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
            $table->dropColumn('fecha_caducidad');
        });
    }
}
