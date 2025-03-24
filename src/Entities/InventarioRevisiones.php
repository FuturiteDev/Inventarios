<?php

namespace Ongoing\Inventarios\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

use Ongoing\Empleados\Entities\Empleado;
use Ongoing\Sucursales\Entities\Sucursales;

/**
 * Class InventarioRevisiones.
 *
 * @package namespace App\Entities;
 */
class InventarioRevisiones extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'inventario_revisiones';

    protected $fillable = ['sucursal_id', 'empleado_id', 'estatus'];

    public function getEstatusDescAttribute(){
        if(!is_null($this->estatus)){
            switch($this->estatus){
                case 0 : $desc = 'Pendiente'; break;
                case 1 : $desc = 'Finalizado'; break;
                default: $desc = '-';
            }

            return $desc;
        }else{
            return '-';
        }
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function revisionProductos()
    {
        return $this->hasMany(InventarioRevisionProductos::class, 'inventario_revision_id');
    }
}
