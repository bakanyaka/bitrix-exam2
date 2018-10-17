<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="news-products-list">
    <h3>
        <?= Loc::getMessage("SIMPLECOMP_EXAM_TEMPLATE_ELEMENTS") ?>: <?= $arResult["PRODUCTS_COUNT"] ?>
    </h3>
    <hr>
    <b><?= Loc::getMessage("SIMPLECOMP_EXAM_TEMPLATE_CATALOG") ?>:</b>
    <ul>
        <? foreach ($arResult["NEWS"] as $newsItem): ?>
            <li>
                <?= "<b>{$newsItem["NAME"]}</b> - {$newsItem["ACTIVE_FROM"]}"?> (<?= join(", ", $newsItem["SECTION_NAME"]) ?>)
                <ul>
                    <? foreach ($newsItem["PRODUCTS"] as $product): ?>
                        <li>
                            <?= "{$product["NAME"]} - {$product["PROPERTY_PRICE_VALUE"]} - {$product["PROPERTY_MATERIAL_VALUE"]} - {$product["PROPERTY_ARTNUMBER_VALUE"]}" ?>
                        </li>
                    <? endforeach; ?>
                </ul>
            </li>
        <? endforeach; ?>
    </ul>
</div>
