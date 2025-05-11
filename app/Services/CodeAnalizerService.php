<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CodeAnalizerService {
  public function analizeCode(string $code) {
    // последоватлеьно закидываем в анализатора
  }

  public function getContractsOnly(string $code){
    // возвращаем массив из кодов контракта (если код слишком длинный то дели на нескольк)
  }
}