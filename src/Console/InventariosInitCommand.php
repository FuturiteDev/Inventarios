<?php

namespace Ongoing\Inventarios\Console;

use Illuminate\Console\Command;
use App\Repositories\NavegacionRepositoryEloquent;

class InventariosInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventarios:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra las rutas en la navegacion del sitio';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NavegacionRepositoryEloquent $nav_repo)
    {

        $nav_gpo = $nav_repo->firstOrCreate([
            'descripcion' => 'Inventarios', 
            'url' => '/', 
            'padre_id' => 0, 
            'permisos' => []
        ]);

        $rutas = [
            [
                'descripcion' => 'Productos', 
                'url' => '/inventarios/productos',
                'padre_id' => $nav_gpo->id, 
                'permisos' => []
            ],
            [
                'descripcion' => 'Categorias', 
                'url' => '/inventarios/categorias',
                'padre_id' => $nav_gpo->id, 
                'permisos' => []
            ],
            [
                'descripcion' => 'Subcategorias', 
                'url' => '/inventarios/subcategorias',
                'padre_id' => $nav_gpo->id, 
                'permisos' => []
            ],
            [
                'descripcion' => 'Colecciones', 
                'url' => '/inventarios/colecciones',
                'padre_id' => $nav_gpo->id, 
                'permisos' => []
            ],
            [
                'descripcion' => 'Inventario', 
                'url' => '/inventarios/existencias-sucursal',
                'padre_id' => $nav_gpo->id, 
                'permisos' => []
            ]
        ];

        foreach($rutas as $row){
            $nav = $nav_repo->findByField('url', $row['url']);
            if($nav->count() == 0){
                $nav_repo->create($row);
            }
            
        }

        $this->info("Proceso finalizado");

        return 0;
    }
}
