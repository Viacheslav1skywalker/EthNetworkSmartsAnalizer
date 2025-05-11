<?php
namespace App\Normalize;

class CodeNormalizer {
  public static function getContractsOnly($code) {
    $pattern = '/^contract\b.*?^\}/ms';
    preg_match($pattern, $code, $matches);

    return $matches;
  }
}