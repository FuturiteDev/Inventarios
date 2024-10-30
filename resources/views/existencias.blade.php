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
                        <h3 class="ps-2">Inventario</h3>
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
                        <button type="button" class="btn btn-icon btn-primary" @click="getInventario(true)">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <div class="d-flex mb-5 justify-content-end">
                        <div class="px-2">
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_inventario">
                            <i class="ki-outline ki-plus fs-2"></i> Agregar Inventario
                        </button>
                    </div>
                    <!--begin::Table-->
                    <v-client-table v-model="listaInventario" :columns="columns" :options="options">
                        <div slot="nombre" slot-scope="props">[[props.row.producto.nombre]]</div>
                        <div slot="sku" slot-scope="props">[[props.row.producto.sku]]</div>
                        <div slot="cantidad" slot-scope="props">[[props.row.cantidad_existente]]</div>
                        <div slot="fecha_caducidad" slot-scope="props">[[props.row.fecha_caducidad | fecha]]</div>
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Inventario" :disabled="loading" @click="deleteInventario(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                <span class="indicator-label"><i class="fas fa-trash-alt"></i></span>
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

        <!--begin::Modal - Add task-->
        <div class="modal fade" id="kt_modal_add_inventario" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold">Agregar Inventario</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body">
                        <!--begin::Form-->
                        <form id="kt_modal_add_inventario_form" class="form" action="#" @submit.prevent="">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Sucursal</label>
                                <v-select 
                                    v-model="inventario_model.sucursal_id"
                                    :options="listaSucursales"
                                    name="sucursal"
                                    data-allow-clear="false"
                                    data-placeholder="Seleccionar sucursal">
                                </v-select>
                            </div>

                            <div class="mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Productos</label>
                                <div class="d-flex mb-5 p-5">
                                    <div class="fv-row min-w-25">
                                        <v-select
                                            v-model="selected_producto"
                                            :options="listaProductos"
                                            name="productos"
                                            data-allow-clear="true"
                                            data-placeholder="Agregar producto">
                                        </v-select>
                                    </div>
                                    <button type="button" class="btn btn-icon btn-light-success border border-success ms-5" @click="addProducto"><i class="fa-solid fa-plus"></i></button>
                                </div>

                                <label class="fw-semibold fs-7 ms-2">Productos agregados</label>
                                <table class="table table-row-dashed table-row-gray-300 no-footer" v-if="inventario_model.productos.length>0">
                                    <thead class="d-none">
                                        <tr>
                                            <th tabindex="0"></th>
                                            <th tabindex="0"></th>
                                            <th tabindex="0"></th>
                                            <th tabindex="0"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="p in inventario_model.productos" :key="'p_' + p.id">
                                            <td>
                                                <div v-text="p.sku" class="form-control form-control-solid"></div>
                                            </td>
                                            <td>
                                                <span class="fv-row">
                                                    <input type="number" v-model="p.cantidad" class="form-control" placeholder="Cantidad" :name="`p_cantidad_${p.id}`">
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fv-row">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Fecha de caducidad" v-model="p.fecha_caducidad" :name="`p_fecha_${p.id}`" :id="`p_fecha_${p.id}`"/>
                                                        <span class="input-group-text">
                                                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                        </span>
                                                    </div>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-icon btn-danger" @click="removeProducto(p.id)"><i class="fa-solid fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="addInventario" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
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
                productos: [],
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
                selected_producto: null,
                inventario_datepickers: [],

                inventario_model: {
                    sucursal_id: null,
                    productos: [],
                },

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
                $("#kt_modal_add_inventario").on('hidden.bs.modal', event => {
                    vm.validator.resetForm();
                    vm.inventario_datepickers.forEach(el => {
                        el.destroy();
                    });
                    vm.inventario_datepickers = [];

                    vm.inventario_model = {
                        sucursal_id: null,
                        productos: [],
                    };
                });

                $("#kt_modal_add_inventario").on('shown.bs.modal', event => {
                    vm.formValidate();
                });
                let picker = $("#datepicker_input").flatpickr({
                    dateFormat: "d/m/Y"
                });
                $( "#datepicker_clear" ).on( "click", function() {
                    picker.clear();
                } );

                vm.getSucursales(vm.sesion.sucursal);
                vm.getProductos();
                vm.getInventario(true, vm.sesion.sucursal);
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
                getProductos() {
                    let vm = this;
                    $.get(
                        '/api/productos/all',
                        res => {
                            vm.productos = res.results;
                        }, 'json'
                    );
                },
                getInventario(showLoader, idSucursal){
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

                    if(idSucursal){
                        vm.sucursalFilter = idSucursal;
                    }
                    vm.requestGet = $.ajax({
                        url: '/api/inventarios/existencia-sucursal',
                        type: 'POST',
                        data: {
                            sucursal_id: idSucursal ?? vm.sucursalFilter
                        }
                    }).done(function (res) {
                        vm.inventario = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getInventario: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                addInventario() {
                    let vm = this;
                    vm.formValidate();

                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/inventarios/agregar-inventario",
                                    data: vm.inventario_model
                                }).done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos del inventario se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.getInventario(true, vm.inventario_model.sucursal_id);
                                        $('#kt_modal_add_inventario').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "Ocurrió un error inesperado al procesar la solicitud.",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed addInventario: " + textStatus, jqXHR);
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
                                vm.getInventario();
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
                addProducto(){
                    let vm = this;
                    if(!vm.inventario_model.productos.some(item => item.id == vm.selected_producto)){
                        let producto = vm.productos.find(item => item.id == vm.selected_producto);
                        if(producto){
                            vm.inventario_model.productos.push({
                                id: producto.id,
                                sku: producto.sku,
                                cantidad: null,
                                fecha_caducidad: null,
                            });

                            vm.$nextTick(() => {
                                let picker = $(`#p_fecha_${producto.id}`).flatpickr({
                                    dateFormat: "d/m/Y"
                                });
                                vm.inventario_datepickers.push(picker);
                            });
                        }
                    }
                    vm.selected_producto = null;
                },
                removeProducto(idProducto){
                    let vm = this;
                    let index = vm.inventario_model.productos.findIndex(item => item.id == idProducto);
                    if(index != -1){
                        vm.$delete(vm.inventario_model.productos, index);

                        vm.$nextTick(() => {
                            let ind = vm.inventario_datepickers.findIndex(item => item.element.id == `p_fecha_${idProducto}`);
                            if(ind != -1) {
                                vm.inventario_datepickers[ind].destroy();
                                vm.$delete(vm.inventario_datepickers, ind);
                            }
                        });
                    }
                },
                formValidate() {
                    let vm = this;
                    if(vm.validator) {
                        vm.validator.destroy();
                        vm.validator = null;
                    }
                    
                    vm.validator = FormValidation.formValidation(
                        document.getElementById('kt_modal_add_inventario_form'), {
                            fields: {
                                'sucursal': {
                                    validators: {
                                        notEmpty: {
                                            message: 'La sucursal es requerida',
                                            trim: true
                                        }
                                    }
                                },
                                'productos': {
                                    validators: {
                                        callback: {
                                            message: 'Se requiere minimo 1 producto',
                                            callback: function (input) {
                                                return vm.inventario_model.productos.length > 0;
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

                    vm.inventario_model.productos.forEach((item, index) => {
                        // vm.validator.addField(('p_sku_' + item.id), {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'La sucursal es requerida',
                        //             trim: true
                        //         }
                        //     }
                        // });

                        vm.validator.addField(('p_cantidad_' + item.id), {
                            validators: {
                                notEmpty: {
                                    message: 'La cantidad es requerida',
                                    trim: true
                                },
                                greaterThan: {
                                    message: 'Cantidad invalida',
                                    min: 1
                                }
                            }
                        });

                        vm.validator.addField(('p_fecha_' + item.id), {
                            validators: {
                                callback: {
                                    callback: function (input) {
                                        if(!item.fecha_caducidad || item.fecha_caducidad==null || item.fecha_caducidad==""){
                                            return {
                                                valid: false,
                                                message: 'La fecha es requerida'
                                            };
                                        }
                                        let today = moment();
                                        let value = moment(item.fecha_caducidad, "DD/MM/Y");

                                        if (today.isSameOrAfter(value)) {
                                            return {
                                                valid: false,
                                                message: 'La fecha debe ser futura'
                                            };
                                        }
                                        return { valid: true, message: '' };
                                    },
                                },
                            }
                        });
                    });
                },
            },
            computed: {
                listaSucursales(){
                    return this.sucursales.map(item => ({id: item.id, text: item.nombre}));
                },
                listaProductos(){
                    return this.productos.map(item => ({id: item.id, text: item.nombre}));
                },
                listaInventario(){
                    if(this.fechaFilter){
                        return this.inventario.filter(item => moment(item.fecha_caducidad).format('DD/MM/Y') == this.fechaFilter);
                    } else {
                        return this.inventario;
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
