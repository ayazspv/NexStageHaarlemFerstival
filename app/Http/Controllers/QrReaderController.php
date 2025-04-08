<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket; 

class QrReaderController extends Controller
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
}