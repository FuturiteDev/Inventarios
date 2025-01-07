<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Productos.
 *
 * @package namespace App\Entities;
 */
class Productos extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'categoria_id',
        'subcategoria_id',
        'nombre',
        'descripcion',
        'sku',
        'precio',
        'caracteristicas_json',
        'extras_json',
        'visitas',
        'estatus'
    ];

    protected $casts = [
        'caracteristicas_json' => 'array',
        'extras_json' => 'array'
    ];
    public function categoria() {
        return $this->belongsTo(Categorias::class, 'categoria_id', 'id')->where('categorias.estatus', 1);
    }

    public function subcategoria() {
        return $this->belongsTo(SubCategoria::class, 'subcategoria_id', 'id')->where('sub_categorias.estatus', 1);

    }

    public function colecciones() {
        return $this->belongsToMany(Colecciones::class, 'colecciones_productos', 'producto_id', 'coleccion_id')->where('colecciones.estatus', 1);
    }

    public function multimedia()
    {
        return $this->hasMany(ProductosMultimedia::class, 'producto_id');
    }

    public function productosPendientesTraspaso()
    {
        return $this->hasMany(ProductosPendientesTraspaso::class);
    }

    public function pedidos() {
        return $this->hasMany(PedidoProductos::class);
    }


}