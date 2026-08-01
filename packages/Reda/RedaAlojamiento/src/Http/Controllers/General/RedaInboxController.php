<?php

namespace Reda\RedaAlojamiento\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\{Currency, Messages, Bookings};
use Auth;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Log;

class RedaInboxController extends Controller
{
    /**
    * Inbox Page - Unified Participant Chat
    */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Get all messages involving the user or linked to their bookings
        // We include messages with receiver_id=0 if they are part of the user's bookings
        $allMessages = Messages::with(['bookings', 'properties:id,name', 'sender', 'receiver'])
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId)
                  ->orWhereHas('bookings', function($query) use ($userId) {
                      $query->where('user_id', $userId)->orWhere('host_id', $userId);
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        // 2. Identify the real "Chat Partner" for each message
        $allMessages->transform(function ($message) use ($userId) {
            $partnerId = null;
            
            // If message has a booking, the partner is the OTHER person in that booking
            if ($message->bookings) {
                $partnerId = ($message->bookings->user_id == $userId) ? $message->bookings->host_id : $message->bookings->user_id;
            } else {
                // If no booking, just use the sender/receiver that isn't the current user
                $partnerId = ($message->sender_id == $userId) ? $message->receiver_id : $message->sender_id;
            }
            
            // Fallback: If partner is still system (0) or admin (1), try to keep it but 
            // usually bookings resolve this.
            $message->chat_partner_id = $partnerId;
            return $message;
        });

        // 3. Group by the resolved Chat Partner
        $data['messages'] = $allMessages->unique('chat_partner_id')->values();

        if (count($data['messages']) > 0) {
            $targetBookingId = $request->id;
            $selectedPartnerId = null;

            if ($targetBookingId) {
                $targetBooking = Bookings::find($targetBookingId);
                if ($targetBooking) {
                    $selectedPartnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;
                    $data['booking'] = $targetBooking->load('users', 'properties');
                }
            }

            if (!$selectedPartnerId) {
                $first = $data['messages']->first();
                $selectedPartnerId = $first->chat_partner_id;
                $data['booking'] = Bookings::where('id', $first->booking_id)->with('users', 'properties')->first();
            }

            // 4. Load the ENTIRE history with this partner
            // Includes: direct messages, messages from admin (1) to either, or system (0) messages in shared bookings
            $data['conversation'] = Messages::where(function($query) use ($userId, $selectedPartnerId) {
                // Direct messages
                $query->where(function($q) use ($userId, $selectedPartnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $selectedPartnerId);
                })->orWhere(function($q) use ($userId, $selectedPartnerId) {
                    $q->where('sender_id', $selectedPartnerId)->where('receiver_id', $userId);
                })
                // OR messages linked to shared bookings (handles admin ID 1 and system ID 0)
                ->orWhereHas('bookings', function($q) use ($userId, $selectedPartnerId) {
                    $q->where(function($sub) use ($userId, $selectedPartnerId) {
                        $sub->where('user_id', $userId)->where('host_id', $selectedPartnerId);
                    })->orWhere(function($sub) use ($userId, $selectedPartnerId) {
                        $sub->where('user_id', $selectedPartnerId)->where('host_id', $userId);
                    });
                });
            })->orderBy('id', 'asc')->get();

            // Log for debugging
            Log::info("REDA Inbox: User $userId chatting with Partner $selectedPartnerId. Messages found: " . $data['conversation']->count());

            if ($data['booking']) {
                $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';
            }
         }
        
        return view('users.inbox', $data);
    }

    /**
     * Load Conversation Details via Ajax
     */
    public function message(Request $request)
    {
        $userId = Auth::id();
        $booking_id = $request->id;
        $targetBooking = Bookings::findOrFail($booking_id);
        
        $partnerId = ($targetBooking->user_id == $userId) ? $targetBooking->host_id : $targetBooking->user_id;

        // Mark all related messages as read
        Messages::whereHas('bookings', function($q) use ($userId, $partnerId) {
            $q->where(function($sub) use ($userId, $partnerId) {
                $sub->where('user_id', $userId)->where('host_id', $partnerId);
            })->orWhere(function($sub) use ($userId, $partnerId) {
                $sub->where('user_id', $partnerId)->where('host_id', $userId);
            });
        })->where('receiver_id', $userId)->update(['read' => 1]);

        // Load unified history
        $data['messages'] = Messages::where(function($query) use ($userId, $partnerId) {
                $query->where(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
                })->orWhere(function($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
                })
                ->orWhereHas('bookings', function($q) use ($userId, $partnerId) {
                    $q->where(function($sub) use ($userId, $partnerId) {
                        $sub->where('user_id', $userId)->where('host_id', $partnerId);
                    })->orWhere(function($sub) use ($userId, $partnerId) {
                        $sub->where('user_id', $partnerId)->where('host_id', $userId);
                    });
                });
            })->orderBy('id', 'asc')->get();

        $data['booking'] = $targetBooking->load('host', 'users', 'properties');
        $data['symbol'] = Currency::getAll()->firstWhere('code', $data['booking']->currency_code)->symbol ?? '$';

        return response()->json([
             "inbox" => view('users.messages', $data)->render(), 
             "booking" => view('users.booking', $data)->render()
        ]);
    }

    /**
    * Unified Reply
    */
    public function messageReply(Request $request)
    {
        $rules = array('msg' => 'required|string');
        $validator = Validator::make($request->all(), $rules);

        if (!$validator->fails()) {
            $message = new Messages;
            $message->property_id = $request->property_id;
            $message->booking_id = $request->booking_id;
            $message->receiver_id = $request->receiver_id;
            $message->sender_id = Auth::id();
            $message->message = $request->msg;
            $message->type_id = 1;
            $message->save();

            Messages::where([['booking_id', '=', $request->booking_id], ['receiver_id', '=', Auth::id()]])->update(['read' => 1]);
            return 1;
        }
        return 0;
    }
}
