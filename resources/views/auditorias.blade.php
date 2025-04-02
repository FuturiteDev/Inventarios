@extends('erp.base')

@section('content')
    <div id="app">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content">
            <div class="card card-flush" id="content-card">
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title flex-column">
                        <h3 class="ps-2">Sucursales Auditadas</h3>
                    </div>
                    <div class="card-toolbar gap-2">
                        <div class="px-2 min-w-200px">
                            <!-- <v-select 
                                v-model="filterSucursal"
                                :options="listaSucursales"
                                data-allow-clear="false"
                                data-placeholder="Filtrar por sucursal">
                            </v-select> -->
                        </div>
                        <button class="btn btn-icon btn-secondary" @click="getAuditorias(true)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Actualizar">
                            <i class="ki-solid ki-arrows-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body py-4">
                    <v-client-table v-model="auditorias" :columns="columns" :options="options">
                        <div slot="id" slot-scope="props">[[props.row.id]]</div>
                        <div slot="sucursal" slot-scope="props">[[props.row.sucursal?.nombre]]</div>
                        <div slot="empleado" slot-scope="props">[[props.row.empleado?.nombre_completo]]</div>
                        <div slot="updated_at" slot-scope="props">[[props.row.updated_at | fecha]]</div>
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-primary" title="Ver detalle" data-bs-toggle="modal" data-bs-target="#kt_modal_show" @click="getDetalleAuditoria(props.row.id)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </v-client-table>
                </div>
            </div>
        </div>

        <!--begin::Modal - Add traspaso-->
        <div class="modal fade" id="kt_modal_show" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content" id="modal_show_content">
                    <div class="modal-header">
                        <h2 class="fw-bold">Historial de auditoría - [[auditoria_model?.id]]</h2>
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                    </div>
                    <div class="modal-body" v-if="auditoria_model">
                        <div class="row g-4">
                            <div class="col-6">
                                <label class="fs-7 fw-bold">Sucursal</label>
                                <div>[[auditoria_model.sucursal.nombre]]</div>
                            </div>
                            <div class="col-6" v-if="auditoria_model.empleado">
                                <label class="fs-7 fw-bold">Empleado</label>
                                <div><span class="fw-semibold">[[auditoria_model.empleado.no_empleado]]</span> - [[auditoria_model.empleado.nombre_completo]]</div>
                            </div>
                        </div>
                        <div class="mb-8">
                            <v-client-table v-model="auditoria_model.revision_productos" :columns="columns_productos" :options="options_productos">
                                <div slot="producto" slot-scope="props">[[props.row.producto?.nombre]]</div>
                                <div slot="sku" slot-scope="props">[[props.row.producto?.sku]]</div>
                                <div slot="existencia_actual" slot-scope="props">[[props.row.existencia_actual]]</div>
                                <div slot="existencia_real" slot-scope="props">[[props.row.existencia_real]]</div>
                                <div slot="imagen" slot-scope="props">
                                    <img :src="props.row.imagen" class="mw-100 mh-250px"/>
                                </div>
                            </v-client-table>
                        </div>
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
                auditorias: [],
                sucursales: [],
                colecciones: [],
                empleados: [],
                tipo_traspasos: [
                    {id: 1, text: 'A otra Sucursal'},
                    {id: 2, text: 'Para cliente'},
                    {id: 3, text: 'Merma'},
                ],
                columns: ['id','sucursal','empleado','updated_at','acciones'],
                options: {
                    headings: {
                        id: 'ID',
                        sucursal: 'Sucursal',
                        empleado: 'Empleado',
                        updated_at: 'Fecha de realización',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        id: 'align-middle text-center px-2 ',
                        sucursal: 'align-middle text-center ',
                        empleado: 'align-middle text-center ',
                        updated_at: 'align-middle text-center ',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['id', 'sucursal','empleado','updated_at'],
                    orderBy: {
                        column: 'updated_at',
                        ascending: false
                    },
                    filterable: ['sucursal', 'empleado'],
                    skin: 'table table-sm table-rounded table-striped table-row-bordered',
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
                        sucursal(row, query) {
                            let value = row.sucursal?.nombre?.toLowerCase();
                            return value.includes(query.toLowerCase());
                        },
                        empleado(row, query) {
                            let value = row.empleado?.nombre_completo?.toLowerCase();
                            return value?.includes(query.toLowerCase());
                        },
                    },
                },
                columns_productos: ['producto', 'sku', 'existencia_actual', 'existencia_real', 'imagen'],
                options_productos: {
                    headings: {
                        producto: 'Producto',
                        sku: 'SKU',
                        existencia_actual: 'Existencia Actual',
                        existencia_real: 'Existencia Real',
                        imagen: 'Imagen',
                    },
                    columnsClasses: {
                        producto: 'align-middle text-center ',
                        sku: 'align-middle text-center ',
                        existencia_actual: 'align-middle text-center ',
                        existencia_real: 'align-middle text-center ',
                        imagen: 'align-middle text-center ',
                    },
                    sortable: ['producto'],
                    filterable: ['producto', 'sku'],
                    skin: 'table table-sm table-rounded table-striped table-row-bordered',
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
                        producto(row, query) {
                            let value = row.producto?.nombre?.toLowerCase();
                            return value.includes(query.toLowerCase());
                        },
                        sku(row, query) {
                            let value = row.producto?.sku?.toLowerCase();
                            return value?.includes(query.toLowerCase());
                        },
                    },
                },

                auditoria_model: null,
                loading: false,
                blockUI: null,
                blockUIModal: null,
            }),
            mounted() {
                let vm = this;
                vm.$forceUpdate();

                let container = document.querySelector('#content-card');
                if (container) {
                    vm.blockUI = new KTBlockUI(container);
                }
                container = document.querySelector('#modal_show_content');
                if (container) {
                    vm.blockUIModal = new KTBlockUI(container);
                }

                $("#kt_modal_show").on('hidden.bs.modal', event => {
                    vm.auditoria_model = null;
                });

                vm.getSucursales();
                vm.getEmpleados();
                vm.getColecciones();
                vm.getAuditorias(true);
            },
            methods: {
                getSucursales() {
                    let vm = this;
                    $.get(
                        '/api/sucursales/all',
                        res => {
                            vm.sucursales = res.results;
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
                getAuditorias(showLoader){
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

                    $.ajax({
                        url: '/api/inventarios/revisiones',
                        type: 'GET',
                    }).done(function (res) {
                        vm.auditorias = res.message;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getAuditorias: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                getDetalleAuditoria(idAuditoria){
                    let vm = this;
                    if (!vm.blockUIModal) {
                        let container = document.querySelector('#modal_show_content');
                        if (container) {
                            vm.blockUIModal = new KTBlockUI(container);
                            vm.blockUIModal.block();
                        }
                    } else {
                        if (!vm.blockUIModal.isBlocked()) {
                            vm.blockUIModal.block();
                        }
                    }

                    $.ajax({
                        url: `/api/inventarios/revisiones-detalles/${idAuditoria}`,
                        type: 'GET',
                    }).done(function (res) {
                        vm.auditoria_model = res.message;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getDetalleAuditoria: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        if (vm.blockUIModal && vm.blockUIModal.isBlocked()) {
                            vm.blockUIModal.release();
                        }
                    });
                },
            },
            computed: {
                listaSucursales(){
                    return this.sucursales.map(item => ({id: item.id, text: item.nombre}));
                },
                listaColecciones() {
                    return this.colecciones.map(item => ({ id: item.id, text: item.nombre }));
                },
                listaEmpleados(){
                    return this.empleados.map(item => ({id: item.no_empleado, text: item.nombre_completo}));
                },
            },
            watch: {
            },
            filters: {
                fecha: function (value, usrFormat, ogFormat) {
                    if (!value) return "---";
                    format = !usrFormat ? "DD/MM/Y HH:mm:ss" : usrFormat;
                    value = value.toString();
                    moment.locale("es");
                    return ogFormat ? moment(value, ogFormat).format(format) : moment(value).format(format);
                },
            },
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
