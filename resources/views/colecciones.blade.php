@extends('erp.base')

@section('content')
    <div id="app">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content">
            <!--begin::Card-->
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title flex-column">
                        <h3 class="ps-2">Listado de Colecciones</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_coleccion" @click="openModalCreate">
                            <i class="ki-outline ki-plus fs-2"></i>
                            Crear coleccion
                        </a>
                    </div>
                </div>
                <!--end::Card toolbar-->                

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <v-client-table v-model="colecciones" :columns="columns" :options="options">
                        <div slot="acciones" slot-scope="props">
                            <a href="#" class="btn btn-icon btn-sm btn-success btn-sm me-2" title="Ver productos" data-bs-toggle="modal" data-bs-target="#kt_modal_producto_coleccion" @click="verProductos(props.row)">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-icon btn-sm btn-info btn-sm me-2" title="Ver/Editar Coleccion" data-bs-toggle="modal" data-bs-target="#kt_modal_add_coleccion" @click="selectColeccion(props.row)">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <a href="#" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Coleccion" @click="deleteColeccion(props.row.id)">
                                <i class="fas fa-trash-alt"></i>
                            </a>
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
                        <h2 class="fw-bold" v-if="isEdit">Actualizar colección</h2>
                        <h2 class="fw-bold" v-else>Crear colección</h2>

                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10">
                        <!--begin::Form-->
                        <form id="kt_modal_add_coleccion_form" class="form" action="#">
                            <!--begin::Scroll-->
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="fv-row mb-7">
                                            <label class="required fw-semibold fs-6 mb-2 ms-2">Nombre</label>
                                            <input type="text" required class="form-control" placeholder="Colección" v-model="coleccion" name="coleccion">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="fv-row mb-7">
                                            <label class="required fw-semibold fs-6 mb-2 ms-2">Descripcion</label>
                                            <input type="text" required class="form-control" placeholder="Descripcion" v-model="descripcion" name="descripcion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Scroll-->
                            <!--begin::Actions-->
                            <div class="text-end pt-15">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-info" @click="updateColeccion" :disabled="isDisabled" v-if="isEdit">Actualizar colección</button>
                                <button type="button" class="btn btn-info" @click="createColeccion" :disabled="isDisabled" v-else>Crear colección</button>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
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
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10">
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
                        <!--begin::Actions-->
                        <div class="text-end pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Modal body-->
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
                        descripcion: 'align-middle text-center ',
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

                validator: null,
                isEdit: false,
                isDisabled: false,
                idColeccion: null,
                coleccion: null,
                descripcion: null,
                
            }),
            mounted() {
                this.$forceUpdate();
                this.getColecciones();
                this.formValidate();
                $("#kt_modal_add_coleccion").on('hidden.bs.modal', event => {
                    this.validator.resetForm();                    
                });
            },
            methods: {
                openModalCreate() {
                    this.isEdit = false;
                    this.clearCampos();
                },
                getColecciones(){
                    let vueThis = this;
                    $.get('/api/colecciones/all', res => {
                        vueThis.colecciones = res.results;                        
                    }, 'json');
                },
                selectColeccion(coleccion) {
                    let vueThis = this;
                    vueThis.clearCampos();
                    vueThis.isEdit = true;

                    vueThis.idColeccion = coleccion.id;
                    vueThis.coleccion = coleccion.nombre;
                    vueThis.descripcion = coleccion.descripcion;               
                },
                verProductos(coleccion) {
                    let vueThis = this;
                    vueThis.coleccion = coleccion.nombre;
                    vueThis.productos = [];
                    $.get('/api/colecciones/productos/' + coleccion.id, res => {
                        vueThis.productos = res.results;                        
                    }, 'json');
                },
                createColeccion() {
                    let vueThis = this;                    
                    vueThis.isDisabled = true;
                    if (vueThis.validator) {
                        vueThis.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                $.ajax({
                                    method: "POST",
                                    url: "/api/colecciones/save",
                                    data: {                                        
                                        nombre: vueThis.coleccion,
                                        descripcion: vueThis.descripcion,
                                        estatus: 1,
                                    }
                                })
                                .done(function(res) {
                                    if (res.code === 200) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos de la coleccion se han almacenado con éxito",
                                            "success"
                                        );
                                        vueThis.getColecciones();
                                        $('#kt_modal_add_coleccion').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo crear la coleccion",
                                            "warning"
                                        );
                                    }
                                })
                                .fail(function(jqXHR, textStatus) {
                                    console.log("Request failed createColeccion: " + textStatus, jqXHR);
                                    Swal.fire(
                                        "¡Error!",
                                        "No se pudo crear la coleccion",
                                        "warning"
                                    );
                                })
                                .always(function(event, xhr, settings) {
                                    vueThis.isDisabled = false;
                                });
                            }
                        });
                    }
                },
                updateColeccion() {
                    let vueThis = this;
                    if (vueThis.validator) {
                        vueThis.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vueThis.isDisabled = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/colecciones/save",
                                    data: {
                                        id: vueThis.idColeccion,
                                        nombre: vueThis.coleccion,
                                        descripcion: vueThis.descripcion,
                                        estatus: 1,
                                    }
                                })
                                .done(function(res) {
                                    
                                    if (res.code === 200) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos de la coleccion han sido actualizados con éxito",
                                            "success"
                                        );
                                        vueThis.getColecciones();
                                        $('#kt_modal_add_coleccion').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo actualizar la coleccion",
                                            "warning"
                                        );
                                    }
                                })
                                .fail(function(jqXHR, textStatus) {
                                    console.log("Request failed createColeccion: " + textStatus, jqXHR);
                                })
                                .always(function(event, xhr, settings) {
                                    vueThis.isDisabled = false;
                                });
                            }
                        });
                    }
                },
                deleteColeccion(idColeccion) {
                    let vueThis = this;
                    vueThis.isDisabled = true;
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
                            $.ajax({
                                method: "POST",
                                url: "/api/colecciones/delete",
                                data: {
                                    coleccion_id: idColeccion,
                                }
                            })
                            .done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la coleccion ha sido eliminado con éxito',
                                    'success'
                                );
                                vueThis.getColecciones();
                            })
                            .always(function(event, xhr, settings) {
                                vueThis.isDisabled = false;
                            });
                        }
                    })
                },
                clearCampos() {
                    this.isEdit = false;                    
                    this.isDisabled = false;
                    this.coleccion = null;
                    this.descripcion = null;
                },
                formValidate() {
                    let vueThis = this;
                    // Define form element
                    const form = document.getElementById('kt_modal_add_coleccion_form');
                    
                    vueThis.validator = FormValidation.formValidation(
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
                }
            },
            computed: {
                
            }
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
