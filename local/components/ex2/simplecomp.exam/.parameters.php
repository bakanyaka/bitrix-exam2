<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    "GROUPS" => [
    ],
    "PARAMETERS" => [
        "NEWS_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SIMPLECOMP_EXAM_PARAMETERS_NEWS_IBLOCK_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "PRODUCTS_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SIMPLECOMP_EXAM_PARAMETERS_PRODUCTS_IBLOCK_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SIMPLECOMP_EXAM_PARAMETERS_PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "CACHE_TIME" => ["DEFAULT" => "36000000"],
    ]
];