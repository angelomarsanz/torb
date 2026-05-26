@extends('template')
@section('main')
<div class="formulario-de-pasos-experiencias" data-step="{{ $paso }}"></div>
<div class="margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-md-10">
            <div class="main-panel min-height mt-4" id="seccion-productos-servicios">
                <div class="row justify-content-center">
                    <div class="col-md-3 pl-4 pr-4">
                        @include('pasos::menu_lateral')
                    </div>

                    <div class="col-md-9 mt-4 mt-sm-0 pl-4 pr-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-700 m-0">{{ __('reda-alojamiento::messages.general.productos_y_servicios') }}</h4>
                            <button type="button" class="btn vbtn-outline-success font-weight-700" id="btn-add-actividad" data-add-url="{{ route('reda.experiencias.actividades.add', $result->id) }}">
                                <i class="fa fa-plus-circle mr-1"></i> {{ __('reda-alojamiento::messages.general.agregar_una_nueva_actividad') }}
                            </button>
                        </div>

                        <div id="bulk-actions-container" class="mb-3 d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bulk_action_select" class="font-weight-600 small mb-1">{{ __('Acciones en lote') }}</label>
                                        <select id="bulk_action_select" class="form-control form-control-sm">
                                            <option value="">{{ __('Seleccione una acción...') }}</option>
                                            <option value="multiplicar_precio">{{ __('Multiplicar precio por un porcentaje') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="post" id="list_des" action="{{ route('reda.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8' enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="stay_on_step" id="stay_on_step" value="0">

                            <div id="productos-servicios-list-container">
                                <!-- Vista de Escritorio (Tabla) -->
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-hover border rounded">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="80">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="mr-2"></div> <!-- Spacer for drag handle alignment -->
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="check-all-actividades">
                                                            <label class="custom-control-label" for="check-all-actividades"></label>
                                                        </div>
                                                    </div>
                                                </th>
                                                <th width="60">{{ __('reda-alojamiento::messages.general.nro') }}</th>
                                                <th width="80">{{ __('reda-alojamiento::messages.general.fotos') }}</th>
                                                <th>{{ __('reda-alojamiento::messages.general.nombre_del_producto_o_servicio') }}</th>
                                                <th width="150">{{ __('reda-alojamiento::messages.general.precio') }}</th>
                                                <th width="150" class="text-center">{{ __('reda-alojamiento::messages.general.acciones') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="actividades-sortable" data-reorder-url="{{ route('reda.experiencias.actividades.reordenar') }}">
                                            @foreach($actividades as $actividad)
                                                <tr class="fila-actividad-{{ $actividad->id }}" data-id="{{ $actividad->id }}">
                                                    <td class="align-middle text-muted text-center">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="cursor-move mr-2"><i class="fa fa-bars"></i></div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input check-actividad" id="check-actividad-{{ $actividad->id }}" value="{{ $actividad->id }}">
                                                                <label class="custom-control-label" for="check-actividad-{{ $actividad->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle font-weight-bold indice-actividad">{{ $actividad->orden_actividad }}</td>
                                                    <td class="align-middle">
                                                        <img src="{{ $actividad->foto_actividad ? asset('public/images/actividades_experiencias/'.$actividad->foto_actividad) : asset('public/images/default-image.png') }}"
                                                            class="img-thumbnail rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                    </td>
                                                    <td class="align-middle text-truncate" style="max-width: 250px;">{{ $actividad->nombre_actividad ?: '---' }}</td>
                                                    <td class="align-middle">
                                                        @if($actividad->precio)
                                                            {{ $actividad->currency->code ?? '' }} {{ number_format($actividad->precio, 2) }}
                                                        @else
                                                            <span class="text-muted small">---</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <div class="btn-group shadow-sm border rounded">
                                                            <button type="button" class="btn btn-sm btn-white text-info btn-modal-actividad" data-mode="view" data-id="{{ $actividad->id }}" title="Ver"><i class="fa fa-eye"></i></button>
                                                            <button type="button" class="btn btn-sm btn-white text-warning btn-edit-actividad" data-id="{{ $actividad->id }}" data-edit-url="{{ route('reda.experiencias.actividades.get_form', $actividad->id) }}" title="Editar"><i class="fa fa-pencil-alt"></i></button>
                                                            <button type="button" class="btn btn-sm btn-white text-danger btn-delete-actividad" data-delete-id="{{ $actividad->id }}" data-delete-url="{{ route('reda.experiencias.actividades.delete', $actividad->id) }}" title="Borrar"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <p class="text-muted small text-center mt-2 italic">
                                        <i class="fa fa-info-circle mr-1"></i> Para reorganizar los productos o servicios arrastra las filas desde las barras de la izquierda hacia cualquier posición que desees.
                                    </p>
                                </div>

                                <!-- Vista Móvil (Cards) -->
                                <div class="d-md-none" id="actividades-cards-container" data-reorder-url="{{ route('reda.experiencias.actividades.reordenar') }}">
                                    @if($actividades->count() > 0)
                                        <div class="mb-2 pl-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="check-all-actividades-mobile">
                                                <label class="custom-control-label font-weight-600" for="check-all-actividades-mobile">{{ __('Seleccionar todas') }}</label>
                                            </div>
                                        </div>
                                        <div id="sortable-cards-mobile">
                                            @foreach($actividades as $actividad)
                                                <div class="card mb-3 shadow-sm card-actividad-movil fila-actividad-{{ $actividad->id }}" data-id="{{ $actividad->id }}">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">

                                                            <div class="d-flex flex-column align-items-center mr-3 text-muted">
                                                                <div class="cursor-move mb-2" style="font-size: 1.2rem;"><i class="fa fa-bars"></i></div>
                                                                <div class="custom-control custom-checkbox p-0 m-0" style="min-height: auto;">
                                                                    <input type="checkbox" class="custom-control-input check-actividad" id="check-actividad-mobile-{{ $actividad->id }}" value="{{ $actividad->id }}">
                                                                    <label class="custom-control-label" for="check-actividad-mobile-{{ $actividad->id }}" style="padding-left: 1.5rem;"></label>
                                                                </div>
                                                            </div>

                                                            <div class="mr-3">
                                                                @if(!empty($actividad->foto_actividad) && file_exists(public_path('images/actividades_experiencias/'.$actividad->foto_actividad)))
                                                                    <img src="{{ asset('public/images/actividades_experiencias/'.$actividad->foto_actividad) }}"
                                                                        alt="{{ $actividad->nombre_actividad }}"
                                                                        class="rounded img-thumbnail"
                                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                                @else
                                                                    <img src="{{ asset('public/images/default-image.png') }}"
                                                                        alt="Default"
                                                                        class="rounded img-thumbnail"
                                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                                @endif
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <h6 class="font-weight-bold mb-1 text-dark text-truncate" style="max-width: 150px;">
                                                                    <span class="indice-actividad-movil">{{ $actividad->orden_actividad }}</span>.
                                                                    {{ $actividad->nombre_actividad ?: '---' }}
                                                                </h6>
                                                                <p class="m-0 text-success font-weight-600 small">
                                                                    {{ moneyFormat($actividad->moneda->code ?? 'USD', $actividad->precio) }}
                                                                </p>
                                                            </div>

                                                            <div class="d-flex flex-column align-items-end justify-content-between" style="height: 60px;">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-primary btn-edit-actividad p-1"
                                                                        data-id="{{ $actividad->id }}"
                                                                        data-edit-url="{{ route('reda.experiencias.actividades.get_form', $actividad->id) }}"
                                                                        title="{{ __('reda-alojamiento::messages.general.editar') }}">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>

                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-danger btn-delete-actividad p-1 mt-1"
                                                                        data-delete-id="{{ $actividad->id }}"
                                                                        data-delete-url="{{ route('reda.experiencias.actividades.delete', $actividad->id) }}"
                                                                        title="{{ __('reda-alojamiento::messages.general.eliminar') }}">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-muted small text-center mt-2 italic">
                                            <i class="fa fa-info-circle mr-1"></i> Para reorganizar los productos o servicios arrastra las tarjetas desde las barras de la izquierda o desde cualquier espacio vacío hacia la posición que desees.
                                        </p>
                                    @else
                                        <div class="text-center py-4 text-muted">
                                            <i class="fa fa-folder-open-o fa-2x mb-2"></i>
                                            <p class="m-0">No hay actividades registradas todavía.</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12 p-0 mt-4 mb-5">
                                    <div class="row m-0 justify-content-between">
                                        <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn_next">
                                            <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                            <span id="btn_next-text">{{ __('reda-alojamiento::messages.general.siguiente') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenedor para nuevas actividades (Formularios completos) -->
                            <div id="actividades-wrapper" class="row mt-4"></div>

                            <div id="new-producto-actions" class="col-md-12 p-0 mt-4 mb-5 d-none">
                                <div class="row m-0 justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn-cancel-new-producto">
                                        {{ __('reda-alojamiento::messages.general.cancelar') }}
                                    </button>
                                    <button type="button" class="btn vbtn-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn-save-new-producto">
                                        <i class="fa fa-spinner fa-spin d-none spinner-save"></i>
                                        <span id="btn-save-new-producto-text">{{ __('reda-alojamiento::messages.general.guardar') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @include('reda-alojamiento::general.modal_notificaciones')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bulk Price Update -->
<div class="modal fade" id="modalBulkPriceUpdate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-700">{{ __('Actualizar precios por porcentaje') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formBulkPriceUpdate">
                    <div class="form-group">
                        <label class="font-weight-600">{{ __('Tipo de cambio') }}</label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-3">
                                <input type="radio" id="tipo_cambio_aumento" name="tipo_cambio" class="custom-control-input" value="aumento" checked>
                                <label class="custom-control-label" for="tipo_cambio_aumento">{{ __('Aumento') }}</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="tipo_cambio_disminucion" name="tipo_cambio" class="custom-control-input" value="disminucion">
                                <label class="custom-control-label" for="tipo_cambio_disminucion">{{ __('Disminución') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bulk_porcentaje" class="font-weight-600">{{ __('Porcentaje (%)') }}</label>
                        <input type="number" id="bulk_porcentaje" name="porcentaje" class="form-control" placeholder="Ej: 10" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">{{ __('Precios a afectar') }}</label>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input check-precio-afectar" id="check_precio_general" value="general" checked>
                            <label class="custom-control-label" for="check_precio_general">{{ __('Precio general') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input check-precio-afectar" id="check_precio_bolivares" value="bolivares">
                            <label class="custom-control-label" for="check_precio_bolivares">{{ __('Precio para pago en Bolívares') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input check-precio-afectar" id="check_precio_promocion" value="promocion">
                            <label class="custom-control-label" for="check_precio_promocion">{{ __('Precio promoción') }}</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('reda-alojamiento::messages.general.cancelar') }}</button>
                <button type="button" class="btn btn-success" id="btn-aceptar-bulk-price">
                    <i class="fa fa-spinner fa-spin d-none spinner-bulk"></i>
                    <span class="btn-text">{{ __('Aceptar') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Único para Actividad (Crear/Editar/Ver) -->
<div class="modal fade" id="actividadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-700" id="actividadModalLabel">Detalle de Actividad</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="actividad-modal-body">
                <!-- Aquí se cargará el HTML del formulario vía Ajax -->
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-success"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('reda-alojamiento::messages.general.cancelar') }}</button>
                <button type="button" class="btn btn-success" id="btn-save-actividad-modal">{{ __('reda-alojamiento::messages.general.guardar') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('reda-alojamiento::messages.general.recortar_imagen') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="image-to-crop" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="crop_photo_id" value="">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('reda-alojamiento::messages.general.cancelar') }}</button>
                <button type="button" class="btn btn-success" id="crop-and-upload" data-origen="actividades-experiencias">{{ __('reda-alojamiento::messages.general.guardar_cambios') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@section('validation_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <!-- SortableJS para Drag and Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/js/additional-method.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js?v=' . time()) }}"></script>
@endsection
