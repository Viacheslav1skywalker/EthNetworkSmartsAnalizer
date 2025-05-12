<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;


class SmartsAnalizerService {
    public static function getCode($contractAdress) {
        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'contract',
            'action' => 'getsourcecode',
            'address' => $contractAdress,
            'apikey' => env('ETHERSCAN_API_KEY')
          ]);
        
          $data = $response->json();

          return $data;
    }

    public static function getABI($contractAdress) {
        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'contract',
            'action' => 'getabi',
            'address' => $contractAdress,
            'apikey' => env('ETHERSCAN_API_KEY')
          ]);
        
          $data = $response->json();

          return $data;
    }

    public static function getAddressesInInternalTr(string $smartAddress) {
        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'account',
            'action' => 'txlistinternal',
            'address' => $smartAddress,
            'startblock' => 0,
            'endblock' => 99999999,
            'page' => 1,
            'offset' => 500,
            'sort' => 'desc',
            'apikey' => env('ETHERSCAN_API_KEY')
        ])->json();

        if ($response['message'] != 'OK') {
            $msg = $response['message'] ?? NULL;
            
            throw new Exception('error ' . $msg);
        }

        $transactionsMap = [];
        foreach ($response['result'] as $transaction) {
            if ($transaction['from'] == mb_strtolower($smartAddress, 'UTF-8')) {
                if (array_key_exists($transaction['to'], $transactionsMap)) {
                    $transactionsMap[$transaction['to']]++;
                } else {
                    $transactionsMap[$transaction['to']] = 1;
                }
            }
        }

        arsort($transactionsMap);

        return array_keys(self::filterAddresses($transactionsMap));
    }

    public static function filterAddresses($addresses) {
        $length = count($addresses);
        $filterArray = [];
        foreach ($addresses as $address => $value) {
            if ($value / $length * 100 > 20) {
                $filterArray[$address] = $value;
            }
        }

        return $filterArray;
    }

    public static function getSourceCode($contractAdress) {
        return self::getCode($contractAdress)['result'][0]['SourceCode'];
    }

    public function getAnalizingCodeInTextFormat(string $contractAdress) {
        $code = $this->getCode($contractAdress);

    }

    public static function getAddressesInInternalTrV2($smartAddress) {
        // https://api.etherscan.io/api?module=account&action=txlistinternal&address=0x7a250d5630B4cF539739dF2C5dAcb4c659F2488D&startblock=0&endblock=99999999&page=1&offset=200&sort=desc&apikey=E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A
        $startTime = Date::now()->subDays(7);
        $endTime = Date::now();

        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'account',
            'action' => 'txlistinternal',
            'address' => $smartAddress,
            'startblock' => 0,
            'endblock' => 99999999,
            'starttimestamp' => $startTime->timestamp,
            'endtimestamp' => $endTime->timestamp,
            'sort' => 'desc',
            'apikey' => env('ETHERSCAN_API_KEY')
        ])->json();

        if ($response['status'] != 1) {
            $msg = $response['message'] ?? NULL;
            
            throw new Exception('error ' . $msg);
        }

        $transactionsMap = [];
        foreach ($response['result'] as $transaction) {
            if ($transaction['from'] == mb_strtolower($smartAddress, 'UTF-8')) {
                if (array_key_exists($transaction['to'], $transactionsMap)) {
                    $transactionsMap[$transaction['to']]++;
                } else {
                    $transactionsMap[$transaction['to']] = 1;
                }
            }
        }

        arsort($transactionsMap);

        return self::filterAddresses($transactionsMap);
    }

    public function doAudit($contractAdress) {
        // отправляем запрос на аудит
    }
}