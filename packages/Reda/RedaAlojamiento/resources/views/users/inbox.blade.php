@extends('template')
@section('main')
<div class="margin-top-85">
	<div class="row m-0">
		{{-- sidebar start--}}
		@include('users.sidebar')
		{{--sidebar end--}}

		<div class="col-lg-10 p-0 mb-5 min-height">
			<div class="main-panel">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12 p-0 mb-3">
							<div class="list-bacground mt-4 rounded-3 p-4 border">
								<span class="text-18 pt-4 pb-4 font-weight-700">{{ __('Inbox') }}</span>
							</div>
						</div>
					</div>
					@if (isset($booking))
						<div class="row">
							<div class="col-md-9 p-0">
								<div class="container-inbox reda-inbox-wrapper">
									<sidebar>
										<div class="list-wrap overflow-hidden-x">
											@forelse ($sidebar_messages as $sideMsg)
												@php
                                                    $user = $sideMsg->chat_partner;
                                                    // Determinar si el último mensaje lo envió el usuario actual (web)
                                                    $loEnvieYo = ($sideMsg->sender_id == Auth::id() && ($sideMsg->sender_type === 'user' || !$sideMsg->sender_type));
												@endphp
												<div class="list p-2 conversassion" data-id="{{ $sideMsg->booking_id }}">
                                                    <div class="reda-chat-avatar-container">
                                                        <img class="reda-property-bg" src="{{ optional($sideMsg->properties)->cover_photo ?? asset('img/unnamed.png') }}" alt="property" />
                                                        <div class="reda-user-avatars">
                                                            <img class="reda-user-avatar guest" src="{{ reda_get_profile_src($sideMsg->bookings->users) }}" title="{{ __('Turista') }}: {{ $sideMsg->bookings->users->first_name }}" />
                                                            <img class="reda-user-avatar host" src="{{ reda_get_profile_src($sideMsg->bookings->host) }}" title="{{ __('Anfitrión') }}: {{ $sideMsg->bookings->host->first_name }}" />
                                                        </div>
                                                    </div>
													<div class="info">
														<h3 class="font-weight-700 mb-1 d-flex justify-content-between align-items-start">
                                                            <span class="reda-name-sidebar line-clamp-2 flex-grow-1 pr-2">{{ optional($sideMsg->properties)->name ?? __('Propiedad') }}</span>
                                                            <span class="text-muted text-12 text-right flex-shrink-0"> {{ $sideMsg->created_at->diffForHumans() }}</span>
                                                        </h3>
														<div class="d-flex justify-content-between">
															<div>
																<p class="text-muted text-14 mb-1 text pr-4">{{ $user->first_name ?? 'Usuario' }}</p>
																@if (!$loEnvieYo)
																	<p class="text-14 m-0 {{ $sideMsg->read == 0  ? 'text-success font-weight-bold' : '' }}" id="msg-{{ $sideMsg->booking_id }}" ><i class="far fa-comment-alt"></i> {{ str_limit($sideMsg->message, 20) }} </p>
																@else
																	<p class="text-14 m-0" >
                                                                        <i class="fas {{ $sideMsg->read == 1 ? 'fa-check-double text-primary' : 'fa-check' }} mr-1"></i>
                                                                        {{ str_limit($sideMsg->message, 20) }} 
                                                                    </p>
																@endif
															</div>
														</div>
													</div>
												</div>
											@empty
												{{ __('No conversation found') }}
											@endforelse
										</div>
									</sidebar>

									<div class="content-inbox container-fluid p-0" id="messages">
										@include('reda-alojamiento::users.messages', ['messages' => $messages])
									</div>
								</div>
							</div>

							<div class="col-md-3 card p-0 " id="booking">
								@include('users.booking')
							</div>
						</div>
					@else
						<div class="row jutify-content-center w-100 p-4 mt-4">
							<div class="text-center w-100">
								<img src="{{ asset('img/unnamed.png') }}" alt="notfound" class="img-fluid">
								<p class="text-center">{{ __('You don’t have any messages when you do, you’ll find them here.') }} </p>
							</div>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
	<script type="text/javascript">
		'use strict'
		var token = "{{ csrf_token() }}";
        window.vRentInboxDisabled = true;
	</script>
	<script src="{{ asset('public/js/reda/vistas/inbox/inbox.min.js') }}?v={{ time() }}"></script>
@endpush

{{-- Overlay para ampliar fotos de chat --}}
<div class="reda-image-zoom-overlay" id="reda-chat-zoom-overlay">
    <div class="zoom-content-wrapper">
        <span class="btn-close-zoom" id="btn-close-reda-zoom">&times;</span>
        <img class="zoom-property-bg" id="zoom-prop-img" src="" alt="property" />
        <div class="zoom-user-avatars">
            <img class="zoom-user-avatar guest" id="zoom-guest-img" src="" />
            <img class="zoom-user-avatar host" id="zoom-host-img" src="" />
        </div>
    </div>
</div>
