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
                        <h3 class="ps-2">Listado de Colecciones</h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_coleccion" @click="isEdit = false">
                            <i class="ki-outline ki-plus fs-2"></i> Crear colección
                        </button>
                    </div>
                </div>
                <!--end::Card toolbar-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <img src="https://cdn.shopify.com/s/files/1/1887/4475/files/IMG_4634.jpg?v=1698102045" style="width: 200px;" alt="demo">
                    <!--begin::Table-->
                    <v-client-table v-model="colecciones" :columns="columns" :options="options">
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-primary btn-sm me-2" title="Ver productos" data-bs-toggle="modal" data-bs-target="#kt_modal_producto_coleccion" @click="getProductos(props.row, true)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-success btn-sm me-2" title="Ver/Editar Coleccion" data-bs-toggle="modal" data-bs-target="#kt_modal_add_coleccion" @click="selectColeccion(props.row)">
                                <i class="fas fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Coleccion" :disabled="loading" @click="deleteColeccion(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
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
        <div class="modal fade" id="kt_modal_add_coleccion" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" v-text="isEdit ? 'Actualizar colección' : 'Crear colección'"></h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body">
                        <!--begin::Form-->
                        <form id="kt_modal_add_coleccion_form" class="form" action="#" @submit.prevent="">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Nombre</label>
                                <input type="text" required class="form-control" placeholder="Colección" v-model="coleccion" name="coleccion">
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Descripcion</label>
                                <input type="text" required class="form-control" placeholder="Descripcion" v-model="descripcion" name="descripcion">
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="updateColeccion" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'" v-if="isEdit">
                            <span class="indicator-label">Actualizar</span>
                            <span class="indicator-progress">Actualizando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                        <button type="button" class="btn btn-primary" @click="createColeccion" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'" v-else>
                            <span class="indicator-label">Crear</span>
                            <span class="indicator-progress">Creando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Add task-->

        <!--begin::Modal - Add task-->
        <div class="modal fade modal-lg" id="kt_modal_producto_coleccion" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" >Productos de coleccion - [[coleccion]]</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10" id="kt_modal_producto_coleccion_body">
                        <!--begin::Card body-->
                        <div class="card-body py-4">
                            <!--begin::Table-->
                            <v-client-table v-model="productos" :columns="columns2" :options="options2"> 
                                <div slot="categoria" slot-scope="props">
                                    [[ props.row.categoria?.nombre ]]
                                </div>
                                <div slot="subcategoria" slot-scope="props">
                                    [[ props.row.subcategoria?.nombre ]]
                                </div>
                            </v-client-table>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary me-3" data-bs-dismiss="modal">Cerrar</button>
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

    <script>
        const app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data: () => ({
                colecciones:[],
                columns: ['id', 'nombre', 'descripcion', 'acciones'],
                options: {
                    headings: {
                        id: 'ID',
                        nombre: 'Coleccion',
                        descripcion: 'Descripción',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        id: 'align-middle px-2 ',
                        nombre: 'align-middle ',
                        descripcion: 'align-middle ',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['nombre', 'descripcion'],
                    filterable: ['nombre', 'descripcion'],
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
                },

                productos:[],
                columns2: ['id', 'sku', 'nombre', 'categoria', 'subcategoria'],
                options2: {
                    headings: {
                        id: 'ID',
                        sku: 'SKU',
                        nombre: 'Nombre',
                        categoria: 'Categoria',
                        subcategoria: 'Subcategoria',
                    },
                    columnsClasses: {
                        id: 'align-middle px-2 ',
                        sku: 'align-middle ',
                        nombre: 'align-middle ',
                        categoria: 'align-middle text-center ',
                        subcategoria: 'align-middle text-center px-2 ',
                    },
                    sortable: ['nombre', 'categoria'],
                    filterable: ['sku', 'nombre'],
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
                },

                idColeccion: null,
                coleccion: null,
                descripcion: null,

                validator: null,
                isEdit: false,
                loading: false,
                blockUI: null,
                blockUIModal: null,
                requestGet: null,
                requestGetModal: null,
            }),
            mounted() {
                let vm = this;
                vm.$forceUpdate();

                let container = document.querySelector('#content-card');
                if (container) {
                    vm.blockUI = new KTBlockUI(container);
                }

                container = document.querySelector('#kt_modal_producto_coleccion_body');
                if (container) {
                    vm.blockUIModal = new KTBlockUI(container);
                }

                vm.getColecciones(true);
                vm.formValidate();
                $("#kt_modal_add_coleccion").on('hidden.bs.modal', event => {
                    vm.validator.resetForm();
                    vm.clearCampos();
                });
            },
            methods: {
                getColecciones(showLoader){
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
                        url: '/api/colecciones/all',
                        type: 'GET',
                    }).done(function (res) {
                        vm.colecciones = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getCategorias: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                getProductos(coleccion, showLoader) {
                    let vm = this;
                    vm.coleccion = coleccion.nombre;
                    vm.productos = [];

                    if (showLoader) {
                        if (!vm.blockUIModal) {
                            let container = document.querySelector('#kt_modal_producto_coleccion_body');
                            if (container) {
                                vm.blockUIModal = new KTBlockUI(container);
                                vm.blockUIModal.block();
                            }
                        } else {
                            if (!vm.blockUIModal.isBlocked()) {
                                vm.blockUIModal.block();
                            }
                        }
                    }

                    if (vm.requestGetModal) {
                        vm.requestGetModal.abort();
                        vm.requestGetModal = null;
                    }

                    vm.loading = true;

                    vm.requestGetModal = $.ajax({
                        url: '/api/colecciones/productos/' + coleccion.id,
                        type: 'GET',
                    }).done(function (res) {
                        vm.productos = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getProductos: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUIModal && vm.blockUIModal.isBlocked()) {
                            vm.blockUIModal.release();
                        }
                    });
                },
                createColeccion() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/colecciones/save",
                                    data: {
                                        nombre: vm.coleccion,
                                        descripcion: vm.descripcion,
                                        estatus: 1,
                                    }
                                }).done(function(res) {
                                    if (res.code === 200) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos de la coleccion se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.getColecciones();
                                        $('#kt_modal_add_coleccion').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo crear la coleccion",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed createColeccion: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                updateColeccion() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/colecciones/save",
                                    data: {
                                        id: vm.idColeccion,
                                        nombre: vm.coleccion,
                                        descripcion: vm.descripcion,
                                        estatus: 1,
                                    }
                                }).done(function(res) {
                                    if (res.code === 200) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos de la coleccion han sido actualizados con éxito",
                                            "success"
                                        );
                                        vm.getColecciones();
                                        $('#kt_modal_add_coleccion').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo actualizar la coleccion",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed updateColeccion: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                deleteColeccion(idColeccion) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro de la coleccion?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            let index = vm.colecciones.findIndex(item => item.id == idColeccion);
                            if(index >= 0){
                                vm.$set(vm.colecciones[index], 'eliminando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: "/api/colecciones/delete",
                                data: {
                                    coleccion_id: idColeccion,
                                }
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la coleccion ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.getColecciones();
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteColeccion: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.colecciones.findIndex(item => item.id == idColeccion);
                                if(index >= 0){
                                    vm.$set(vm.colecciones[index], 'eliminando', false);
                                }
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
                selectColeccion(coleccion) {
                    let vm = this;
                    vm.clearCampos();
                    vm.isEdit = true;

                    vm.idColeccion = coleccion.id;
                    vm.coleccion = coleccion.nombre;
                    vm.descripcion = coleccion.descripcion;
                },
                formValidate() {
                    let vm = this;
                    // Define form element
                    const form = document.getElementById('kt_modal_add_coleccion_form');
                    
                    vm.validator = FormValidation.formValidation(
                        form, {
                            fields: {
                                'coleccion': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Nombre de la coleccion es requerido',
                                            trim: true
                                        }
                                    }
                                },
                                'descripcion': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Descripcion es requerido',
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
                        }
                    );
                },
                clearCampos() {
                    this.isEdit = false;
                    this.loading = false;
                    this.coleccion = null;
                    this.descripcion = null;
                },
            },
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
