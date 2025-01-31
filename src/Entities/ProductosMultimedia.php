<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class ProductosMultimedia.
 *
 * @package namespace App\Entities;
 */
class ProductosMultimedia extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'producto_id',
        'file_name',
        'path',
        'url',
        'portada',
        'estatus'
    ];

    public function producto() {
        return $this->belongsTo(Productos::class, 'producto_id', 'other_key');
    }

}
