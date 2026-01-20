@extends('erp.base')

@section('meta_description')
    <style>
        textarea {
            resize: none;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex flex-column flex-column-fluid" id="app">
        <div class="app-toolbar py-3 py-lg-6" id="kt_app_toolbar">
            <!--begin::Toolbar container-->
            <div class="app-container d-flex flex-stack" id="kt_app_toolbar_container">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Subcategorías</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-line text-muted fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-hover-primary" href="{{ route('home') }}">Inicio</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Control Interno</li>
                        <li class="breadcrumb-item text-muted">Configuración de inventario</li>
                        <li class="breadcrumb-item text-muted">Subcategorías</li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
            </div>
            <!--end::Toolbar container-->
        </div>

        <!--begin::Content-->
        <div class="app-content flex-column-fluid" id="kt_app_content">
            <div class="app-container" id="kt_app_content_container">
                <div class="card card-flush" id="content-card">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title flex-column">
                            <h3 class="mb-2">Listado de subcategorías</h3>
                        </div>
                        <div class="card-toolbar">
                            <div class="px-2">
                                <v-select
                                    class="form-control me-3" data-allow-clear="true" data-placeholder="Filtrar por categoría" v-model="categoriaFilter" :options="listaCategorias">
                                </v-select>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_subcategory" type="button" @click="isEdit = false">
                                <i class="fa-solid fa-plus"></i> Agregar subcategoría
                            </button>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div class="subcategories-table" id="kt_ecommerce_category_table_wrapper">
                            <v-client-table v-model="listaSubcategorias" :columns="columns" :options="options">
                                <div slot="categoria" slot-scope="props">[[props.row.categoria?.nombre ?? '']]</div>
                                <div slot="acciones" slot-scope="props">
                                    <button class="btn btn-icon btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_add_subcategory" type="button" title="Editar subcategoria" @click="selectSubcategory(props.row)">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-danger" type="button" title="Eliminar" @click="deleteSubcategory(props.row.id)" :disabled="loading" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                        <span class="indicator-label"><i class="fas fa-trash-alt"></i></span>
                                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                    </button>
                                </div>
                            </v-client-table>
                        </div>
                    </div>
                </div>


                <!-- Modals -->
                <div class="modal fade" id="modal_add_subcategory" data-bs-backdrop="static" tabindex="-1">
                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="fw-bold" v-text="isEdit ? 'Actualizar información subcategoria' : 'Crear subcategoría'"></h2>

                                <!--begin::Close-->
                                <div class="btn btn-close" data-bs-dismiss="modal"></div>
                                <!--end::Close-->
                            </div>
                            <!--begin::Modal body-->
                            <div class="modal-body mx-5">
                                <form class="form" id="modal_add_subcategory_form" action="#" @submit.prevent="">
                                    <div class="fv-row mb-7">
                                        <label class="required fw-semibold fs-6 ms-2" for="cat_id">Categoría</label>
                                        <v-select
                                            id="cat_id" name="categoria_id" data-allow-clear="true" data-placeholder="Selecciona una categoría" data-dropdown-parent="#modal_add_subcategory" v-model="subcategoria_model.idCategoria" :options="listaCategorias">
                                        </v-select>
                                    </div>

                                    <div class="fv-row mb-7">
                                        <label class="required fw-semibold fs-6 ms-2" for="subCat_name">Nombre</label>
                                        <input class="form-control" id="subCat_name" name="name" type="text" placeholder="Nombre de la subcategoría" v-model="subcategoria_model.nombre" />
                                    </div>

                                    <div class="fv-row mb-7">
                                        <label class="required fw-semibold fs-6 ms-2" for="subCat_desc">Descripción</label>
                                        <textarea class="form-control" id="subCat_desc" name="desc" rows="3" placeholder="Descripción de la subcategoría" v-model="subcategoria_model.descripcion"></textarea>
                                    </div>

                                    <div class="fv-row mb-2">
                                        <label class="required fw-semibold fs-6 ms-2">Características</label>
                                        <div class="text-end">
                                            <button class="btn btn-primary btn-sm" type="button" @click="showEditCaracteristicas" v-if="!isCaracteristicaEdit&&isEdit">
                                                <i class="fa-solid fa-pencil"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-light-success border border-success" type="button" type="button" @click="addCaracteristica" v-else>
                                                <i class="fa-solid fa-plus"></i> Agregar característica
                                            </button>
                                        </div>
                                        <input name="caracteristicas" type="hidden" />
                                    </div>
                                    <div class="mb-7" v-if="subcategoria_caracteristicas.length>0">
                                        <div class="bg-light border border-1 border-gray-300 p-4 rounded rounded-1">
                                            <table class="no-footer table" :class="{ 'bg-white table-bordered': isEdit }">
                                                <thead :class="{ 'd-none': !(isEdit && !isCaracteristicaEdit) }">
                                                    <tr>
                                                        <th class="VueTables__heading text-center align-middle" tabindex="0">Etiqueta</th>
                                                        <th class="VueTables__heading text-center align-middle" tabindex="0">Tipo de campo</th>
                                                        <th class="VueTables__heading text-center align-middle" tabindex="0">Valor</th>
                                                        <th class="VueTables__heading text-center align-middle" tabindex="0" v-if="isCaracteristicaEdit == isEdit"></th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="!isEdit">
                                                    <tr class="align-middle text-center" v-for="(caracteristica, index) in subcategoria_caracteristicas" :key="'c_' + caracteristica.id">
                                                        <td>
                                                            <input class="form-control form-control-sm" type="text" v-model="caracteristica.etiqueta" placeholder="Etiqueta">
                                                        </td>
                                                        <td>
                                                            <v-select
                                                                class="form-control-sm" data-placeholder="Tipo de campo" data-minimum-results-for-search ="Infinity" v-model="caracteristica.tipo" :options="listInputType">
                                                            </v-select>
                                                        </td>
                                                        <td>
                                                            <input class="form-control form-control-sm" type="text" v-model="caracteristica.valor" placeholder="Valor" v-if="caracteristica.tipo=='texto'">
                                                            <input class="form-control form-control-sm" type="number" v-model="caracteristica.valor" placeholder="Valor" v-else>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-danger btn-sm btn-icon" type="button" @click="deleteCaracteristica(index)">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else-if="!isCaracteristicaEdit">
                                                    <tr class="align-middle text-center" v-for="(caracteristica, index) in subcategoria_caracteristicas" :key="'c_' + caracteristica.id">
                                                        <td>[[caracteristica.etiqueta]]</td>
                                                        <td>[[caracteristica.tipo]]</td>
                                                        <td>[[caracteristica.valor]]</td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr class="align-middle text-center" v-for="(caracteristica, index) in caracteristicasEdit" :key="'c_' + caracteristica.id">
                                                        <td>
                                                            <input class="form-control form-control-sm" type="text" v-model="caracteristica.etiqueta" placeholder="Etiqueta">
                                                        </td>
                                                        <td>
                                                            <v-select
                                                                class="form-control-sm" data-placeholder="Tipo de campo" data-minimum-results-for-search="Infinity" v-model="caracteristica.tipo" :options="listInputType">
                                                            </v-select>
                                                        </td>
                                                        <td>
                                                            <input class="form-control form-control-sm" type="text" v-model="caracteristica.valor" placeholder="Valor" v-if="caracteristica.tipo=='texto'">
                                                            <input class="form-control form-control-sm" type="number" v-model="caracteristica.valor" placeholder="Valor" v-if="caracteristica.tipo=='numero'">
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-danger btn-sm btn-icon" type="button" @click="deleteCaracteristica(index)">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="d-flex justify-content-end mt-3" v-if="isCaracteristicaEdit">
                                                <button class="btn btn-secondary btn-sm me-2" type="button" @click="isCaracteristicaEdit=false">
                                                    <i class="fa-solid fa-xmark"></i> Cancelar
                                                </button>
                                                <button class="btn btn-primary btn-sm" type="button" @click="saveCaracteristicas">
                                                    <i class="fa-solid fa-check"></i> Finalizar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!--end::Form-->
                            </div>
                            <!--end::Modal body-->

                            <div class="modal-footer">
                                <button class="btn btn-primary" type="button" @click="saveSubcategory" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                                    <span class="indicator-label" v-text="isEdit ? 'Actualizar' : 'Crear'"></span>
                                    <span class="indicator-progress">[[isEdit ? 'Actualizando' : 'Creando']] <span class="spinner-border spinner-border-sm align-middle"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin Modals -->
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('/common_assets/js/vue-tables-2.min.js') }}"></script>
    <script src="{{ asset('/common_assets/js/vue_components/v-select.js') }}"></script>

    <script>
        const app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data: () => ({
                subcategorias: {!! json_encode($subCategorias->original['results']) !!},
                categorias: [],
                listInputType: [{
                        id: 'texto',
                        text: 'Texto'
                    },
                    {
                        id: 'numero',
                        text: 'Número'
                    },
                ],

                columns: ['categoria', 'nombre', 'descripcion', 'acciones'],
                options: {
                    headings: {
                        categoria: 'Categoría',
                        nombre: 'Subcategoría',
                        descripcion: 'Descripción',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        categoria: 'align-middle px-3',
                        nombre: 'align-middle ',
                        descripcion: 'align-middle mw-325px',
                        acciones: 'align-middle text-center px-3',
                    },
                    sortable: ['nombre', 'descripcion', 'categoria'],
                    filterable: ['nombre', 'descripcion', 'categoria'],
                    skin: 'table table-sm table-striped border align-middle table-row-bordered fs-6',
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

                subcategoria_model: {
                    idSubcategoria: null,
                    idCategoria: null,
                    nombre: null,
                    descripcion: null,
                },
                subcategoria_caracteristicas: [],
                caracteristicasEdit: [],

                categoriaFilter: null,

                isEdit: false,
                isCaracteristicaEdit: false,
                loading: false,
                validator: null,
            }),
            mounted() {
                this.$forceUpdate();
                this.getCategorias();
                this.initFormValidate();
                this.initModals();
            },
            methods: {
                // Inits
                initModals() {
                    let vm = this;

                    $("#modal_add_subcategory").on('hidden.bs.modal', event => {
                        if (vm.validator) {
                            vm.validator.resetForm();
                        }

                        vm.isEdit = false;
                        vm.loading = false;

                        vm.subcategoria_model = {
                            idSubcategoria: null,
                            idCategoria: null,
                            nombre: null,
                            descripcion: null,
                        };
                        vm.subcategoria_caracteristicas = [];
                    });
                },
                initFormValidate() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.destroy();
                        vm.validator = null;
                    }

                    vm.validator = FormValidation.formValidation(
                        document.getElementById('modal_add_subcategory_form'), {
                            fields: {
                                'categoria_id': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Seleccionar una categoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'name': {
                                    validators: {
                                        notEmpty: {
                                            message: 'El nombre de la subcategoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'desc': {
                                    validators: {
                                        notEmpty: {
                                            message: 'La descripción de la subcategoría es requerida',
                                            trim: true,
                                        },
                                    }
                                },
                                'caracteristicas': {
                                    validators: {
                                        callback: {
                                            callback: function(input) {
                                                if (vm.subcategoria_caracteristicas.length <= 0) {
                                                    return {
                                                        valid: false,
                                                        message: 'Debes agregar por lo menos una caracteristica'
                                                    };
                                                }

                                                if (vm.subcategoria_caracteristicas.some((sub) => !sub.etiqueta || !sub.tipo)) {
                                                    return {
                                                        valid: false,
                                                        message: 'Hay campos pendientes de llenar'
                                                    };
                                                }

                                                return {
                                                    valid: true,
                                                    message: ''
                                                };
                                            },
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
                // Request get
                getCategorias() {
                    let vm = this;
                    $.get('/api/categorias/all', res => {
                        vm.categorias = res.results;
                    }, 'json');
                },
                // Request
                saveSubcategory() {
                    let vm = this;
                    if (vm.validator) {
                        vm.validator.validate().then(status => {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: 'POST',
                                    url: '/api/sub-categorias/save',
                                    data: {
                                        id: vm.subcategoria_model.idSubcategoria,
                                        categoria_id: vm.subcategoria_model.idCategoria,
                                        nombre: vm.subcategoria_model.nombre,
                                        descripcion: vm.subcategoria_model.descripcion,
                                        caracteristicas_json: vm.subcategoria_caracteristicas,
                                    }
                                }).done(function(res) {
                                    if (res.status) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            res.message,
                                            "success"
                                        );
                                        vm.subcategorias = res.results;
                                        vm.getCategorias();
                                        $("#modal_add_subcategory").modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed saveSubcategory: " + textStatus, jqXHR);
                                    Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                                }).always(function() {
                                    vm.loading = false;
                                });
                            }
                        });
                    }

                },
                deleteSubcategory(subcategory_id) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro de la subccategoría?',
                        text: 'Esta acción es irreversible',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            let index = vm.subcategorias.findIndex(item => item.id == subcategory_id);
                            if (index >= 0) {
                                vm.$set(vm.subcategorias[index], 'eliminando', true);
                            }
                            $.post('/api/sub-categorias/save', {
                                id: subcategory_id,
                                estatus: 0,
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la subcategoría ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.subcategorias = res.results;
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteSubcategory: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.subcategorias.findIndex(item => item.id == subcategory_id);
                                if (index >= 0) {
                                    vm.$set(vm.subcategorias[index], 'eliminando', false);
                                }
                            }).always(function() {
                                vm.loading = false;
                            });
                        }
                    });
                },
                saveCaracteristicas() {
                    let vm = this;
                    $.post('/api/sub-categorias/save', {
                        id: vm.subcategoria_model.idSubcategoria,
                        categoria_id: vm.subcategoria_model.idCategoria,
                        caracteristicas_json: vm.caracteristicasEdit,
                    });
                    vm.subcategoria_caracteristicas = vm.caracteristicasEdit;
                    vm.isCaracteristicaEdit = false;
                },
                // Utils
                selectSubcategory(subcategory) {
                    this.isEdit = true;

                    this.subcategoria_model = {
                        idSubcategoria: subcategory.id,
                        idCategoria: subcategory.categoria_id,
                        nombre: subcategory.nombre,
                        descripcion: subcategory.descripcion,
                    };
                    this.subcategoria_caracteristicas = subcategory.caracteristicas_json.map((item) => Object.assign({}, item));
                },
                showEditCaracteristicas() {
                    this.isCaracteristicaEdit = true;
                    this.caracteristicasEdit = this.subcategoria_caracteristicas.map((caracteristica) => Object.assign({}, caracteristica));
                },
                addCaracteristica() {
                    if (this.isCaracteristicaEdit) {
                        this.caracteristicasEdit.push({
                            etiqueta: null,
                            tipo: null,
                            valor: null,
                        });
                    } else {
                        this.subcategoria_caracteristicas.push({
                            etiqueta: null,
                            tipo: null,
                            valor: null,
                        });
                    }
                },
                deleteCaracteristica(index) {
                    this.subcategoria_caracteristicas.splice(index, 1);
                },
            },
            computed: {
                listaSubcategorias() {
                    if (this.categoriaFilter) {
                        return this.subcategorias.filter(item => item.categoria_id.toString() == this.categoriaFilter);
                    } else {
                        return this.subcategorias;
                    }
                },
                listaCategorias() {
                    return this.categorias.map(item => ({
                        id: item.id,
                        text: item.nombre
                    }));
                },
            }
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
