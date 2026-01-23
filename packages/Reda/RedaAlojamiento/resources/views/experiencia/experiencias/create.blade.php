@extends('template')
@section('main')
<div class="mb-4 margin-top-85">
	<div class="row m-0">
		@include('users.sidebar')
		<div class="col-md-10  min-height">
			<div class="main-panel m-4 list-background border rounded-3">
				<h3 class="text-center mt-5 text-24 font-weight-700">{{ __('List Your Space') }}</h3>
				<p class="text-center text-16 pl-4 pr-4">{{ __(':x Lets you make money renting out your place.', ['x' => siteName()]) }}</p>
				<form id="list_space" method="post" action="{{ url('property/create') }}" class="mt-4" id="lys_form" accept-charset='UTF-8'>
					{{ csrf_field() }}
                    <h1>Experiencias</h1>
                    <div id="create_experiencia">
                        <!-- El contenido dinámico se cargará aquí -->
                    </div>
                    <div class="row p-4">
						<div class="col-md-12">
							<div class="float-right">
								<button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3 mt-4 mb-4" id="btn_next"> <i class="spinner fa fa-spinner fa-spin d-none" ></i>
									<span id="btn_next-text">{{ __('Continue') }}</span>

								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection