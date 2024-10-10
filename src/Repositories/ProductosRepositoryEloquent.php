<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\ProductosRepository;
use Ongoing\Inventarios\Entities\Productos;

/**
 * Class ProductosRepositoryEloquent.
 *
 * @package namespace Ongoing\Inventarios\Repositories;
 */
class ProductosRepositoryEloquent extends BaseRepository implements ProductosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Productos::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
