<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name']; 
    $phone = $_POST['phone']; 
    $comment = $_POST['comment']; 

    $subdomain = 'turkeypsn063'; 
    $access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImRiODY3MjczYzc1NjM3YmMxNGNiZDc1ZWExODFlYTZkZGRkZTM3Y2Q1YjY0MDA3NDExZmE5OTQ4NTFjMTM2MDc2ZjA5NzVjN2QzM2YyMjdlIn0.eyJhdWQiOiIzM2Q2YzZjMC1mMDJhLTQ5OWEtYWE2Yy01MWU5YTFkMzc1ZTUiLCJqdGkiOiJkYjg2NzI3M2M3NTYzN2JjMTRjYmQ3NWVhMTgxZWE2ZGRkZGUzN2NkNWI2NDAwNzQxMWZhOTk0ODUxYzEzNjA3NmYwOTc1YzdkMzNmMjI3ZSIsImlhdCI6MTcyODEzNjYzMSwibmJmIjoxNzI4MTM2NjMxLCJleHAiOjE3Mjk5ODcyMDAsInN1YiI6IjExNjA5NzEwIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxOTkxMzEwLCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiOTliMDVmMmMtMTAxOS00OGRhLWIyYTYtNzBlMzdmOTVjMmVmIiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.lD14iWDaRoWrySkBPhQjtz7qQdRQvbqXThzvCyijWy-1TUxrk6Ii1CdgCEOYkWvUhMx9rd8mUEwSncNCFNHMLUzBHnjGTE1nIGKLPgFKweOFuN6GPi8vxj14HkvnGNlPRiCz9Hl40scPjzv27aB-wKwqQJPg6bKUVKVw_aPCnEgr2WqUBw_MWOk-2vlwKSHHKEBCBsHl9u5-wszgt34haQnMXCzSe28smgHODqOK15YN4Bz_PlqUOMRiKkF_o75jOlWOkr4t7KzMNPur1AD4kmxktqX4vkSgc_f0FJQgej3IS01ahpY6jYFjDb7XK9tEjX5nAfk-JenKMP_Uovm4Lw'; // токен доступа amoCRM


    $leadData = [
        'name' => 'Заявка с сайта ' . date('Y-m-d H:i:s'), 
        'custom_fields_values' => [
            [
                'field_id' => 638915, // источник
                'values' => [
                    [
                        'value' => 'Сайт'
                    ]
                ]
            ]
        ],
        '_embedded' => [
            'tags' => [
                ['name' => 'Сайт'], 
            ],
            'contacts' => [
                [
                    'name' => $name, 
                    'custom_fields_values' => [
                        [
                            'field_id' => 639921, // номер телефона
                            'values' => [
                                [
                                    'value' => $phone 
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $urlLead = "https://$subdomain.amocrm.ru/api/v4/leads/complex";
    $responseLead = file_get_contents($urlLead, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer $access_token\r\n",
            'content' => json_encode([$leadData]), 
        ],
    ]));
    
    if ($responseLead === FALSE) {
        echo "Ошибка при отправке данных в amoCRM";
    } else {

        $leadResponseData = json_decode($responseLead, true);

        if (isset($leadResponseData[0]['id'])) {
            $leadId = $leadResponseData[0]['id']; 
            echo "ID сделки: $leadId<br>";


            $noteData = [
                [
                    'note_type' => 'common', 
                    'params' => [
                        'text' => $comment 
                    ],
                    'entity_id' => $leadId, 
                    'entity_type' => 'leads'
                ]
            ];

            $urlNote = "https://$subdomain.amocrm.ru/api/v4/leads/$leadId/notes";
            $responseNote = file_get_contents($urlNote, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nAuthorization: Bearer $access_token\r\n",
                    'content' => json_encode($noteData),
                ],
            ]));

            if ($responseNote === FALSE) {
                echo "Ошибка при добавлении комментария к сделке";
            } else {
                echo "Комментарий успешно добавлен к сделке!";
                var_dump($responseNote); 
            }
        } else {
            echo "Ошибка: ID сделки не найден.";
        }
    }
}
?>
