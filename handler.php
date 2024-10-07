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



    $contactWebhookUrl = 'https://b24-kzv2jy.bitrix24.ru/rest/1/t5aditcnha4b1oy0/crm.contact.add.json';


$contactData = [
    'fields' => [
        'NAME' => $name,
        'PHONE' => [
            ['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']
        ],
    ],
    'params' => ['REGISTER_SONET_EVENT' => 'Y'] 
];

$ch = curl_init($contactWebhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contactData));

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    echo "Ошибка cURL: " . $error;
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$responseData = json_decode($response, true);

if ($httpCode != 200 || isset($responseData['error'])) {
    echo "Ошибка при создании контакта: " . ($responseData['error_description'] ?? "Неизвестная ошибка");
    var_dump($responseData);
    exit;
}


$contactId = $responseData['result'];


curl_close($ch);

$dealWebhookUrl = 'https://b24-kzv2jy.bitrix24.ru/rest/1/t5aditcnha4b1oy0/crm.deal.add.json';

$dealData = [
    'fields' => [
        'TITLE' => 'Заявка с сайта ' . date('Y-m-d H:i:s'), 
        'CONTACT_ID' => $contactId,
        'COMMENTS' => $comment, 
        'SOURCE_ID' => 'WEB',
        'STAGE_ID' => 'NEW', 
        'UF_CRM_1728163910686' => 'Сайт', // Источник
        'UF_CRM_1728199578391' => '57',
    ],
    'params' => ['REGISTER_SONET_EVENT' => 'Y'] 
];


$ch = curl_init($dealWebhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dealData));


$response = curl_exec($ch);


if ($response === false) {
    $error = curl_error($ch);
    echo "Ошибка cURL: " . $error;
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$responseData = json_decode($response, true);


if ($httpCode != 200 || isset($responseData['error'])) {
    echo "Ошибка при создании сделки: " . ($responseData['error_description'] ?? "Неизвестная ошибка");
    var_dump($responseData); 
} else {
    echo "Сделка успешно создана в Bitrix24!";
    var_dump($responseData); 

    $dealId = $responseData['result'];

    $commentUrl = 'https://b24-kzv2jy.bitrix24.ru/rest/1/t5aditcnha4b1oy0/crm.timeline.comment.add.json';
    $commentData = [
        'fields' => [
            'ENTITY_ID' => $dealId, 
            'ENTITY_TYPE' => 'deal', 
            'COMMENT' => $comment 
        ]
    ];

    $ch = curl_init($commentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commentData));

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        echo "Ошибка cURL при добавлении комментария: " . $error;
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseData = json_decode($response, true);

    if ($httpCode != 200 || isset($responseData['error'])) {
        echo "Ошибка при добавлении комментария: " . ($responseData['error_description'] ?? "Неизвестная ошибка");
        var_dump($responseData); 
    } else {
        echo "Комментарий успешно добавлен!";
    }

    curl_close($ch);
}

curl_close($ch);
}
?>
