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
                        <h3 class="ps-2">Listado de Productos</h3>
                    </div>
                    <div class="card-toolbar">
                        <div class="px-2">
                            <v-select 
                                class="form-control me-3"
                                v-model="categoriaFilter"
                                :options="listaCategorias"
                                data-allow-clear="true"
                                data-placeholder="Filtrar por categoría"
                                @change="getSubcategoriasFilter">
                            </v-select>
                        </div>
                        <div class="px-2" v-if="categoriaFilter">
                            <v-select
                                class="form-control me-3"
                                v-model="subcategoriaFilter"
                                :options="listaSubcategoriasFilter"
                                data-allow-clear="true"
                                data-placeholder="Filtrar por subcategoría">
                            </v-select>
                        </div>
                        <div class="px-2">
                            <v-select
                                class="form-control me-3"
                                v-model="coleccionFilter"
                                :options="listaColecciones"
                                data-allow-clear="true"
                                data-placeholder="Filtrar por coleccion">
                            </v-select>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_producto" @click="isEdit = false">
                            <i class="ki-outline ki-plus fs-2"></i> Agregar producto
                        </button>
                    </div>
                </div>
                <!--end::Card toolbar-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <v-client-table v-model="listaProductos" :columns="columns" :options="options">
                        <div slot="categoria" slot-scope="props">
                            [[props.row.categoria?.nombre ?? 'N/A']]
                        </div>
                        <div slot="subcategoria" slot-scope="props">
                            [[props.row.subcategoria?.nombre ?? 'N/A']]
                        </div>
                        <div slot="acciones" slot-scope="props">
                            <button type="button" class="btn btn-icon btn-sm btn-success me-2" title="Ver/Editar Producto" data-bs-toggle="modal" data-bs-target="#kt_modal_add_producto" @click="selectProducto(props.row)">
                                <i class="fas fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-primary me-2" title="Multimedia" data-bs-toggle="modal" data-bs-target="#kt_modal_producto_multimedia" @click="getMultimedia(props.row.id, true)" id="openMultimediaBtn">
                                <i class="fas fa-photo-film"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger me-2" title="Eliminar Producto" @click="deleteProducto(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
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
        <div class="modal fade" id="kt_modal_add_producto" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" v-text="isEdit ? 'Actualizar producto' : 'Crear producto'"></h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5">
                        <!--begin::Form-->
                        <form id="kt_modal_add_producto_form" class="form" action="#" @submit.prevent="">
                            <!--begin::Scroll-->
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true"
                                data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="sku">Sku</label>
                                    <input type="text" class="form-control" placeholder="Sku" id="sku" name="sku" v-model="sku">
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="subCat_name">Nombre</label>
                                    <input type="text" class="form-control" placeholder="Nombre del producto" id="nombre" name="nombre" v-model="nombre">
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="subCat_desc">Descripción</label>
                                    <textarea class="form-control" rows="3" placeholder="Descripción del producto" id="descripcion" name="descripcion" v-model="descripcion"></textarea>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold mb-2" for="col_id">Colecciones</label>
                                    <v-select v-if="colecciones != []"
                                        class="form-control"
                                        v-model="idColecciones"
                                        :options="listaColecciones"
                                        multiple
                                        data-allow-clear="true"
                                        data-placeholder="Selecciona las colecciones"
                                        id="col_id"
                                        name="col_id"
                                        data-dropdown-parent="#kt_modal_add_producto">
                                    </v-select>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="cat_id">Categoría</label>
                                    <v-select v-if="categorias != []"
                                        class="form-control"
                                        v-model="idCategoria"
                                        :options="listaCategorias"
                                        data-allow-clear="true"
                                        data-placeholder="Selecciona una categoría"
                                        id="cat_id"
                                        name="cat_id"
                                        data-dropdown-parent="#kt_modal_add_producto"
                                        @change="getSubcategorias">
                                    </v-select>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="subcat_id">Subcategoría</label>
                                    <v-select v-if="!setEdit"
                                        class="form-control"
                                        v-model="idSubcategoria"
                                        :options="listaSubcategorias"
                                        data-allow-clear="true"
                                        data-placeholder="Selecciona una subcategoría"
                                        id="subcat_id"
                                        name="subcat_id"
                                        data-dropdown-parent="#kt_modal_add_producto"
                                        @change="getCaracteristicasSubcategoria">
                                    </v-select>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 ms-2" for="precio">Precio</label>
                                    <input type="text" class="form-control" placeholder="Precio" id="precio" name="precio" v-model="precio" onblur="formatoNumero(this)">
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold mb-2">Características</label>
                                    <div class="row mt-5">
                                        <div class="col-lg-12">
                                            <ul class="list-group">
                                                <li class="list-group-item d-flex align-items-stretch justify-content-between" v-for="(caracteristica, index) in caracteristicas_subcategoria">
                                                    <label class="col-sm-2 control-label">[[caracteristica.etiqueta]]</label>
                                                    <div class="col-lg-6">
                                                        <input type="text" v-model="caracteristica.valor" class="form-control" :name="`caracteristica${caracteristica.etiqueta}`">
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold mb-2">Características Extras</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" v-model="extra_input" placeholder="Escribe la característica">
                                        <button type="button" class="btn btn-light-success border border-success" @click="addExtra">
                                            <i class="fa-solid fa-plus"></i> Agregar característica extra
                                        </button>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col-lg-12">
                                            <ul class="list-group">
                                                <li class="list-group-item d-flex justify-content-between align-items-center" v-for="(extra, index) in extras">
                                                    <label class="col-sm-2 control-label">[[extra.etiqueta]]</label>
                                                    <div class="col-lg-6">
                                                        <input type="text" v-model="extra.valor" class="form-control" :name="`extra${extra.etiqueta}`">
                                                    </div>
                                                    <div class="caracteristicas-acciones">
                                                        <button type="button" class="btn btn-danger btn-sm btn-icon" @click="deleteExtra(index)">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </li>
                                            </ul>
                                            <span class="text-danger" v-if="msgError">Característica inválida</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Scroll-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                    <div class="modal-footer" v-if="!setEdit">
                        <button type="button" class="btn btn-primary" @click="saveProducto" :disabled="loading" :data-kt-indicator="loading ? 'on' : 'off'">
                            <span class="indicator-label" v-text="isEdit ? 'Actualizar' : 'Crear'"></span>
                            <span class="indicator-progress">[[isEdit ? 'Actualizando' : 'Creando']] <span class="spinner-border spinner-border-sm align-middle"></span></span>
                        </button>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Add task-->

        <!--begin::Modal - Multimedia-->
        <div class="modal fade" id="kt_modal_producto_multimedia" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold">Multimedia producto</h2>

                        <!--begin::Close-->
                        <div class="btn btn-close" data-bs-dismiss="modal"></div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10" id="kt_modal_producto_multimedia_body">
                        <div class="text-center p-10" v-if="loading">
                            <span class="text-center"></span><br>
                            <p class="fs-5 fw-medium">Obteniendo información</p>
                        </div>
                        <div class="row" v-else>
                            <div class="d-flex justify-content-end mb-10">
                                <button type="button" class="btn btn-sm" :class="formMultimedia ? 'btn-light' : 'btn-primary'" @click="formMultimedia=!formMultimedia;initForm()">
                                    <i class="fa-solid" :class="formMultimedia ? 'fa-chevron-left' : 'fa-plus'"></i>
                                    <span class="align-middle" v-text="formMultimedia ? 'Cancelar' : 'Agregar multimedia'"></span>
                                </button>
                            </div>

                            <div v-if="formMultimedia">
                                <form id="kt_modal_multimedia_form" class="form" method="POST" action="javascript:void(0)" enctype="multipart/form-data">
                                    <input type="hidden" id="producto_id" name="producto_id" v-model="idProducto">
                                    <!--begin::Input group-->
                                    <div class="d-flex flex-column fv-row mb-7">
                                        <label class="required form-label fw-bold">Archivo multimedia</label>
                                        <input class="form-control" type="file" id="formFile" name="archivo" @change="imgPreview" required>
                                        <div class="image-preview w-25 mx-auto">
                                            <img src="{{ asset('assets-1/media/svg/files/blank-image.svg') }}" id="image-preview" class="img-fluid">
                                        </div>
                                    </div>
                                    <!--end::Input group-->

                                    <!--begin::Actions-->
                                    <div class="text-end pt-15">
                                        <button type="submit" class="btn btn-sm btn-primary" :disabled="loading">Agregar multimedia</button>
                                    </div>
                                </form>
                            </div>
                            <div v-else>
                                <div>
                                    <v-client-table :data="multimedia" :columns="columnsMultimedia" :options="optionsMultimedia">
                                        <div slot="preview" slot-scope="props">
                                            <img :src="props.row.url" class="img-fluid" style="max-width: 100px;">
                                        </div>
                                        <div slot="acciones" slot-scope="props">
                                            <button type="button" class="btn btn-icon btn-sm btn-danger" title="Eliminar Marca" @click.prevent="deleteMultimedia(props.row.id)" :data-kt-indicator="props.row.eliminando ? 'on' : 'off'">
                                                <span class="indicator-label"><i class="fas fa-trash-alt"></i></span>
                                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                            </button>
                                        </div>
                                    </v-client-table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Multimedia-->


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
                productos: [],
                categorias: [],
                subcategorias: [],
                subcategorias_filter: [],
                colecciones: [],
                columns: ['id', 'sku', 'nombre', 'descripcion', 'categoria', 'subcategoria', 'precio', 'acciones'],
                options: {
                    headings: {
                        id: 'ID',
                        sku: 'Sku',
                        nombre: 'Nombre',
                        descripcion: 'Descripción',
                        categoria: 'Categoría',
                        subcategoria: 'Subcategoría',
                        precio: 'Precio',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        id: 'align-middle px-2 ',
                        sku: 'align-middle ',
                        nombre: 'align-middle ',
                        descripcion: 'align-middle text-center ',
                        categoria: 'align-middle text-center ',
                        subcategoria: 'align-middle text-center ',
                        precio: 'align-middle text-center',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['sku', 'nombre', 'descripcion', 'categoria', 'subcategoria', 'precio'],
                    filterable: ['sku', 'nombre', 'descripcion', 'categoria', 'subcategoria', 'precio'],
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
                columnsMultimedia: ['preview', 'file_name', 'acciones'],
                optionsMultimedia: {
                    headings: {
                        preview: 'Preview',
                        file_name: 'Nombre',
                        acciones: 'Acciones',
                    },
                    columnsClasses: {
                        preview: 'align-middle px-2 ',
                        file_name: 'align-middle ',
                        acciones: 'align-middle text-center px-2 ',
                    },
                    sortable: ['file_name'],
                    filterable: ['file_name'],
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

                categoriaFilter: null,
                subcategoriaFilter: null,
                coleccionFilter: null,

                idProducto: null,
                producto: null,
                nombre: null,
                descripcion: null,
                sku: null,
                precio: null,
                idCategoria: null,
                idSubcategoria: null,
                idColecciones: [],
                visitas: 1,
                estatus: 1,
                caracteristicas_subcategoria: [],
                extras: [],
                extra_input: null,
                formMultimedia: false,
                multimedia: [],

                setEdit: false,
                validator: null,
                isEdit: false,
                msgError: false,
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

                container = document.querySelector('#kt_modal_producto_multimedia_body');
                if (container) {
                    vm.blockUIModal = new KTBlockUI(container);
                }

                vm.getProductos(true);
                vm.getCategorias();
                vm.getColecciones();
                vm.formValidate();
                $("#kt_modal_add_producto").on('hidden.bs.modal', event => {
                    vm.formValidate();
                    vm.clearCampos();
                });
            },
            methods: {
                initForm() {
                    if (this.formMultimedia) {
                        let vm = this;
                        setTimeout(() => {
                            $('#kt_modal_multimedia_form').submit(function(e) {
                                e.preventDefault();
                                var formData = new FormData(this);
                                let datos = Object.fromEntries(formData.entries());
                                $.ajax({
                                    type: 'POST',
                                    url: "/api/productos/multimedia/save",
                                    data: formData,
                                    cache: false,
                                    contentType: false,
                                    processData: false,
                                }).done(function(res) {
                                    if (res.status) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            "Se a agregado contenido multimedia al producto con éxito",
                                            "success"
                                        )
                                        vm.multimedia = res.results;
                                        vm.formMultimedia = false;
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res.message,
                                            "warning"
                                        );
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    console.log("Request failed saveMultimedia: " + textStatus, jqXHR);
                                });
                            });
                        }, 500);
                    } else {
                        $('#image-preview').attr('src', '{{ asset('assets-1/media/svg/files/blank-image.svg') }}');
                    }
                },
                getProductos(showLoader) {
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
                        url: '/api/productos/all',
                        type: 'GET',
                    }).done(function (res) {
                        vm.productos = res.results;
                    }).fail(function (jqXHR, textStatus) {
                        if (textStatus != 'abort') {
                            console.log("Request failed getProductos: " + textStatus, jqXHR);
                        }
                    }).always(function () {
                        vm.loading = false;

                        if (vm.blockUI && vm.blockUI.isBlocked()) {
                            vm.blockUI.release();
                        }
                    });
                },
                getColecciones() {
                    let vm = this;
                    $.get('/api/colecciones/all', res => {
                        vm.colecciones = res.results;
                    }, 'json');
                },
                getCategorias() {
                    let vm = this;
                    $.get('/api/categorias/all', res => {
                        vm.categorias = res.results;
                    }, 'json');
                },
                getSubcategoriasFilter() {
                    let vm = this;
                    $.get(`/api/sub-categorias/categoria/${vm.categoriaFilter}`, res => {
                        vm.subcategorias_filter = res.results;
                    }, 'json');
                },
                getSubcategorias() {
                    let vm = this;
                    if (vm.setEdit == false) {
                        $.get(`/api/sub-categorias/categoria/${vm.idCategoria}`, res => {
                            vm.subcategorias = res.results;
                        }, 'json');
                    }
                },
                getMultimedia(idProducto, showLoader) {
                    let vm = this;
                    vm.idProducto = idProducto;
                    vm.formMultimedia = false;

                    if (showLoader) {
                        if (!vm.blockUIModal) {
                            let container = document.querySelector('#kt_modal_producto_multimedia_body');
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
                        url: `/api/productos/${idProducto}/multimedia/all`,
                        type: 'GET',
                    }).done(function (res) {
                        vm.multimedia = res.results;
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
                saveProducto() {
                    let vm = this
                    vm.formValidate();

                    vm.caracteristicas_subcategoria.forEach(item => {
                        vm.validator.addField(`caracteristica${item.etiqueta}`, {
                            validators: {
                                notEmpty: {
                                    message: 'Campo requerido',
                                    trim: true
                                },
                            }
                        });
                    });

                    vm.extras?.forEach(item => {
                        vm.validator.addField(`extra${item.etiqueta}`, {
                            validators: {
                                notEmpty: {
                                    message: 'Campo requerido',
                                    trim: true
                                },
                            }
                        });
                    });

                    vm.validator.validate().then(function(status) {
                        if (status == 'Valid') {
                            vm.loading = true;
                            $.ajax({
                                method: "POST",
                                url: "/api/productos/save",
                                data: {
                                    id: vm.isEdit ? vm.idProducto : null,
                                    categoria_id: vm.idCategoria,
                                    subcategoria_id: vm.idSubcategoria,
                                    sku: vm.sku,
                                    precio: vm.precio,
                                    nombre: vm.nombre,
                                    descripcion: vm.descripcion,
                                    caracteristicas_json: vm.caracteristicas_subcategoria,
                                    extras_json: vm.extras,
                                    colecciones: vm.idColecciones,
                                    visitas: vm.visitas,
                                    estatus: vm.estatus,
                                }
                            }).done(function(res) {
                                if (res.status === true) {
                                    Swal.fire(
                                        "¡Guardado!",
                                        vm.isEdit ?
                                        "Los datos del se han actualizado con éxito" :
                                        "Los datos del se han almacenado con éxito",
                                        "success"
                                    );
                                    vm.getProductos();
                                    $('#kt_modal_add_producto').modal('hide');
                                } else {
                                    Swal.fire(
                                        "¡Error!",
                                        res?.message ?? "No se pudo realizar la acción",
                                        "warning"
                                    );
                                }
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed saveProducto: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    });

                },
                deleteProducto(idProducto) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro de la producto?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            vm.loading = true;
                            let index = vm.productos.findIndex(item => item.id == idProducto);
                            if(index >= 0){
                                vm.$set(vm.productos[index], 'eliminando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: "/api/productos/delete",
                                data: {
                                    producto_id: idProducto
                                }
                            }).done(function(res) {
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro del producto ha sido eliminado con éxito',
                                    'success'
                                );
                                vm.getProductos();
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteProducto: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.productos.findIndex(item => item.id == idProducto);
                                if(index >= 0){
                                    vm.$set(vm.productos[index], 'eliminando', false);
                                }
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
                deleteMultimedia(idMultimedia) {
                    let vm = this;
                    Swal.fire({
                        title: '¿Estas seguro de que deseas eliminar el registro de la multimedia?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let index = vm.multimedia.findIndex(item => item.id == idMultimedia);
                            if(index >= 0){
                                vm.$set(vm.multimedia[index], 'eliminando', true);
                            }
                            $.ajax({
                                method: "POST",
                                url: "/api/productos/multimedia/delete",
                                data: {
                                    productos_multimedia_id: idMultimedia
                                }
                            }).done(function(res) {
                                vm.getMultimedia(vm.idProducto);
                                Swal.fire(
                                    'Registro eliminado',
                                    'El registro de la multimedia ha sido eliminado con éxito',
                                    'success'
                                );
                            }).fail(function(jqXHR, textStatus) {
                                console.log("Request failed deleteMultimedia: " + textStatus, jqXHR);
                                Swal.fire("¡Error!", "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.", "error");

                                index = vm.multimedia.findIndex(item => item.id == idMultimedia);
                                if(index >= 0){
                                    vm.$set(vm.multimedia[index], 'eliminando', true);
                                }
                            }).always(function(event, xhr, settings) {
                                vm.loading = false;
                            });
                        }
                    })
                },
                getCaracteristicasSubcategoria() {
                    let vm = this;
                    if (vm.setEdit == false) {
                        let subcategoria = vm.subcategorias.find(item => item.id == vm.idSubcategoria);
                        let list = subcategoria?.caracteristicas_json.map((caracteristica, index) => ({ etiqueta: caracteristica, valor: ""}) );

                        vm.caracteristicas_subcategoria = list;
                    }
                },
                selectProducto(producto) {
                    let vm = this;
                    vm.clearCampos();
                    vm.isEdit = true;
                    vm.setEdit = true;

                    vm.idProducto = producto.id;
                    vm.producto = producto;
                    vm.sku = producto.sku;
                    vm.nombre = producto.nombre;
                    vm.descripcion = producto.descripcion;
                    vm.precio = producto.precio;
                    producto.colecciones?.forEach(element => {
                        vm.idColecciones.push(element.id);
                    });
                    vm.visitas = producto.visitas;
                    vm.estatus = producto.estatus;
                    vm.extras = producto.extras_json ?? [];
                    vm.idCategoria = producto.categoria_id;

                    $.ajax({
                        method: 'GET',
                        url: `/api/sub-categorias/categoria/${producto.categoria_id}`,
                    }).done(function(res) {
                        vm.subcategorias = res.results;
                        vm.$nextTick(() => {
                            let subcategoria = vm.subcategorias.find(item => item.id == producto.subcategoria_id);
                            let list = [];
                            subcategoria?.caracteristicas_json.forEach((caracteristica, index) => {
                                const result = producto.caracteristicas_json.find((element) => element.etiqueta == caracteristica);
                                if(result){
                                    list.push({
                                        etiqueta: caracteristica,
                                        valor: result.valor,
                                    });
                                }else{
                                    list.push({
                                        etiqueta: caracteristica,
                                        valor: "",
                                    });
                                }
                                
                            });
                            vm.caracteristicas_subcategoria = list;

                            vm.idSubcategoria = producto.subcategoria_id;
                        });
                    }).fail(function(jqXHR, textStatus) {
                        vm.subcategorias = [];
                    }).always(function() {
                        vm.$nextTick(() => {
                            vm.setEdit = false;
                        });
                    });
                },
                addExtra() {
                    let vm = this;
                    if (vm.extra_input) {
                        vm.extras.push({
                            etiqueta: vm.extra_input,
                            valor: "",
                        });
                        vm.extra_input = null;
                    }
                    if (this.extras.length > 0) {
                        this.msgError = false;
                    } else {
                        this.msgError = true;
                    }
                },
                deleteExtra(index) {
                    this.extras.splice(index, 1);
                },
                imgPreview() {
                    let vm = this;
                    let file = document.getElementById('formFile').files[0];
                    let reader = new FileReader();
                    reader.onloadend = function() {
                        document.getElementById('image-preview').src = reader.result;
                    }
                    if (file) {
                        reader.readAsDataURL(file);
                    } else {
                        document.getElementById('image-preview').src = "";
                    }
                },
                formValidate() {
                    let vm = this;
                    if(vm.validator){
                        vm.validator.destroy();
                        vm.validator = null;
                    }

                    this.validator = FormValidation.formValidation(
                        document.getElementById('kt_modal_add_producto_form'), {
                            fields: {
                                'cat_id': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Seleccionar una categoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'subcat_id': {
                                    validators: {
                                        notEmpty: {
                                            message: 'Seleccionar una subcategoría es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'nombre': {
                                    validators: {
                                        notEmpty: {
                                            message: 'El nombre del producto es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'descripcion': {
                                    validators: {
                                        notEmpty: {
                                            message: 'La descripción del producto es requerida',
                                            trim: true,
                                        },
                                    }
                                },
                                'sku': {
                                    validators: {
                                        notEmpty: {
                                            message: 'El sku del producto es requerido',
                                            trim: true,
                                        },
                                    }
                                },
                                'precio': {
                                    validators: {
                                        notEmpty: {
                                            message: 'El precio del producto es requerido',
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
                clearCampos() {
                    this.isEdit = false;
                    this.loading = false;
                    this.msgError = false;

                    this.idProducto = null;
                    this.producto = null;
                    this.sku = null;
                    this.nombre = null;
                    this.descripcion = null;
                    this.idCategoria = null;
                    this.idSubcategoria = null;
                    this.precio = null;
                    this.idColecciones = [];
                    this.visitas = 1;
                    this.estatus = 1;
                    this.caracteristicas_subcategoria = [];
                    this.extras = [];
                    this.extra = null;
                },
            },
            computed: {
                listaProductos() {
                    let vm = this;
                    if (!vm.categoriaFilter && !vm.subcategoriaFilter && !vm.coleccionFilter) {
                        return vm.productos;
                    }
                    let productos = vm.productos?.filter(function(e) {
                        let col = e.colecciones.find(item => {
                            return item.id == vm.coleccionFilter;
                        });

                        let categoriaFilter = vm.categoriaFilter ? e.categoria_id == vm.categoriaFilter : true;
                        let subcategoriaFilter = vm.subcategoriaFilter ? e.subcategoria_id == vm.subcategoriaFilter : true;
                        let coleccionFilter = vm.coleccionFilter ? col != null : true;

                        return categoriaFilter && subcategoriaFilter && coleccionFilter;
                    }) ?? [];
                    return productos;
                },
                listaCategorias() {
                    return this.categorias.map(item => ({ id: item.id, text: item.nombre }));
                },
                listaSubcategoriasFilter() {
                    return this.subcategorias_filter.map(item => ({ id: item.id, text: item.nombre }));
                },
                listaSubcategorias() {
                    return this.subcategorias.map(item => ({ id: item.id, text: item.nombre, caracteristicas: item.caracteristicas_json }));
                },
                listaColecciones() {
                    return this.colecciones.map(item => ({ id: item.id, text: item.nombre }));
                },
            }
        });
        function formatoNumero(input) {
            let valor = input.value;

            valor = parseFloat(valor.replace(/[^\d.]/g, ''));

            if (!isNaN(valor)) {
                valor = '$' + valor.toFixed(2);
            } else {
                valor = '';
            }
            input.value = valor;
        }
        Vue.use(VueTables.ClientTable);
    </script>
@endsection