<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arCurrentValues */

$module_id = 'dmbgeo.prefilters';
if (CModule::IncludeModule($module_id)) {
    $iblockIncluded = CModule::IncludeModule('iblock');
    $catalogIncluded = CModule::includeModule('catalog');
    $arSites = array();
    foreach (\Prefilters::getSites() as $SITE) {
        $arSites[$SITE['LID']] = "[" . $SITE["LID"] . "] " . $SITE["NAME"];
    }
    $arCurrentValues['SITE_ID'] = $arCurrentValues['SITE_ID'] ?? \Prefilters::getSites()[0]['LID'];
    $arSections = array();
    $rsSection = \CIBlockSection::GetList(
        array("NAME" => "ASC"),
        array("IBLOCK_ID" => \Prefilters::getOption('IBLOCK_ID', $arCurrentValues['SITE_ID']), "DEPTH_LEVEL" => 1, "ACTIVE" => 'Y')
    );
    while ($arSection = $rsSection->fetch()) {
        $arSections[$arSection["ID"]] = "[" . $arSection["ID"] . "] " . $arSection["NAME"];
    }
    $arSort = CIBlockParameters::GetElementSortFields(
        array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
        array('KEY_LOWERCASE' => 'Y')
    );


    $arAscDesc = array(
        "asc" => GetMessage("IBLOCK_SORT_ASC"),
        "desc" => GetMessage("IBLOCK_SORT_DESC"),
    );
    $arComponentParameters = array(
        'PARAMETERS' => array(
            "SITE_ID" => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage("SITE_ID"),
                "TYPE" => "LIST",
                "VALUES" => $arSites,
                "REFRESH" => "Y",
                "DEFAULT" => $arCurrentValues['SITE_ID'],
            ),
            "SECTION_ID" => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage("SECTION_LIST_ID"),
                "TYPE" => "LIST",
                "VALUES" => $arSections,
                "DEFAULT" => '',
            ),
            'FILTER_NAME' => array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => GetMessage('VAR_SMART_FILTER_NAME'),
                'TYPE' => 'STRING',
                'DEFAULT' => 'arrFilter',
            ),
            'RENDER_TEMPLATE' => array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => GetMessage('RENDER_TEMPLATE'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y',
            ),
            "ELEMENT_SORT_FIELD" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
                "TYPE" => "LIST",
                "VALUES" => $arSort,
                "ADDITIONAL_VALUES" => "Y",
                "DEFAULT" => "id",
            ),
            "ELEMENT_SORT_ORDER" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
                "TYPE" => "LIST",
                "VALUES" => $arAscDesc,
                "DEFAULT" => "desc",
                "ADDITIONAL_VALUES" => "Y",
            ),
            "ELEMENT_SORT_FIELD2" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD2"),
                "TYPE" => "LIST",
                "VALUES" => $arSort,
                "ADDITIONAL_VALUES" => "Y",
                "DEFAULT" => "id",
            ),
            "ELEMENT_SORT_ORDER2" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER2"),
                "TYPE" => "LIST",
                "VALUES" => $arAscDesc,
                "DEFAULT" => "desc",
                "ADDITIONAL_VALUES" => "Y",
            ),
            "SEF_MODE" => array(
                "section" => array(
                    "NAME" => GetMessage("PREFILTER_SECTION_PAGE"),
                    "DEFAULT" => "#SECTION_ID#/",
                    "VARIABLES" => array(
                        "SECTION_ID",
                        "SECTION_CODE",
                        "SECTION_CODE_PATH",
                    ),
                ),
                "element" => array(
                    "NAME" => GetMessage("PREFILTER_DETAIL_PAGE"),
                    "DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/",
                    "VARIABLES" => array(
                        "ELEMENT_ID",
                        "ELEMENT_CODE",
                        "SECTION_ID",
                        "SECTION_CODE",
                        "SECTION_CODE_PATH",
                    ),
                )
            ),
            'CACHE_TIME' => array('DEFAULT' => 36000),
        ),
    );

    \CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
    if ($arCurrentValues["SEF_MODE"] == "Y") {
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"] = array();
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["ELEMENT_ID"] = array(
            "NAME" => GetMessage("CP_BC_VARIABLE_ALIASES_ELEMENT_ID"),
            "TEMPLATE" => "#ELEMENT_ID#",
        );
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["ELEMENT_CODE"] = array(
            "NAME" => GetMessage("CP_BC_VARIABLE_ALIASES_ELEMENT_CODE"),
            "TEMPLATE" => "#ELEMENT_CODE#",
        );
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["SECTION_ID"] = array(
            "NAME" => GetMessage("CP_BC_VARIABLE_ALIASES_SECTION_ID"),
            "TEMPLATE" => "#SECTION_ID#",
        );
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["SECTION_CODE"] = array(
            "NAME" => GetMessage("CP_BC_VARIABLE_ALIASES_SECTION_CODE"),
            "TEMPLATE" => "#SECTION_CODE#",
        );
        $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["SECTION_CODE_PATH"] = array(
            "NAME" => GetMessage("CP_BC_VARIABLE_ALIASES_SECTION_CODE_PATH"),
            "TEMPLATE" => "#SECTION_CODE_PATH#",
        );
        
        $smartBase = ($arCurrentValues["SEF_URL_TEMPLATES"]["section"]? $arCurrentValues["SEF_URL_TEMPLATES"]["section"]: "#SECTION_ID#/");
        $arComponentParameters["PARAMETERS"]["SEF_MODE"]["smart_filter"] = array(
            "NAME" => GetMessage("CP_BC_SEF_MODE_SMART_FILTER"),
            "DEFAULT" => $smartBase."filter/#SMART_FILTER_PATH#/apply/",
            "VARIABLES" => array(
                "SECTION_ID",
                "SECTION_CODE",
                "SECTION_CODE_PATH",
                "SMART_FILTER_PATH",
            ),
        );
    }
}
