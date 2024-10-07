<?php
$name = $_POST['name']; 
$phone = $_POST['phone']; 
$comment = $_POST['comment']; 


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
?>
