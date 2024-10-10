<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class ColeccionesProductos.
 *
 * @package namespace App\Entities;
 */
class ColeccionesProductos extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['coleccion_id', 'producto_id', 'estatus'];

    public function productos() {
        return $this->hasMany(Productos::class, 'id', 'producto_id')->with(['categoria', 'subcategoria'])->where("productos.estatus", 1);
    }

    public function coleccion() {
        return $this->belongsTo(Colecciones::class, 'coleccion_id', 'id');
    }
}
