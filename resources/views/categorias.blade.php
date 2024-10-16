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
                        <h3 class="ps-2">Listado de Categorias</h3>
                    </div>
                    <div class="card-toolbar">
                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_categoria" @click="isEdit = false">
                            <i class="ki-outline ki-plus fs-2"></i> Crear categoria
                        </a>
                    </div>
                </div>
                <!--end::Card toolbar-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <v-client-table v-model="categorias" :columns="columns" :options="options">
                        <div slot="acciones" slot-scope="props">
                            <a class="btn btn-icon btn-sm btn-info btn-sm me-2" title="Ver/Editar Categoria" data-bs-toggle="modal" data-bs-target="#kt_modal_add_categoria" @click.prevent="selectCategoria(props.row)">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <a class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Categoria" :disabled="loading" @click.prevent="deleteCategoria(props.row.id)">
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
        <div class="modal fade" id="kt_modal_add_categoria" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" v-if="isEdit">Actualizar categoría</h2>
                        <h2 class="fw-bold" v-else>Crear categoría</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10">
                        <!--begin::Form-->
                        <form id="kt_modal_add_categoria_form" class="form" action="#">
                            <!--begin::Scroll-->
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                                <div class="fw-bold fs-3 mb-7">Captura la categoría</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="fv-row mb-7">
                                            <label class="required fw-semibold fs-6 mb-2 ms-2">Categoría</label>
                                            <input type="text" required class="form-control" placeholder="Categoría" v-model="categoria" name="categoria">
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
                                <button type="button" class="btn btn-secondary" @click="updateCategoria" :disabled="loading" v-if="isEdit">Actualizar categoría</button>
                                <button type="button" class="btn btn-secondary" @click="createCategoria" :disabled="loading" v-else>Crear categoría</button>
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

    </div>
@endsection

@section('scripts')
    <script src="/common_assets/js/vue-tables-2.min.js"></script>
    {{-- <script src="common_assets/js/vue-select2.js"></script> --}}

    <script>
        const app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data: () => ({
                categorias:[],
                columns: ['id', 'nombre', 'descripcion', 'acciones'],
                options: {
                    headings: {
                        id: 'ID',
                        nombre: 'Categoria',
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

                idCategoria: null,
                categoria: null,
                descripcion: null,
                
                validator: null,
                isEdit: false,
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

                vm.getCategorias(true);
                vm.formValidate();
                $("#kt_modal_add_categoria").on('hidden.bs.modal', event => {
                    vm.validator.resetForm();
                    vm.clearCampos();
                });
            },
            methods: {
                getCategorias(showLoader) {
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
                        url: '/api/categorias/all',
                        type: 'GET',
                    }).done(function (res) {
                        vm.categorias = res.results;
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
                createCategoria() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/categorias/save",
                                    data: {
                                        action: 1, //1 = Crear, 2 = Modificar o 3 = Eliminar
                                        nombre: vm.categoria,
                                        descripcion: vm.descripcion,
                                        estatus: 1,
                                    }
                                }).done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Los datos de la categoria se han almacenado con éxito",
                                            "success"
                                        );
                                        vm.getCategorias();
                                        $('#kt_modal_add_categoria').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo crear la categoria",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed createCategoria: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                updateCategoria() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: "POST",
                                    url: "/api/categorias/save",
                                    data: {
                                        id: vm.idCategoria,
                                        action: 2, //1 = Crear, 2 = Modificar o 3 = Eliminar
                                        nombre: vm.categoria,
                                        descripcion: vm.descripcion,
                                    }
                                }).done(function(res) {
                                    Swal.fire(
                                        "¡Guardado!",
                                        "Los datos de la categoria han sido actualizados con éxito",
                                        "success"
                                    );
                                    vm.getCategorias();
                                    $('#kt_modal_add_categoria').modal('hide');
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed createCategoria: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function(event, xhr, settings) {
                                    vm.loading = false;
                                });
                            }
                        });
                    }
                },
                deleteCategoria(idCategoria) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro de la categoria?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            $.ajax({
                                method: "POST",
                                url: "/api/categorias/save",
                                data: {
                                    id: idCategoria,
                                    action: 3, //1 = Crear, 2 = Modificar o 3 = Eliminar
                                    estatus: 0,
                                }
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la categoria ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.getCategorias();
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteCategoria: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
                selectCategoria(categoria) {
                    let vm = this;
                    vm.clearCampos();
                    vm.isEdit = true;
                    vm.idCategoria = categoria.id;
                    vm.categoria = categoria.nombre;
                    vm.descripcion = categoria.descripcion;
                },
                formValidate() {
                    let vm = this;
                    const form = document.getElementById('kt_modal_add_categoria_form');

                    vm.validator = FormValidation.formValidation(
                        form, {
                            fields: {
                                'categoria': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Nombre de la categoria es requerido',
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
                    this.categoria = null;
                    this.descripcion = null;
                    this.loading = false;
                },
            },
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
