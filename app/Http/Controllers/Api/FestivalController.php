<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FestivalController
{
    public function getFestivals()
    {
        try {
            // Check if the festivals table exists and has data
            if (!Schema::hasTable('festivals')) {
                return response()->json([
                    'error' => 'Festivals table does not exist'
                ], 500);
            }
            
            $festivals = Festival::all();
            return response()->json($festivals);
        } catch (\Exception $e) {
            Log::error('Error in getFestivals: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getFestivalPrice($id)
    {
        try {
            $festival = Festival::findOrFail($id);
            
            // Check if the price column exists
            if (!isset($festival->price)) {
                return response()->json([
                    'error' => 'Price column not found. Make sure you ran the migration.',
                    'price' => 0.00 // Default price
                ]);
            }
            
            return response()->json([
                'price' => (float)$festival->price
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getFestivalPrice: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => $e->getMessage(),
                'price' => 0.00 // Default price on error
            ], 500);
        }
    }
    
    public function getSpecialTicketPrices()
    {
        try {
            return response()->json([
                'day_pass' => 50.00,
                'full_pass' => 150.00
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSpecialTicketPrices: ' . $e->getMessage());
            
            return response()->json([
                'error' => $e->getMessage(),
                'day_pass' => 50.00,
                'full_pass' => 150.00
            ], 500);
        }
    }
}