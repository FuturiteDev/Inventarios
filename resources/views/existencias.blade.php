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
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="inventarioSucursal" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card card-flush" id="content-card-sucursal">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title flex-column">
                                <h3 class="ps-2">Existencias por Sucursal</h3>
                            </div>
                            <div class="card-toolbar">
                                <div class="px-2 min-w-200px">
                                    <v-select 
                                        v-model="sucursalFilter"
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
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body py-4">
                            <div class="px-2 mb-5 mw-300px">
                                <div class="input-group" id="datepicker_wrapper">
                                    <span class="input-group-text">
                                        <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <input id="datepicker_input" type="text" class="form-control border-right-0" placeholder="Fecha de caducidad" v-model="fechaFilter"/>
                                    <span class="bg-white border-left-0 input-group-text">
                                        <button type="button" class="btn-close" id="datepicker_clear"></button>
                                    </span>
                                </div>
                            </div>
                            <!--begin::Table-->
                            <v-client-table v-model="listaInventario" :columns="columns" :options="options">
                                <div slot="nombre" slot-scope="props">[[props.row.producto.nombre]]</div>
                                <div slot="sku" slot-scope="props">[[props.row.producto.sku]]</div>
                                <div slot="cantidad" slot-scope="props">[[props.row.cantidad_existente]]</div>
                                <div slot="fecha_caducidad" slot-scope="props">[[props.row.fecha_caducidad | fecha]]</div>
                                <div slot="acciones" slot-scope="props">
                                    <button type="button" class="btn btn-icon btn-sm btn-primary btn-sm me-2" title="Agregar a Traspaso" data-bs-toggle="modal" data-bs-target="#kt_modal_add_traspaso" @click="modalTraspaso(props.row)"><i class="fa-solid fa-truck-fast"></i></button>
                                    <button type="button" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Inventario" :disabled="loading" @click="deleteInventario(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                        <span class="indicator-label"><i class="fas fa-trash-alt"></i></i></span>
                                        <span class="indicator-progress"><i class="fas fa-trash-alt"></i><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                    </button>
                                </div>
                            </v-client-table>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <div class="tab-pane fade" id="inventarioGeneral" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card card-flush" id="content-card-general">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title flex-column">
                                <h3 class="ps-2">Existencias Generales</h3>
                            </div>
                            <div class="card-toolbar">
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body py-4" v-if="sucursales && sucursales.length>0">
                            <!--begin::Table-->
                            <v-client-table v-model="inventario_general" :columns="columnsGlobal" :options="optionsGlobal">
                                <div v-for="item in sucursales" :slot="item.id" slot-scope="props">
                                    [[props.row.sucursales.find((i) => i.sucursal_id == item.id)?.cantidad_existente ?? '']]
                                </div>
                            </v-client-table>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
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
                inventario:[],
                sucursales: [],
                inventario_general: [],
                columns: ['id','nombre','sku','cantidad','fecha_caducidad','acciones'],
                options: {
                    headings: {
                        id: 'ID',
                        sku: 'SKU',
                        nombre: 'Producto',
                        cantidad: 'Cantidad',
                        fecha_caducidad: 'Fecha de caducidad',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        id: 'align-middle px-2 ',
                        sku: 'align-middle text-center ',
                        nombre: 'align-middle ',
                        cantidad: 'align-middle text-center ',
                        fecha_caducidad: 'align-middle text-center ',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['sku', 'fecha_caducidad'],
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
                            let value = row.producto.nombre.toLowerCase();
                            return value.includes(query.toLowerCase());
                        },
                        sku(row, query) {
                            let value = row.producto.sku?.toLowerCase();
                            return value?.includes(query.toLowerCase());
                        },
                    },
                },

                sucursalFilter: null,
                fechaFilter: null,

                traspaso_model: {},

                validator: null,
                loading: false,
                blockUISucursal: null,
                blockUIGeneral: null,
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

                $("#kt_modal_add_traspaso").on('hidden.bs.modal', event => {
                    vm.validator.resetForm();
                    vm.traspaso_model = {};
                });

                vm.formValidate();
                vm.getSucursales();
                vm.getInventarioGeneral();
            },
            methods: {
                getSucursales() {
                    let vm = this;
                    $.get(
                        '/api/sucursales/all',
                        res => {
                            vm.sucursales = res.results;
                            vm.$nextTick(() => {
                                vm.sucursalFilter = res.results[0].id;
                                vm.getInventarioSucursal(true, res.results[0].id);
                            });
                        }, 'json'
                    );
                },
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

                    vm.loading = true;

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
                        vm.loading = false;

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

                    vm.loading = true;

                    if(idSucursal){
                        vm.sucursalFilter = idSucursal;
                    }
                    $.ajax({
                        url: '/api/inventarios/existencia-sucursal',
                        type: 'POST',
                        data: {
                            sucursal_id: idSucursal ?? vm.sucursalFilter
                        }
                    }).done(function (res) {
                        vm.inventario = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getInventarioSucursal: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUISucursal && vm.blockUISucursal.isBlocked()) {
                            vm.blockUISucursal.release();
                        }
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
                                        sucursal_origen_id: vm.sucursalFilter,
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
                                        vm.getInventarioSucursal(true, vm.sucursalFilter);
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
                                vm.getInventarioSucursal(true, vm.sucursalFilter);
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
                modalTraspaso(producto){
                    this.traspaso_model = {
                        producto_id: producto.producto_id,
                        fecha_caducidad: producto.fecha_caducidad,
                        producto_nombre: producto.producto.nombre,
                        cantidad_existente: producto.cantidad_existente,
                    };
                },
                formValidate() {
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
            },
            computed: {
                listaSucursales(){
                    return this.sucursales.map(item => ({id: item.id, text: item.nombre}));
                },
                listaInventario(){
                    if(this.fechaFilter){
                        return this.inventario.filter(item => moment(item.fecha_caducidad).format('DD/MM/Y') == this.fechaFilter);
                    } else {
                        return this.inventario;
                    }
                },
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

                    console.log(headings);
                    
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
                    };
                },
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
