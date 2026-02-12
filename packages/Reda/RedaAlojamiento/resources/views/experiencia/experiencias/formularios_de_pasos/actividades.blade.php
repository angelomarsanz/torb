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
                        <h4 class="font-weight-700">Actividades</h4>
                        <form method="post" id="list_des" action="{{ route('reda.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8' enctype="multipart/form-data">
                            {{ csrf_field() }}
                            
                            <div class="table-responsive border rounded-3 mt-4">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 80px;">Nro.</th>
                                            <th>Descripción</th>
                                            <th style="width: 250px;">Foto</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($actividades as $actividad)
                                        <tr>
                                            <td class="vertical-align-top pt-3">
                                                <input type="number" 
                                                    name="actividades[{{ $actividad->id }}][orden_actividad]" 
                                                    value="{{ old('actividades.'.$actividad->id.'.orden_actividad', $actividad->orden_actividad) }}" 
                                                    class="form-control text-center @error('actividades.'.$actividad->id.'.orden_actividad') is-invalid @enderror" 
                                                    min="1"
                                                    required>
                                                @error('actividades.'.$actividad->id.'.orden_actividad')
                                                    <span class="text-danger small font-weight-700">Debe ser un número válido mayor a cero.</span>
                                                @enderror
                                            </td>
                                            <td class="vertical-align-top pt-3">
                                                <textarea name="actividades[{{ $actividad->id }}][descripcion_actividad]" 
                                                        class="form-control @error('actividades.'.$actividad->id.'.descripcion_actividad') is-invalid @enderror" 
                                                        placeholder="Describe la actividad..."
                                                        rows="2"
                                                        required>{{ old('actividades.'.$actividad->id.'.descripcion_actividad', $actividad->descripcion_actividad) }}</textarea>
                                                @error('actividades.'.$actividad->id.'.descripcion_actividad')
                                                    <span class="text-danger small font-weight-700">Este campo es obligatorio.</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <div class="actividad-foto-container {{ !$actividad->foto_actividad ? 'placeholder-height' : '' }}">
                                                    @if($actividad->foto_actividad)
                                                        <img src="{{ asset('public/images/actividades_experiencias/' . $actividad->foto_actividad) }}" alt="Foto">

                                                        <label class="edit-photo-overlay" for="file-{{ $actividad->id }}">
                                                            <i class="fa fa-edit"></i> Editar
                                                        </label>
                                                        
                                                        <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]" data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
                                                    @else
                                                        <label class="custom-file-upload m-0" for="file-{{ $actividad->id }}">
                                                            <i class="fa fa-cloud-upload"></i> Cargar foto
                                                            <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]" data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
                                                        </label>
                                                    @endif
                                                </div>
                                                @error("foto_actividad_id_" . $actividad->id)
                                                    <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                                                        <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                                    </div>
                                                @enderror
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr class="bg-light no-validar"">
                                            <td colspan="3" class="text-center p-0">
                                                <a href="{{ route('reda.experiencias.actividades.add', $result->id) }}" 
                                                class="btn btn-link w-100 py-3 text-success decoration-none font-weight-700"
                                                id="btn-add-actividad">
                                                    <i class="fa fa-plus-circle"></i> Agregar una nueva actividad
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-center">
                                {{ $actividades->appends(request()->query())->links() }}
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
<div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Recortar Imagen') }}</h5>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-success" id="crop-and-upload" data-origen="actividades-experiencias">{{ __('Guardar Cambios') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@section('validation_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/js/additional-method.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js?v=' . time()) }}"></script>
@endsection