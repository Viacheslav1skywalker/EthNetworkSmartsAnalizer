<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Services\GigaChatAiAnalizerService;
use App\Services\SmartsAnalizerService;
use Illuminate\Support\Facades\DB;
use App\Services\Graph;
use Illuminate\Support\Facades\Date;
use App\Services\AnaliticsOptimizer;
use App\Jobs\ProcessSubscribingContracts;
use App\Normalize\CodeNormalizer;


class AnalizeContractsController
{
    public function analizeContractCode($smartAddress) {
      // валидация
      $response = SmartsAnalizerService::getCode($smartAddress);
      // dd($response);
      // ks();
      if ($response['message'] != 'OK') {
        // dd($response->json());
        return response('контракт не существует или не верефицирован', 404);
      }

      $contractName = $response['result'][0]['ContractName'];

      $code = $response['result'][0]['SourceCode'];

      $matches = CodeNormalizer::getContractsOnly($code);
      // dd($code);
      // ks();
      $nameInfo = "Название: $contractName \n";
      if ($matches == []) {
        return response("$nameInfo Не удалось распознать код смарт контракта", 200);
      }

      $resData = [];
      foreach ($matches as $match) {

        $mbLength = strlen($match); 
        if ($mbLength > 50000) {
          $code = str_split($match, 50000);
        } else {
          $code = [$match];
        }

        $resData += $code;
      }

      $textAnalize = GigaChatAiAnalizerService::getCicleAnalize($resData);
      
      $res = "";

      // dd('техт анализа', $textAnalize);
      return response($textAnalize, $status = 200);
    }

    public function subscibeContract($user_id, $smartAddress) {

      // dd(SmartsAnalizerService::getABI($smartAddress));
      // kd();
      DB::table('subscribe_contracts')
        ->where($smartAddress, '=', );
      
      DB::table('subscribe_contracts')->insert([
        'user_id' => $user_id,
        'address' => $smartAddress,
        'time_checking' => 2, // каждые два часа
        'message_on' => 'email', // default messaging
        'dependencies' => json_encode(Graph::createGraph($smartAddress, 2)),
        // 'code_hash' => hash('sha256', $code)
      ]);
      
      ProcessSubscribingContracts::dispatch($smartAddress, $user_id)
        ->onQueue('subscribing');

      return response('ok', 200);
    }

    public function getGraphAnalize($smartAddress) {
      $smartAddress = mb_strtolower($smartAddress, 'UTF-8');
      $graph = Graph::createGraph($smartAddress, 2);

      return response(json_encode($graph), 200);
    }

    public function test() {
      $graphOld = DB::table('subscribe_contracts')
        ->where('address', '=', '0x7a250d5630b4cf539739df2c5dacb4c659f2488d')
        ->value('dependencies'); // Автоматически возвращает значение поля
      dd($graphOld);
    }

    public function getSubContracts($userId) {
      $res = DB::table('subscribe_contracts')
        ->where('user_id', '=', $userId)
        ->get()
        ->toArray();

      return response(json_encode($res), 200);
    }

    public function getNotifications($user_id) {
      $res = DB::table('notifications')
        ->where('user_id', '=', $user_id)
        ->orderBy('date_changed', 'desc')
        ->get();

      return response(json_encode($res->toArray()), 200); 
    }
}
