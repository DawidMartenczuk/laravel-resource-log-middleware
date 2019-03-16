<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class LogResources
{
    public function handle($request, Closure $next)
    {
        /**
         * Run other middlewares and request before this middleware (store response)
         */
        $response = $next($request);

        $file = storage_path('resource_logs.json');
        $currentKey = substr(Carbon::now()->format('Y-m-d H:i'), 0, -1);
    
        /**
         * Open exists log file, or create new resources usage array
         */
        if(file_exists($file)) {
            $content = file_get_contents($file);
            $resources = $content ? json_decode($content, true) : [];
        }
        else {
            $resources = [];
        }

        $changes = false;

        /**
         * Get resources usage registered earlier
         */
        $actualPeak = array_key_exists($currentKey, $resources) ? $resources[$currentKey] : [0,0];

        /**
         * Get CPU and Memory usage
         */
        $memPeak = round(memory_get_peak_usage(true) / 1024 / 1024);
        $cpuPeak = round(sys_getloadavg()[0]  * 100);

        /**
         * Assign resources usage if larger than registered earlier
         */
        if($actualPeak[0] < $memPeak) {
            $actualPeak[0] = $memPeak;
            $changes = true;
        }

        if($actualPeak[1] < $cpuPeak) {
            $actualPeak[1] = $cpuPeak;
            $changes = true;
        }

        /**
         * If no changes don`t write a file - returns response
         */
        if(!$changes) {
            return $response;
        }

        /**
         * Write changes to resources usage log file
         */
        $resources[$currentKey] = $actualPeak;
        $f = fopen($file, 'w+');
        fwrite($f, json_encode($resources));
        fclose($f);

        /**
         * Returns response
         */
        return $response;
    }
}