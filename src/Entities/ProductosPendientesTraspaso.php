<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;

    // Definir la tabla
    protected $table = 'productos_pendientes_traspaso';

    // Campos que son asignables masivamente
    protected $fillable = [
        'sucursal_origen',
        'sucursal_destino',
        'producto_id',
        'cantidad',
        'estatus',
    ];

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_origen');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_destino');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

}
