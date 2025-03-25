<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Colecciones.
 *
 * @package namespace App\Entities;
 */
class Colecciones extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'estatus'
    ];

    public function productos() {
        return $this->belongsToMany(Productos::class, 'colecciones_productos', 'coleccion_id', 'producto_id')->where('productos.estatus', 1);
    }

}
