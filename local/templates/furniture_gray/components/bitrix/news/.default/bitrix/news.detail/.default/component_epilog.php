<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!empty($arResult["CANONICAL_LINK"])) {
   $APPLICATION->SetPageProperty("canonical", $arResult["CANONICAL_LINK"]);
}