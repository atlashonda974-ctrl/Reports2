<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Cache; 

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
     protected function schedule(Schedule $schedule)
    {
       
        $schedule->call(function () {
            $cacheKey = 'uw_dashboard_data';
            $apiUrl = 'http://172.16.22.204/dashboardApi/branch_portal/uw_data.php?expiryfrom=01-Jan-2025&expiryto=14-Nov-2025';

            try {
                $response = Http::timeout(30)->get($apiUrl);
                
                if ($response->successful()) {
                    $mainData = $response->json();

                    if (!empty($mainData)) {
                        $cachePayload = [
                            'data' => $mainData,
                            'last_refresh' => now()->toDateTimeString()
                        ];

                        Cache::put($cacheKey, $cachePayload, 30 * 60); 
                        \Log::info(' UW Dashboard cache REFRESHED via scheduler at: ' . now()->toDateTimeString());
                    }
                } else {
                    \Log::warning('UW Dashboard API returned non-success status: ' . $response->status());
                }
            } catch (\Exception $e) {
                \Log::error('Failed to refresh UW dashboard cache: ' . $e->getMessage());
            }
        })->everyFiveMinutes(); 

        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
