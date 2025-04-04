<?php

namespace Ongoing\Inventarios\Console;

use Illuminate\Console\Command;
use App\Entities\Navegacion;

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
    public function handle()
    {

        $menu_navegacion = [
            [
                'descripcion' => 'Control Interno',
                'url' => '/',
                'icono' => '',
                'orden' => 1,
                'submenu' => [
                    [
                        'descripcion' => 'Configuración de inventario',
                        'url' => '/',
                        'orden' => 1,
                        'permisos' => [],
                        'submenu' => [
                            [
                                'descripcion' => 'Productos',
                                'url' => '/inventarios/productos',
                                'orden' => 1,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Categorias',
                                'url' => '/inventarios/categorias',
                                'orden' => 2,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Subcategorias',
                                'url' => '/inventarios/subcategorias',
                                'orden' => 3,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Colecciones',
                                'url' => '/inventarios/colecciones',
                                'orden' => 4,
                                'permisos' => []
                            ]
                        ]
                    ],
                    [
                        'descripcion' => 'Gestión de inventario',
                        'url' => '/',
                        'orden' => 2,
                        'permisos' => [],
                        'submenu' => [
                            [
                                'descripcion' => 'Inventario central y catering',
                                'url' => '/inventarios/producto-terminado',
                                'orden' => 1,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Distribución del inventario',
                                'url' => '/inventarios/existencias-sucursal',
                                'orden' => 2,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Traspasos',
                                'url' => '/inventarios/traspasos',
                                'orden' => 3,
                                'permisos' => []
                            ],
                            [
                                'descripcion' => 'Inventario reportado',
                                'url' => '/inventarios/sucursales-auditadas',
                                'orden' => 4,
                                'permisos' => []
                            ]
                        ]
                    ]
                ],
            ]
        ];

        foreach ($menu_navegacion as $row) {
            $submenu = $row['submenu'] ?? [];
            unset($row['submenu']);
            $nav = Navegacion::firstOrCreate(['url' => $row['url'], 'descripcion' => $row['descripcion']], $row);
            $nav->fill($row)->save();
            $ids_submenu = [];
            foreach ($submenu as $sub) {
                $sub_submenu = $sub['submenu'] ?? [];
                unset($sub['submenu']);
                $sub_nav = $nav->submenu()->firstOrCreate(['url' => $sub['url'], 'descripcion' => $sub['descripcion']], $sub);
                $sub_nav->fill($sub)->save();
                $ids_submenu[] = $sub_nav->id;
                $ids_sub_sub = [];
                foreach ($sub_submenu as $sub_sub) {
                    $sub_sub_nav = $sub_nav->submenu()->firstOrCreate(['url' => $sub_sub['url'], 'descripcion' => $sub_sub['descripcion']], $sub_sub);
                    $sub_sub_nav->fill($sub_sub)->save();
                    $ids_sub_sub[] = $sub_sub_nav->id;
                }
                // $sub_nav->submenu()->whereNotIn('id', $ids_sub_sub)->delete();
            }
            // $nav->submenu()->whereNotIn('id', $ids_submenu)->delete();
        }
    }
}
