<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (is_set($arParams["CANONICAL_IBLOCK_ID"]) && intval($arParams["CANONICAL_IBLOCK_ID"]) > 0) {

    $select = ["ID", "NAME"];
    $filter = [
        "IBLOCK_ID" => 5,
        "PROPERTY_REL_NEWS" => $arParams["ELEMENT_ID"]
    ];

    $iterator = CIBlockElement::GetList([], $filter, false, array(), $select);
    if ($result = $iterator->GetNext()) {
        $arResult["CANONICAL_LINK"] = $result["NAME"];
        $this->getComponent()->setResultCacheKeys(["CANONICAL_LINK"]);
    }
}

