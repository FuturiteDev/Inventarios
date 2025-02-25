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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_subcategory" @click="isEdit = false">
                        <i class="fa-solid fa-plus"></i> Agregar subcategoría
                    </button>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="subcategories-table" id="kt_ecommerce_category_table_wrapper">
                    <v-client-table v-model="listaSubcategorias" :columns="columns" :options="options">
                        <div slot="categoria" slot-scope="props">[[props.row.categoria?.nombre ?? '']]</div>
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-success" title="Editar subcategoria" data-bs-toggle="modal" data-bs-target="#modal_add_subcategory" @click="selectSubcategory(props.row)">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger" title="Eliminar" @click="deleteSubcategory(props.row.id)" :disabled="loading" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                <span class="indicator-label"><i class="fas fa-trash-alt"></i></span>
                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                            </button>
                        </div>
                    </v-client-table>
                </div>
            </div>
        </div>


        <!-- Modals -->
        <div class="modal fade" tabindex="-1" id="modal_add_subcategory" data-bs-backdrop="static">
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
                        <form id="modal_add_subcategory_form" class="form" action="#" @submit.prevent="">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2" for="cat_id">Categoría</label>
                                <v-select
                                    v-model="subcategoria_model.idCategoria"
                                    :options="listaCategorias"
                                    data-allow-clear="true"
                                    data-placeholder="Selecciona una categoría"
                                    id="cat_id"
                                    name="categoria_id"
                                    data-dropdown-parent="#modal_add_subcategory">
                                </v-select>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2" for="subCat_name">Nombre</label>
                                <input type="text" class="form-control" placeholder="Nombre de la subcategoría" id="subCat_name" name="name" v-model="subcategoria_model.nombre"/>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 ms-2" for="subCat_desc">Descripción</label>
                                <textarea class="form-control" rows="3" placeholder="Descripción de la subcategoría" id="subCat_desc" name="desc" v-model="subcategoria_model.descripcion"></textarea>
                            </div>

                            <div class="fv-row mb-2">
                                <label class="required fw-semibold fs-6 ms-2">Características</label>
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary btn-sm" @click="showEditCaracteristicas" v-if="!isCaracteristicaEdit&&isEdit">
                                        <i class="fa-solid fa-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-success border border-success" type="button" @click="addCaracteristica" v-else>
                                        <i class="fa-solid fa-plus"></i> Agregar característica
                                    </button>
                                </div>
                                <input type="hidden" name="caracteristicas"/>
                            </div>
                            <div class="mb-7" v-if="subcategoria_caracteristicas.length>0">
                                <div class="bg-light border border-1 border-gray-300 p-4 rounded rounded-1">
                                    <table class="no-footer table" :class="{'bg-white table-bordered': isEdit }">
                                        <thead :class="{'d-none': !(isEdit&&!isCaracteristicaEdit) }">
                                            <tr>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Etiqueta</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Tipo de campo</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle">Valor</th>
                                                <th tabindex="0" class="VueTables__heading text-center align-middle" v-if="isCaracteristicaEdit == isEdit"></th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="!isEdit">
                                            <tr class="align-middle text-center" v-for="(caracteristica, index) in subcategoria_caracteristicas" :key="'c_' + caracteristica.id">
                                                <td>
                                                    <input type="text" v-model="caracteristica.etiqueta" class="form-control form-control-sm" placeholder="Etiqueta">
                                                </td>
                                                <td>
                                                    <v-select
                                                        v-model="caracteristica.tipo"
                                                        class="form-control-sm"
                                                        :options="listInputType"
                                                        data-placeholder="Tipo de campo"
                                                        data-minimum-results-for-search ="Infinity">
                                                    </v-select>
                                                </td>
                                                <td>
                                                    <input type="text" v-model="caracteristica.valor" class="form-control form-control-sm" placeholder="Valor" v-if="caracteristica.tipo=='texto'">
                                                    <input type="number" v-model="caracteristica.valor" class="form-control form-control-sm" placeholder="Valor" v-else>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-icon" @click="deleteCaracteristica(index)">
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
                                                    <input type="text" v-model="caracteristica.etiqueta" class="form-control form-control-sm" placeholder="Etiqueta">
                                                </td>
                                                <td>
                                                    <v-select
                                                        v-model="caracteristica.tipo"
                                                        class="form-control-sm"
                                                        :options="listInputType"
                                                        data-placeholder="Tipo de campo"
                                                        data-minimum-results-for-search="Infinity">
                                                    </v-select>
                                                </td>
                                                <td>
                                                    <input type="text" v-model="caracteristica.valor" class="form-control form-control-sm" placeholder="Valor" v-if="caracteristica.tipo=='texto'">
                                                    <input type="number" v-model="caracteristica.valor" class="form-control form-control-sm" placeholder="Valor" v-if="caracteristica.tipo=='numero'">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-icon" @click="deleteCaracteristica(index)">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" v-if="isCaracteristicaEdit">
                                        <button type="button" class="btn btn-secondary btn-sm me-2" @click="isCaracteristicaEdit=false">
                                            <i class="fa-solid fa-xmark"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" @click="saveCaracteristicas">
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
                        <button type="button" class="btn btn-primary" @click="saveSubcategory" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label" v-text="isEdit ? 'Actualizar' : 'Crear'"></span>
                            <span class="indicator-progress">[[isEdit ? 'Actualizando' : 'Creando']] <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
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
                subcategorias: {!! json_encode($subCategorias->original['results']) !!},
                categorias: [],
                listInputType: [
                    {id: 'texto', text: 'Texto'},
                    {id: 'numero', text: 'Número'},
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
                initModals(){
                    let vm = this;

                    $("#modal_add_subcategory").on('hidden.bs.modal', event => {
                        if(vm.validator){
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
                    if(vm.validator){
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
                                            callback: function (input) {
                                                if (vm.subcategoria_caracteristicas.length <= 0) {
                                                    return { valid: false, message: 'Debes agregar por lo menos una caracteristica' };
                                                }

                                                if(vm.subcategoria_caracteristicas.some((sub) => !sub.etiqueta || !sub.tipo)){
                                                    return { valid: false, message: 'Hay campos pendientes de llenar' };
                                                }

                                                return { valid: true, message: '' };
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
                getCategorias(){
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
                            if(index >= 0){
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
                                if(index >= 0){
                                    vm.$set(vm.subcategorias[index], 'eliminando', false);
                                }
                            }).always(function(){
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
                    this.subcategoria_caracteristicas = subcategory.caracteristicas_json.map((item) => ({
                        etiqueta: item.etiqueta,
                        tipo: item.tipo,
                        valor: item.valor,
                    }));
                },
                showEditCaracteristicas() {
                    this.isCaracteristicaEdit = true;
                    this.caracteristicasEdit = this.subcategoria_caracteristicas.map((caracteristica) => Object.assign({}, caracteristica));
                },
                addCaracteristica() {
                    if(this.isCaracteristicaEdit){
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
                    if(this.categoriaFilter) {
                        return this.subcategorias.filter(item => item.categoria_id.toString() == this.categoriaFilter);
                    } else {
                        return this.subcategorias;
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
