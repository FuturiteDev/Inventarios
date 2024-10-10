<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\ColeccionesProductosRepository;
use Ongoing\Inventarios\Entities\ColeccionesProductos;

/**
 * Class ColeccionesProductosRepositoryEloquent.
 *
 * @package namespace Ongoing\Inventarios\Repositories;
 */
class ColeccionesProductosRepositoryEloquent extends BaseRepository implements ColeccionesProductosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ColeccionesProductos::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
