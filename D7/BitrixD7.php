<?php
use Bitrix\Main\Loader;
use Bitrix\Crm\ContactTable;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\Timeline\CommentEntry;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!Loader::includeModule('crm')) {
    echo "Ошибка: Модуль CRM не загружен!";
    exit;
}

$name = $_POST['name']; 
$phone = $_POST['phone']; 
$comment = $_POST['comment']; 

$contactResult = ContactTable::add([
    'NAME' => $name,
    'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
]);

if (!$contactResult->isSuccess()) {
    echo "Ошибка при создании контакта: " . implode(', ', $contactResult->getErrorMessages());
    exit;
}


$contactId = $contactResult->getId();


$dealFields = [
    'TITLE' => 'Заявка с сайта ' . date('Y-m-d H:i:s'),
    'CONTACT_ID' => $contactId,
    'COMMENTS' => $comment,
    'SOURCE_ID' => 'WEB',
    'STAGE_ID' => 'NEW',
    'UF_CRM_1728163910686' => 'Сайт', //Тег
    'UF_CRM_1728199578391' => '57',  //Источник
];

$dealResult = DealTable::add($dealFields);

if (!$dealResult->isSuccess()) {
    echo "Ошибка при создании сделки: " . implode(', ', $dealResult->getErrorMessages());
    exit;
}


$dealId = $dealResult->getId();


$commentFields = [
    'ENTITY_TYPE' => 'deal', 
    'ENTITY_ID' => $dealId,  
    'COMMENT' => $comment,  
];

$commentResult = CommentEntry::add($commentFields);

if (!$commentResult->isSuccess()) {
    echo "Ошибка при добавлении комментария: " . implode(', ', $commentResult->getErrorMessages());
} else {
    echo "Сделка и комментарий успешно добавлены!";
}

?>
