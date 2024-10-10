<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\ColeccionesRepository;
use Ongoing\Inventarios\Entities\Colecciones;

/**
 * Class ColeccionesRepositoryEloquent.
 *
 * @package namespace Ongoing\Inventarios\Repositories;
 */
class ColeccionesRepositoryEloquent extends BaseRepository implements ColeccionesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Colecciones::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
