<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class InventarioRevisionProductos.
 *
 * @package namespace App\Entities;
 */
class InventarioRevisionProductos extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'inventario_revision_productos';

    protected $fillable = ['inventario_revision_id', 'producto_id', 'existencia_actual', 'existencia_real', 'imagen'];

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
