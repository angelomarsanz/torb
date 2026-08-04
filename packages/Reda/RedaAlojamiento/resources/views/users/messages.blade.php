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
	<div class="message-wrap">
		@foreach ($messages as $message)
		<div class="{{ $message->sender_id == Auth::id() ? 'message-list me' : 'message-list' }}">
			@if($message->sender_id != Auth::id())
				<div class="reda-inbox-avatar mr-2">
					<img src="{{ $message->custom_sender_foto ?? reda_get_profile_src($message->sender) }}" class="rounded-circle border reda-avatar-30">
				</div>
			@endif
			<div class="d-flex flex-column">
				<div class="msg pl-2 pr-2 pb-2 pt-2 mb-1" >
					@if($message->sender_id != Auth::id())
						<span class="d-block text-10 font-weight-700 text-uppercase mb-1 opacity-75">{{ $message->custom_sender_name ?? ($message->sender->first_name ?? 'Usuario') }}</span>
					@endif
					<p class="m-0">{{ $message->message }}</p>
				</div>			
				<div class="time {{ $message->sender_id == Auth::id() ? 'text-right' : '' }}">{{ $message->created_at->diffForHumans() }}</div>
			</div>
		</div>
		@endforeach
		<div class="message-list me">						 
				<div class="msg_txt mb-0"></div>	
				<div class="time msg_time mt-0"></div>	 
		</div>	
	</div>

	<div class="message-footer">
		<input type="text" class="cht_msg" data-placeholder="Send a message to {0}" />
		<a href="javascript:void(0)" class="btn btn-success chat text-18 send-btn" data-booking="{{ $booking->id }}" data-receiver="{{ $booking->$users->id }}" data-property="{{ $booking->property_id }}"><i class="fa fa-paper-plane" aria-hidden="true"></i></a>
	</div>




	<script src="{{ asset('js/inboxEnterEvent.min.js') }}"></script>
