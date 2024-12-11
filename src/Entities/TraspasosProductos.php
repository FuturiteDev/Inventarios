<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Ongoing\Inventarios\Entities\Productos;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class TraspasosProductos.
 *
 * @package namespace App\Entities;
 */
class TraspasosProductos extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'traspaso_id',
        'producto_id',
        'cantidad',
        'cantidad_recibida',
        'foto',
    ];

    /**
     * Relación con la tabla 'traspasos'
     */
    public function traspaso()
    {
        return $this->belongsTo(Traspasos::class);
    }

    /**
     * Relación con la tabla 'productos'
     */
    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

    /**
     * Mutador para guardar la foto de manera apropiada (opcional)
     */
    public function setFotoAttribute($value)
    {
        if (is_file($value)) {
            $this->attributes['foto'] = $value->store('traspasos/fotos', 'public');
        } else {
            $this->attributes['foto'] = $value;
        }
    }

}
