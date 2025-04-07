<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdersExport implements FromCollection
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected $festivalType;

    /**
     * Accept optional filtering parameters.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param mixed|null $festivalType  (for example, '0' for Jazz, '1' for Yummy or null for all)
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, $festivalType = null)
    {
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->festivalType = $festivalType;
    }

    /**
     * Build the export collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Eager-load user and tickets with their festival
        $query = Order::with(['user', 'tickets.festival']);

        if ($this->startDate) {
            $query->whereDate('ordered_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('ordered_at', '<=', $this->endDate);
        }

        $orders = $query->get();

        // Map each order to an array of the desired fields.
        $exportData = $orders->map(function ($order) {
            // Concatenate first and last name
            $fullName = trim($order->user->firstName . ' ' . $order->user->lastName);
            // Count total tickets in the order (this count is before filtering by festival type)
            $ticketCount = $order->tickets->count();
            // Total price of the order
            $totalPrice = $order->total_price;

            // If a festival type filter is provided, filter the tickets accordingly.
            $tickets = $order->tickets;
            if (!is_null($this->festivalType) && $this->festivalType !== '') {
                $tickets = $tickets->filter(function($ticket) {
                    return isset($ticket->festival->type) && $ticket->festival->type == $this->festivalType;
                });
            }

            // Group tickets by festival name.
            $groupedTickets = [];
            foreach ($tickets as $ticket) {
                if ($ticket->festival) {
                    $name = $ticket->festival->name;
                    if (isset($groupedTickets[$name])) {
                        $groupedTickets[$name]++;
                    } else {
                        $groupedTickets[$name] = 1;
                    }
                }
            }

            // Build a string like "2x Jazz, 1x Yummy"
            $ticketsString = implode(', ', array_map(function($name) use ($groupedTickets) {
                return $groupedTickets[$name] . "x " . $name;
            }, array_keys($groupedTickets)));

            return [
                'Full Name'   => $fullName,
                'Email'       => $order->user->email,
                'Count'       => $ticketCount,
                'Total Price' => $totalPrice,
                'Tickets'     => $ticketsString,
            ];
        });

        return new Collection($exportData);
    }
}
