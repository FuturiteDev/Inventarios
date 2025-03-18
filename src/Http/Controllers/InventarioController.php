<?php

namespace Ongoing\Inventarios\Http\Controllers;

use Log;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

use Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent;
use Ongoing\Inventarios\Entities\Inventario;

use Illuminate\Http\Request;
use Ongoing\Sucursales\Entities\Sucursales;
use Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosRepositoryEloquent;
use App\Mail\SendBrevoMail;
use App\Repositories\UsuarioRepositoryEloquent;
use Ongoing\Empleados\Entities\Empleado;
use App\Repositories\NotificacionesRepositoryEloquent;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;


class InventarioController extends Controller
{
    protected $inventario;
    protected $sucursales;
    protected $productos;
    protected $usuarios;
    protected $notificaciones;

    /**
     * Summary of __construct
     * @param \Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent $inventario
     * @param \Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent $sucursales
     */
    public function __construct(
        InventarioRepositoryEloquent $inventario,
        SucursalesRepositoryEloquent $sucursales,
        ProductosRepositoryEloquent $productos,
        UsuarioRepositoryEloquent $usuarios,
        NotificacionesRepositoryEloquent $notificaciones
    ) {
        $this->inventario = $inventario;
        $this->sucursales = $sucursales;
        $this->productos = $productos;
        $this->usuarios = $usuarios;
        $this->notificaciones = $notificaciones;
    }

    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function index()
    {
        Gate::authorize('access-granted', '/inventarios/producto-terminado');
        return view('inventarios::producto_terminado');
    }

    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function existenciasSucursal()
    {
        Gate::authorize('access-granted', '/inventarios/existencias-sucursal');
        return view('inventarios::existencias');
    }

    public function getProductosConExistencias(Request $request)
    {
        try {
            $productos = $this->productos->where('estatus', 1)
                ->with([
                    'categoria',
                    'subcategoria',
                    'inventarios' => function ($query) use ($request) {
                        $query->where('sucursal_id', $request->sucursal_id)
                            ->where('estatus', '>', 0);
                    }
                ])
                ->get();

            $productosList = $productos->map(function ($producto) {
                $productosGrouped = $producto->inventarios->groupBy('fecha_caducidad');

                $inventarioData = $productosGrouped->map(function ($group) {
                    $inventario = $group->first();
                    return [
                        'id' => $inventario->id,
                        'sucursal_id' => $inventario->sucursal_id,
                        'producto_id' => $inventario->producto_id,
                        'cantidad_total' => $group->sum('cantidad_total'),
                        'cantidad_existente' => $group->sum('cantidad_disponible'),
                        'fecha_caducidad' => $inventario->fecha_caducidad,
                        'estatus' => $inventario->estatus,
                        'estatus_desc' => $inventario->estatus_desc,
                    ];
                })->values();

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'sku' => $producto->sku,
                    'estatus' => $producto->estatus_desc,
                    'caracteristicas' => $producto->caracteristicas_json,
                    'extras' => $producto->extras_json,
                    'categoria' => $producto->categoria ? [
                        'id' => $producto->categoria->id,
                        'nombre' => $producto->categoria->nombre,
                    ] : null,
                    'subcategoria' => $producto->subcategoria ? [
                        'id' => $producto->subcategoria->id,
                        'nombre' => $producto->subcategoria->nombre,
                    ] : null,
                    'inventario' => $inventarioData,
                    'colecciones' => $producto->colecciones
                ];
            });

            return response()->json([
                'status' => true,
                'results' => $productosList
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->getProductosConExistencias() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->getProductosConExistencias() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }


    /**
     * Registro de inventarios de productos terminados
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agregarInventarios(Request $request)
    {
        try {
            
            // Validaciones
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursales,id',
                'productos' => 'required|array',
                'productos.*.id' => 'required|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                // 'productos.*.fecha_caducidad' => 'required|date',
                // 'productos.*.fecha_elaboracion' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => true,
                    'message' => "Algun producto no existe en la base de datos",
                    "info" => $validator->errors(),
                ], 400);
            }

            if ($request->numero_empleado) {
                $data = [
                    'sucursal_id' => $request->sucursal_id,
                    'productos' => []
                ];

                foreach ($request->productos as $producto) {
                    $data['productos'][] = [
                        'producto_id' => $producto['id'],
                        'cantidad' => $producto['cantidad'],
                        'fecha_elaboracion' => $producto['fecha_elaboracion'] ?? null,
                        'fecha_caducidad' => $producto['fecha_caducidad'] ?? null
                    ];
                }

                $notificacion = [
                    'usuario_id' => $request->usuario_id ?? null,
                    'traspaso_id' => $request->traspaso_id ?? null,
                    'titulo' => "Inventarios agregados correctamente.",
                    'data' => $data,
                    'mensaje' => "Inventarios agregados."
                ];

                $this->sendNotification($notificacion);
            }

            $sucursal_id = $request->sucursal_id;

            foreach ($request->productos as $producto) {
                $cantidad = $producto['cantidad'];

                for ($i = 0; $i < $cantidad; $i++) {
                    $this->inventario->create([
                        'sucursal_id' => $sucursal_id,
                        'producto_id' => $producto['id'],
                        'cantidad_total' => 1,
                        'cantidad_disponible' => 1,
                        'fecha_elaboracion' => $producto['fecha_elaboracion'],
                        'fecha_caducidad' => $producto['fecha_caducidad'],
                        'estatus' => 1,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Productos ingresados correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->agregarInventarios() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->agregarInventarios() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * Summary of eliminarInventarios
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function eliminarInventarios(Request $request)
    {
        try {

            $rowInv = $this->inventario->find($request->inventario_id);

            $rowInv->estatus = 0;
            $rowInv->save();

            return response()->json([
                'status' => true,
                'message' => "Registro eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->eliminarInventarios() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->eliminarInventarios() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function revisionSucursal($sucursalId)
    {
        try {
            $sucursal = Sucursales::find($sucursalId);

            if (!$sucursal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sucursal no encontrada.'
                ], 404);
            }

            $productos = Inventario::with(['producto.categoria', 'producto.subcategoria'])
                ->where('sucursal_id', $sucursalId)
                ->where('estatus', 1)
                ->get()
                ->groupBy('producto_id')
                ->map(function ($items) {
                    $primerRegistro = $items->first();

                    return [
                        'id' => $primerRegistro->producto->id,
                        'sku' => $primerRegistro->producto->sku,
                        'nombre' => $primerRegistro->producto->nombre,
                        'categoria' => $primerRegistro->producto->categoria,
                        'subcategoria' => $primerRegistro->producto->subcategoria,
                        'total_existencias' => $items->sum('cantidad_disponible'),
                        'colecciones' => $primerRegistro->producto->colecciones,
                    ];
                })->values();

            return response()->json([
                'status' => true,
                'results' => [
                    'sucursal_id' => $sucursal->id,
                    'nombre' => $sucursal->nombre,
                    'productos' => $productos,
                ],
                'message' => 'Lista de productos activos con existencias en la sucursal.'
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->revisionSucursal() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->revisionSucursal() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * Actualizar el inventario de un producto en especifico
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarInventario(Request $request)
    {
        try {
            $sucursal_id = $request->sucursal_id;
            $producto_id = $request->producto_id;
            $cantidad = $request->cantidad;
            $fecha_caducidad = $request->fecha_caducidad;
            $numero_empleado = $request->numero_empleado;
            $comentarios = $request->comentarios;
            $usuario_id = $request->usuario_id;


            $inventarioActual = $this->inventario
                ->where('sucursal_id', $sucursal_id)
                ->where('producto_id', $producto_id)
                ->where('fecha_caducidad', $fecha_caducidad)
                ->where('cantidad_disponible', 1)
                ->get();

            $empleado = Empleado::with('jefe')->where('no_empleado', $numero_empleado)->first();

            $cantidadActual = 0;

            $titulo = "El inventario no aumento ni disminuyo.";
            $mensaje = "El inventario sigue igual.";

            if ($inventarioActual->count() > 0) {
                $cantidadActual = $inventarioActual->sum('cantidad_total');
                $diferencia = $cantidad - $cantidadActual;

                $titulo = "Se agrego correctamente el inventario.";
                $mensaje = "Se agrego el producto " . $producto_id . " a stock por la cantidad de " . $cantidad . ", diferencia con la cantidad actual (" . $cantidadActual . ") es de " . $diferencia;

                if ($diferencia < 0) {

                    $titulo = "Se disminuyo correctamente el inventario.";
                    $mensaje = "Se disminuyo el producto " . $producto_id . " la cantidad de " . $cantidad . ", diferencia con la cantidad actual (" . $cantidadActual . ") es de " . $diferencia;
                    $registros = $inventarioActual->take(abs($diferencia));

                    foreach ($registros as $registro) {
                        $registro->cantidad_disponible = 0;
                        $registro->save();
                    }

                    $destinatarios = ["to" => []];

                    if ($empleado && $empleado->jefe) {
                        $destinatarios["to"][] = (object)[
                            'email' => $empleado->jefe->email,
                            'name' => $empleado->jefe->nombre_completo,
                        ];
                    }

                    $arrData = [
                        'comentarios' => $comentarios,
                        'subject' => "Notificación de ajuste en inventario",
                        'view' => 'mail.recovery_password',
                    ];

                    SendBrevoMail::send($destinatarios, $arrData);
                } elseif ($diferencia > 0) {
                    for ($i = 0; $i < $diferencia; $i++) {
                        $this->inventario->create([
                            'sucursal_id' => $sucursal_id,
                            'producto_id' => $producto_id,
                            'cantidad_total' => 1,
                            'cantidad_disponible' => 1,
                            'fecha_caducidad' => $fecha_caducidad,
                            'estatus' => 1,
                        ]);
                    }
                }
            } else {
                for ($i = 0; $i < $cantidad; $i++) {
                    $this->inventario->create([
                        'sucursal_id' => $sucursal_id,
                        'producto_id' => $producto_id,
                        'cantidad_total' => 1,
                        'cantidad_disponible' => 1,
                        'fecha_caducidad' => $fecha_caducidad,
                        'estatus' => 1,
                    ]);
                }
            }

            $cantidadReal = $this->inventario
                ->where('sucursal_id', $sucursal_id)
                ->where('producto_id', $producto_id)
                ->where('fecha_caducidad', $fecha_caducidad)
                ->where('cantidad_disponible', 1)
                ->sum('cantidad_total');

            DB::table('log_registro_inventarios')->insert([
                'sucursal_id' => $sucursal_id,
                'producto_id' => $producto_id,
                'existencia_actual' => $cantidadActual,
                'existencia_real' => $cantidadReal,
                'empleado_id' => $empleado->id,
                'comentarios' => $comentarios,
            ]);

            $detalleInventario = $this->inventario
                ->where('sucursal_id', $sucursal_id)
                ->where('producto_id', $producto_id)
                ->where('cantidad_disponible', 1)
                ->select('fecha_caducidad', DB::raw('SUM(cantidad_total) as cantidad_total'))
                ->groupBy('fecha_caducidad')
                ->get();


            $data = [];

            $data['sucursal_id'] = $sucursal_id;
            $data['producto_id'] = $producto_id;
            $data['fecha_caducidad'] = $fecha_caducidad;

            $notificacion = [
                'usuario_id' => $usuario_id,
                'traspaso_id' => null,
                'titulo' => $titulo,
                'data' => $data,
                'mensaje' => $mensaje
            ];

            $this->sendNotification($notificacion);

            return response()->json([
                'status' => true,
                'message' => "Inventario registrado correctamente",
                'detalle' => $detalleInventario,
            ], 200);
        } catch (\Exception $e) {
            Log::error("InventarioController->registrarInventario() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->registrarInventario() | " . $e->getMessage(),
            ], 500);
        }
    }

    public function revisionInventario(Request $request)
    {
        try {
            $empleado_id = $request->empleado_id;
            $sucursal_id = $request->sucursal_id;
            $productos = $request->productos;
            $comentarios = $request->comentarios ?? null;

            $empleado = Empleado::where('id', $empleado_id)->with('jefe')->first();
            $sucursal = Sucursales::find($sucursal_id);

            if (!$empleado || !$sucursal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empleado o sucursal no encontrados.'
                ], 400);
            }

            $productosConDiferencias = [];

            foreach ($productos as $producto) {
                $id_producto = $producto['id_producto'];
                $cantidad_real = $producto['cantidad_real'];
                $cantidad_reportada = $producto['cantidad_reportada'];

                // $this->inventario->updateOrCreate(
                //     ['sucursal_id' => $sucursal_id, 'producto_id' => $id_producto],
                //     ['cantidad_total' => $cantidad_real, 'cantidad_disponible' => $cantidad_real]
                // );

                if ($cantidad_reportada < $cantidad_real) {
                    // DB::table('log_registro_inventarios')->insert([
                    //     'empleado_id' => $empleado_id,
                    //     'sucursal_id' => $sucursal_id,
                    //     'producto_id' => $id_producto,
                    //     'existencia_actual' => $cantidad_real,
                    //     'existencia_real' => $cantidad_reportada,
                    //     'comentarios' => $comentarios,
                    //     'created_at' => now(),
                    // ]);

                    $productoInfo = $this->productos->find($id_producto);
                    $productosConDiferencias[] = [
                        'codigo' => $productoInfo->sku ?? 'N/A',
                        'nombre' => $productoInfo->nombre ?? 'N/A',
                        'cantidad_sistema' => $cantidad_real,
                        'cantidad_reportada' => $cantidad_reportada
                    ];
                }
            }

            Log::info("Empleado: " . $empleado);
            Log::info("Jefe: " . $empleado->jefe);

            if (!empty($productosConDiferencias)) {
                $destinatarios = [
                    "to" => [
                        [
                            'email' => $empleado->jefe->email ?? 'N/A',
                            'name' => $empleado->jefe->nombre_completo ?? 'N/A'
                        ],
                        // Correos fijos
                        [
                            'email' => "betina@apetit.com.mx",
                            'name' => "Betina"
                        ],
                        [
                            'email' => "queta@apetit.com.mx",
                            'name' => "Queta"
                        ]
                    ]
                ];

                Log::info('Productos con diferencias:', $productosConDiferencias);


                $arrData = [
                    'title' => "Inconsistencia en revisión de inventario",
                    'subject' => "Notificación de ajuste en inventario",
                    'view' => 'mail.notificaciones_inventarios',
                    'data' => [
                        'sucursal_id' => $sucursal->id,
                        'sucursal_nombre' => $sucursal->nombre,
                        'fecha' => now()->format('Y-m-d H:i:s'),
                        'empleado_id' => $empleado->id,
                        'empleado_nombre' => $empleado->nombre_completo,
                        'productos' => $productosConDiferencias
                    ]
                ];

                SendBrevoMail::send($destinatarios, $arrData);
            }

            return response()->json([
                'status' => true,
                'message' => "Revisión de inventario registrada correctamente."
            ], 200);
        } catch (\Exception $e) {
            Log::error("InventarioController->revisionInventario() | " . $e->getMessage() . " | Línea: " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->revisionInventario() | " . $e->getMessage()
            ], 500);
        }
    }

    public function sendNotification($request)
    {
        try {

            $this->notificaciones->create([
                'usuario_id' => $request['usuario_id'],
                'trapaso_id' => $request['traspaso_id'],
                'titulo' => $request['titulo'],
                'mensaje' => $request['mensaje'],
                'data' => $request['data'],
                'leido' => $request['leido'] ?? 0
            ]);

            $FcmToken = $this->usuarios->whereNotNull('device_token')->where('id', $request['usuario_id'])->first();

            if ($FcmToken) {
                $firebase = (new Factory)->withServiceAccount(storage_path() . '/app/apetit-60f6a-firebase-adminsdk-fbsvc-cb77effb73.json');

                $messaging = $firebase->createMessaging();
                $message = CloudMessage::withTarget('token', $FcmToken->device_token)
                    ->withNotification(["title" => $request['titulo'], "body" => $request['mensaje']])
                    ->withAndroidConfig(
                        AndroidConfig::fromArray(['notification' => ["title" => $request['titulo'], "body" => $request['mensaje'], 'channel_id' => 'general']])
                            ->withSound('cash.mp3')
                    )
                    ->withApnsConfig(
                        ApnsConfig::new()
                            ->withApsField('alert', ["title" => $request['titulo'], "body" => $request['mensaje']])
                            ->withSound('cash.aiff')
                            ->withBadge(1)
                    );

                $messaging->send($message);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::info("[TraspasosController-sendNotification] - ERROR " . $e->getMessage());
            report($e);

            return ['success' => false];
        }
    }

    public function productosExistenciaGeneral(Request $request)
    {
        try {
            $productos = $this->productos->where('estatus', 1)
                ->with([
                    'inventarios',
                    'categoria:id,nombre,descripcion',
                    'subcategoria:id,nombre,descripcion'
                ])
                ->get();

            $sucursales = $this->sucursales->where('estatus', 1)->get();

            $results = $productos->map(function ($producto) use ($sucursales) {
                $totalExistencia = $producto->inventarios->sum('cantidad_disponible');

                $sucursalData = $sucursales->map(function ($sucursal) use ($producto) {
                    $cantidad = $producto->inventarios
                        ->where('sucursal_id', $sucursal->id)
                        ->sum('cantidad_disponible');

                    return [
                        'sucursal_id'         => $sucursal->id,
                        'sucursal_nombre'     => $sucursal->nombre,
                        'cantidad_existente'  => $cantidad,
                    ];
                })->filter(function ($data) {
                    return $data['cantidad_existente'] > 0;
                })->values();

                return [
                    'producto_id'      => $producto->id,
                    'nombre'           => $producto->nombre,
                    'sku'              => $producto->sku,
                    'total_existencia' => $totalExistencia,
                    'sucursales'       => $sucursalData,
                    'categoria'       => $producto->categoria,
                    'subcategoria'       => $producto->subcategoria,
                    'caracteristicas' => $producto->caracteristicas_json,
                    'extras' => $producto->extras_json,
                    'colecciones' => $producto->colecciones,
                ];
            });

            return response()->json([
                'status'  => true,
                'results' => $results
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->productosExistenciaGeneral() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status'  => false,
                'message' => "[ERROR] InventarioController->productosExistenciaGeneral() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function getProductosConPocaExistencia(Request $request)
    {
        try {
            $request->validate([
                'sucursal_id' => 'required|integer|exists:sucursales,id',
                'existencia_minima' => 'required|integer|min:0',
            ]);

            $productos = $this->productos->where('estatus', 1)
                ->with([
                    'inventarios' => function ($query) use ($request) {
                        $query->where('sucursal_id', $request->sucursal_id)
                            ->where('estatus', 1)
                            ->where('cantidad_disponible', '>', 0);
                    },
                    'categoria:id,nombre',
                    'subcategoria:id,nombre'
                ])
                ->get();


            $productosFiltrados = $productos->map(function ($producto) use ($request) {
                $cantidad_total = $producto->inventarios->sum('cantidad_total');
                $cantidad_existente = $producto->inventarios->sum('cantidad_disponible');

                if ($cantidad_existente <= $request->existencia_minima) {
                    return [
                        'id' => $producto->id,
                        'sucursal_id' => $request->sucursal_id,
                        'producto_id' => $producto->id,
                        'cantidad_existente' => $cantidad_existente,
                        'producto' => [
                            'id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'sku' => $producto->sku,
                            'estatus' => $producto->estatus_desc,
                            'caracteristicas' => $producto->caracteristicas_json,
                            'extras' => $producto->extras_json,
                            'categoria' => $producto->categoria ? [
                                'id' => $producto->categoria->id,
                                'nombre' => $producto->categoria->nombre,
                            ] : null,
                            'subcategoria' => $producto->subcategoria ? [
                                'id' => $producto->subcategoria->id,
                                'nombre' => $producto->subcategoria->nombre,
                            ] : null,
                            'colecciones' => $producto->colecciones
                        ],
                    ];
                }
                return null;
            })->filter()->values();

            return response()->json([
                'status' => true,
                'results' => $productosFiltrados
            ], 200);
        } catch (\Exception $e) {
            Log::info("InventarioController->getProductosConPocaExistencia() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->getProductosConPocaExistencia() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }
}
