<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class GigaChatAiAnalizerService implements AIAnalizerInterface { 
    private int $limitData;

    protected array $prompts;

    public static function getTextAnalizeCode(string $code) {
        $prompt = "проанализируй код данного смарт контракта 
                по следующим характеристикам: \n 1) Безопасность \n 2) Газовая эффективность
                \n 3) Совместимость и стандарты \n 4) А также какие функции есть в этом смарте
                \n Опищи все вышеизложенное для обычного пользователя
                \n $code";

        $response = Http::withOptions([
            'allow_redirects' => true,  // Эквивалент -L в curl (следовать редиректам)
            'verify' => false,         // Эквивалент -k в curl (отключить проверку SSL)
        ])
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . self::authorize(),
            'X-Client-ID' => env('GIGACHAT_CLIENT_ID'),
        ])
        ->post('https://gigachat.devices.sberbank.ru/api/v1/chat/completions', [
            'model' => 'GigaChat-Pro-preview',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 1000,
        ]);

        return self::getResponce($response);
    }

    protected static function authorize() {
        $response = Http::timeout(60)
            ->connectTimeout(15)  
            ->withOptions([
            'allow_redirects' => true,  // Эквивалент -L в curl (следовать редиректам)
            'verify' => false,         // Эквивалент -k в curl (отключить проверку SSL)
        ])
        ->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => '6cbefaa6-777b-479a-bbe2-2df1e74664c8',
            'Authorization' => 'Basic YmE4ZGZhOTAtMjU5ZC00NDZjLTk2MDQtOWYyOTc3NDA3N2FmOmY2YTZmN2NlLWNkNDYtNGFiNC04ODRiLTZlZTA0MTA5ZjE2Nw==',
        ])
        ->asForm()
        ->post('https://ngw.devices.sberbank.ru:9443/api/v2/oauth', [
            'scope' => 'GIGACHAT_API_PERS',
        ]);

        return $response->json()['access_token'];
    }

    public static function getCicleAnalize(array $code) {
        if (count($code) > 1) {
            // prompts
            $length = count($code);
            $firstChunk = $code[0];
            $endChunk = $code[$length - 1];
            $start = <<<TEXT
            Начинаю новый анализ кода, забудь про старые запросы и анализируй все последующие пока
            в последнем я не напишу 'end query'
            я буду кусками скидывать код контракта. Твоя задача:
            проанализируй код данного смарт контракта 
                по следующим характеристикам: \n 1) Безопасность \n 2) Газовая эффективность
                \n 3) Совместимость и стандарты
            А также если в коде присутствуют зависимости с другими смарт контрактами, ты должен списком указать эти зависиимости
            указав адреса этих смартов
            также не надо давать подробные ответы на каждый мой запрос, но ты должен дать исчерпывающий ответ на весь единый код после 
            моего последнего запроса который я обозначу как 'end query' \n
            первый фрагмент кода: $firstChunk
            TEXT;
            $end = <<<TEXT
            скидываю последний фрагмент кода всего смарт контракта:
            $endChunk
            TEXT;
            self::getTextAnalizeCode($start);
            for ($i = 1; $i <= count($code) - 2; $i++) {
                self::getTextAnalizeCode($code[$i]);
            }

            return self::getTextAnalizeCode($end);
        } 

        return self::getTextAnalizeCode($code[0]);
    }

    // @TODO: добавить обработку других сообщений от Api GigaChat
    public static function getResponce($arrayResponce) {
        if ($arrayResponce['message'] == 'Payment Required') {
            
            return 'требуются токены для GigaChat';
        }
        try {
            return $arrayResponce['choices'][0]['message']['content'];
        } catch(Exception $e) {

            return "error";
        }
    }
}

