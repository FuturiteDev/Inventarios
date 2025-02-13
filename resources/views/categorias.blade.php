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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_categoria" @click="isEdit = false">
                            <i class="ki-outline ki-plus fs-2"></i> Crear categoria
                        </button>
                    </div>
                </div>
                <!--end::Card toolbar-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <v-client-table v-model="categorias" :columns="columns" :options="options">
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-success me-2" title="Ver/Editar Categoria" data-bs-toggle="modal" data-bs-target="#kt_modal_add_categoria" @click="selectCategoria(props.row)">
                                <i class="fas fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger me-2" title="Eliminar Categoria" :disabled="loading" @click="deleteCategoria(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
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
        <div class="modal fade" id="kt_modal_add_categoria" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" v-text="isEdit ? 'Actualizar categoría' : 'Crear categoría'"></h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body">
                        <!--begin::Form-->
                        <form id="kt_modal_add_categoria_form" class="form" action="#" @submit.prevent="">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Imagen:</label>
                                <input type="file" class="form-control" name="imagen" id="imagen" accept="image/png, image/jpeg" @input="onInput"/>
                                <div class="text-center w-100">
                                    <img id="preview_original" style="margin: 0 auto;" class="mw-100 mh-250px" :src="categoria_model.imagen_path"/>
                                    <img id="preview" style="margin: 0 auto;" class="mw-100 mh-250px d-none"/>
                                </div>
                            </div>
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Categoría</label>
                                <input type="text" required class="form-control" placeholder="Categoría" v-model="categoria_model.nombre" name="nombre">
                            </div>
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2">Descripcion</label>
                                <input type="text" required class="form-control" placeholder="Descripcion" v-model="categoria_model.descripcion" name="descripcion">
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="updateCategoria" :disabled="loading" v-if="isEdit" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label">Actualizar</span>
                            <span class="indicator-progress">Actualizando <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                        <button type="button" class="btn btn-primary" @click="createCategoria" :disabled="loading" v-else :data-kt-indicator="loading ? 'on' : 'off'">
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

    </div>
@endsection

@section('scripts')
    <script src="/common_assets/js/vue-tables-2.min.js"></script>

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

                categoria_model: {
                    idCategoria: null,
                    nombre: null,
                    descripcion: null,
                    imagen_path: null,
                },

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
                                let form = document.querySelector("#kt_modal_add_categoria_form");
                                let data = new FormData(form);
                                if($('#imagen').val().trim() == ""){
                                    data.delete("imagen");
                                }

                                data.set("action", 1); //1 = Crear, 2 = Modificar o 3 = Eliminar
                                data.set("estatus", 1);

                                $.ajax({
                                    method: "POST",
                                    url: "/api/categorias/save",
                                    data: data,
                                    processData: false,
                                    contentType: false,
                                    dataType: "JSON"
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
                                let form = document.querySelector("#kt_modal_add_categoria_form");
                                let data = new FormData(form);
                                if($('#imagen').val().trim() == ""){
                                    data.delete("imagen");
                                }

                                data.set("action", 2); //1 = Crear, 2 = Modificar o 3 = Eliminar
                                data.set("id", vm.categoria_model.idCategoria);

                                $.ajax({
                                    method: "POST",
                                    url: "/api/categorias/save",
                                    data: data,
                                    processData: false,
                                    contentType: false,
                                    dataType: "JSON"
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
                            let index = vm.categorias.findIndex(item => item.id == idCategoria);
                            if(index >= 0){
                                vm.$set(vm.categorias[index], 'eliminando', true);
                            }
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

                                index = vm.sucursales.findIndex(item => item.id == idCategoria);
                                if(index >= 0){
                                    vm.$set(vm.sucursales[index], 'eliminando', false);
                                }
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
                    vm.categoria_model = {
                        idCategoria: categoria.id,
                        nombre: categoria.nombre,
                        descripcion: categoria.descripcion,
                        imagen_path: categoria.imagen,
                    };
                },
                formValidate() {
                    let vm = this;
                    const form = document.getElementById('kt_modal_add_categoria_form');

                    vm.validator = FormValidation.formValidation(
                        form, {
                            fields: {
                                'nombre': {
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
                                'imagen': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Imagen requerida',
                                            trim: true
                                        },
                                        file: {
                                        extension: 'jpg,jpeg,png',
                                        type: 'image/jpeg,image/png',
                                        message: 'El archivo no es válido'
                                    },
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
                    this.categoria_model = {
                        idCategoria: null,
                        nombre: null,
                        descripcion: null,
                        imagen_path: null,
                    };

                    $('#imagen').val("");
                    $('#preview').attr('src', "");
                    $('#preview_original').removeClass("d-none");
                    $('#preview').addClass("d-none");
                },
                onInput(event){
                    $('#preview').attr('src', window.URL.createObjectURL(event.target.files[0]));
                    $('#preview').removeClass("d-none");
                    $('#preview_original').addClass("d-none");
                }
            },
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
