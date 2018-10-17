<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => Loc::getMessage('SIMPLECOMP_EXAM_COMPONENT_NAME'),
    'DESCRIPTION' => Loc::getMessage('SIMPLECOMP_EXAM_COMPONENT_DESCRIPTION'),
    'SORT' => 10,
    'PATH' => array(
        'ID' => 'exam2',
        'NAME' => Loc::getMessage('SIMPLECOMP_EXAM_COMPONENT_GROUP'),
        'SORT' => 10,
    )
);