<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"SET_SPECIAL_DATE" => Array(
		"NAME" => GetMessage("T_NEWS_PARAMS_SET_SPECIAL_DATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
    "CANONICAL_IBLOCK_ID" => Array(
        "NAME" => GetMessage("T_NEWS_PARAMS_CANONICAL_IBLOCK_ID"),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
);
