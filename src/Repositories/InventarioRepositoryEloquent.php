<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\InventarioRepository;
use Ongoing\Inventarios\Entities\Inventario;
use App\Validators\InventarioValidator;

/**
 * Class InventarioRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class InventarioRepositoryEloquent extends BaseRepository implements InventarioRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Inventario::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
