@extends('erp.base')

@section('meta_description')
    <style>
        textarea {
            resize: none;
        }
    </style>
@endsection

@section('content')
    <div id="app" class="app-content flex-column-fluid">
        <div class="card card-flush" id="content-card">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title flex-column">
                    <h3 class="mb-2">Listado de subcategorías</h3>
                </div>
                <div class="card-toolbar">
                    <div class="px-2">
                        <v-select
                            class="form-control me-3"
                            v-model="categoriaFilter"
                            :options="listaCategorias"
                            data-allow-clear="true"
                            data-placeholder="Filtrar por categoría">
                        </v-select>
                    </div>
                    <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_add_subcategory" @click.prevent="isEdit = false">
                        <i class="fa-solid fa-plus"></i> Agregar subcategoría
                    </a>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="subcategories-table" id="kt_ecommerce_category_table_wrapper">
                    <v-client-table v-model="listaSubcategorias" :columns="columns" :options="options">
                        <div slot="categoria" slot-scope="props">[[props.row.categoria?.nombre ?? '']]</div>
                        <div slot="acciones" slot-scope="props">
                            <a class="btn btn-icon btn-sm btn-info" title="Editar subcategoria" data-bs-toggle="modal" data-bs-target="#kt_add_subcategory" @click.prevent="selectSubcategory(props.row)">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <a class="btn btn-icon btn-sm btn-danger" title="Eliminar" @click.prevent="deleteSubcategory(props.row.id)" :disabled="loading">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </v-client-table>
                </div>
            </div>
        </div>


        <!-- Modals -->
        <div class="modal fade" tabindex="-1" id="kt_add_subcategory" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-text="isEdit ? 'Actualizar información subcategoria' : 'Crear subcategoría'"></h5>

                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                    </div>

                    <div class="modal-body">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-bold mb-2" for="cat_id">Categoría</label>
                            <v-select
                                class="form-control"
                                v-model="idCategoria"
                                :options="listaCategorias"
                                data-allow-clear="true"
                                data-placeholder="Selecciona una categoría"
                                id="cat_id"
                                name="cat_id"
                                data-dropdown-parent="#kt_add_subcategory">
                            </v-select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-bold mb-2" for="subCat_name">Nombre</label>
                            <input type="text" class="form-control" placeholder="Nombre de la subcategoría" id="subCat_name" name="subCat_name" v-model="nombre">
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-bold mb-2" for="subCat_desc">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Descripción de la subcategoría" id="subCat_desc" name="subCat_desc" v-model="descripcion"></textarea>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-bold mb-2">Características</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="inputGroupSelect04" aria-label="Example select with button addon" v-model="caracteristica">
                                <button class="btn btn-light-success border border-success" type="button" @click.stop="addCaracteristica();validateCaracteristicas();">
                                    <i class="fa-solid fa-plus"></i> Agregar caracteristica
                                </button>
                            </div>
                            <div class="row mt-5">
                                <div class="col-lg-6 col-md-12">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center" v-for="(caracteristica, index) in caracteristicas">
                                            <span v-text="caracteristica" v-if="!isCaracteristicaEdit"></span>
                                            <input type="text" v-model="caracteristicasEdit[index]" class="form-control form-control-sm me-3" v-if="isCaracteristicaEdit">
                                            <div class="caracteristicas-acciones">
                                                <button type="button" class="btn btn-danger btn-sm btn-icon" @click="deleteCaracteristica(index)">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="button" class="btn btn-primary btn-sm" @click="showEditCaracteristicas" v-if="!isCaracteristicaEdit && caracteristicas.length > 0 && isEdit">
                                            <i class="fa-solid fa-pencil"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm me-2" v-if="isCaracteristicaEdit" @click="isCaracteristicaEdit=false;caracteristicasEdit=caracteristicas">
                                            <i class="fa-solid fa-xmark"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" @click="saveCaracteristicas" v-if="isCaracteristicaEdit">
                                            <i class="fa-solid fa-check"></i> Finalizar
                                        </button>
                                    </div>
                                    <span class="text-danger" v-if="msgError">Debes agregar por lo menos una caracteristica</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-secondary" v-text="isEdit ? 'Guardar cambios' : 'Crear subcategoria'" :disabled="loading" @click="updateOrCreateSubcategory"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin Modals -->
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
                subcategories: {!! json_encode($subCategorias->original['results']) !!},
                categorias: [],

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
                        nutriologo: 'align-middle',
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

                isEdit: false,
                isCaracteristicaEdit: false,
                idCategoria: null,
                idSubcategoria: null,

                nombre: null,
                descripcion: null,
                caracteristica: null,
                inputEditCaracteristica: null,
                caracteristicas: [],
                caracteristicasEdit: [],

                categoriaFilter: null,
                validator: null,
                msgError: false,
                loading: false,
            }),
            mounted() {
                this.$forceUpdate();
                this.getCategorias();
                this.formValidate();

                $("#kt_add_subcategory").on('hidden.bs.modal', event => {
                    this.validator.resetForm();
                    this.clearCampos();
                });
            },
            methods: {
                getCategorias(){
                    let vm = this;
                    $.get('/api/categorias/all', res => {
                        vm.categorias = res.results;
                    }, 'json');
                },
                updateOrCreateSubcategory() {
                    let vm = this;
                    this.validateCaracteristicas();

                    if (vm.validator) {
                        vm.validator.validate().then(status => {
                            if (status == 'Valid') {
                                vm.loading = true;
                                $.ajax({
                                    method: 'POST',
                                    url: '/api/sub-categorias/save',
                                    data: {
                                        id: vm.idSubcategoria,
                                        categoria_id: vm.idCategoria,
                                        nombre: vm.nombre,
                                        descripcion: vm.descripcion,
                                        caracteristicas_json: vm.caracteristicas,
                                    }
                                }).done(function(res) {
                                    if (res.status) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            res.message,
                                            "success"
                                        );
                                        vm.subcategories = res.results;
                                        vm.getCategorias();
                                        $("#kt_add_subcategory").modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.",
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed updateOrCreateSubcategory: " + textStatus, jqXHR);
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
                            $.post('/api/sub-categorias/save', {
                                id: subcategory_id,
                                estatus: 0,
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la subcategoría ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.subcategories = res.results;
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteSubcategory: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                            }).always(function(){
                                vm.loading = false;
                            });
                        }
                    });
                },
                saveCaracteristicas() {
                    $.post('/api/sub-categorias/save', {
                        id: this.idSubcategoria,
                        categoria_id: this.idCategoria,
                        caracteristicas_json: this.caracteristicasEdit,
                    });
                    this.caracteristicas = this.caracteristicasEdit;
                    this.isCaracteristicaEdit = false;
                },
                selectSubcategory(subcategory) {
                    this.isEdit = true;
                    this.idCategoria = subcategory.categoria_id;
                    this.idSubcategoria = subcategory.id;
                    this.nombre = subcategory.nombre;
                    this.descripcion = subcategory.descripcion;
                    this.caracteristicas = subcategory.caracteristicas_json;
                },
                addCaracteristica() {
                    if (this.caracteristica) {
                        this.caracteristicas.push(this.caracteristica);
                        this.caracteristica = null;
                    }
                },
                deleteCaracteristica(index) {
                    this.caracteristicas.splice(index, 1);
                },
                showEditCaracteristicas() {
                    this.caracteristicasEdit = [];
                    this.isCaracteristicaEdit = true;
                    this.caracteristicas.forEach((caracteristica, index) => {
                        this.caracteristicasEdit[index] = caracteristica;
                    });
                },
                formValidate() {
                    const form = document.getElementById('kt_add_subcategory');
                    this.validator = FormValidation.formValidation(
                        form, {
                            fields: {
                                'cat_id': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Seleccionar una categoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'subCat_name': {
                                    validators: {
                                        notEmpty: {
                                            message: 'El nombre de la subcategoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'subCat_desc': {
                                    validators: {
                                        notEmpty: {
                                            message: 'La descripción de la subcategoría es requerida',
                                            trim: true,
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
                validateCaracteristicas() {
                    if (this.caracteristicas.length > 0) {
                        this.msgError = false;
                    } else {
                        this.msgError = true;
                    }
                },
                clearCampos() {
                    this.idCategoria = null;
                    this.nombre = null;
                    this.descripcion = null;
                    this.caracteristicas = [];
                    this.loading = false;
                },
            },
            computed: {
                listaSubcategorias() {
                    if(this.categoriaFilter) {
                        return this.subcategories.filter(item => item.categoria_id.toString() == this.categoriaFilter);
                    } else {
                        return this.subcategories;
                    }
                },
                listaCategorias() {
                    return this.categorias.map(item => ({ id: item.id, text: item.nombre }));
                },
            }
        });

        Vue.use(VueTables.ClientTable);
    </script>
@endsection
