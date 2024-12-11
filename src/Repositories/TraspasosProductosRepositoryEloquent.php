<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\TraspasosRepository;
use Ongoing\Inventarios\Entities\TraspasosProductos;
use App\Validators\TraspasosProductosValidator;

/**
 * Class TraspasosProductosRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class TraspasosProductosRepositoryEloquent extends BaseRepository implements TraspasosProductosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return TraspasosProductos::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
