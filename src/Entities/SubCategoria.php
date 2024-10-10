<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class SubCategoria.
 *
 * @package namespace App\Entities;
 */
class SubCategoria extends Model implements Transformable {
    use TransformableTrait;

    protected $table = 'sub_categorias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'estatus',
        'caracteristicas_json'
    ];

    protected $casts = [
        'caracteristicas_json' => 'array'
    ];

    public function categoria() {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }
}
