<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\SubCategoriaRepository;
use Ongoing\Inventarios\Entities\SubCategoria;

/**
 * Class SubCategoriaRepositoryEloquent.
 *
 * @package namespace Ongoing\Inventarios\Repositories;
 */
class SubCategoriaRepositoryEloquent extends BaseRepository implements SubCategoriaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return SubCategoria::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
