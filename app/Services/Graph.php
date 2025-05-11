<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Services\SmartsAnalizerService;


class Graph {
  public static function build($smartAddress, &$visited, $depth = 4) {
    if ($depth <= 0) {
      return [];
    }

    if (isset($visited[$smartAddress])) {
      return $visited[$smartAddress];
    }

    $result = [];

    $visited[$smartAddress] = &$result;

    $numbers = SmartsAnalizerService::getAddressesInInternalTr($smartAddress);

    foreach ($numbers as $number => $address) {
        $result[$number] = self::build($number, $visited, $depth - 1);
    }

    return $result;
  }

  public static function createGraph($smartAddress, $depthLimit = 4) {
    $graph = [];
  
    // Начинаем построение графа с начального id
    self::buildGraph($smartAddress, 1, $graph, $depthLimit);

    return $graph;
  }


  private static function buildGraph($id, $depth, &$graph, $depthLimit) {
    // Если достигли предела глубины, выходим
    if ($depth > $depthLimit) {
      if (!isset($graph[$id])) {
        $graph[$id] = [];
      }

      return;
    };

    // Если узел уже добавлен в граф, просто возвращаем
    if (!isset($graph[$id])) {
        $graph[$id] = SmartsAnalizerService::getAddressesInInternalTr($id);

        // Рекурсивно обходим все связанные узлы
        foreach ($graph[$id] as $childId) {
            self::buildGraph($childId, $depth + 1, $graph, $depthLimit);
        }
    }
}
}



// {"0x7a250d5630b4cf539739df2c5dacb4c659f2488d":["0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2","0x369418f70cce4d4fee21934cb7a13cd009c59888","0x33edc3294103f590fa31974f183a936a7e60852b","0xfca88920ca5639ad5e954ea776e73dec54fdc065","0x3125cccd8862eee9e8f2160113871f50fbd55edd"],"0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2":["0x66a9893cc07d91d95644aedd05d03f95e1dba8af","0x7a250d5630b4cf539739df2c5dacb4c659f2488d","0x5418226af9c8d5d287a78fbbbcd337b86ec07d61","0x5703b683c7f928b721ca95da988d73a3299d4757","0x111111125421ca6dc452d289314280a0f8842a65","0x1111111254eeb25477b68fb85ed929f73a960582","0x5141b82f5ffda4c6fe1e372978f1c5427640a190","0x85cd07ea01423b1e937929b44e4ad8c40bbb5e71","0x80a64c6d7f12c47b7c66c5b4e20e72bc1fcd5d9e","0x5c7bcd6e7de5423a257d81b442095a1a6ced35c5","0x74de5d4fcbf63e00296fd95d33236b9794016631"],"0x66a9893cc07d91d95644aedd05d03f95e1dba8af":[],"0x5418226af9c8d5d287a78fbbbcd337b86ec07d61":[],"0x5703b683c7f928b721ca95da988d73a3299d4757":[],"0x111111125421ca6dc452d289314280a0f8842a65":[],"0x1111111254eeb25477b68fb85ed929f73a960582":[],"0x5141b82f5ffda4c6fe1e372978f1c5427640a190":[],"0x85cd07ea01423b1e937929b44e4ad8c40bbb5e71":[],"0x80a64c6d7f12c47b7c66c5b4e20e72bc1fcd5d9e":[],"0x5c7bcd6e7de5423a257d81b442095a1a6ced35c5":[],"0x74de5d4fcbf63e00296fd95d33236b9794016631":[],"0x369418f70cce4d4fee21934cb7a13cd009c59888":[],"0x33edc3294103f590fa31974f183a936a7e60852b":["0x175ba7eed4cfe1d161d488c50492dc77550f58ca","0x9befa3fa1e5cbaefaf557466bea099d8351f1a17"],"0x175ba7eed4cfe1d161d488c50492dc77550f58ca":[],"0x9befa3fa1e5cbaefaf557466bea099d8351f1a17":[],"0xfca88920ca5639ad5e954ea776e73dec54fdc065":["0x7a250d5630b4cf539739df2c5dacb4c659f2488d","0xfde73eba30891d103e3c7402f6aca9860992357c","0xaa59a1d797c417d79f4509cb83e09428c4b5ffd9","0x74d8b81e1ce4d66ec0c63ee212b1cb57180b2da9"],"0xfde73eba30891d103e3c7402f6aca9860992357c":[],"0xaa59a1d797c417d79f4509cb83e09428c4b5ffd9":[],"0x74d8b81e1ce4d66ec0c63ee212b1cb57180b2da9":[],"0x3125cccd8862eee9e8f2160113871f50fbd55edd":["0xc3edd1144a150fbb7d5cc5add8e2ce85e840bbb0"],"0xc3edd1144a150fbb7d5cc5add8e2ce85e840bbb0":[]}