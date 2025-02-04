<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Ongoing\Empleados\Entities\Empleado;
use Ongoing\Sucursales\Entities\Sucursales;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;


/**
 * Class Traspasos.
 *
 * @package namespace App\Entities;
 */
class Traspasos extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // Definir las columnas asignables
    protected $fillable = [
        'sucursal_origen_id',
        'sucursal_destino_id',
        'empleado_id',
        'asignado_a',
        'tipo',
        'comentarios',
        'estatus',
    ];

    /**
     * Relación con la tabla 'sucursales' (origen)
     */
    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_origen_id');
    }

    /**
     * Relación con la tabla 'sucursales' (destino)
     */
    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_destino_id');
    }

    /**
     * Relación con la tabla 'empleados'
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function empleadoAsignado()
    {
        return $this->belongsTo(Empleado::class, 'asignado_a');
    }

    /**
     * Relación con los productos del traspaso
     */
    public function traspasoProductos()
    {
        return $this->hasMany(TraspasosProductos::class, 'traspaso_id');
    }

}
