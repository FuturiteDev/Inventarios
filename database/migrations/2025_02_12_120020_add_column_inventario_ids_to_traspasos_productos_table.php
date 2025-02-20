<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInventarioIdsToTraspasosProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('traspasos_productos', function (Blueprint $table) {
            $table->date('fecha_caducidad')->nullable()->after('cantidad');
            $table->string('inventario_ids')->nullable()->after('foto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('traspasos_productos', function (Blueprint $table) {
            $table->dropColumn('fecha_caducidad');
            $table->dropColumn('inventario_ids');
        });
    }
}
