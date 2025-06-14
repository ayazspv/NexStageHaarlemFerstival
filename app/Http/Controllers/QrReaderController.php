<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\FestivalEvent; 

class QrReaderController  
{
    public function validateQrCode(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'qrCode' => 'required|string',
        ]);

        // Extract the QR code from the request
        $qrCode = $request->input('qrCode');

        // Query the tickets table for the QR code
        $ticketExists = Ticket::where('qr_code', $qrCode)->exists();

        // Return true if the ticket exists, otherwise false
        return response()->json(['valid' => $ticketExists]);
    }

    public function fetchTicketDetails(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'qrCode' => 'required|string',
        ]);

        // Extract the QR code from the request
        $qrCode = $request->input('qrCode');

        // Query the ticket and include related event and festival details
        $ticket = Ticket::with(['event.festival'])->where('qr_code', $qrCode)->first();

        // Return null if the ticket is not found
        if (!$ticket) {
            return response()->json([
                'ticket' => null,
                'event' => null,
                'festival' => null,
            ]);
        }

        // Return the ticket details along with event and festival
        return response()->json([
            'ticket' => $ticket,
            'event' => $ticket->event,
            'festival' => $ticket->event->festival,
        ]);
    }
}