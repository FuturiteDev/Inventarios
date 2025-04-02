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
                    <div class="card-toolbar">

                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <v-client-table v-model="traspasos" :columns="columns" :options="options">
                        <div slot="id" slot-scope="props">[[props.row.id]]</div>
                        <div slot="origen" slot-scope="props">[[props.row.sucursal_origen?.nombre]]</div>
                        <div slot="destino" slot-scope="props">[[props.row.sucursal_destino?.nombre]]</div>
                        <div slot="tipo" slot-scope="props"><div>[[ tipos.find(item => item.id == props.row.tipo)?.text ?? '--']]</div></div>
                        <div slot="estatus" slot-scope="props">[[props.row.estatus_desc]]</div>
                        <div slot="created_at" slot-scope="props">[[props.row.created_at | fecha]]</div>
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-primary btn-sm me-2" title="Ver Traspaso" data-bs-toggle="modal" data-bs-target="#kt_modal_ver_traspaso" @click="initModalTraspaso(props.row)"><i class="fas fa-eye"></i></button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger" title="Cancelar Traspaso" :disabled="loading" @click="cancelTraspaso(props.row.id)" :data-kt-indicator="props.row.cancelando ? 'on' : 'off'">
                                <span class="indicator-label"><i class="fa-solid fa-trash-alt"></i></span>
                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                            </button>
                        </div>
                    </v-client-table>
                    <!--end::Table-->
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
                traspasos:[],
                tipos: [
                    {id: 1, text: 'A otra Sucursal'},
                    {id: 2, text: 'Para cliente'},
                    {id: 3, text: 'Merma'},
                ],
                columns: ['id','origen','destino','tipo','estatus','created_at','acciones'],
                options: {
                    headings: {
                        origen: 'Sucursal de origen',
                        destino: 'Sucursal de destino',
                        created_at: 'Fecha',
                    },
                    columnsClasses: {
                        id: 'align-middle text-center px-2 ',
                        origen: 'align-middle text-center ',
                        destino: 'align-middle text-center ',
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

                vm.getTraspasos(true);
            },
            methods: {
                getTraspasos(showLoader){
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
                        url: '/api/traspasos/list',
                        type: 'GET',
                    }).done(function (res) {
                        vm.traspasos = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getTraspasos: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                getTraspasoDetalle(traspaso_id){
                    let vm = this;
                    $.ajax({
                        url: `/api/traspasos/get/${traspaso_id}`,
                        type: 'GET',
                    }).done(function (res) {
                        vm.show_traspaso = res.results;

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

                        console.log(res.results);
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getTraspasoDetalle: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;
                    });
                },
                initModalTraspaso(traspaso){
                    this.show_traspaso = traspaso;
                    this.getTraspasoDetalle(traspaso.id);
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
                            if(index >= 0){
                                vm.$set(vm.traspasos[index], 'cancelando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: `/api/traspasos/cancelar/${idTraspaso}`,
                            }).done(function(res) {
                                Swal.fire(
                                    'Traspaso cancelado',
                                    'El traspaso ha sido cancelado con éxito',
                                    'success'
                                );
                                index = vm.traspasos.findIndex(item => item.id == idTraspaso);
                                if(index >= 0){
                                    vm.$set(vm.traspasos[index], 'cancelando', false);
                                }
                                vm.$nextTick(() => {
                                    vm.getTraspasos();
                                });
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed cancelTraspaso: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.traspasos.findIndex(item => item.id == idTraspaso);
                                if(index >= 0){
                                    vm.$set(vm.traspasos[index], 'cancelando', false);
                                }
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
            },
            computed: {
                
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
