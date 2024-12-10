<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ongoing\Sucursales\Entities\Sucursales;

/**
 * Class ProductosPendientesTraspaso.
 *
 * @package namespace App\Entities;
 */
class ProductosPendientesTraspaso extends Model implements Transformable
{
    use HasFactory;
    use TransformableTrait;

    // Definir la tabla
    protected $table = 'productos_pendientes_traspaso';

    // Campos que son asignables masivamente
    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'cantidad',
        'estatus',
    ];

    // Relación con la tabla de sucursales
    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class);
    }

    // Relación con la tabla de productos
    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

}
