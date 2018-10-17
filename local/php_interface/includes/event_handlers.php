<?php
$eventManager = \Bitrix\Main\EventManager::getInstance();

const PRODUCTS_IBLOCK_ID = 2;
const CONTENT_MANAGER_GROUP_ID = 5;
const META_IBLOCK_ID = 6;

// SEO Tool
$eventManager->addEventHandler("main", "OnPageStart", function () {
    global $APPLICATION;
    $currentPage = $APPLICATION->GetCurPage();
    if (strpos($currentPage, '/bitrix/admin/') === 0) {
        return;
    }
    if (!Bitrix\Main\Loader::includeModule('iblock')) {
        return;
    }

    $filter = [
        "IBLOCK_ID" => META_IBLOCK_ID,
        "NAME" => $currentPage,
    ];
    $select = ["ID", "PROPERTY_TITLE", "PROPERTY_DESCRIPTION"];
    $result = CIBlockElement::GetList([], $filter, false, false, $select);
    if ($match = $result->Fetch()) {
        $APPLICATION->SetPageProperty('title',$match['PROPERTY_TITLE_VALUE']);
        $APPLICATION->SetPageProperty('description',$match['PROPERTY_DESCRIPTION_VALUE']);
    }
});

// Remove all menu links but news from admin page of content managers
$eventManager->addEventHandler("main", "OnBuildGlobalMenu", function (&$aGlobalMenu, &$aModuleMenu) {
    global $USER;
    if ($USER->IsAdmin() || !in_array(CONTENT_MANAGER_GROUP_ID, $USER->GetUserGroupArray())) {
        return true;
    }

    foreach ($aGlobalMenu as $key => $v) {
        if ($key !== 'global_menu_content') {
            unset($aGlobalMenu[$key]);
        }
    }

    foreach ($aModuleMenu as $index => $menu) {
        if ($menu["items_id"] !== 'menu_iblock_/news') {
            unset($aModuleMenu[$index]);
        }
    }
    return true;
});

// Modify author in Feedback form
$eventManager->addEventHandler("main", "OnBeforeEventAdd", function (&$event, &$lid, &$arFields) {
    global $USER;

    if ($event !== "FEEDBACK_FORM") {
        return true;
    }

    if (!$USER->IsAuthorized()) {
        $arFields["AUTHOR"] = "Пользователь не авторизован, данные из формы: {$arFields["AUTHOR"]}";
    } else {
        $arFields["AUTHOR"] = "Пользователь авторизован: {$USER->GetID()} ({$USER->GetLogin()}) {$USER->GetFullName()}, данные из формы: {$arFields["AUTHOR"]}";
    }
    CEventLog::Add([
        "SEVERITY" => "INFO",
        "AUDIT_TYPE_ID" => "MAIL_DATA_REPLACED",
        "MODULE_ID" => "main",
        "ITEM_ID" => $USER->GetID(),
        "DESCRIPTION" => "Замена данных в отсылаемом письме – {$arFields["AUTHOR"]}"
    ]);
});

// Before Product deactivated event
$eventManager->addEventHandler("iblock", "OnBeforeIBlockElementUpdate", function (&$arFields) {
    global $APPLICATION;

    // If element does not belong to products iblock or element was not deactivated do nothing
    if ($arFields["IBLOCK_ID"] !== PRODUCTS_IBLOCK_ID || $arFields["ACTIVE"] !== "N") {
        return true;
    }

    $select = ["ID", "NAME", "SHOW_COUNTER", "ACTIVE"];
    $filter = [
        "ID" => $arFields["ID"],
        "IBLOCK_ID" => $arFields["IBLOCK_ID"]
    ];
    $iterator = CIBlockElement::GetList([], $filter, false, array(), $select);

    $original = $iterator->GetNext();

    // If Active field was not changed do nothing
    if (!$original || $original["ACTIVE"] === $arFields["ACTIVE"]) {
        return true;
    }

    if ($original["SHOW_COUNTER"] > 2) {
        $APPLICATION->throwException("Товар невозможно деактивировать, у него {$original["SHOW_COUNTER"]} просмотров");
        return false;
    }

    return true;
});