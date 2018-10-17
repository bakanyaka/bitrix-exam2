<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (is_set($arParams["SET_SPECIAL_DATE"]) && $arParams["SET_SPECIAL_DATE"] === "Y" && !empty($arResult["SPECIAL_DATE"])) {
   $APPLICATION->SetPageProperty("specialdate", $arResult["SPECIAL_DATE"]);
}