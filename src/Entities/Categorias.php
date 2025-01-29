<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
/**
 * Class Categorias.
 *
 * @package namespace App\Entities;
 */
class Categorias extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'descripcion', 'imagen', 'estatus'];

    public function banner(): HasOne
    {
        return $this->hasOne(Banners::class, "categoria_id");
    }

    public function getImagenUrlAttribute(){
        if(!empty($this->imagen)){
            return asset('storage/categorias/'.$this->imagen);
        }else{
            return asset('assets-1/media/avatars/blank.png');
        }
    }
}