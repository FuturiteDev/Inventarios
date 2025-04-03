@extends('erp.base')

@section('content')
    <div id="app">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content">
            <ul class="mt-5 nav nav-fill nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#inventarioSucursal" role="tab">Existencias por Sucursal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#inventarioGeneral" role="tab">Existencias Generales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#pocaExistenciaSucursal" role="tab">Pocas Existencias por Sucursal</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <!--begin::Tab-->
                <div class="tab-pane fade show active" id="inventarioSucursal" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card card-flush" id="content-card-sucursal">
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title flex-column">
                                <h3 class="ps-2">Existencias por Sucursal</h3>
                            </div>
                            <div class="card-toolbar gap-2">
                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#kt_modal_ver_traspaso">Ver traspasos</button>
                                <div class="px-2 min-w-200px">
                                    <v-select 
                                        v-model="filterSucursalExistencias"
                                        :options="listaSucursales"
                                        data-allow-clear="false"
                                        data-placeholder="Filtrar por sucursal">
                                    </v-select>
                                </div>
                                <button type="button" class="btn btn-icon btn-primary" @click="getInventarioSucursal(true)">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body py-4">
                            <!--begin::Toolbar-->
                            <div class="d-flex gap-2 mb-5">
                                <div>
                                    <div class="input-group" id="datepicker_wrapper">
                                        <span class="input-group-text">
                                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                        <input id="datepicker_input1" type="text" class="form-control border-right-0" placeholder="Fecha de caducidad" v-model="filterFechaExistencias"/>
                                        <span class="bg-white border-left-0 input-group-text">
                                            <button type="button" class="btn-close" id="datepicker_clear1"></button>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterColeccionExistencias"
                                        :options="listaColecciones"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por coleccion">
                                    </v-select>
                                </div>
                                <!-- <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterCategoriaExistencias"
                                        :options="listaCategorias"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por categoría">
                                    </v-select>
                                </div>
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterSubcategoriaExistencias"
                                        :options="listaSubcategorias1"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por subcategoria">
                                    </v-select>
                                </div> -->
                                <div class="align-content-center">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" v-model="showProductosTiendaExistencias"/>
                                        <label class="form-check-label text-gray-700 fw-bold">Productos de Tienda</label>
                                    </div>
                                </div>
                                <div class="flex-fill text-end gap-5">
                                    <button type="button" class="btn btn-success btn-sm" @click="printTableExistencias"><i class="fa-solid fa-print"></i> Imprimir</button>
                                </div>
                            </div>
                            <!--begin::Toolbar-->
                            <v-client-table v-model="listaInventario" :columns="columns" :options="options" ref="existencias">
                                <div slot="nombre" slot-scope="props">[[props.row.producto.nombre]]</div>
                                <div slot="sku" slot-scope="props">[[props.row.producto.sku]]</div>
                                <div slot="cantidad" slot-scope="props">[[props.row.cantidad_existente]]</div>
                                <div slot="fecha_caducidad" slot-scope="props">[[props.row.fecha_caducidad | fecha]]</div>
                                <div slot="cantidad_existente" slot-scope="props">[[props.row.cantidad_existente ?? '0']]</div>
                                <div slot="acciones" slot-scope="props">
                                    <button type="button" class="btn btn-icon btn-sm btn-primary btn-sm me-2" title="Agregar a Traspaso" data-bs-toggle="modal" data-bs-target="#kt_modal_add_traspaso" @click="modalTraspaso(props.row)"><i class="fa-solid fa-truck-fast"></i></button>
                                    <button type="button" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Inventario" :disabled="loading" @click="deleteInventario(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                        <span class="indicator-label"><i class="fas fa-trash-alt"></i></i></span>
                                        <span class="indicator-progress"><i class="fas fa-trash-alt"></i><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                    </button>
                                </div>
                            </v-client-table>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Tab-->
                <!--begin::Tab-->
                <div class="tab-pane fade" id="inventarioGeneral" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card card-flush" id="content-card-general">
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title flex-column">
                                <h3 class="ps-2">Existencias Generales</h3>
                            </div>
                        </div>
                        <div class="card-body py-4" v-if="sucursales && sucursales.length>0">
                            <!--begin::Toolbar-->
                            <div class="d-flex gap-2 mb-5">
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterColeccionGeneral"
                                        :options="listaColecciones"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por coleccion">
                                    </v-select>
                                </div>
                                <!-- <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterCategoriaGeneral"
                                        :options="listaCategorias"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por categoría">
                                    </v-select>
                                </div>
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterSubcategoriaGeneral"
                                        :options="listaSubcategorias2"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por subcategoria">
                                    </v-select>
                                </div> -->
                                <div class="align-content-center">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" v-model="showProductosTiendaGeneral"/>
                                        <label class="form-check-label text-gray-700 fw-bold">Productos de Tienda</label>
                                    </div>
                                </div>
                                <div class="px-4">
                                    <button class="btn btn-icon btn-secondary" @click="getInventarioGeneral(true)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Actualizar">
                                        <i class="ki-solid ki-arrows-circle"></i>
                                    </button>
                                </div>
                                <div class="flex-fill text-end">
                                    <button type="button" class="btn btn-success btn-sm" @click="printTableGeneral"><i class="fa-solid fa-print"></i> Imprimir</button>
                                </div>
                            </div>
                            <!--begin::Toolbar-->
                            <v-client-table v-model="listaGeneral" :columns="columnsGlobal" :options="optionsGlobal" ref="general">
                                <div v-for="item in sucursales" :slot="item.id" slot-scope="props">
                                    [[props.row.sucursales.find((i) => i.sucursal_id == item.id)?.cantidad_existente ?? '']]
                                </div>
                            </v-client-table>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Tab-->
                <!--begin::Tab-->
                <div class="tab-pane fade" id="pocaExistenciaSucursal" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card card-flush" id="content-card-pocaexistencia">
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title flex-column">
                                <h3 class="ps-2">Pocas Existencias por Sucursal</h3>
                            </div>
                            <div class="card-toolbar gap-2">
                                <div>
                                    <input type="number" class="form-control" placeholder="Cantidad minima" v-model="filterCantidadMinima"/>
                                </div>
                                <div class="min-w-200px">
                                    <v-select 
                                        v-model="filterSucursalPocaExistencias"
                                        :options="listaSucursales"
                                        data-allow-clear="false"
                                        data-placeholder="Filtrar por sucursal">
                                    </v-select>
                                </div>
                                <button type="button" class="btn btn-icon btn-primary" @click="getPocaExistenciaSucursal(true)">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body py-4">
                            <!--begin::Card-->
                            <div class="d-flex gap-2 mb-5">
                                <div>
                                    <div class="input-group" id="datepicker_wrapper">
                                        <span class="input-group-text">
                                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                        <input id="datepicker_input2" type="text" class="form-control border-right-0" placeholder="Fecha de caducidad" v-model="filterFechaPocaExistencias"/>
                                        <span class="bg-white border-left-0 input-group-text">
                                            <button type="button" class="btn-close" id="datepicker_clear2"></button>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterColeccionPocaExistencias"
                                        :options="listaColecciones"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por coleccion">
                                    </v-select>
                                </div>
                                <!-- <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterCategoriaPocaExistencias"
                                        :options="listaCategorias"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por categoría">
                                    </v-select>
                                </div>
                                <div>
                                    <v-select
                                        class="pe-20"
                                        v-model="filterSubcategoriaPocaExistencias"
                                        :options="listaSubcategorias3"
                                        data-allow-clear="true"
                                        data-placeholder="Filtrar por subcategoria">
                                    </v-select>
                                </div> -->
                                <div class="align-content-center">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" v-model="showProductosTiendaPocaExistencias"/>
                                        <label class="form-check-label text-gray-700 fw-bold">Productos de Tienda</label>
                                    </div>
                                </div>
                                <div class="flex-fill text-end">
                                    <button type="button" class="btn btn-success btn-sm" @click="printTablePocasExistencias"><i class="fa-solid fa-print"></i> Imprimir</button>
                                </div>
                            </div>
                            <!--end::Toolbar-->
                            <v-client-table v-model="listaPocaExistencia" :columns="columnsPocaExistencia" :options="options" ref="pocaExistencia">
                                <div slot="nombre" slot-scope="props">[[props.row.producto.nombre]]</div>
                                <div slot="sku" slot-scope="props">[[props.row.producto.sku]]</div>
                                <div slot="cantidad" slot-scope="props">[[props.row.cantidad_existente]]</div>
                                <div slot="cantidad_existente" slot-scope="props">[[props.row.cantidad_existente ?? '0']]</div>
                            </v-client-table>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Tab-->
            </div>
        </div>
        <!--end::Content-->

        <!--begin::Modal - Add task-->
        <div class="modal fade" id="kt_modal_add_traspaso" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold">Agregar a Traspaso</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body">
                        <!--begin::Form-->
                        <form id="kt_modal_add_traspaso_form" class="form" action="#" @submit.prevent="">
                            <div class="row mb-7">
                                <label class="form-label">Producto</label>
                                <div>[[traspaso_model.producto_nombre]]</div>
                            </div>
                            <div class="row mb-7">
                                <label class="form-label">Fecha de caducidad</label>
                                <div>[[traspaso_model.fecha_caducidad | fecha]]</div>
                            </div>
                            <div class="row fv-row mb-7">
                                <label class="required form-label">Sucursal Destino</label>
                                <v-select 
                                    v-model="traspaso_model.sucursal_id"
                                    :options="listaSucursales"
                                    name="sucursal"
                                    data-allow-clear="false"
                                    data-placeholder="Seleccionar sucursal">
                                </v-select>
                            </div>
                            <div class="row fv-row mb-7">
                                <label class="required form-label">Cantidad</label>
                                <input type="number" v-model="traspaso_model.cantidad" class="form-control" placeholder="Cantidad" name="cantidad">
                            </div>                            
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="registrarTraspaso" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label">Guardar</span>
                            <span class="indicator-progress">Guardando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Add task-->

        <!--begin::Modal - Add traspaso-->
        <div class="modal fade" id="kt_modal_ver_traspaso" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold" v-if="!confirmar_traspaso">Traspasos pendientes</h2>
                        <h2 class="fw-bold" v-else>Confirmar traspaso - [[confirmar_traspaso.sucursal_destino_nombre]]</h2>
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                    </div>
                    <div class="modal-body">
                        <div class="row" v-if="!confirmar_traspaso&&traspasos_pendientes">
                            <div class="col-lg-6" v-if="traspasos_pendientes.productos_pendientes&&traspasos_pendientes.productos_pendientes.length>0">
                                <div class="card card-bordered" v-for="traspaso in traspasos_pendientes.productos_pendientes">
                                    <div class="p-4 d-flex">
                                        <div class="flex-fill">
                                            <div class="fs-6 fw-bold">[[traspaso.sucursal_destino_nombre]]</div>
                                            <div class="fw-semibold text-muted">[[traspaso.productos.reduce((sum, item) => sum + (item.cantidad ?? 0), 0)]] productos</div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-primary btn-sm btn-icon" @click="showTraspaso(traspaso)">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" v-else>
                                <div class="fs-6 fw-semibold text-center">Sin traspasos pendientes</div>
                            </div>
                        </div>
                        <form id="kt_modal_ver_traspaso_form" class="form" action="#" @submit.prevent="" v-else-if="confirmar_traspaso">
                            <table class="table table-row-dashed table-row-gray-300 table-bordered mb-7">
                                <thead>
                                    <tr>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Producto</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Fecha</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="align-middle" v-for="producto in confirmar_traspaso.productos" :key="'p_' + producto.id">
                                        <td>
                                            <div>[[producto.nombre]]</div>
                                            <div class="text-muted">[[producto.sku]]</div>
                                        </td>
                                        <td>[[producto.fecha_caducidad | fecha]]</td>
                                        <td>
                                            <span class="fv-row">
                                                <input type="number" v-model="producto.cantidad" class="form-control" placeholder="Cantidad" :name="`p_cantidad_${producto.producto_pendiente_id}`">
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="fv-row mb-7" id="select_tipo_traspaso">
                                <label class="form-label required">Tipo de traspaso</label>
                                <v-select
                                    v-model="confirmar_traspaso.tipo"
                                    name="tipo_traspaso"
                                    :options="tipo_traspasos"
                                    data-allow-clear="false"
                                    data-placeholder="Tipo de traspaso"
                                    data-dropdown-parent="#select_tipo_traspaso"
                                    data-minimum-results-for-search ="Infinity">
                                </v-select>
                            </div>

                            <div class="fv-row mb-7" id="select_asignar">
                                <label class="form-label required">Asignar a</label>
                                <v-select
                                    v-model="confirmar_traspaso.asignado_a"
                                    name="asignado_a"
                                    :options="listaEmpleados"
                                    data-allow-clear="false"
                                    data-dropdown-parent="#select_asignar"
                                    data-placeholder="Chofer asignado">
                                </v-select>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="form-label">Comentarios</label>
                                <textarea name="comentarios" rows="5" class="form-control" v-model="confirmar_traspaso.comentarios" style="resize: none"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" v-if="confirmar_traspaso">
                        <button type="button" class="btn btn-primary" @click="confirmarTraspaso" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label">Guardar</span>
                            <span class="indicator-progress">Guardando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal - Add traspaso-->

    </div>
@endsection

@section('scripts')
    <script src="/common_assets/js/vue-tables-2.min.js"></script>
    <script src="/common_assets/js/vue_components/v-select.js"></script>

    <script>
        const app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data: () => ({
                sesion: {!! Auth::user() !!},
                inventario: [],
                inventario_general: [],
                inventario_pocaexistencia: [],
                sucursales: [],
                colecciones: [],
                traspasos_pendientes: [],
                empleados: [],
                tipo_traspasos: [
                    {id: 1, text: 'A otra Sucursal'},
                    {id: 2, text: 'Para cliente'},
                    {id: 3, text: 'Merma'},
                ],
                // categorias: [],
                // subcategorias_existencias: [],
                // subcategorias_general: [],
                // subcategorias_pocaexistencias: [],
                columns: ['sku','nombre','cantidad_existente','fecha_caducidad','acciones'],
                columnsPocaExistencia: ['sku','nombre','cantidad_existente'],
                options: {
                    headings: {
                        sku: 'SKU',
                        nombre: 'Producto',
                        cantidad_existente: 'Cantidad Existente',
                        fecha_caducidad: 'Fecha de caducidad',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        sku: 'align-middle text-center ',
                        nombre: 'align-middle ',
                        cantidad_existente: 'align-middle text-center ',
                        fecha_caducidad: 'align-middle text-center ',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['sku', 'fecha_caducidad','cantidad_existente','nombre'],
                    filterable: ['nombre', 'sku'],
                    skin: 'table table-sm table-rounded table-striped border align-middle table-row-bordered fs-6',
                    columnsDropdown: true,
                    resizableColumns: false,
                    sortIcon: {
                        base: 'ms-3 fas',
                        up: 'fa-sort-asc text-gray-400',
                        down: 'fa-sort-desc text-gray-400',
                        is: 'fa-sort text-gray-400',
                    },
                    texts: {
                        count: "Mostrando {from} de {to} de {count} registros|{count} registros|Un registro",
                        first: "Primera",
                        last: "Última",
                        filterPlaceholder: "Buscar...",
                        limit: "Registros:",
                        page: "Página:",
                        noResults: "No se encontraron resultados",
                        loading: "Cargando...",
                        columns: "Columnas",
                    },
                    filterAlgorithm: {
                        nombre(row, query) {
                            let value = row.producto?.nombre?.toLowerCase();
                            return value.includes(query.toLowerCase());
                        },
                        sku(row, query) {
                            let value = row.producto?.sku?.toLowerCase();
                            return value?.includes(query.toLowerCase());
                        },
                    },
                    customSorting: {
                        nombre: function (ascending) {
                            return function (a, b) {
                                let nameA = a.producto?.nombre?.toLowerCase();
                                let nameB = b.producto?.nombre?.toLowerCase();

                                return ascending
                                    ? nameA > nameB ? 1 : nameA == nameB ? 0 : -1
                                    : nameA < nameB ? 1 : nameA == nameB ? 0 : -1;
                            }
                        },
                        sku: function (ascending) {
                            return function (a, b) {
                                let nameA = a.producto?.sku?.toLowerCase();
                                let nameB = b.producto?.sku?.toLowerCase();

                                return ascending
                                    ? nameA > nameB ? 1 : nameA == nameB ? 0 : -1
                                    : nameA < nameB ? 1 : nameA == nameB ? 0 : -1;
                            }
                        },
                    },
                    orderBy: {
                        column: 'sku',
                        ascending: true
                    },
                },

                filterSucursalExistencias: null,
                filterSucursalPocaExistencias: null,
                filterFechaExistencias: null,
                filterFechaPocaExistencias: null,
                filterColeccionExistencias: null,
                filterColeccionGeneral: null,
                filterColeccionPocaExistencias: null,
                // filterCategoriaExistencias: null,
                // filterCategoriaGeneral: null,
                // filterCategoriaPocaExistencias: null,
                // filterSubcategoriaExistencias: null,
                // filterSubcategoriaGeneral: null,
                // filterSubcategoriaPocaExistencias: null,
                showProductosTiendaExistencias: false,
                showProductosTiendaGeneral: false,
                showProductosTiendaPocaExistencias: false,
                filterCantidadMinima: 5,

                traspaso_model: {},
                confirmar_traspaso: null,

                validator: null,
                validatorConfirm: null,
                loading: false,
                blockUISucursal: null,
                blockUIGeneral: null,
                blockUIPocaExistencia: null,
            }),
            mounted() {
                let vm = this;
                vm.$forceUpdate();

                let container = document.querySelector('#content-card-sucursal');
                if (container) {
                    vm.blockUISucursal = new KTBlockUI(container);
                }

                container = document.querySelector('#content-card-general');
                if (container) {
                    vm.blockUIGeneral = new KTBlockUI(container);
                }

                container = document.querySelector('#content-card-pocaexistencia');
                if (container) {
                    vm.blockUIPocaExistencia = new KTBlockUI(container);
                }

                $("#kt_modal_add_traspaso").on('hidden.bs.modal', event => {
                    vm.validator.resetForm();
                    vm.traspaso_model = {};
                });

                let picker1 = $("#datepicker_input1").flatpickr({
                    dateFormat: "d/m/Y"
                });
                $( "#datepicker_clear1" ).on( "click", function() {
                    picker1.clear();
                } );

                let picker2 = $("#datepicker_input2").flatpickr({
                    dateFormat: "d/m/Y"
                });
                $( "#datepicker_clear2" ).on( "click", function() {
                    picker2.clear();
                } );

                $("#kt_modal_ver_traspaso").on('hidden.bs.modal', event => {
                    vm.confirmar_traspaso = null;
                });

                vm.initFormValidate();
                vm.getSucursales();
                vm.getEmpleados();
                //vm.getCategorias();
                vm.getColecciones();
                vm.getInventarioGeneral();
            },
            methods: {
                initFormValidate() {
                    let vm = this;
                    if(vm.validator) {
                        vm.validator.destroy();
                        vm.validator = null;
                    }
                    
                    vm.validator = FormValidation.formValidation(
                        document.getElementById('kt_modal_add_traspaso_form'), {
                            fields: {
                                'sucursal': {
                                    validators: {
                                        notEmpty: {
                                            message: 'La sucursal es requerida',
                                            trim: true
                                        }
                                    }
                                },
                                'cantidad': {
                                    validators: {
                                        callback: {
                                            callback: function (input) {
                                                if(!input.value || input.value == '' || input.value == 0){
                                                    return {
                                                        valid: false,
                                                        message: 'Cantidad invalida'
                                                    };
                                                }

                                                if(input.value > vm.traspaso_model.cantidad_existente){
                                                    return {
                                                        valid: false,
                                                        message: 'No hay inventario suficiente'
                                                    };
                                                }
                                                return { valid: true, message: '' };
                                            },
                                        },
                                        
                                    }
                                }
                            },

                            plugins: {
                                trigger: new FormValidation.plugins.Trigger(),
                                bootstrap: new FormValidation.plugins.Bootstrap5({
                                    rowSelector: '.fv-row',
                                    eleInvalidClass: '',
                                    eleValidClass: ''
                                })
                            }
                        }
                    );
                },
                getSucursales() {
                    let vm = this;
                    $.get(
                        '/api/sucursales/all',
                        res => {
                            vm.sucursales = res.results;
                            vm.$nextTick(() => {
                                vm.filterSucursalExistencias = res.results[0].id;
                                vm.filterSucursalPocaExistencias = res.results[0].id;
                                vm.getInventarioSucursal(true, res.results[0].id);
                                vm.getPocaExistenciaSucursal(true, res.results[0].id);
                            });
                        }, 'json'
                    );
                },
                getColecciones() {
                    let vm = this;
                    $.get('/api/colecciones/all', res => {
                        vm.colecciones = res.results;
                    }, 'json');
                },
                getEmpleados() {
                    let vm = this;
                    $.ajax({
                        method: "GET",
                        url: "/api/empleados/all",
                        dataType: "JSON",
                    }).done(function(res) {
                        vm.empleados = res.results;
                    }).fail(function(jqXHR, textStatus) {
                        console.log("Request failed getEmpleados: " + textStatus, jqXHR);
                    });
                },
                // getCategorias() {
                //     let vm = this;
                //     $.get('/api/categorias/all', res => {
                //         vm.categorias = res.results;
                //     }, 'json');
                // },
                // getSubcategorias1(categoriaID) {
                //     let vm = this;
                //     $.get(`/api/sub-categorias/categoria/${categoriaID}`, res => {
                //         vm.subcategorias_existencias = res.results;
                //     }, 'json');
                // },
                // getSubcategorias2(categoriaID) {
                //     let vm = this;
                //     $.get(`/api/sub-categorias/categoria/${categoriaID}`, res => {
                //         vm.subcategorias_general = res.results;
                //     }, 'json');
                // },
                // getSubcategorias3(categoriaID) {
                //     let vm = this;
                //     $.get(`/api/sub-categorias/categoria/${categoriaID}`, res => {
                //         vm.subcategorias_pocaexistencias = res.results;
                //     }, 'json');
                // },
                getInventarioGeneral(showLoader) {
                    let vm = this;
                    if (showLoader) {
                        if (!vm.blockUIGeneral) {
                            let container = document.querySelector('#content-card-general');
                            if (container) {
                                vm.blockUIGeneral = new KTBlockUI(container);
                                vm.blockUIGeneral.block();
                            }
                        } else {
                            if (!vm.blockUIGeneral.isBlocked()) {
                                vm.blockUIGeneral.block();
                            }
                        }
                    }
                    $.ajax({
                        url: '/api/inventarios/existencia-general',
                        type: 'GET',
                    }).done(function (res) {
                        vm.inventario_general = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getInventarioGeneral: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        if (vm.blockUIGeneral && vm.blockUIGeneral.isBlocked()) {
                            vm.blockUIGeneral.release();
                        }
                    });
                },
                getInventarioSucursal(showLoader, idSucursal){
                    let vm = this;
                    if (showLoader) {
                        if (!vm.blockUISucursal) {
                            let container = document.querySelector('#content-card-sucursal');
                            if (container) {
                                vm.blockUISucursal = new KTBlockUI(container);
                                vm.blockUISucursal.block();
                            }
                        } else {
                            if (!vm.blockUISucursal.isBlocked()) {
                                vm.blockUISucursal.block();
                            }
                        }
                    }

                    if(idSucursal){
                        vm.filterSucursalExistencias = idSucursal;
                    }

                    vm.getTraspasosPendientesSucursal(idSucursal ?? vm.filterSucursalExistencias);

                    $.ajax({
                        url: '/api/inventarios/existencia-sucursal',
                        type: 'POST',
                        data: {
                            sucursal_id: idSucursal ?? vm.filterSucursalExistencias
                        }
                    }).done(function (res) {
                        vm.inventario = res.results
                        .flatMap(item => item.inventario
                            .map(i => Object.assign(i, {
                                producto: {
                                    id: item.id,
                                    nombre: item.nombre,
                                    sku: item.sku,
                                    estatus: item.estatus,
                                    caracteristicas: item.caracteristicas,
                                    extras: item.extras,
                                    categoria: item.categoria,
                                    subcategoria: item.subcategoria,
                                    subcategoria: item.subcategoria,
                                    colecciones: item.colecciones,
                                },
                            }))
                        );
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getInventarioSucursal: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        if (vm.blockUISucursal && vm.blockUISucursal.isBlocked()) {
                            vm.blockUISucursal.release();
                        }
                    });
                },
                getPocaExistenciaSucursal(showLoader, idSucursal){
                    let vm = this;
                    if (showLoader) {
                        if (!vm.blockUIPocaExistencia) {
                            let container = document.querySelector('#content-card-pocaexistencia');
                            if (container) {
                                vm.blockUIPocaExistencia = new KTBlockUI(container);
                                vm.blockUIPocaExistencia.block();
                            }
                        } else {
                            if (!vm.blockUIPocaExistencia.isBlocked()) {
                                vm.blockUIPocaExistencia.block();
                            }
                        }
                    }

                    if(idSucursal){
                        vm.filterSucursalPocaExistencias = idSucursal;
                    }
                    $.ajax({
                        url: '/api/inventarios/poca-existencia',
                        type: 'POST',
                        data: {
                            sucursal_id: idSucursal ?? vm.filterSucursalPocaExistencias,
                            existencia_minima: vm.filterCantidadMinima,
                        }
                    }).done(function (res) {
                        vm.inventario_pocaexistencia = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getPocaExistenciaSucursal: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        if (vm.blockUIPocaExistencia && vm.blockUIPocaExistencia.isBlocked()) {
                            vm.blockUIPocaExistencia.release();
                        }
                    });
                },
                getTraspasosPendientesSucursal(idSucursal){
                    let vm = this;
                    $.ajax({
                        url: `/api/traspasos/sucursal/pendientes/${idSucursal}`,
                        type: 'GET',
                    }).done(function (res) {
                        vm.traspasos_pendientes = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getTraspasosPendientesSucursal: " + textStatus, jqXHR);
                        }
                    }).always(function () {

                    });
                },
                registrarTraspaso() {
                    let vm = this;

                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/traspasos/pendientes/registrar",
                                    data: {
                                        id: vm.isEdit ? vm.traspaso_model.id : null,
                                        sucursal_origen_id: vm.filterSucursalExistencias,
                                        sucursal_destino_id: vm.traspaso_model.sucursal_id,
                                        producto_id: vm.traspaso_model.producto_id,
                                        cantidad: vm.traspaso_model.cantidad,
                                        fecha_caducidad: vm.traspaso_model.fecha_caducidad,
                                    }
                                }).done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos del traspaso se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.getInventarioSucursal(true, vm.filterSucursalExistencias);
                                        vm.getPocaExistenciaSucursal(true, vm.filterSucursalPocaExistencias);
                                        vm.getInventarioGeneral(true);
                                        $('#kt_modal_add_traspaso').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "Ocurrió un error inesperado al procesar la solicitud.",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed registrarTraspaso: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                confirmarTraspaso() {
                    let vm = this;

                    if (vm.validatorConfirm) {
                        vm.validatorConfirm.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/traspasos/save",
                                    data: {
                                        sucursal_origen_id: vm.confirmar_traspaso.sucursal_origen_id,
                                        sucursal_destino_id: vm.confirmar_traspaso.sucursal_destino_id,
                                        asignado_a: vm.confirmar_traspaso.asignado_a,
                                        tipo: vm.confirmar_traspaso.tipo,
                                        comentarios: vm.confirmar_traspaso.comentarios,
                                        productos: vm.confirmar_traspaso.productos.map(item => ({
                                            producto_id: item.producto_pendiente_id,
                                            cantidad: item.cantidad ?? 0,
                                            cantidad_recibida: 0,
                                            foto: null,
                                        })),
                                    }
                                }).done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos del traspaso se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.getInventarioSucursal(true, vm.filterSucursalExistencias);
                                        $('#kt_modal_ver_traspaso').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "Ocurrió un error inesperado al procesar la solicitud.",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed confirmarTraspaso: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                deleteInventario(idInventario) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro del inventario?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            let index = vm.inventario.findIndex(item => item.id == idInventario);
                            if(index >= 0){
                                vm.$set(vm.inventario[index], 'eliminando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: "/api/inventarios/eliminar-inventario",
                                data: {
                                    inventario_id: idInventario,
                                }
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro del inventario ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.getInventarioSucursal(true, vm.filterSucursalExistencias);
                                vm.getPocaExistenciaSucursal(true, vm.filterSucursalPocaExistencias);
                                vm.getInventarioGeneral(true);
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteInventario: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.inventario.findIndex(item => item.id == idInventario);
                                if(index >= 0){
                                    vm.$set(vm.inventario[index], 'eliminando', false);
                                }
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
                modalTraspaso(item){
                    this.traspaso_model = {
                        producto_id: item.producto_id,
                        fecha_caducidad: item.fecha_caducidad,
                        producto_nombre: item.producto.nombre,
                        cantidad_existente: item.cantidad_existente,
                    };
                },
                showTraspaso(traspaso){
                    this.confirmar_traspaso = {
                        asignado_a: "8080",
                        sucursal_origen_id: this.traspasos_pendientes.sucursal_origen_id,
                        sucursal_origen_nombre: this.traspasos_pendientes.sucursal_origen_nombre,
                        sucursal_destino_id: traspaso.sucursal_destino_id,
                        sucursal_destino_nombre: traspaso.sucursal_destino_nombre,
                        productos_cantidad: traspaso.productos.reduce((sum, item) => sum + (item.cantidad ?? 0), 0),
                        productos: traspaso.productos.flatMap(item => {
                            return item.fechas.map(i => {
                                return {
                                    producto_id: item.producto_id,
                                    nombre: item.nombre,
                                    sku: item.sku,
                                    categoria: item.categoria,
                                    subcategoria: item.subcategoria,
                                    producto_pendiente_id: i.producto_pendiente_id,
                                    fecha_caducidad: i.fecha_caducidad,
                                    cantidad: i.cantidad,
                                    stock: i.stock,
                                };
                            });
                        }),
                    };
                },
                printTableExistencias(){
                    let vm = this;
                    let sucursal = vm.sucursales.find(item => item.id == vm.filterSucursalExistencias);
                    let cols = vm.$refs.existencias.columns.slice(0, vm.$refs.existencias.columns.length-1);
                    
                    var content = '<div style="font-family: Inter, Helvetica, sans-serif;">';
                    content += '<div style="padding: 0 35px;">';
                    content += '<h3 style="margin-bottom: 0;text-align: center;">Existencias por Sucursal</h3>';
                    content += `<h4 style="text-align: center;">${sucursal?.nombre ?? '-'}</h4>`;
                    content += '</div>';
                    content += '<div style="padding-bottom: 16px;padding-top: 16px;padding-right: 8px;padding-left: 8px;">';
                    content += '<table style="border-radius: 7.6px;border-collapse: collapse;border: 1px solid #e6e6e6;vertical-align: middle;width: 100%;margin-bottom: 16px;">';
                    content += '<thead>';
                    content += '<tr>';
                    cols.forEach(col => {
                        content += `<th style="padding: 8px 8px;font-weight: 600;text-transform: uppercase;font-size: 14px;text-align: center;border-bottom: 1px solid #8b8b8b;">${vm.$refs.existencias.options.headings[col]}</th>`;
                    });
                    content += '</tr>';
                    content += '</thead>';
                    content += '<tbody>';
                    vm.$refs.existencias.allFilteredData.forEach(item => {
                        content += '<tr>';
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.id}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.producto.nombre}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.producto.sku}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.cantidad_existente ?? '0'}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${vm.$options.filters.fecha(item.fecha_caducidad)}</td>`;
                        content += '</tr>';
                    });
                    content += '</tbody>';
                    content += '</table>';
                    content += '</div>';
                    content += '</div>';

                    var WinPrint = window.open('', '', 'left=0,top=0,width=970,height=660,toolbar=0,scrollbars=0,status=0');
                    WinPrint.document.write(content);
                    WinPrint.document.close();
                    WinPrint.focus();
                    WinPrint.print();
                    WinPrint.close();                    
                },
                printTableGeneral(){
                    let vm = this;
                    let sucursal = vm.sucursales.find(item => item.id == vm.filterSucursalExistencias);
                    let cols = vm.$refs.general.columns.slice();
                    
                    var content = '<div style="font-family: Inter, Helvetica, sans-serif;">';
                    content += '<div style="padding: 0 35px;"><h3 text-align: center;">Existencias Generales</h3></div>';
                    content += '<div style="padding-bottom: 16px;padding-top: 16px;padding-right: 8px;padding-left: 8px;">';
                    content += '<table style="border-radius: 7.6px;border-collapse: collapse;border: 1px solid #e6e6e6;vertical-align: middle;width: 100%;margin-bottom: 16px;table-layout: fixed;">';
                    content += '<thead>';
                    content += '<tr>';
                    cols.forEach(col => {
                        content += `<th style="padding: 8px 8px;font-weight: 600;text-transform: uppercase;font-size: ${80/cols.length}%;text-align: center;border-bottom: 1px solid #8b8b8b;">${vm.$refs.general.options.headings[col]}</th>`;
                    });
                    content += '</tr>';
                    content += '</thead>';
                    content += '<tbody>';

                    vm.$refs.general.allFilteredData.forEach(item => {
                        content += '<tr>';
                        cols.forEach(col => {
                            if(isNaN(parseInt(col))) {
                                content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item[col] ?? ''}</td>`;
                            } else {
                                let sid = parseInt(col);
                                content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.sucursales?.find((i) => i.sucursal_id == sid)?.cantidad_existente ?? ''}</td>`;
                            }
                        });
                        content += '</tr>';
                    });

                    content += '</tbody>';
                    content += '</table>';
                    content += '</div>';
                    content += '</div>';

                    var WinPrint = window.open('', '', 'left=0,top=0,width=970,height=660,toolbar=0,scrollbars=0,status=0');
                    WinPrint.document.write(content);
                    WinPrint.document.close();
                    WinPrint.focus();
                    WinPrint.print();
                    WinPrint.close();
                },
                printTablePocasExistencias(){
                    let vm = this;
                    let sucursal = vm.sucursales.find(item => item.id == vm.filterSucursalPocaExistencias);
                    let cols = vm.$refs.pocaExistencia.columns;
                    
                    var content = '<div style="font-family: Inter, Helvetica, sans-serif;">';
                    content += '<div style="padding: 0 35px;">';
                    content += '<h3 style="margin-bottom: 0;text-align: center;">Pocas Existencias por Sucursal</h3>';
                    content += `<h4 style="text-align: center;">${sucursal?.nombre ?? '-'}</h4>`;
                    content += '<div>';
                    content += '<span style="font-weight: 600;">Cantidad minima:</span>';
                    content += `<span style="padding: 0 8px;">${vm.filterCantidadMinima}</span>`;
                    content += '</div>';
                    content += '</div>';
                    content += '<div style="padding-bottom: 16px;padding-top: 16px;padding-right: 8px;padding-left: 8px;">';
                    content += '<table style="border-radius: 7.6px;border-collapse: collapse;border: 1px solid #e6e6e6;vertical-align: middle;width: 100%;margin-bottom: 16px;">';
                    content += '<thead>';
                    content += '<tr>';
                    cols.forEach(col => {
                        content += `<th style="padding: 8px 8px;font-weight: 600;text-transform: uppercase;font-size: 14px;text-align: center;border-bottom: 1px solid #8b8b8b;">${vm.$refs.pocaExistencia.options.headings[col]}</th>`;
                    });
                    content += '</tr>';
                    content += '</thead>';
                    content += '<tbody>';

                    vm.$refs.pocaExistencia.allFilteredData.forEach(item => {
                        content += '<tr>';
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.id}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.producto?.nombre ?? ''}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.producto?.sku ?? ''}</td>`;
                        content += `<td style="padding: 8px 8px;text-align: center;vertical-align: middle;border-bottom: 1px solid #8b8b8b;">${item.cantidad_existente ?? '0'}</td>`;
                        content += '</tr>';
                    });

                    content += '</tbody>';
                    content += '</table>';
                    content += '</div>';
                    content += '</div>';

                    var WinPrint = window.open('', '', 'left=0,top=0,width=970,height=660,toolbar=0,scrollbars=0,status=0');
                    WinPrint.document.write(content);
                    WinPrint.document.close();
                    WinPrint.focus();
                    WinPrint.print();
                    WinPrint.close();
                },
            },
            computed: {
                listaSucursales(){
                    return this.sucursales.map(item => ({id: item.id, text: item.nombre}));
                },
                listaInventario(){
                    let vm = this;
                    let list = vm.inventario;
                    if(vm.filterFechaExistencias){
                        list = list.filter(item => moment(item.fecha_caducidad).format('DD/MM/Y') == vm.filterFechaExistencias);
                    }
                    if(vm.filterColeccionExistencias){
                        list = list.filter( item => item.producto?.colecciones.some(col => col.id == vm.filterColeccionExistencias));
                    }
                    // if(this.filterCategoriaExistencias){
                    //     list = list.filter(item => item.producto?.categoria?.id == this.filterCategoriaExistencias);
                    // }
                    // if(this.filterSubcategoriaExistencias){
                    //     list = list.filter(item => item.producto?.subcategoria?.id == this.filterSubcategoriaExistencias);
                    // }
                    if(vm.showProductosTiendaExistencias){
                        list = list.filter(function (item) { 
                            let tags = item.producto?.extras?.find(el => el.slug == "tags");
                            return tags && tags?.valor.includes("TIENDA");
                        });
                    }
                    return list;
                },
                listaGeneral(){
                    let vm = this;
                    let list = vm.inventario_general;
                    if(vm.filterColeccionGeneral){
                        list = list.filter(item => item.colecciones.some(col => col.id == vm.filterColeccionGeneral));
                    }
                    // if(this.filterCategoriaGeneral){
                    //     list = list.filter(item => item.categoria?.id == this.filterCategoriaGeneral);
                    // }
                    // if(this.filterSubcategoriaGeneral){
                    //     list = list.filter(item => item.subcategoria?.id == this.filterSubcategoriaGeneral);
                    // }
                    if(vm.showProductosTiendaGeneral){
                        list = list.filter(function (item) { 
                            let tags = item.extras?.find(el => el.slug == "tags");
                            return tags && tags?.valor.includes("TIENDA");
                        });
                    }
                    return list;
                },
                listaPocaExistencia(){
                    let vm = this;
                    let list = vm.inventario_pocaexistencia;
                    if(vm.filterFechaPocaExistencias){
                        list = list.filter(item => moment(item.fecha_caducidad).format('DD/MM/Y') == vm.filterFechaPocaExistencias);
                    }
                    if(vm.filterColeccionPocaExistencias){
                        list = list.filter(item => item.producto?.colecciones.some(col => col.id == vm.filterColeccionPocaExistencias));
                    }
                    // if(this.filterCategoriaPocaExistencias){
                    //     list = list.filter(item => item.producto?.categoria?.id == this.filterCategoriaPocaExistencias);
                    // }
                    // if(this.filterSubcategoriaPocaExistencias){
                    //     list = list.filter(item => item.producto?.subcategoria?.id == this.filterSubcategoriaPocaExistencias);
                    // }
                    if(vm.showProductosTiendaPocaExistencias){
                        list = list.filter(function (item) { 
                            let tags = item.producto?.extras?.find(el => el.slug == "tags");
                            return tags && tags?.valor.includes("TIENDA");
                        });
                    }
                    return list;
                },
                listaColecciones() {
                    return this.colecciones.map(item => ({ id: item.id, text: item.nombre })).sort( (a, b) => a.text < b.text ? -1 : (a.text > b.text) ? 1 : 0);
                },
                listaEmpleados(){
                    return this.empleados.map(item => ({id: item.no_empleado, text: item.nombre_completo}));
                },
                // listaCategorias() {
                //     return this.categorias.map(item => ({ id: item.id, text: item.nombre }));
                // },
                // listaSubcategorias1() {
                //     return this.subcategorias_existencias.map(item => ({ id: item.id, text: item.nombre }));
                // },
                // listaSubcategorias2() {
                //     return this.subcategorias_general.map(item => ({ id: item.id, text: item.nombre }));
                // },
                // listaSubcategorias3() {
                //     return this.subcategorias_pocaexistencias.map(item => ({ id: item.id, text: item.nombre }));
                // },
                columnsGlobal(){
                    let columns = ['sku','nombre','total_existencia'];
                    this.sucursales.forEach(item => {
                        columns.push(`${item.id}`)
                    });
                    return columns;
                },
                optionsGlobal(){
                    let headings = {
                        sku: 'SKU',
                        nombre: 'Producto',
                        total_existencia: 'Total de existencias',
                    };
                    let columnsClasses = {
                        sku: 'align-middle text-center ',
                        nombre: 'align-middle text-center ',
                        total_existencia: 'align-middle text-center ',
                    };
                    let columns = [];

                    this.sucursales.forEach(item => {
                        headings[`${item.id}`] = item.nombre;
                        columnsClasses[`${item.id}`] = 'align-middle text-center ';
                        columns.push(`${item.id}`);
                    });
                    
                    return {
                        sortIcon: {
                            base: 'ms-3 fas',
                            up: 'fa-sort-asc text-gray-400',
                            down: 'fa-sort-desc text-gray-400',
                            is: 'fa-sort text-gray-400',
                        },
                        texts: {
                            count: "Mostrando {from} de {to} de {count} registros|{count} registros|Un registro",
                            first: "Primera",
                            last: "Última",
                            filterPlaceholder: "Buscar...",
                            limit: "Registros:",
                            page: "Página:",
                            noResults: "No se encontraron resultados",
                            loading: "Cargando...",
                            columns: "Columnas",
                        },
                        skin: 'table table-sm table-rounded table-striped border align-middle table-row-bordered fs-6',
                        headings: headings,
                        columnsClasses: columnsClasses,
                        sortable: ['sku', 'nombre'],
                        filterable: ['nombre', 'sku'],
                        columnsDropdown: true,
                        resizableColumns: false,
                        orderBy: {
                            column: 'sku',
                            ascending: true
                        },
                    };
                },
            },
            watch: {
                // filterCategoriaExistencias(n,o){
                //     if(n){
                //         this.getSubcategorias1(n);
                //     } else {
                //         this.subcategorias_existencias = [];
                //     }
                // },
                // filterCategoriaGeneral(n,o){
                //     if(n){
                //         this.getSubcategorias2(n);
                //     } else {
                //         this.subcategorias_general = [];
                //     }
                // },
                // filterCategoriaPocaExistencias(n,o){
                //     if(n){
                //         this.getSubcategorias3(n);
                //     } else {
                //         this.subcategorias_pocaexistencias = [];
                //     }
                // }
                confirmar_traspaso(n,o){
                    let vm = this;
                    if(n){
                        vm.$nextTick(() => {
                            if(vm.validatorConfirm) {
                                vm.validatorConfirm.destroy();
                                vm.validatorConfirm = null;
                            }
                            
                            vm.validatorConfirm = FormValidation.formValidation(
                                document.getElementById('kt_modal_ver_traspaso_form'), {
                                fields: {
                                    'tipo_traspaso': {
                                        validators: {
                                            notEmpty: {
                                                message: 'El tipo de traspaso es requerido',
                                                trim: true
                                            }
                                        }
                                    },
                                    'asignado_a': {
                                        validators: {
                                            notEmpty: {
                                                message: 'El chofer es requerido',
                                                trim: true
                                            }
                                        }
                                    },
                                },

                                plugins: {
                                    trigger: new FormValidation.plugins.Trigger(),
                                    bootstrap: new FormValidation.plugins.Bootstrap5({
                                        rowSelector: '.fv-row',
                                        eleInvalidClass: '',
                                        eleValidClass: ''
                                    })
                                }
                            });
                        });
                    } else {
                        if(vm.validatorConfirm){
                            vm.validatorConfirm.resetForm();
                        }
                    }
                }
            },
            filters: {
                fecha: function (value, usrFormat) {
                    if (!value) return '---';
                    format = !usrFormat ? 'DD/MM/Y' : usrFormat;
                    value = value.toString();
                    moment.locale('es');
                    return moment(value).format(format);
                },
            },
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
