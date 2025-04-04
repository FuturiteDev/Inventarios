@extends('erp.base')

@section('content')
    <div id="app" class="col-12 py-4">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content container-xxl">
            <!--begin::Card-->
            <div class="card card-flush" id="content-card">
                <!--begin::Card header-->
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title flex-column">
                        <h3 class="ps-2">Inventario central y catering</h3>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Form-->
                    <form id="add_inventario_form" class="form" action="#" @submit.prevent="">
                        <div class="row fv-row mb-7">
                            <label class="required form-label">Productos</label>
                            <div class="col-8">
                                <v-select-extra
                                    v-model="selected_producto"
                                    name="productos"
                                    data-placeholder="Agregar producto"
                                    :options="listaProductos"
                                    data-allow-clear="true">
                                </v-select-extra>
                            </div>
                            <div class="col-2 align-content-center">
                                <div class="form-check form-check-custom form-check-success">
                                    <input class="border-success form-check-input" type="checkbox" v-model="showTienda"/>
                                    <label class="form-check-label text-gray-700 fw-bold">Productos de Tienda</label>
                                </div>
                            </div>
                            <div class="col-2 align-content-center">
                                <button type="button" class="btn btn-light-success border border-success ms-5" @click="addProducto"><i class="fa-solid fa-plus"></i> Agregar</button>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="form-label">Productos agregados</label>
                            <table class="table table-row-dashed table-row-gray-300 no-footer" v-if="inventario_productos.length>0">
                                <thead>
                                    <tr>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Producto</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">SKU</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Cantidad</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Fecha de elaboración</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Días anaquel</th>
                                        <th tabindex="0" class="VueTables__heading text-center align-middle">Fecha de caducidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="align-middle" v-for="p in inventario_productos" :key="'p_' + p.id">
                                        <td>
                                            <span v-text="p.nombre ?? ''" class="form-control form-control-solid"></span>
                                        </td>
                                        <td>
                                            <span v-text="p.sku ?? ''" class="form-control form-control-solid"></span>
                                        </td>
                                        <td>
                                            <span class="fv-row">
                                                <input type="number" v-model="p.cantidad" class="form-control" placeholder="Cantidad" :name="`p_cantidad_${p.id}`">
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fv-row">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" placeholder="Fecha de elaboración" v-model="p.fecha_elaboracion" :name="`p_fecha_${p.id}`" :id="`p_fecha_${p.id}`"/>
                                                    <span class="input-group-text">
                                                        <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                    </span>
                                                </div>
                                            </span>
                                        </td>
                                        <td>
                                            <span v-text="p.dias_anaquel ?? ''" class="form-control form-control-solid"></span>
                                        </td>
                                        <td>
                                            <span v-text="p.fecha_caducidad ?? ''" class="form-control form-control-solid"></span>
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
                <!--end::Card body-->
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-primary" @click="addInventario" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                        <span class="indicator-label">Guardar inventario</span>
                        <span class="indicator-progress">Guardando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                    </button>
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content-->
    </div>
@endsection

@section('scripts')
    <script src="/common_assets/js/vue-tables-2.min.js"></script>
    <script src="/common_assets/js/vue_components/v-select.js"></script>
    <script src="/common_assets/js/vue_components/v-select-extra.js"></script>

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
                showTienda: false,
                selected_producto: null,
                inventario_datepickers: [],

                inventario_sucursal_id: null,
                inventario_productos: [],

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

                vm.formValidate();

                let picker = $("#datepicker_input").flatpickr({
                    dateFormat: "d/m/Y"
                });
                $( "#datepicker_clear" ).on( "click", function() {
                    picker.clear();
                } );
                vm.getSucursales();
                vm.getProductos();
            },
            methods: {
                getSucursales() {
                    let vm = this;
                    $.get(
                        '/api/sucursales/all',
                        res => {
                            vm.sucursales = res.results;
                            vm.$nextTick(() => {
                                let index = vm.sucursales.findIndex(item => item.matriz == 1);
                                if(index != -1){
                                    vm.inventario_sucursal_id = vm.sucursales[index].id;
                                } else {
                                    vm.inventario_sucursal_id = vm.sucursales[0].id;
                                }
                            });
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
                                    data: {
                                        sucursal_id: vm.inventario_sucursal_id,
                                        productos: vm.inventario_productos.map(el => ({
                                            id: el.id,
                                            cantidad: el.cantidad,
                                            fecha_elaboracion: moment(el.fecha_elaboracion,'DD/MM/YYYY').format('YYYY-MM-DD'),
                                            fecha_caducidad: moment(el.fecha_caducidad,'DD/MM/YYYY').format('YYYY-MM-DD'),
                                        })),
                                    }
                                }).done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos del inventario se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.validator.destroy();
                                        vm.inventario_datepickers.forEach(el => {
                                            el.destroy();
                                        });
                                        vm.inventario_datepickers = [];
                                        vm.inventario_productos = [];
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
                addProducto(){
                    let vm = this;
                    if(!vm.inventario_productos.some(item => item.id == vm.selected_producto)){
                        let producto = vm.productos.find(item => item.id == vm.selected_producto);
                        if(producto){
                            let dias_anaquel = producto.caracteristicas_json?.find(item => item.slug && item.slug == 'vida_util');
                            let fecha_caducidad = dias_anaquel ? moment().add(dias_anaquel.valor, 'd').format('DD/MM/YYYY') : null;
                            
                            vm.inventario_productos.push({
                                id: producto.id,
                                nombre: producto.nombre,
                                sku: producto.sku,
                                cantidad: null,
                                fecha_elaboracion: moment().format('DD/MM/YYYY'),
                                dias_anaquel: dias_anaquel?.valor,
                                fecha_caducidad: fecha_caducidad,
                            });

                            vm.$nextTick(() => {
                                let picker = $(`#p_fecha_${producto.id}`).flatpickr({
                                    dateFormat: "d/m/Y",
                                    defaultDate: "today",
                                    onChange: function(selectedDates, dateStr, instance) {
                                        let index = vm.inventario_productos.findIndex(item => item.id == producto.id);
                                        if(index!=-1){
                                            let fecha =  moment(selectedDates[0]).add(vm.inventario_productos[index].dias_anaquel, 'd').format('DD/MM/YYYY');
                                            vm.$set(vm.inventario_productos[index], 'fecha_caducidad', fecha);
                                        }
                                    },
                                });
                                vm.inventario_datepickers.push(picker);
                            });
                        }
                    }
                    vm.selected_producto = null;
                },
                removeProducto(idProducto){
                    let vm = this;
                    let index = vm.inventario_productos.findIndex(item => item.id == idProducto);
                    if(index != -1){
                        vm.$delete(vm.inventario_productos, index);

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
                        document.getElementById('add_inventario_form'), {
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
                                                return vm.inventario_productos.length > 0;
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

                    vm.inventario_productos.forEach((item, index) => {
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
                                        if(!item.fecha_elaboracion || item.fecha_elaboracion==null || item.fecha_elaboracion==""){
                                            return {
                                                valid: false,
                                                message: 'La fecha es requerida'
                                            };
                                        }
                                        let today = moment();
                                        let value = moment(item.fecha_elaboracion, "DD/MM/Y");

                                        // if (today.isAfter(value)) {
                                        //     return {
                                        //         valid: false,
                                        //         message: 'La fecha debe ser futura'
                                        //     };
                                        // }
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
                    if(this.showTienda){
                        return this.productos.filter(function (item) {
                            let tags = item.extras_json?.find(el => el.slug == "tags");
                            return tags && tags?.valor.includes("TIENDA");
                        }).map(item => ({id: item.id, text: item.nombre, extra: item.sku ?? ''}));
                    }
                    return this.productos.map(item => ({id: item.id, text: item.nombre, extra: item.sku ?? ''}));
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
