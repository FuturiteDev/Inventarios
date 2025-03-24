<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

use Ongoing\Inventarios\Repositories\InventarioRevisionProductosRepository;
use Ongoing\Inventarios\Entities\InventarioRevisionProductos;
use App\Validators\InventarioRevisionProductosValidator;

/**
 * Class InventarioRevisionProductosRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class InventarioRevisionProductosRepositoryEloquent extends BaseRepository implements InventarioRevisionProductosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return InventarioRevisionProductos::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
