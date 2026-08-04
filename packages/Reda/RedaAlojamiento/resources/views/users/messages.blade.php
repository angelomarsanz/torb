	<header>
		@php 
			$users = ($booking->host_id == Auth::id()) ? 'users' : 'host';
		@endphp
		<a href="{{ url('users/show/' . $booking->$users->id) }}">
			<img src="{{ $booking->$users->profile_src }}" alt="img" class="img-40x40">
		</a>
		
		<div class="info">
			<div class="d-flex justify-content-between">
				<div>
					<span class="user">{{ $booking->$users->full_name }}</span>
				</div>
			</div>
		</div>
		<div class="open">
			<i class="fas fa-inbox"></i>
			<a href="javascript:;">UP</a>
		</div>
	</header>
	<div class="message-wrap-reda">
		@foreach ($messages as $message)
            @php
                $esMio = ($message->sender_id == Auth::id() && ($message->sender_type === 'user' || !$message->sender_type));
                $nombreParaMostrar = $message->custom_sender_name ?? ($message->sender->first_name ?? __('Usuario'));
                $rolParaMostrar = $message->sender_role ?? '';
            @endphp
            <div class="message-list-reda {{ $esMio ? 'me' : '' }} d-flex align-items-start">
                <div class="reda-avatar-container flex-shrink-0">
                    <img src="{{ $message->custom_sender_foto ?? reda_get_profile_src($message->sender) }}" class="rounded-circle reda-avatar-30 shadow-sm border" title="{{ $nombreParaMostrar }} ({{ $rolParaMostrar }})">
                </div>

                <div class="d-flex flex-column msg-bubble-container">
                    <div class="msg-reda shadow-sm p-2 px-3">
                        <span class="d-block text-10 font-weight-700 text-uppercase mb-1 opacity-75">
                            {{ $nombreParaMostrar }} @if($rolParaMostrar) ({{ $rolParaMostrar }}) @endif
                        </span>
                        <p class="m-0 text-13">{{ $message->message }}</p>
                    </div>			
                    <div class="time-reda text-10 mt-1 opacity-50">{{ $message->created_at->diffForHumans() }}</div>
                </div>
            </div>
		@endforeach

        {{-- Contenedor para mensajes temporales enviados vía JS --}}
		<div class="message-list-reda me d-none" id="reda-temp-msg-container">
            <div class="reda-avatar-container flex-shrink-0">
                <img src="{{ reda_get_profile_src(Auth::user()) }}" class="rounded-circle reda-avatar-30 shadow-sm border sender_foto">
            </div>
            <div class="d-flex flex-column msg-bubble-container">
                <div class="msg-reda shadow-sm p-2 px-3">
                    <span class="d-block text-10 font-weight-700 text-uppercase mb-1 opacity-75">
                        <span class="sender_name"></span> (<span class="sender_role"></span>)
                    </span>
                    <p class="m-0 text-13 msg_txt"></p>
                </div>
                <div class="time-reda text-10 mt-1 opacity-50 msg_time"></div>
            </div>
		</div>	
	</div>

	<div class="message-footer">
		<input type="text" class="cht_msg" data-placeholder="{{ __('Escribe un mensaje...') }}" />
		<a href="javascript:void(0)" class="btn btn-success chat text-18 send-btn" data-booking="{{ $booking->id }}" data-receiver="{{ $booking->$users->id }}" data-property="{{ $booking->property_id }}"><i class="fa fa-paper-plane" aria-hidden="true"></i></a>
	</div>

    <script>
        window.RedaCurrentUser = {
            name: "{{ Auth::user()->first_name }}",
            role: "{{ ($booking->host_id == Auth::id()) ? __('anfitrión') : __('turista') }}"
        };
    </script>
	<script src="{{ asset('js/inboxEnterEvent.min.js') }}"></script>
