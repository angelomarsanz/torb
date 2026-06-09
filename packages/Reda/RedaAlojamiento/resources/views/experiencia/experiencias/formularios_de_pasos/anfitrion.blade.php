@extends('template')
@section('main')
@php
    $anfitrion = $result->anfitrion;
@endphp
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
                        <form method="post" id="list_des" action="{{ route('reda.negocios.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8' enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-3 pr-sm-3 pt-4">
                                <h4 class="font-weight-700 mb-4">{{ __('Nosotros') }}</h4>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="trayectoria_profesional" class="font-weight-700">
                                                {{ __('Comente sobre ustedes, su experiencia en el negocio, personal capacitado, nombre o marca de clientes importantes que pueden recomendarlos, etc.') }}
                                            </label>
                                            <textarea name="trayectoria_profesional" id="trayectoria_profesional" class="form-control" rows="5" placeholder="{{ __('Describa aquí su trayectoria...') }}">{{ old('trayectoria_profesional', $anfitrion->trayectoria_profesional ?? '') }}</textarea>
                                            @error('trayectoria_profesional')
                                                <span class="text-danger small font-weight-700">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-4 text-center">
                                        <label class="font-weight-700 d-block mb-3">{{ __('Suba una foto de usted o de su equipo de trabajo.') }}</label>
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="actividad-foto-card-container {{ !($anfitrion->foto_anfitrion ?? '') ? 'no-image' : '' }}" id="foto-container-anfitrion">
                                                @if(!empty($anfitrion->foto_anfitrion))
                                                    <img src="{{ asset('public/images/anfitriones_experiencias/'.$anfitrion->foto_anfitrion) }}"
                                                         class="img-fluid rounded-3 shadow-sm" alt="Foto">
                                                    <label class="edit-photo-overlay-outline" for="file-anfitrion" title="Cambiar imagen">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </label>
                                                @else
                                                    <label class="upload-placeholder" for="file-anfitrion">
                                                        <i class="fa fa-image fa-2x mb-2 text-muted"></i>
                                                        <span class="small text-muted">{{ __('Subir foto') }}</span>
                                                    </label>
                                                @endif
                                                <input id="file-anfitrion" type="file" name="foto_anfitrion"
                                                       data-id="{{ $anfitrion->id ?? '' }}" class="upload_photos" accept="image/*" style="display:none;">
                                            </div>
                                            @error("foto_anfitrion")
                                                <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                    <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 p-0 mt-4 mb-5">
                                <div class="row m-0 justify-content-between">
                                    <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn_next">
                                        <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                        <span id="btn_next-text">{{ __('Siguiente') }}</span>
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

<!-- Modal para Recortar Imagen -->
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
                <button type="button" class="btn btn-success" id="crop-and-upload" data-origen="anfitrion-experiencia">{{ __('reda-alojamiento::messages.general.guardar_cambios') }}</button>
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
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/js/additional-method.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
@endsection
