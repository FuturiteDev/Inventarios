<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Ongoing\Sucursales\Entities\Sucursales;
use Ongoing\Inventarios\Entities\Productos;
/**
 * Class Inventario.
 *
 * @package namespace App\Entities;
 */
class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'cantidad_total',
        'cantidad_disponible',
        'fecha_caducidad',
        'estatus',
    ];

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class);
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

    // Atributo append
    protected $appends = ['estatus_desc'];

    public function getEstatusDescAttribute()
    {
        switch ($this->estatus) {
            case 0:
                return 'Eliminado';
            case 1:
                return 'Activo';
            case 2:
                return 'Traspaso pendiente';
            default:
                return 'Desconocido';
        }
    }
}