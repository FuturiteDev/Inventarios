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
use App\Mail\SendBrevoMail;
use Ongoing\Empleados\Entities\Empleado;

class InventarioController extends Controller
{
    protected $inventario;
    protected $sucursales;

    /**
     * Summary of __construct
     * @param \Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent $inventario
     * @param \Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent $sucursales
     */
    public function __construct(
        InventarioRepositoryEloquent $inventario,
        SucursalesRepositoryEloquent $sucursales
    ) {
        $this->inventario = $inventario;
        $this->sucursales = $sucursales;
    }

    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function index()
    {
        Gate::authorize('access-granted', '/inventarios/inventario');
        return view('inventarios::inventario');
    }


    /**
     * Summary of getProductosConExistencias
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProductosConExistencias(Request $request)
    {
        try {
            // Obtener productos con existencias
            $productos = Inventario::with(['producto' => function ($query) {
                $query->where('estatus', 1); // Producto activo
            }])
                ->where('sucursal_id', $request->sucursal_id)
                ->where('cantidad_disponible', '>', 0)
                ->where('estatus', '>', 0)
                ->get();

            // Filtrar productos activos y agruparlos por producto_id y fecha_caducidad
            $productosGrouped = $productos->filter(function ($inventario) {
                return $inventario->producto !== null;
            })->groupBy(function ($inventario) {
                return $inventario->producto_id . '-' . $inventario->fecha_caducidad;
            });

            // Formatear la respuesta
            $productosList = $productosGrouped->map(function ($group) {
                $inventario = $group->first();
                $cantidad_total = $group->sum('cantidad_total');
                $cantidad_existente = $group->sum('cantidad_disponible');

                return [
                    'id' => $inventario->id,
                    'sucursal_id' => $inventario->sucursal_id,
                    'producto_id' => $inventario->producto_id,
                    'cantidad_total' => $cantidad_total,
                    'cantidad_existente' => $cantidad_existente,
                    'fecha_caducidad' => $inventario->fecha_caducidad,
                    'estatus' => $inventario->estatus,
                    'estatus_desc' => $inventario->estatus === 1 ? 'activo' : 'inactivo',
                    'producto' => [
                        'id' => $inventario->producto->id,
                        'nombre' => $inventario->producto->nombre,
                        'sku' => $inventario->producto->sku,
                        'estatus' => $inventario->producto->estatus,
                    ],
                ];
            })->values();

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
     * Summary of agregarInventarios
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
                'productos.*.fecha_caducidad' => 'required|date',
                'productos.*.fecha_elaboracion' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => true,
                    'message' => "Algun producto no existe en la base de datos",
                    "info" => $validator->errors(),
                ], 400);
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
     * Summary of registrarInventario
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

            $inventarioActual = $this->inventario
                ->where('sucursal_id', $sucursal_id)
                ->where('producto_id', $producto_id)
                ->where('fecha_caducidad', $fecha_caducidad)
                ->where('cantidad_disponible', 1)
                ->get();

            $empleado = Empleado::with('jefe')->where('no_empleado', $numero_empleado)->first();

            $cantidadActual = 0;

            if ($inventarioActual->count() > 0) {
                $cantidadActual = $inventarioActual->sum('cantidad_total');
                $diferencia = $cantidad - $cantidadActual;

                if ($diferencia < 0) {
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
            $comentarios = $request->comentarios;

            foreach ($productos as $producto) {
                $id_producto = $producto['id_producto'];
                $cantidad_real = $producto['cantidad_real'];
                $cantidad_reportada = $producto['cantidad_reportada'];

                DB::table('inventarios')->updateOrInsert(
                    [
                        'sucursal_id' => $sucursal_id,
                        'producto_id' => $id_producto
                    ],
                    [
                        'cantidad_actual' => $cantidad_real,
                        'cantidad_disponible' => $cantidad_real
                    ]
                );

                $diferencia = $cantidad_real !== $cantidad_reportada;

                if ($diferencia) {
                    $empleado = Empleado::where('id', $empleado_id)->with('jefe')->first();

                    // Guardar en base de datos
                    DB::table('log_revision_inventarios')->insert([
                        'empleado_id' => $empleado_id,
                        'sucursal_id' => $sucursal_id,
                        'producto_id' => $id_producto,
                        'existencia_actual' => $cantidad_real,
                        'existencia_real' => $cantidad_reportada,
                        'comentarios' => $comentarios
                    ]);

                    // O enviar correo, falta confirmacion
                    if ($empleado) {
                        $destinatarios = [
                            "to" => [
                                (object) [
                                    'email' => $empleado->jefe->email,
                                    'name' => $empleado->jefe->nombre_completo
                                ]
                            ]
                        ];

                        $arrData = [
                            'comentarios' => "Revision",
                            'subject' => "Notificación de ajuste en inventario",
                            'view' => 'mail.recovery_password',
                        ];

                        SendBrevoMail::send($destinatarios, $arrData);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Revisión de inventario registrada correctamente."
            ], 200);
        } catch (\Exception $e) {
            Log::error("InventarioController->guardarRevisionInventario() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->guardarRevisionInventario() | " . $e->getMessage()
            ], 500);
        }
    }
}
