<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\{Bookings, Messages, Properties};
use Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function iniciarChat($property_id)
    {
        $user_id = Auth::id();
        $property = Properties::findOrFail($property_id);
        $host_id = $property->host_id;

        if ($user_id == $host_id) {
            return redirect()->back()->with('error', 'You cannot send a message to yourself.');
        }

        // 1. Search for an existing "Inquiry" (booking with status '') 
        // between these two users for this property.
        $booking = Bookings::where('user_id', $user_id)
            ->where('host_id', $host_id)
            ->where('property_id', $property_id)
            ->where('status', '')
            ->first();

        // 2. If not exists, try to find ANY booking between them for this property
        if (!$booking) {
            $booking = Bookings::where('user_id', $user_id)
                ->where('host_id', $host_id)
                ->where('property_id', $property_id)
                ->orderBy('id', 'desc')
                ->first();
        }

        // 3. If still not exists, create a new "Inquiry" booking
        if (!$booking) {
            $booking = new Bookings;
            $booking->property_id = $property_id;
            $booking->host_id = $host_id;
            $booking->user_id = $user_id;
            $booking->start_date = date('Y-m-d');
            $booking->end_date = date('Y-m-d', strtotime('+1 day'));
            $booking->status = ''; // Inquiry
            $booking->currency_code = 'USD'; // Default or from property
            $booking->total_night = 1;
            $booking->per_night = $property->property_price->price ?? 0;
            $booking->total = $booking->per_night;
            $booking->code = 'INQ' . time();
            $booking->save();

            // Create an initial system message or empty message?
            // The user wants to start a chat. Redirecting to inbox might be enough.
        }

        // Redirect to inbox. The InboxController needs to be updated to handle 
        // grouping, but for now, we redirect to the original inbox 
        // which expects booking_id in some way (or we just go to /inbox).
        
        // If we want to open this specific conversation, we might need a way to tell the inbox which one.
        // Original inbox uses the first message's booking_id if no ID is passed.
        
        return redirect('inbox');
    }
}
