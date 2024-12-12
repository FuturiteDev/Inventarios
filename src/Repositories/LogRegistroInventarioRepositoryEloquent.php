<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\LogRegistroInventarioRepository;
use Ongoing\Inventarios\Entities\LogRegistroInventario;
use App\Validators\LogRegistroInventarioValidator;

/**
 * Class LogRegistroInventarioRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class LogRegistroInventarioRepositoryEloquent extends BaseRepository implements LogRegistroInventarioRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LogRegistroInventario::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
