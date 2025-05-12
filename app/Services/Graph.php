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
  
    self::buildGraph($smartAddress, 1, $graph, $depthLimit);

    return $graph;
  }


  private static function buildGraph($id, $depth, &$graph, $depthLimit) {
    if ($depth > $depthLimit) {
      if (!isset($graph[$id])) {
        $graph[$id] = [];
      }

      return;
    };

    if (!isset($graph[$id])) {
        $graph[$id] = SmartsAnalizerService::getAddressesInInternalTr($id);

        foreach ($graph[$id] as $childId) {
            self::buildGraph($childId, $depth + 1, $graph, $depthLimit);
        }
    }
  }
}

