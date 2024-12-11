<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\traspasosRepository;
use Ongoing\Inventarios\Entities\Traspasos;
use App\Validators\TraspasosValidator;

/**
 * Class TraspasosRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class TraspasosRepositoryEloquent extends BaseRepository implements TraspasosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Traspasos::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
