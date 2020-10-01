<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use \Bitrix\Main\Localization\Loc;
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$module_id = 'dmbgeo.prefilters';
if (CModule::IncludeModule($module_id)) {
    if (Prefilters::getOption('MODULE_STATUS') == 'Y') {
        $arParams["IBLOCK_ID"] = \Prefilters::getOption('IBLOCK_ID');
        $arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);
        if ($arParams["FILTER_NAME"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])) {
            $arParams["FILTER_NAME"] = "arrFilter";
        }

        $arParams['ACTION_VARIABLE'] = (isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : 'action');
        if ($arParams["ACTION_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"])) {
            $arParams["ACTION_VARIABLE"] = "action";
        }
        if(empty($arParams['ELEMENT_SORT_FIELD'])){
            $arParams['ELEMENT_SORT_FIELD']='SORT';
        }

        if(empty($arParams['ELEMENT_SORT_ORDER'])){
            $arParams['ELEMENT_SORT_ORDER']='ASC';
        }
        if(empty($arParams['ELEMENT_SORT_FIELD2'])){
            $arParams['ELEMENT_SORT_FIELD2']='NAME';
        }

        if(empty($arParams['ELEMENT_SORT_ORDER2'])){
            $arParams['ELEMENT_SORT_ORDER2']='ASC';
        }
        
        $arOrder = array($arParams['ELEMENT_SORT_FIELD'] => $arParams['ELEMENT_SORT_ORDER'],$arParams['ELEMENT_SORT_FIELD2'] => $arParams['ELEMENT_SORT_ORDER2']);
       
$arParams['ACTION_VARIABLE'] = (isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : 'action');
if ($arParams["ACTION_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";

$smartBase = ($arParams["SEF_URL_TEMPLATES"]["section"]? $arParams["SEF_URL_TEMPLATES"]["section"]: "#SECTION_ID#/");
$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_ID#/",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"compare" => "compare.php?action=COMPARE",
	"smart_filter" => $smartBase."filter/#SMART_FILTER_PATH#/apply/"
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
	"action",
);

        if ($arParams["SEF_MODE"] == "Y") {
            $arVariables = array();

            $engine = new CComponentEngine($this);

            if (\Bitrix\Main\Loader::includeModule('iblock')) {
                $engine->addGreedyPart("#SECTION_CODE_PATH#");
		        $engine->addGreedyPart("#SMART_FILTER_PATH#");
		        $engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
            }
            $arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
            $arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

            $componentPage = $engine->guessComponentPath(
                $arParams["SEF_FOLDER"],
                $arUrlTemplates,
                $arVariables
            );
            // var_debug($componentPage,$arUrlTemplates,$arVariables,$arDefaultUrlTemplates404);
            CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

            $arResult = array(
                "FOLDER" => $arParams["SEF_FOLDER"],
                "URL_TEMPLATES" => $arUrlTemplates,
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases,
            );
            
            if (isset($arVariables['ELEMENT_CODE'])) {
                $ELEMENT_VAR_VALUE = $arVariables['ELEMENT_CODE'];
                $ELEMENT_VAR = "CODE";
            } elseif (isset($arVariables['ELEMENT_ID'])) {
                $ELEMENT_VAR_VALUE = $arVariables['ELEMENT_ID'];
                $ELEMENT_VAR = "ID";
            } else {
                $ELEMENT_VAR = null;
                $ELEMENT_VAR_VALUE = null;
            }
            
            if (isset($arVariables['SECTION_CODE'])) {
                $SECTION_VAR_VALUE = $arVariables['SECTION_CODE'];
                $SECTION_VAR = "SECTION_CODE";
            } elseif (isset($arVariables['SECTION_ID'])) {
                $SECTION_VAR_VALUE = $arVariables['SECTION_ID'];
                $SECTION_VAR = "SECTION_ID";
            } elseif (isset($arVariables['SECTION_CODE_PATH'])) {
                $SECTION_VAR_VALUE = array_pop(explode("/", $arVariables['SECTION_CODE_PATH']));
                $SECTION_VAR = "SECTION_CODE";
            } else {
                $SECTION_VAR = null;
                $SECTION_VAR_VALUE = null;
            }
            
            if (isset($arVariables['ELEMENT_CODE']) || isset($arVariables['ELEMENT_ID'])) {
                $arFilter = array(
                    "ACTIVE" => "Y",
                    'IBLOCK_ID' => $arParams["IBLOCK_ID"],
                    'INCLUDE_SUBSECTIONS' => $arParams["SECTION_ID"],
                );

                if ($SECTION_VAR) {
                    $arFilter[$SECTION_VAR] = $SECTION_VAR_VALUE;
                }

                if ($ELEMENT_VAR) {
                    $arFilter[$ELEMENT_VAR] = $ELEMENT_VAR_VALUE;
                }
                $rsElement = \CIBlockElement::GetList(
                    $arOrder,
                    $arFilter
                );
                if ($arElement = $rsElement->GetNextElement()) {
                    $arResult['ELEMENT'] = $arElement->GetFields();
                    $arResult['ELEMENT']['PROPERTIES'] = $arElement->GetProperties();
                    $arSection = \CIBlockSection::GetByID($ITEM['IBLOCK_SECTION_ID'])->fetch();
                    if (is_array($arSection)) {
                        $ITEM['SECTION']['SECTION_ID'] = $arSection['ID'];
                        $ITEM['SECTION']['SECTION_CODE'] = $arSection['CODE'];
                    }

                    // $APPLICATION->AddChainItem($$arResult['ELEMENT']['NAME']);
                    $newUrl = explode(str_replace("www.","",$_SERVER['SERVER_NAME']), $arResult['ELEMENT']['PROPERTIES']['FILTER_URL']['~VALUE'] ?? "");
                    $newUrl=$newUrl[1]??$newUrl[0];
                    if ($newUrl !== "") {
                        \Prefilters::setNewUrl($newUrl);
                    }

                    foreach (['PROPERTIES']['FILTER_PARAM']['~VALUE'] ?? array() as $key => $param) {
                        $GLOBALS[$arParams['FILTER_NAME']][] = array($param => $arResult['ELEMENT']['PROPERTIES']['FILTER_PARAM']['~DESCRIPTION'][$key]);
                    }
                }
            }

            if ($arParams['RENDER_TEMPLATE'] == "Y") {
                $arFilter = array(
                    "ACTIVE" => "Y",
                    'IBLOCK_ID' => $arParams["IBLOCK_ID"],
                    'INCLUDE_SUBSECTIONS' => $arParams["SECTION_ID"],
                );

                if ($SECTION_VAR) {
                    $arFilter[$SECTION_VAR] = $SECTION_VAR_VALUE;

                    $rsElement = \CIBlockElement::GetList(
                        $arOrder,
                        $arFilter
                    );

                    $SELECT_ID = $arResult['ELEMENT']['ID'] ?? "0";
                    $ELEMENT_PATH = ($arParams["SEF_FOLDER"] ?? "") . ($arParams['SEF_URL_TEMPLATES']['element'] ?? "");
                    $SECTION_PATH = ($arParams["SEF_FOLDER"] ?? "") . ($arParams['SEF_URL_TEMPLATES']['section'] ?? "");
                    $rsElement->SetUrlTemplates($ELEMENT_PATH, $SECTION_PATH);
                    while ($arElement = $rsElement->GetNext()) {

                        $ITEM = $arElement;

                        if ($ITEM['ID'] == $SELECT_ID) {
                            $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($ITEM["IBLOCK_ID"], $ITEM["ID"]); 
                            $ITEM["IPROPERTY_VALUES"] = $ipropValues->getValues();
                            $GLOBALS['PREDFILTER_SEO']=$ITEM["IPROPERTY_VALUES"];
                            if(!empty($ITEM["IPROPERTY_VALUES"]['ELEMENT_META_TITLE'])){
                                $APPLICATION->SetPageProperty("title",  $ITEM["IPROPERTY_VALUES"]['ELEMENT_META_TITLE']);
                            }
                            if(!empty($ITEM["IPROPERTY_VALUES"]['ELEMENT_META_KEYWORDS'])){
                                $APPLICATION->SetPageProperty("keywords",  $ITEM["IPROPERTY_VALUES"]['ELEMENT_META_KEYWORDS']);
                            }
                            if(!empty($ITEM["IPROPERTY_VALUES"]['ELEMENT_META_DESCRIPTION'])){
                                $APPLICATION->SetPageProperty("description",  $ITEM["IPROPERTY_VALUES"]['ELEMENT_META_DESCRIPTION']);
                            }

                            $ITEM['SELECTED'] = true;
                        } else {
                            $ITEM['SELECTED'] = false;
                        }

                        if ($ITEM['PREVIEW_PICTURE']) {
                            $ITEM['PREVIEW_PICTURE'] = \CFile::GetFileArray($ITEM['PREVIEW_PICTURE']);
                        }
                        if ($ITEM['DETAIL_PICTURE']) {
                            $ITEM['DETAIL_PICTURE'] = \CFile::GetFileArray($ITEM['DETAIL_PICTURE']);
                        }

                        $arResult['ITEMS'][] = $ITEM;
                    }

                    if (!empty($arResult['ITEMS'])) {
                        $this->IncludeComponentTemplate();
                    }
                }
            }
        } else {
            ShowError(Loc::getMessage('ERROR_PREFILTERS_SEF_MODE'));
        }
    } else {
        ShowError(Loc::getMessage('ERROR_PREFILTERS_MODUL_STATUS'));
    }
} else {
    ShowError(Loc::getMessage('ERROR_PREFILTERS_MODUL_INSTALL'));
}
