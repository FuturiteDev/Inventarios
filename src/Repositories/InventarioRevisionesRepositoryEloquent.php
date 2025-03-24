<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\InventarioRevisionesRepository;
use Ongoing\Inventarios\Entities\InventarioRevisiones;
use App\Validators\InventarioRevisionesValidator;

/**
 * Class InventarioRevisionesRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class InventarioRevisionesRepositoryEloquent extends BaseRepository implements InventarioRevisionesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return InventarioRevisiones::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
