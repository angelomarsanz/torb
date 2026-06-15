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
                        <form id="img_form" enctype='multipart/form-data' method="post" action="{{ route('reda.negocios.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            <input type="hidden" id="experiencia_id" value="{{ $result->id }}">
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-0 pr-sm-0">
                                <div class="form-group col-md-12 main-panelbg pb-3 pt-3 mt-sm-0">
                                    <h4 class="text-18 font-weight-700 pl-3">{{ __('reda-alojamiento::messages.general.fotos') }}</h4>
                                </div>

                                <div class="row mt-4 p-4">
                                    <div class="col-md-12">
                                        <div class="alert alert-danger d-none" id="error-message"></div>
                                        <input type="file" name="photo" class="upload_photos" data-origen="fotos-experiencias" accept="image/*">
                                        <p class="text-14 mt-2 text-muted">{{ __('reda-alojamiento::messages.general.elige_imagenes_de_alta_calidad_jpg_png_gif') }}</p>
                                    </div>
                                    @error('foto')
                                        <div class="mb-3">
                                            <span class="text-danger font-weight-700 text-14">
                                                <i class="fa fa-exclamation-circle mr-1"></i> {{ $message }}
                                            </span>
                                        </div>
                                    @enderror
                                </div>

                                <div class="row p-4" id="photo-list">
                                    @forelse($result->fotos as $foto)
                                        <div class="col-md-4 mb-4 photo-item" id="photo-{{ $foto->id }}">
                                            <div class="card position-relative h-100">
                                                <div class="reda-photo-controls">
                                                    <button type="button" class="reda-btn-photo-action make-default" data-id="{{ $foto->id }}" data-origen="fotos-experiencias">
                                                        <i class="fa-star {{ $foto->cover_photo ? 'fas text-warning' : 'far' }}"></i>
                                                    </button>

                                                    <div class="d-flex">
                                                        <button type="button" class="reda-btn-photo-action btn-crop mr-1" data-id="{{ $foto->id }}" data-src="{{ asset('images/experiencias/'.$result->id.'/'.$foto->photo) }}" data-origen="fotos-experiencias">
                                                            <i class="fa fa-crop text-info"></i>
                                                        </button>
                                                        <button type="button" class="reda-btn-photo-action delete-photo" data-id="{{ $foto->id }}" data-origen="fotos-experiencias">
                                                            <i class="fa fa-trash text-danger"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <img src="{{ asset('public/images/experiencias/'.$result->id.'/'.$foto->photo) }}?v={{ time() }}" class="card-img-top img-cover-200">
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-md-12" id="no-photos-message">
                                            <p class="text-center text-muted">{{ __('reda-alojamiento::messages.general.no_hay_fotos_subidas_todavia') }}</p>
                                        </div>
                                    @endforelse
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
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@section('validation_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('public/js/additional-method.min.js') }}"></script>
    @include('reda-alojamiento::general.main_footer')
    <script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
@endsection
