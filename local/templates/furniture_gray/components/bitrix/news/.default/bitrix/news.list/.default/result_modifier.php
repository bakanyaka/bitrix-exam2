<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if (is_set($arResult["ITEMS"][0]["ACTIVE_FROM"])) {
    $arResult["SPECIAL_DATE"] = $arResult["ITEMS"][0]["ACTIVE_FROM"];
    $this->getComponent()->setResultCacheKeys(["SPECIAL_DATE"]);
}

