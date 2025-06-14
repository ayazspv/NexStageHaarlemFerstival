<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\HomepageContent;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController
{
    public function index()
    {
        // First, let's get all festivals
        $festivals = Festival::all();

        // Then calculate ticket availability for each festival
        $festivals = $festivals->map(function ($festival) {
            // Get total tickets sold for this festival from confirmed/paid orders
            $ticketsSold = DB::table('tickets')
                ->join('orders', 'tickets.order_id', '=', 'orders.id')
                ->where('tickets.festival_id', $festival->id)
                ->whereIn('orders.status', ['confirmed', 'paid', 'completed', 'processing'])
                ->sum('tickets.quantity');

            // Calculate availability
            $totalTickets = $festival->ticket_amount ?? 0;
            $ticketsAvailable = max(0, $totalTickets - $ticketsSold);

            // Calculate availability percentage
            $availabilityPercentage = $totalTickets > 0
                ? round(($ticketsAvailable / $totalTickets) * 100, 1)
                : 0;

            // Determine status color
            $availabilityStatus = 'available'; // green
            if ($availabilityPercentage <= 10) {
                $availabilityStatus = 'critical'; // red
            } elseif ($availabilityPercentage <= 50) {
                $availabilityStatus = 'warning'; // yellow
            }

            // Add calculated fields to festival object
            $festival->total_tickets = $totalTickets;
            $festival->tickets_available = $ticketsAvailable;
            $festival->tickets_sold = $ticketsSold;
            $festival->availability_percentage = $availabilityPercentage;
            $festival->availability_status = $availabilityStatus;
            $festival->is_sold_out = $ticketsAvailable <= 0 && $totalTickets > 0;

            return $festival;
        });

        $homepageContent = HomepageContent::first();

        // Handle hero image path correctly
        $heroUrl = null;
        if ($homepageContent && $homepageContent->hero_image_path) {
            // Make sure we don't double-prefix with 'storage/'
            if (strpos($homepageContent->hero_image_path, 'storage/') === 0) {
                $heroUrl = '/' . $homepageContent->hero_image_path;
            } else {
                $heroUrl = '/storage/' . $homepageContent->hero_image_path;
            }
        } else {
            // Fallback to default if exists
            if (Storage::disk('public')->exists('festivals/hero.png')) {
                $heroUrl = '/storage/festivals/hero.png';
            }
        }

        return Inertia::render('Home', [
            'festivals' => $festivals,
            'heroUrl' => $heroUrl,
            'homepageContent' => $homepageContent->content ?? null,
        ]);
    }
}
