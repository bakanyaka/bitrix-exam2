<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SimplecompExamComponent extends CBitrixComponent
{
    /**
     * arResult keys to cache
     * @var array
     */
    protected $cacheKeys = ["PRODUCTS_COUNT", "MIN_PRICE", "MAX_PRICE"];

    /**
     * Prepares all the parameters passed.
     * @param mixed[] $params List of unchecked parameters
     * @return mixed[] Cleaned up parameters
     */
    public function onPrepareComponentParams($params)
    {

        $params['CACHE_TIME'] = intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 36000000;

        $params['NEWS_IBLOCK_ID'] = trim($params['NEWS_IBLOCK_ID']);
        $params['PRODUCTS_IBLOCK_ID'] = trim($params['PRODUCTS_IBLOCK_ID']);
        $params['PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE'] = trim($params['PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE']);

        return $params;
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->checkParams();
            $this->executeBeforeCaching();
            if (!$this->readCache()) {
                $this->getResult();
                $this->SetResultCacheKeys($this->cacheKeys);
                $this->includeComponentTemplate();
            }
            $this->executeAfterCaching();
        } catch (Exception $e) {
            $this->AbortResultCache();
            ShowError($e->getMessage());
        }
    }

    /**
     * Checks that all required modules are installed
     * @throws Main\LoaderException
     */
    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new Main\LoaderException(Loc::getMessage('SIMPLECOMP_EXAM_CLASS_IBLOCK_MODULE_NOT_INSTALLED'));
        }
    }

    /**
     * Validates parameters
     * @throws Main\ArgumentNullException
     */
    protected function checkParams()
    {
        if (intval($this->arParams['NEWS_IBLOCK_ID']) < 1) {
            throw new Main\ArgumentNullException('NEWS_IBLOCK_ID');
        }
        if (intval($this->arParams['PRODUCTS_IBLOCK_ID']) < 1) {
            throw new Main\ArgumentNullException('PRODUCTS_IBLOCK_ID');
        }
        if (strlen($this->arParams['PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE']) < 1) {
            throw new Main\ArgumentNullException('PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE');
        }
    }


    /**
     *  Executes actions before caching
     */
    protected function executeBeforeCaching()
    {
    }

    protected function readCache()
    {
        return !$this->startResultCache(false);
    }

    protected function getResult()
    {
        $newsLinkCode = $this->arParams["PRODUCTS_SECTION_NEWS_REL_PROPERTY_CODE"];
        $news = [];
        // Get section IDs and section names grouped by news ID
        $filter = [
            "IBLOCK_ID" => $this->arParams["PRODUCTS_IBLOCK_ID"],
            "ACTIVE" => "Y",
            "!$newsLinkCode" => false
        ];
        $select = ["ID", "IBLOCK_ID", "NAME", $newsLinkCode];
        $rsSections = CIBlockSection::GetList([], $filter, false, $select);
        $sectionIDs= [];
        while ($section = $rsSections->GetNext()) {
            $sectionIDs[] = $section["ID"];
            foreach ($section[$newsLinkCode] as $newsId) {
                if (!isset($news[$newsId])) {
                    $news[$newsId]["SECTION_ID"] = [$section["ID"]];
                    $news[$newsId]["SECTION_NAME"] = [$section["NAME"]];
                } else if (!in_array($section["ID"],  $news[$newsId]["SECTION_ID"])) {
                    $news[$newsId]["SECTION_ID"][] = $section["ID"];
                    $news[$newsId]["SECTION_NAME"][] = $section["NAME"];
                }
            }
        }

        // Get products grouped by section ID
        $filter = [
            "IBLOCK_ID" => $this->arParams["PRODUCTS_IBLOCK_ID"],
            "ACTIVE" => "Y",
            "IBLOCK_SECTION_ID" => $sectionIDs
        ];
        $select = ["ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_MATERIAL", "PROPERTY_PRICE", "PROPERTY_ARTNUMBER"];
        $rsProducts = CIBlockElement::GetList([], $filter, false, false, $select);
        $products = [];
        $this->arResult["PRODUCTS_COUNT"] = 0;
        $this->arResult["MAX_PRICE"] = 0;
        $this->arResult["MIN_PRICE"] = 0;
        while ($product = $rsProducts->GetNext()) {
            $products[$product["IBLOCK_SECTION_ID"]][] = $product;
            $this->arResult["PRODUCTS_COUNT"]++;
            if ($product["PROPERTY_PRICE_VALUE"] > $this->arResult["MAX_PRICE"]) {
                $this->arResult["MAX_PRICE"] = $product["PROPERTY_PRICE_VALUE"];
            }
            if ($this->arResult["MIN_PRICE"] === 0 || $product["PROPERTY_PRICE_VALUE"] < $this->arResult["MIN_PRICE"]) {
                $this->arResult["MIN_PRICE"] = $product["PROPERTY_PRICE_VALUE"];
            }
        }

        // Get news and attach related sections and products to them
        $filter = [
            "IBLOCK_ID" => $this->arParams["NEWS_IBLOCK_ID"],
            "ACTIVE" => "Y",
            "ID" => array_keys($news) // Only news that have related product sections
        ];
        $select = ["ID", "NAME", "ACTIVE_FROM"];
        $newsResult = [];
        $rsNews = CIBlockElement::GetList([], $filter, false, false, $select);
        while ($newsElement = $rsNews->GetNext()) {
            $newsElement = array_merge($newsElement, $news[$newsElement["ID"]]); // merge related sections
            $newsElement["PRODUCTS"] = [];
            foreach ($newsElement["SECTION_ID"] as $sectionID) {
                // merge products for each section
                if (isset($products[$sectionID])) {
                    $newsElement["PRODUCTS"] = array_merge($newsElement["PRODUCTS"], $products[$sectionID]);
                }
            }
            // Sort products by name
            usort($newsElement["PRODUCTS"], function ($a, $b) {
                return $a["NAME"] <=> $b["NAME"];
            });
            $newsResult[] = $newsElement;
        }
        $this->arResult["NEWS"] = $newsResult;
    }

    protected function executeAfterCaching()
    {
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('SIMPLECOMP_EXAM_CLASS_PRODUCTS_IN_CATALOG') . " " . $this->arResult["PRODUCTS_COUNT"]);
        $this->addPricesViewContent($this->arResult["MIN_PRICE"], $this->arResult["MAX_PRICE"]);
    }

    protected function addPricesViewContent($minPrice, $maxPrice) {
        global $APPLICATION;
        $content = "
            <div style=\"color:red; margin: 34px 15px 35px 15px\">
                --- <br>
                Минимальная цена: $minPrice <br>
                Максимальная цена: $maxPrice <br>
                ---
            </div>
        ";
        $APPLICATION->AddViewContent("PRICES_BLOCK", $content);
    }

}