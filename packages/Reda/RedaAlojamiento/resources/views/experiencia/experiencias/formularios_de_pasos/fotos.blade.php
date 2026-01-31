@extends('template')
@section('main')
<div class="formulario-de-pasos" data-step="{{ $paso }}"></div>
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
                        <form id="img_form" enctype='multipart/form-data' method="post" action="{{ route('reda.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-0 pr-sm-0">
                                <div class="form-group col-md-12 main-panelbg pb-3 pt-3 mt-sm-0">
                                    <h4 class="text-18 font-weight-700 pl-3">{{ __('Fotos') }}</h4>
                                </div>

                                <div class="row mt-4 p-4">
                                    <div class="col-md-12">
                                        <div class="alert alert-danger d-none" id="error-message"></div>
                                        <input type="file" name="photos[]" class="form-control text-16" id="photo_file" multiple accept="image/*">
                                        <p class="text-14 mt-2 text-muted">{{ __('Elige imágenes de alta calidad (jpg, png, gif)') }}</p>
                                    </div>
                                </div>

                                <div class="row p-4" id="photo-list">
                                    @forelse($result->fotos as $foto)
                                        <div class="col-md-4 mt-4 photo-item" id="photo-{{ $foto->id }}">
                                            <div class="card">
                                                <img src="{{ asset('public/images/experiencias/' . $result->id . '/' . $foto->photo) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                                <div class="card-body p-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-photo" data-id="{{ $foto->id }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-md-12" id="no-photos-message">
                                            <p class="text-center text-muted">{{ __('No hay fotos subidas todavía.') }}</p>
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

@section('validation_script')
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js?v=' . time()) }}"></script>
@endsection