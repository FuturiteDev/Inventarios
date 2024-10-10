<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\ProductosMultimediaRepository;
use Ongoing\Inventarios\Entities\ProductosMultimedia;

/**
 * Class ProductosMultimediaRepositoryEloquent.
 *
 * @package namespace Ongoing\Inventarios\Repositories;
 */
class ProductosMultimediaRepositoryEloquent extends BaseRepository implements ProductosMultimediaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductosMultimedia::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
