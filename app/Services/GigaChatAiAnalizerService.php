<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GigaChatAiAnalizerService implements AIAnalizerInterface { 
    private int $limitData;

    protected array $prompts;

    // public const START_CICLE = `Сейчас я буду в несколько запросов присылать тебе данные кода
    // смарт контракта, конечный запрос я обозначу как 'end query'`;

    // public const END_CICLE = 'выведи списком все смарт контракты, которые связаны данным';


    public static function getTextAnalizeCode(string $code) {
        // делим код на несколько данных
        // если их несколько то прописываем цикл
        // 
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

        return $response->json()['choices'][0]['message']['content'];
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

        // dd($response->json());
        return $response->json()['access_token'];
    }

    public static function getCicleAnalize(array $code) {
        if (count($code) > 1) {
            $text = '';
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
            проанализируй его по тем требованиям, о которых я писал и выведи в формате "$[адресс_зависимости1, задресс_ависимости2...]$"
            TEXT;
            // dd($start);
            $text .= "\n" . self::getResponce(self::getTextAnalizeCode($start));
            for ($i = 1; $i <= count($code) - 2; $i++) {
                $text .= "\n" . self::getResponce(self::getTextAnalizeCode($code[$i]));
            }

            return $text . "\n" .  self::getResponce(self::getTextAnalizeCode($end));
            
        } 

        return self::getTextAnalizeCode($code[0]);
    }

    public static function getResponce($arrayResponce) {
        dd($arrayResponce);
        ld();
        return $arrayResponce['choices'][0]['message']['content'];
    }
}


// curl -k -L -X POST 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth' \
// -H 'Content-Type: application/x-www-form-urlencoded' \
// -H 'Accept: application/json' \
// -H 'RqUID: 6cbefaa6-777b-479a-bbe2-2df1e74664c8' \
// -H 'Authorization: Basic OTNmZDk4MmMtMTlmMC00ODI1LTg0YzAtOGI3ZTM4YjgxZWYxOmM0NjMzOGY4LTJhM2ItNDRhNi05ZjI4LWVhYTQyNzM1NjY5OA==' \
// --data-urlencode 'scope=GIGACHAT_API_PERS'






// eyJjdHkiOiJqd3QiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwiYWxnIjoiUlNBLU9BRVAtMjU2In0.ahGyWwwUy04CqT86ILM3su-27CwjYC7MFHrM1DVZYrXBXoO_IELQ4fEJcCIYVIx3rtv_w0OPX0jy8japzZfqr-VLmq75x7t99k88j-CWmItK_tzUlUK_sQR9tJpN0QGYvuRkFFykxU2YutFxzMQUiogKKbZagRHQSDQYJn9TlVUJowGIcpmuNGRN0V0evIEscs1oeMwszajSsUMnKuz8EoTSon8nVVTcIl_Aej2LWZsqpy173GnLVpC6XYSNFjdXoiyog0kbH4f-DnnPxcRAla6uv3QBDhJSGup1FVCWP9bL6KE2mAtn6fMgFMkfoSUNyWx2O_x12l4Id3AF7RLAog.5SuGGCKDt81kQRuXPDEl6Q.8tfIWJqX3Z0d55ug8pCjj2YOOHBXcBGsSgtPXly_KVE4FQFgEH4x1ymYHSL7OE5zx5nhqN352Wu0YvcW-uMmvdFfFRHM7bOsT6GpmBUh-vRrkr39xcybPN7mfMb3XX3_ZD3oyIi1ZCD3oyChpeAlbitBIoYHTfjGmme3Ew6pQTYgBHZnt_rutKDHzlIhCaA0V6nJjVZXpepEmh8gEnYPAd3j5tSZnN9Pd5Wgz3QkY44ndvQmKQf1fp61oH-002VEtQDwddSX8k9-gHmIhUjLY672VniypHfDybRVn8evkSlNDodB6XDRllRnoOaM9SJ9Dhu8cMXx9poONVGmnQL1-RdXof8sLKU7xHbvS6LtOdv22KpLYURJA64n873wy7FiZzO0fPuXrRkG4av7TIMZP8qra076u3akVmzOn-NQViKvNIvRaxh3Xml_PYLnoQgZt5zXws7EnTHkXUN3L0xeMLt3r4dem7wwSSQrrvRi-R_dM9VDrMeIxoulS7lqRsJjhbcGDq3vgel0ulmrqQwLR21xvUqQZetlmbI1bNAKIb5T_e40_uH2i6OCReRT5EcVVQqhw7-yFslH9on9dIE6mQGvqYPBlSWNuUF5i3oHwZF59RGQ5K2E-KO2qy-ZeC335eoApDATDGSfWVe6gxybtY_EqBz3lquNGiXXFrtHmiKYaM_2nmN6E-zkd9Fo2NzJRghFqyurpyiAXaxDdJLDLspPifBTLSKBT5g5QIyps94.iwzWN6AM2RfYE7H-XbNAUCtOrsYAx8a1lPGOEaXRyKw



// curl -k -L -X POST \
//   'https://gigachat.devices.sberbank.ru/api/v1/chat/completions' \
//   -H 'Content-Type: application/json' \
//   -H 'Authorization: Bearer eyJjdHkiOiJqd3QiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwiYWxnIjoiUlNBLU9BRVAtMjU2In0.Wn2jKrN0meIbj5kIxDs0Z8REO17bxbiPcR5-I84GxcSBa_LMnyPnmBSEwtezWjxUirlFTff4jSWzcwqvwbcIEFziuqrQhQOolxHKZx6ZzGQRcOgyrl4cUtQde8M83ybZnmvKmG2GMKOA-5cuGzZAW30d0snePm16790zPKQNJsZomJ8K5h61zqlHmFmNcqsMEu8rc8H1IlIRf7By5dGGzsj2tXNnFJLNTf7wzB91X7OHApvRkr34QVSSelQFXOcy6A2GlEkGUWIw8L3FwrwD7d2lo1znJx8fMpuYf1INOUtLLekAhjGeQyNdRGrr3pQiFbDPhAC-qEdKMb2gJVazyw.0UXZFo_qj_JitJtw8nxEWw.2QwV8uSMxapsnuWPoX_lYiXkdCBrMKQeyQVOWfulAD_kf0Y8i1D5DZjroDjoamxgzV2wnUcsaFIE-zwNfGdX5b5blhcA66j5ZAItqytz8QVZYuERoXYyz7Uki-bp7pf9iDWkI3V3quOII6IVzDe1aUIzZ1R34BVIeA388qmPkvEgk5Va-n6X0cghqwaJgSpqzzfHli1bNxd_iAcN494h7i89MmML9qScKRQ_Y0ygQr0CyzPMhYTWq6ggn6BXYFTyX7eqa_jGJwIIINWf7BsEVY14C1TmNe1TNSPV-0fVUKgNY70rnhyf0AV-Z352-efgRJPummUS5tQl3LLp-w3AZOF2WK7jPn2poY2YfmWEaYa0aYLz895PeQzi-puCllVEER0PWIiVyaKGiF21gmCvKiMm2gQcyMdXFTwcQUA1YE8wtJOwhlHbpizr8uJry5G49j2zh2aw7M8k6yKVYP5DsXn_SENQjNJXug8MOvY_DTy510jVB-rwrSs5FGmaiHdmMK0nmVtLMKvnAMgXWtq6oUxir7_Eqv1ruBGzk9Lvc_3d89Cjo6jLpp5CQDWx7X9CYdAXZ4L9RVvit0iAqqS-MsokS2bBGYdAWskmlhHKStUlNBmln8wipgvTtkSNTGqHFpbbaoyW96ZFvh8LPvuiMh_dFcwiJ896umLrg-eUCi-_lWrJsHH7tjMXWjWZ0XpkPEHLZvvMkNsdzXfGoHC6y016fmAJMZVs63oj2Ip1oLY.rlXrMXkxEoFO_8ovkfJb-ogsb91GUWhqM846bVnzobo' \
//   -H 'X-Client-ID: 93fd982c-19f0-4825-84c0-8b7e38b81ef1' \
//   -d '{
//     "model": "GigaChat-Pro-preview",
//     "messages": [
//       {
//         "role": "user",
//         "content": "Привет! Расскажи, как работает API GigaChat?"
//       }
//     ],
//     "temperature": 0.7,
//     "max_tokens": 1000
//   }'




// curl -k -L https://gigachat.devices.sberbank.ru/api/v1/models -H 'Accept: application/json' -H 'Authorization: Bearer eyJjdHkiOiJqd3QiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwiYWxnIjoiUlNBLU9BRVAtMjU2In0.ahGyWwwUy04CqT86ILM3su-27CwjYC7MFHrM1DVZYrXBXoO_IELQ4fEJcCIYVIx3rtv_w0OPX0jy8japzZfqr-VLmq75x7t99k88j-CWmItK_tzUlUK_sQR9tJpN0QGYvuRkFFykxU2YutFxzMQUiogKKbZagRHQSDQYJn9TlVUJowGIcpmuNGRN0V0evIEscs1oeMwszajSsUMnKuz8EoTSon8nVVTcIl_Aej2LWZsqpy173GnLVpC6XYSNFjdXoiyog0kbH4f-DnnPxcRAla6uv3QBDhJSGup1FVCWP9bL6KE2mAtn6fMgFMkfoSUNyWx2O_x12l4Id3AF7RLAog.5SuGGCKDt81kQRuXPDEl6Q.8tfIWJqX3Z0d55ug8pCjj2YOOHBXcBGsSgtPXly_KVE4FQFgEH4x1ymYHSL7OE5zx5nhqN352Wu0YvcW-uMmvdFfFRHM7bOsT6GpmBUh-vRrkr39xcybPN7mfMb3XX3_ZD3oyIi1ZCD3oyChpeAlbitBIoYHTfjGmme3Ew6pQTYgBHZnt_rutKDHzlIhCaA0V6nJjVZXpepEmh8gEnYPAd3j5tSZnN9Pd5Wgz3QkY44ndvQmKQf1fp61oH-002VEtQDwddSX8k9-gHmIhUjLY672VniypHfDybRVn8evkSlNDodB6XDRllRnoOaM9SJ9Dhu8cMXx9poONVGmnQL1-RdXof8sLKU7xHbvS6LtOdv22KpLYURJA64n873wy7FiZzO0fPuXrRkG4av7TIMZP8qra076u3akVmzOn-NQViKvNIvRaxh3Xml_PYLnoQgZt5zXws7EnTHkXUN3L0xeMLt3r4dem7wwSSQrrvRi-R_dM9VDrMeIxoulS7lqRsJjhbcGDq3vgel0ulmrqQwLR21xvUqQZetlmbI1bNAKIb5T_e40_uH2i6OCReRT5EcVVQqhw7-yFslH9on9dIE6mQGvqYPBlSWNuUF5i3oHwZF59RGQ5K2E-KO2qy-ZeC335eoApDATDGSfWVe6gxybtY_EqBz3lquNGiXXFrtHmiKYaM_2nmN6E-zkd9Fo2NzJRghFqyurpyiAXaxDdJLDLspPifBTLSKBT5g5QIyps94.iwzWN6AM2RfYE7H-XbNAUCtOrsYAx8a1lPGOEaXRyKw'