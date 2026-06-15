@extends('template')
@section('main')
<div class="formulario-de-pasos-experiencias" data-step="{{ $paso }}"></div>
<div class="margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-md-10">
            <div class="main-panel min-height mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-3 pl-4 pr-4">
                        @include('pasos::menu_lateral')
                    </div>

                    <div class="col-md-9 mt-4 mt-sm-0 pl-4 pr-4">
                        <form method="post" id="list_des" action="{{ route('reda.negocios.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-0 pr-sm-0 ">
                                <div class="form-group col-md-12 main-panelbg pb-3 pt-3 mt-sm-0 ">
                                    <h4 class="text-18 font-weight-700 pl-3">{{ __('reda-alojamiento::messages.general.descripcion') }}</h4>
                                </div>

                                <div class="row mt-4 p-4">
                                    <div class="col-md-12">
                                        <label>{{ __('reda-alojamiento::messages.general.nombre_del_negocio') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="titulo" class="form-control text-16" value="{{ $result->titulo }}">
                                        @error('titulo')
                                            <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <label>{{ __('reda-alojamiento::messages.general.descripcion_del_negocio') }} <span class="text-danger">*</span></label>
                                        <textarea name="descripcion" class="form-control text-16" rows="6">{{ $result->descripcion }}</textarea>
                                        @error('descripcion')
                                            <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <label>{{ __('reda-alojamiento::messages.general.categoria_del_negocio') }} <span class="text-danger">*</span></label>
                                        <select name="categoria_negocio" class="form-control text-16 select-search" id="categoria_negocio">
                                            <option value="" disabled {{ (old('categoria_negocio') ?? $result->categoria_negocio) == '' ? 'selected' : '' }}>
                                                {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                            </option>
                                            @foreach($categoriasNegocios as $key => $value)
                                                <option value="{{ $key }}" {{ (old('categoria_negocio') ?? $result->categoria_negocio) == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('categoria_negocio')
                                            <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- Logo del Negocio -->
                                    <div class="col-md-12 mt-4">
                                        <label class="form-label small font-weight-700 w-100 mb-2">{{ __('Logo del negocio') }} <span class="text-danger">*</span></label>
                                        <div class="actividad-foto-card-container {{ !$result->ruta_imagenes ? 'no-image' : '' }}" id="foto-container-logo">
                                            @if($result->ruta_imagenes)
                                                <img src="{{ asset('public/images/logos_negocios/'.$result->ruta_imagenes) }}"
                                                     class="img-fluid rounded-3 shadow-sm" alt="Logo">
                                                <label class="edit-photo-overlay-outline" for="file-logo" title="{{ __('Cambiar logo') }}">
                                                    <i class="fa fa-pencil-alt"></i>
                                                </label>
                                            @else
                                                <label class="upload-placeholder" for="file-logo">
                                                    <i class="fa fa-image fa-2x mb-2 text-muted"></i>
                                                    <span class="small text-muted">{{ __('Subir logo') }}</span>
                                                </label>
                                            @endif
                                            <input id="file-logo" type="file" name="logo_negocio"
                                                   data-id="{{ $result->id }}" data-origen="logo-negocio" class="upload_photos" accept="image/*" style="display:none;">
                                        </div>
                                        <input type="hidden" name="logo_exists" id="logo_exists" value="{{ $result->ruta_imagenes ? '1' : '' }}">
                                        @error('logo_exists')
                                            <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                            </div>
                                        @enderror
                                        <p class="text-muted small mt-2 italic">
                                            <i class="fa fa-info-circle mr-1"></i> {{ __('Este logo aparecerá en el listado y detalle de su negocio.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 p-0 mt-4 mb-5">
                                <div class="row m-0 justify-content-between">
                                    <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn_next">
                                        <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                        <span id="btn_next-text">{{ __('reda-alojamiento::messages.general.siguiente') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    @include('reda-alojamiento::general.main_footer')
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
    
    <script>
        // Escuchamos el evento de actualización de media para el logo
        document.addEventListener('mediaUpdated', function(e) {
            if (e.detail.origen === 'logo-negocio') {
                const data = e.detail.response;
                const container = $('#foto-container-logo');
                const negocioId = data.id;
                const nuevaUrl = data.path;

                if (nuevaUrl && container.length) {
                    container.html(`
                        <img src="${nuevaUrl}?v=${new Date().getTime()}" class="img-fluid rounded-3 shadow-sm" alt="Logo">
                        <label class="edit-photo-overlay-outline" for="file-logo" title="${window.RedaAlojamientoJson['Cambiar logo'] || 'Cambiar logo'}">
                            <i class="fa fa-pencil-alt"></i>
                        </label>
                        <input id="file-logo" type="file" name="logo_negocio"
                               data-id="${negocioId}" data-origen="logo-negocio" class="upload_photos" accept="image/*" style="display:none;">
                    `);
                    container.removeClass('no-image');
                }
            }
        });
    </script>
@endsection
