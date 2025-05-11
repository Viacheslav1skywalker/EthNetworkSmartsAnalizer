<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AnaliticsOptimizer;
use Illuminate\Support\Facades\DB;
use App\Services\Graph;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;


class ProcessSubscribingContracts implements ShouldQueue
{
    use Queueable;

    public $contractAddress;

    public $userId;
    /**
     * Create a new job instance.
     */
    public function __construct(string $contractAddress, int $userId)
    {
        $this->contractAddress = $contractAddress;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $graphOld = DB::table('subscribe_contracts')
            ->where('address', '=', $this->contractAddress)
            ->get()
            ->toArray();
        
        if (!$graphOld) {
            return;
        }

        $deps = $graphOld[0]->dependencies;

        $checkTime = Carbon::parse($graphOld[0]->time_checking);
        $currentTime = Carbon::now();

        if ($currentTime->diffInHours($checkTime) > 2) {
            $graphNew = array_keys(Graph::createGraph($this->contractAddress, 3));
            $graphOld = array_keys(json_decode($deps, true));
            $info = AnaliticsOptimizer::analizeGraphs($graphOld, $graphNew);
    
            DB::table('subscribe_contracts')
                ->where('address', '=', $this->contractAddress)
                ->update(['change_data' => json_encode($info), ]);
    
            
            if ($info['added_nodes'] || $info['removed_nodes']) {
                DB::table('notifications')
                    ->insert([
                        'user_id' => $this->userId,
                        'contract' => $this->contractAddress,
                        'changed_data' => json_encode($info),
                        'date_changed' => Date::now(),
                    ]);
            }
        }
            
        self::dispatch($this->contractAddress, $this->userId)
            ->onQueue('subscribing');

        sleep(60);
    }
}
