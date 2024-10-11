@extends('erp.base')

@section('meta_description')
    <style>
        .loader {
            display: inline-block;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            position: relative;
            animation: rotate 1s linear infinite
        }

        .loader::before {
            content: "";
            box-sizing: border-box;
            position: absolute;
            inset: 0px;
            border-radius: 50%;
            border: 3px solid var(--bs-primary);
            animation: prixClipFix 2s linear infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg)
            }
        }

        @keyframes prixClipFix {
            0% {
                clip-path: polygon(50% 50%, 0 0, 0 0, 0 0, 0 0, 0 0)
            }

            25% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 0, 100% 0, 100% 0)
            }

            50% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 100% 100%, 100% 100%)
            }

            75% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 100%)
            }

            100% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 0)
            }
        }
    </style>
@endsection

@section('content')
    <div id="app">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content">
            <!--begin::Card-->
            <div class="card card-flush">
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
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_producto" @click="openModalCreate">
                            <i class="ki-outline ki-plus fs-2"></i>
                            Agregar producto
                        </a>
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
                            <a href="#" class="btn btn-icon btn-sm btn-success btn-sm me-2" title="Ver/Editar Producto" data-bs-toggle="modal" data-bs-target="#kt_modal_add_producto" @click="selectProducto(props.row)">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <a href="#" class="btn btn-icon btn-sm btn-primary btn-sm me-2" title="Multimedia" data-bs-toggle="modal" data-bs-target="#kt_modal_producto_multimedia" @click="getMultimedia(props.row.id)" id="openMultimediaBtn">
                                <i class="fas fa-photo-film"></i>
                            </a>
                            <a href="#" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Producto" @click="deleteProducto(props.row.id)">
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
        <div class="modal fade" id="kt_modal_add_producto" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_add_user_header">
                        <h2 class="fw-bold" v-if="isEdit">Actualizar producto</h2>
                        <h2 class="fw-bold" v-else>Crear producto</h2>

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
                        <form id="kt_modal_add_producto_form" class="form" action="#">
                            <!--begin::Scroll-->
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                                <div class="fv-row mb-7">
                                    <label class="required fs-6 fw-bold mb-2" for="sku">Sku</label>
                                    <input type="text" class="form-control" placeholder="Sku" id="sku" name="sku" v-model="sku">
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="required fs-6 fw-bold mb-2" for="subCat_name">Nombre</label>
                                    <input type="text" class="form-control" placeholder="Nombre del producto" id="nombre" name="nombre" v-model="nombre">
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="required fs-6 fw-bold mb-2" for="subCat_desc">Descripción</label>
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
                                    <label class="required fs-6 fw-bold mb-2" for="cat_id">Categoría</label>
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
                                    <label class="required fs-6 fw-bold mb-2" for="subcat_id">Subcategoría</label>
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
                                    <label class="required fs-6 fw-bold mb-2" for="precio">Precio</label>
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
                                        <button class="btn btn-light-success border border-success" type="button" @click="addExtra">
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
                            <!--begin::Actions-->
                            <div class="text-end pt-15">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-info" @click="saveProducto" :disabled="isDisabled" v-if="isEdit">Actualizar producto</button>
                                <button type="button" class="btn btn-info" @click="saveProducto" :disabled="isDisabled" v-else>Crear producto</button>
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
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-10">
                        <div class="text-center p-10" v-if="loadingMultimedia">
                            <span class="loader text-center"></span><br>
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
                                        <button type="submit" class="btn btn-sm btn-primary">Agregar multimedia</button>
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
                                            <a href="#" class="btn btn-icon btn-sm btn-danger btn-sm me-2" title="Eliminar Marca" @click="deleteMultimedia(props.row.id)">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
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
                validator: null,
                isEdit: false,
                isDisabled: false,
                msgError: false,
                setEdit: false,

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
                loadingMultimedia: false,
                formMultimedia: false,
                multimedia: [],
            }),
            watch: {},
            mounted() {
                this.$forceUpdate();
                this.getProductos();
                this.getCategorias();
                this.getColecciones();
                this.formValidate();
                $("#kt_modal_add_producto").on('hidden.bs.modal', event => {
                    this.validator?.resetForm();
                });
            },
            methods: {
                openModalCreate() {
                    this.isEdit = false;
                    this.clearCampos();
                },
                getProductos() {
                    let vueThis = this;
                    $.get('/api/productos/all', res => {
                        vueThis.productos = res.results;
                    }, 'json');
                },
                getColecciones() {
                    let vueThis = this;
                    $.get('/api/colecciones/all', res => {
                        vueThis.colecciones = res.results;
                    }, 'json');
                },
                getCategorias() {
                    let vueThis = this;
                    $.get('/api/categorias/all', res => {
                        vueThis.categorias = res.results;
                    }, 'json');
                },
                getSubcategoriasFilter() {
                    let vueThis = this;
                    $.get(`/api/sub-categorias/categoria/${vueThis.categoriaFilter}`, res => {
                        vueThis.subcategorias_filter = res.results;
                    }, 'json');
                },
                getSubcategorias() {
                    let vueThis = this;
                    if (vueThis.setEdit == false) {
                        $.get(`/api/sub-categorias/categoria/${vueThis.idCategoria}`, res => {
                            vueThis.subcategorias = res.results;
                        }, 'json');
                    }
                },
                getMultimedia(idProducto) {
                    let vueThis = this;
                    vueThis.idProducto = idProducto;
                    vueThis.formMultimedia = false;
                    vueThis.loadingMultimedia = true;
                    $.get(`/api/productos/${idProducto}/multimedia/all`, res => {
                        vueThis.multimedia = res.results;
                    }, 'json').always(function(res) {
                        vueThis.loadingMultimedia = false;
                    })
                },
                getCaracteristicasSubcategoria() {
                    let vueThis = this;
                    if (vueThis.setEdit == false) {
                        let subcategoria = vueThis.subcategorias.find(item => item.id == vueThis.idSubcategoria);
                        let list = [];
                        subcategoria?.caracteristicas_json.forEach((caracteristica, index) => {
                            list.push({
                                etiqueta: caracteristica,
                                valor: "",
                            });
                        });

                        vueThis.caracteristicas_subcategoria = list;
                    }
                },
                selectProducto(producto) {
                    let vueThis = this;
                    vueThis.clearCampos();
                    vueThis.isEdit = true;
                    vueThis.setEdit = true;

                    vueThis.idProducto = producto.id;
                    vueThis.producto = producto;
                    vueThis.sku = producto.sku;
                    vueThis.nombre = producto.nombre;
                    vueThis.descripcion = producto.descripcion;
                    vueThis.precio = producto.precio;
                    producto.colecciones?.forEach(element => {
                        vueThis.idColecciones.push(element.id);
                    });
                    vueThis.visitas = producto.visitas;
                    vueThis.estatus = producto.estatus;
                    vueThis.extras = producto.extras_json ?? [];
                    vueThis.idCategoria = producto.categoria_id;
                    
                    $.get(`/api/sub-categorias/categoria/${producto.categoria_id}`, res => {
                        vueThis.subcategorias = res.results;
                        vueThis.$nextTick(() => {
                            let subcategoria = vueThis.subcategorias.find(item => item.id == producto.subcategoria_id);
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
                            vueThis.caracteristicas_subcategoria = list;


                            vueThis.idSubcategoria = producto.subcategoria_id;
                            vueThis.setEdit = false;
                        });
                    }, 'json');
                },
                saveProducto() {
                    let vueThis = this
                    vueThis.validator.resetForm();
                    vueThis.validator.destroy();
                    vueThis.formValidate();

                    vueThis.caracteristicas_subcategoria.forEach(item => {
                        vueThis.validator.addField(`caracteristica${item.etiqueta}`, {
                            validators: {
                                notEmpty: {
                                    message: 'Campo requerido',
                                    trim: true
                                },
                            }
                        });
                    });

                    vueThis.extras?.forEach(item => {
                        vueThis.validator.addField(`extra${item.etiqueta}`, {
                            validators: {
                                notEmpty: {
                                    message: 'Campo requerido',
                                    trim: true
                                },
                            }
                        });
                    });

                    vueThis.validator.validate().then(function(status) {
                        if (status == 'Valid') {
                            vueThis.isDisabled = true;
                            $.ajax({
                                    method: "POST",
                                    url: "/api/productos/save",
                                    data: {
                                        id: vueThis.isEdit ? vueThis.idProducto : null,
                                        categoria_id: vueThis.idCategoria,
                                        subcategoria_id: vueThis.idSubcategoria,
                                        sku: vueThis.sku,
                                        precio: vueThis.precio,
                                        nombre: vueThis.nombre,
                                        descripcion: vueThis.descripcion,
                                        caracteristicas_json: vueThis.caracteristicas_subcategoria,
                                        extras_json: vueThis.extras,
                                        colecciones: vueThis.idColecciones,
                                        visitas: vueThis.visitas,
                                        estatus: vueThis.estatus,
                                    }
                                })
                                .done(function(res) {
                                    if (res.status === true) {
                                        Swal.fire(
                                            "¡Guardado!",
                                            vueThis.isEdit ?
                                            "Los datos del se han actualizado con éxito" :
                                            "Los datos del se han almacenado con éxito",
                                            "success"
                                        );
                                        vueThis.getProductos();
                                        $('#kt_modal_add_producto').modal('hide');
                                    } else {
                                        Swal.fire(
                                            "¡Error!",
                                            res?.message ?? "No se pudo realizar la acción",
                                            "warning"
                                        );
                                    }
                                })
                                .fail(function(jqXHR, textStatus) {
                                    console.log("Request failed saveProducto: " + textStatus, jqXHR);
                                    Swal.fire(
                                        "¡Error!",
                                        "Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo nuevamente.",
                                        "warning"
                                    );
                                })
                                .always(function(event, xhr, settings) {
                                    vueThis.isDisabled = false;
                                });
                        }
                    });

                },
                deleteProducto(idProducto) {
                    let vueThis = this;
                    vueThis.isDisabled = true;
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
                            $.ajax({
                                    method: "POST",
                                    url: "/api/productos/delete",
                                    data: {
                                        producto_id: idProducto
                                    }
                                })
                                .done(function(res) {
                                    Swal.fire(
                                        'Registro eliminado',
                                        'El registro del producto ha sido eliminado con éxito',
                                        'success'
                                    );
                                    vueThis.getProductos();
                                })
                                .always(function(event, xhr, settings) {
                                    vueThis.isDisabled = false;
                                });
                        } else {
                            vueThis.isDisabled = false;
                        }
                    })
                },
                addExtra() {
                    let vueThis = this;
                    if (vueThis.extra_input) {
                        vueThis.extras.push({
                            etiqueta: vueThis.extra_input,
                            valor: "",
                        });
                        vueThis.extra_input = null;
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
                clearCampos() {
                    this.isEdit = false;
                    this.isDisabled = false;
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
                formValidate() {
                    let vueThis = this;
                    const form = document.getElementById('kt_modal_add_producto_form');

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
                imgPreview() {
                    let vueThis = this;
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
                                Swal.fire(
                                    "¡Error!",
                                    "No se pudo eliminar la multimedia",
                                    "warning"
                                );
                            });
                        }
                    })
                }
            },
            computed: {
                listaProductos() {
                    let vueThis = this;
                    if (!vueThis.categoriaFilter && !vueThis.subcategoriaFilter && !vueThis.coleccionFilter) {
                        return vueThis.productos;
                    }
                    let productos = vueThis.productos?.filter(function(e) {
                        let col = e.colecciones.find(item => {
                            return item.id == vueThis.coleccionFilter;
                        });

                        let categoriaFilter = vueThis.categoriaFilter ? e.categoria_id == vueThis.categoriaFilter : true;
                        let subcategoriaFilter = vueThis.subcategoriaFilter ? e.subcategoria_id == vueThis.subcategoriaFilter : true;
                        let coleccionFilter = vueThis.coleccionFilter ? col != null : true;

                        return categoriaFilter && subcategoriaFilter && coleccionFilter;
                    }) ?? [];
                    return productos;
                },
                listaCategorias() {
                    let categorias = [];
                    this.categorias.forEach(item => {
                        categorias.push({
                            id: item.id,
                            text: item.nombre
                        });
                    });
                    return categorias;
                },
                listaSubcategoriasFilter() {
                    let subcategorias = [];
                    this.subcategorias_filter.forEach(item => {
                        subcategorias.push({
                            id: item.id,
                            text: item.nombre
                        });
                    });
                    return subcategorias;
                },
                listaSubcategorias() {
                    let subcategorias = [];
                    this.subcategorias.forEach(item => {
                        subcategorias.push({
                            id: item.id,
                            text: item.nombre,
                            caracteristicas: item.caracteristicas_json
                        });
                    });
                    return subcategorias;
                },
                listaColecciones() {
                    let colecciones = [];
                    this.colecciones.forEach(item => {
                        colecciones.push({
                            id: item.id,
                            text: item.nombre
                        });
                    });
                    return colecciones;
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