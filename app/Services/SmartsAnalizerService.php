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
            'apikey' => 'E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A'
          ]);
        
          $data = $response->json();

          return $data;
    }

    public static function getABI($contractAdress) {
        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'contract',
            'action' => 'getabi',
            'address' => $contractAdress,
            'apikey' => 'E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A'
          ]);
        
          $data = $response->json();

          return $data;
    }

    public static function getAddressesInInternalTr(string $smartAddress) {
        // https://api.etherscan.io/api?module=account&action=txlistinternal&address=0x7a250d5630B4cF539739dF2C5dAcb4c659F2488D&startblock=0&endblock=99999999&page=1&offset=200&sort=desc&apikey=E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A
        $response = Http::get('https://api.etherscan.io/api', [
            'module' => 'account',
            'action' => 'txlistinternal',
            'address' => $smartAddress,
            'startblock' => 0,
            'endblock' => 99999999,
            'page' => 1,
            'offset' => 500,
            'sort' => 'desc',
            'apikey' => 'E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A'
        ])->json();

        // dd($response);
        // kd();
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
        // dd($addresses);
        // ld();
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
            // 'page' => 2,
            // 'offset' => 400,
            'sort' => 'desc',
            'apikey' => 'E971AI54IUSEPMFFEVMXWFC2SAW46C2N9A'
        ])->json();
        // dd($response);
        // dl();
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

    public function getContractsDependencies() {
        // получаем зависимости контрактов в смарте в графовой структуре данных
    }
}







// "0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2" => 135
// "0x354c7b004839009fc87f104fc8914b44fdd6d078" => 19
// "0xfca88920ca5639ad5e954ea776e73dec54fdc065" => 10
// "0xbcb4e4bcc41ab1494a3eb3456ed4edb8da5d46e4" => 8

// "0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2" => 1854
// "0xfca88920ca5639ad5e954ea776e73dec54fdc065" => 112
// "0x4d4c3751f492c35a7a029052aad02cdfc6d5e342" => 80
// "0xbc530bfa3fca1a731149248afc7f750c18360de1" => 58
// "0xbcb4e4bcc41ab1494a3eb3456ed4edb8da5d46e4" => 37




// "0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2" => 3633
// "0xfca88920ca5639ad5e954ea776e73dec54fdc065" => 220
// "0x4d4c3751f492c35a7a029052aad02cdfc6d5e342" => 124
// "0x89b6c60e4a8b4b465252b7ac393d813c600d201f" => 77