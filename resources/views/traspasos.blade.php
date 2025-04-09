@extends('erp.base')

@section('content')
    <div id="app">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content">
            <!--begin::Card-->

            <div class="card card-flush" id="content-card">
                <!--begin::Card header-->
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title flex-column">
                        <h3 class="ps-2">Traspasos</h3>
                    </div>
                    <div class="card-toolbar gap-2">
                        <div class="min-w-200px me-5">
                            <v-select
                                v-model="filterEstatus"
                                :options="estatus"
                                data-allow-clear="false"
                                data-placeholder="Filtrar por estatus">
                            </v-select>
                        </div>
                        <div class="min-w-200px">
                            <div class="input-group">
                                <input class="form-control form-control-sm" id="frmPeriodo" placeholder="Periodo" />
                                <span class="input-group-text"><i class="fas fa-calendar fs-4"></i></span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-icon btn-primary" @click="getTraspasos(true)">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body">
                    <ul class="mt-5 nav nav-fill nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" v-for="(sucursal,index) in sucursales">
                            <a class="nav-link" :class="{'active':index==0}" data-bs-toggle="tab" :href="'#sucursal'+sucursal.id" role="tab">[[sucursal.nombre]]</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <!--begin::Tab-->
                        <div class="tab-pane fade" :class="{'show active':index==0}" :id="'sucursal'+sucursal.id" role="tabpanel" v-for="(sucursal,index) in sucursales">
                            <!--begin::Table-->
                            <v-client-table v-model="listaTraspasosGroup[sucursal.id]" :columns="columns" :options="options" v-if="listaTraspasosGroup[sucursal.id]">
                                <div slot="id" slot-scope="props">[[props.row.id]]</div>
                                <div slot="sucursal_origen" slot-scope="props">[[props.row.sucursal_origen?.nombre]]</div>
                                <div slot="sucursal_destino" slot-scope="props">[[props.row.sucursal_destino?.nombre]]</div>
                                <div slot="tipo" slot-scope="props">
                                    <div>[[ tipos.find(item => item.id == props.row.tipo)?.text ?? '--']]</div>
                                </div>
                                <div slot="estatus" slot-scope="props">[[props.row.estatus_desc]]</div>
                                <div slot="created_at" slot-scope="props">[[props.row.created_at | fecha]]</div>
                                <div slot="acciones" slot-scope="props">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-icon btn-sm btn-primary" title="Ver Traspaso" data-bs-toggle="modal" data-bs-target="#kt_modal_ver_traspaso" @click="initModalTraspaso(props.row)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-info" title="Recibir Traspaso" data-bs-toggle="modal" data-bs-target="#kt_modal_recibir_traspaso" @click="initModalTraspaso(props.row)" v-if="sucursal.matriz == 1">
                                            <i class="fa-solid fa-truck-arrow-right"></i></span>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-danger" title="Cancelar Traspaso" :disabled="loading" @click="cancelTraspaso(props.row.id)" :data-kt-indicator="props.row.cancelando ? 'on' : 'off'">
                                            <span class="indicator-label"><i class="fa-solid fa-trash-alt"></i></span>
                                            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </v-client-table>
                            <!--end::Table-->
                        </div>
                        <!--end::Tab-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content-->

        <!--begin::Modal - Show task-->
        <div class="modal fade" id="kt_modal_ver_traspaso" tabindex="-1" aria-hidden="true">
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
                    <div class="modal-body" v-if="show_traspaso">
                        <div class="row g-7 mb-7">
                            <div class="col-6">
                                <label class="fs-6 fw-bold">ID</label>
                                <div>[[show_traspaso.id]]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Fecha</label>
                                <div>[[show_traspaso.created_at | fecha]]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Sucursal origen</label>
                                <div>[[show_traspaso.sucursal_origen?.nombre]]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Sucursal destino</label>
                                <div>[[show_traspaso.sucursal_destino?.nombre]]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Tipo de traspaso</label>
                                <div>[[ tipos.find(item => item.id == show_traspaso.tipo)?.text ?? '--']]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Estatus de traspaso</label>
                                <div>[[show_traspaso.estatus_desc]]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Empleado</label>
                                <div>[[show_traspaso.empleado?.nombre_completo ?? '--']]</div>
                                <div class="text-muted">[[show_traspaso.empleado?.no_empleado ?? '--']]</div>
                            </div>
                            <div class="col-6">
                                <label class="fs-6 fw-bold">Empleado asignado</label>
                                <div>[[show_traspaso.empleado_asignado?.nombre_completo ?? '--']]</div>
                                <div class="text-muted">[[show_traspaso.empleado_asignado?.no_empleado ?? '--']]</div>
                            </div>
                            <div class="col-12" v-if="show_traspaso.productos">
                                <label class="fs-6 fw-bold">Productos</label>
                                <table class="border border-1 no-footer table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle">Producto</th>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle">SKU</th>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle">Fecha de caducidad</th>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle">Cantidad</th>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle">Cantidad Recibida</th>
                                            <th tabindex="0" class="VueTables__heading text-center align-middle"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="align-middle" v-for="prod in show_traspaso.productos" :key="'prod_' + prod.id">
                                            <td class="text-center align-middle">[[prod.nombre ?? '']]</td>
                                            <td class="text-center align-middle">[[prod.sku ?? '']]</td>
                                            <td class="text-center align-middle">[[prod.fecha_caducidad | fecha]]</td>
                                            <td class="text-center align-middle">[[prod.cantidad ?? '']]</td>
                                            <td class="text-center align-middle">[[prod.cantidad_recibida ?? '']]</td>
                                            <td class="text-center align-middle">
                                                <a class="btn btn-primary btn-icon btn-sm" href="prod.foto" target="_blank"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12">
                                <label class="fs-6 fw-bold">Comentarios</label>
                                <div>[[show_traspaso.comentarios]]</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Show task-->

        <!--begin::Modal - Show task-->
        <div class="modal fade" id="kt_modal_recibir_traspaso" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <h2 class="fw-bold">Recibir Traspaso - [[recibir_traspaso?.id ?? '']]</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body" v-if="recibir_traspaso">
                        <form id="kt_modal_recibir_traspaso_form" class="form" action="#" @submit.prevent="">
                            <div class="row g-7 mb-7">
                                <div class="col-6">
                                    <label class="fs-6 fw-bold">Sucursal origen</label>
                                    <div>[[recibir_traspaso.sucursal_origen?.nombre]]</div>
                                </div>
                                <div class="col-6">
                                    <label class="fs-6 fw-bold">Sucursal destino</label>
                                    <div>[[recibir_traspaso.sucursal_destino?.nombre]]</div>
                                </div>
                                <div class="col-6">
                                    <label class="fs-6 fw-bold">Tipo de traspaso</label>
                                    <div>[[ tipos.find(item => item.id == recibir_traspaso.tipo)?.text ?? '--']]</div>
                                </div>
                                <div class="col-6">
                                    <label class="fs-6 fw-bold">Estatus de traspaso</label>
                                    <div>[[recibir_traspaso.estatus_desc]]</div>
                                </div>
                                <div class="col-6">
                                    <label class="fs-6 fw-bold">Empleado asignado</label>
                                    <div>
                                        <span class="fw-bold">[[recibir_traspaso.empleado_asignado?.no_empleado ?? '--']]</span>
                                        <span> - [[recibir_traspaso.empleado_asignado?.nombre_completo ?? '--']]</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="fs-6 fw-bold">Productos</label>
                                    <table class="border border-1 no-footer table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Producto</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Fecha de caducidad</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Cantidad</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="align-middle" v-for="producto in recibir_traspaso.productos" :key="'producto_' + producto.id">
                                                <td>
                                                    <div>[[producto.nombre]]</div>
                                                    <div class="text-muted">[[producto.sku]]</div>
                                                </td>
                                                <td>[[producto.fecha_caducidad | fecha]]</td>
                                                <td>
                                                    <span class="fv-row">
                                                        <input type="number" v-model="producto.cantidad" class="form-control" placeholder="Cantidad" :name="`p_cantidad_${producto.id}`">
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-bold">Comentarios</label>
                                    <textarea class="form-control" rows="3" id="comentarios" name="comentarios" v-model="recibir_traspaso.comentarios"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer" v-if="recibir_traspaso">
                        <button type="button" class="btn btn-primary" :disabled="loading" @click="recibirTraspaso" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label">Recibir</span>
                            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Show task-->

    </div>
@endsection

@section('scripts')
    <script src="/common_assets/js/vue-tables-2.min.js"></script>
    <script src="/common_assets/js/vue_components/v-select.js"></script>
    <script src="/common_assets/js/vue_components/v-file.js"></script>

    <script>
        const app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data: () => ({
                sesion: {!! Auth::user() !!},
                estatus: [
                    { id: '1', text: 'En proceso' },
                    { id: '2', text: 'Finalizado' },
                ],
                traspasos: [],
                tipos: [
                    { id: 1, text: 'A otra Sucursal' },
                    { id: 2, text: 'Para cliente' },
                    { id: 3, text: 'Merma' },
                ],
                sucursales: [],
                columns: ['id', 'sucursal_origen', 'sucursal_destino', 'tipo', 'estatus', 'created_at', 'acciones'],
                options: {
                    headings: {
                        sucursal_origen: 'Sucursal de origen',
                        sucursal_destino: 'Sucursal de destino',
                        created_at: 'Fecha',
                    },
                    columnsClasses: {
                        id: 'align-middle text-center px-2 ',
                        sucursal_origen: 'align-middle text-center ',
                        sucursal_destino: 'align-middle text-center ',
                        tipo: 'align-middle text-center ',
                        estatus: 'align-middle text-center ',
                        fecha: 'align-middle text-center ',
                        acciones: 'align-middle text-center px-2',
                    },
                    sortable: ['id', 'fecha'],
                    filterable: false,
                    skin: 'table table-sm table-rounded table-striped border align-middle table-row-bordered fs-6',
                    columnsDropdown: false,
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
                },

                traspaso_model: {},
                show_traspaso: {},
                recibir_traspaso: null,

                filterFechaInicio: null,
                filterFechaFin: null,
                filterEstatus: '1',

                validator: null,
                loading: false,
                blockUI: null,
                requestGet: null,
            }),
            mounted() {
                let vm = this;
                vm.$forceUpdate();

                let container = document.querySelector('#content-card');
                if (container) {
                    vm.blockUI = new KTBlockUI(container);
                }

                vm.initDaterangepicker();
                vm.getSucursales();
            },
            methods: {
                initDaterangepicker() {
                    var vm = this;
                    var input = $("#frmPeriodo");

                    input.daterangepicker({
                        autoUpdateInput: false,
                        locale: {
                            cancelLabel: 'Limpiar',
                            applyLabel: 'Aplicar',
                            customRangeLabel: 'Personalizado'
                        },
                        ranges: {
                            "7 Dias": [moment().subtract(6, "days"), moment()],
                            "15 Dias": [moment().subtract(14, "days"), moment()],
                            "30 Dias": [moment().subtract(29, "days"), moment()],
                            "Este mes": [moment().startOf("month"), moment().endOf("month")],
                            "Mes anterior": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
                        }
                    });

                    input.on('apply.daterangepicker', function (ev, picker) {
                        input.val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));

                        let startDate = picker.startDate.clone();
                        let endDate = picker.endDate.clone();

                        vm.filterFechaInicio = startDate.startOf('day').format('YYYY-MM-DD HH:mm:ss');
                        vm.filterFechaFin = endDate.endOf('day').format('YYYY-MM-DD HH:mm:ss');
                    });
                    input.on('cancel.daterangepicker', function (ev, picker) {
                        input.val('');
                        vm.filterFechaInicio = '';
                        vm.filterFechaFin = '';
                    });

                    // Initial values
                    let initialInicio = moment().subtract(6, "days").startOf('day');
                    let initialFin = moment().endOf('day');
                    input.val(initialInicio.format('DD/MM/YYYY') + ' - ' + initialFin.format('DD/MM/YYYY'));
                    vm.filterFechaInicio = initialInicio.format('YYYY-MM-DD HH:mm:ss');
                    vm.filterFechaFin =initialFin.format('YYYY-MM-DD HH:mm:ss');

                    vm.getTraspasos(true);
                },
                initModalTraspaso(traspaso) {
                    this.show_traspaso = traspaso;
                    this.recibir_traspaso = null;
                    this.getTraspasoDetalle(traspaso.id);
                },
                getSucursales() {
                    let vm = this;
                    $.get(
                        '/api/sucursales/all',
                        res => {
                            vm.sucursales = res.results;
                        }, 'json'
                    );
                },
                initFormValidate() {
                    let vm = this;
                    if(vm.validator) {
                        vm.validator.destroy();
                        vm.validator = null;
                    }
                    
                    vm.validator = FormValidation.formValidation(
                        document.getElementById('kt_modal_recibir_traspaso_form'), {
                            fields: {
                                'comentarios': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Campo requerido',
                                            trim: true
                                        }
                                    },
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

                    vm.recibir_traspaso.productos.forEach((item, index) => {
                        vm.validator.addField(('p_cantidad_' + item.id), {
                            validators: {
                                notEmpty: {
                                    message: 'La cantidad es requerida',
                                    trim: true
                                },
                                greaterThan: {
                                    message: 'Cantidad invalida',
                                    min: 0
                                }
                            }
                        });
                    });

                },
                getTraspasos(showLoader) {
                    let vm = this;
                    if (showLoader) {
                        if (!vm.blockUI) {
                            let container = document.querySelector('#content-card');
                            if (container) {
                                vm.blockUI = new KTBlockUI(container);
                                vm.blockUI.block();
                            }
                        } else {
                            if (!vm.blockUI.isBlocked()) {
                                vm.blockUI.block();
                            }
                        }
                    }

                    if (vm.requestGet) {
                        vm.requestGet.abort();
                        vm.requestGet = null;
                    }

                    vm.loading = true;
                    vm.requestGet = $.ajax({
                        url: "/api/traspasos/list",
                        type: "POST",
                        data: {
                            fecha_inicio: vm.filterFechaInicio,
                            fecha_fin: vm.filterFechaFin,
                            estatus: vm.filterEstatus,
                        }
                    }).done(function (res) {
                        let results = res.results ?? [];
                        vm.traspasos = results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != "abort") {
                            console.log("Request failed getTraspasos: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                getTraspasoDetalle(traspaso_id) {
                    let vm = this;
                    $.ajax({
                        url: `/api/traspasos/get/${traspaso_id}`,
                        type: 'GET',
                    }).done(function (res) {
                        vm.show_traspaso = res.results;
                        vm.recibir_traspaso = {
                            id: res.results.id,
                            sucursal_origen: res.results.sucursal_origen,
                            sucursal_destino: res.results.sucursal_destino,
                            estatus_desc: res.results.estatus_desc,
                            empleado_asignado: res.results.empleado_asignado,
                            tipo: res.results.tipo,
                            productos: res.results.detalle
                            .flatMap(item => item.fechas
                                .map(i => ({
                                    id: i.id,
                                    nombre: item.nombre,
                                    sku: item.sku,
                                    fecha_caducidad: i.fecha_caducidad,
                                    cantidad: i.cantidad,
                                }))
                            ),
                            comentarios: null,
                        }

                        vm.show_traspaso.productos = res.results.detalle
                            .flatMap(item => item.fechas
                                .map(i => ({
                                    nombre: item.nombre,
                                    sku: item.sku,
                                    id: i.id,
                                    fecha_caducidad: i.fecha_caducidad,
                                    cantidad: i.cantidad,
                                    cantidad_recibida: i.cantidad_recibida,
                                }))
                            );
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getTraspasoDetalle: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;
                    });
                },
                recibirTraspaso() {
                    let vm = this;
                    vm.initFormValidate();

                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    url: '/api/traspasos/recibir',
                                    type: 'POST',
                                    data: {
                                        traspaso_id: vm.recibir_traspaso.id,
                                        comentarios: vm.recibir_traspaso.comentarios,
                                        productos: vm.recibir_traspaso.productos
                                        .map(i => ({
                                            id: i.id,
                                            cantidad_recibida: i.cantidad,
                                        })),
                                    }
                                }).done(function (res) {
                                    Swal.fire(
                                        'Traspaso recibido',
                                        'El traspaso ha sido recibido con éxito',
                                        'success'
                                    );
                                    vm.getTraspasos();
                                    $('#kt_modal_recibir_traspaso').modal('hide');
                                }).fail(function (jqXHR, textStatus) {
                                    if (textStatus != 'abort') {
                                        console.log("Request failed getTraspasoDetalle: " + textStatus, jqXHR);
                                    }
                                }).always(function () {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                cancelTraspaso(idTraspaso) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas cancelar el traspaso?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, continuar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            let index = vm.traspasos.findIndex(item => item.id == idTraspaso);
                            if (index >= 0) {
                                vm.$set(vm.traspasos[index], 'cancelando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: `/api/traspasos/cancelar/${idTraspaso}`,
                            }).done(function (res) {
                                Swal.fire(
                                    'Traspaso cancelado',
                                    'El traspaso ha sido cancelado con éxito',
                                    'success'
                                );
                                index = vm.traspasos.findIndex(item => item.id == idTraspaso);
                                if (index >= 0) {
                                    vm.$set(vm.traspasos[index], 'cancelando', false);
                                }
                                vm.$nextTick(() => {
                                    vm.getTraspasos();
                                });
                            }).fail(function (jqXHR, textStatus) {
                                console.log("Request failed cancelTraspaso: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.traspasos.findIndex(item => item.id == idTraspaso);
                                if (index >= 0) {
                                    vm.$set(vm.traspasos[index], 'cancelando', false);
                                }
                            }).always(function (event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
            },
            computed: {
                listaTraspasosGroup() {
                    let vm = this;
                    if (vm.traspasos && vm.traspasos.length > 0) {
                        let list = vm.traspasos;
                        var obj = {};

                        vm.sucursales.forEach(el => {
                            obj[el.id] = vm.traspasos.filter(item => item.sucursal_destino_id == el.id);
                        });
                        return obj;
                    } else {
                        return {};
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
