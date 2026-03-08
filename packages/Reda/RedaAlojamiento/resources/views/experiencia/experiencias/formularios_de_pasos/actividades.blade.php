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
                        <h4 class="font-weight-700">{{ __('reda-alojamiento::messages.php.productos_servicios') }}</h4>
                        <form method="post" id="list_des" action="{{ route('reda.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8' enctype="multipart/form-data">
                            {{ csrf_field() }}
                            
                            <div class="container-actividades mt-4">
                                <div id="actividades-wrapper" class="row">
                                    @foreach($actividades as $actividad)
                                        @include('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.fila_actividad', ['actividad' => $actividad])
                                    @endforeach
                                </div>
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
                            <button type="button" 
                                class="btn-floating-add-simple" 
                                id="btn-add-actividad" 
                                data-url="{{ route('reda.experiencias.actividades.add', $result->id) }}" 
                                title="Agregar una nueva actividad">
                                <i class="fa fa-plus"></i>
                            </button>                        
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
    <script>window.RedaTrans = @json(__('reda-alojamiento::messages'));</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/js/additional-method.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js?v=' . time()) }}"></script>
@endsection