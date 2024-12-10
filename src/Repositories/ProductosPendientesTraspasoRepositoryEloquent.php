<?php

namespace Ongoing\Inventarios\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Ongoing\Inventarios\Repositories\ProductosPendientesTraspasoRepository;
use Ongoing\Inventarios\Entities\ProductosPendientesTraspaso;
use App\Validators\ProductosPendientesTraspasoValidator;
/**
 * Class ProductosPendientesTraspasoRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ProductosPendientesTraspasoRepositoryEloquent extends BaseRepository implements ProductosPendientesTraspasoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductosPendientesTraspaso::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
