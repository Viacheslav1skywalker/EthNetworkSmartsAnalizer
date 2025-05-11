<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class AnaliticsOptimizer {
    public static function addSetAddressesToSub($addresses)
    {
        foreach($addresses as $address) {
            DB::table('subscribe_contracts')->insert([
                'address' => $address,
                'time_checking' => 2
            ]);
        }
    }

    public static function analizeGraphs($oldDataKeys, $newDataKeys) 
    {
        return [
            'added_nodes' => array_values(array_diff($newDataKeys, $oldDataKeys)),
            'removed_nodes' => array_values(array_diff($oldDataKeys, $oldDataKeys)),
        ];
    }
}