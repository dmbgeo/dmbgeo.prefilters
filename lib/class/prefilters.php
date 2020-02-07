<?
class Prefilters
{
    public static $MODULE_ID = 'dmbgeo.prefilters';

    public static function getSites()
    {
        $SITES = array();
        $rsSites = \CSite::GetList($by = "sort", $order = "desc");
        while ($arSite = $rsSites->Fetch()) {
            $SITES[] = $arSite;
        }
        return $SITES;
    }

    public static function setNewUrl($newUrl){
        GLOBAL $APPLICATION;
        $_SERVER['REQUEST_URI']=$newUrl;
        $application = \Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $Response = $context->getResponse();
        $Server = $context->getServer();
        $server_get = $Server->toArray();
        $server_get["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
        $Server->set($server_get);
        $context->initialize(new Bitrix\Main\HttpRequest($Server, array(), array(), array(), $_COOKIE), $Response, $Server);
        $APPLICATION->SetCurPage($_SERVER["REQUEST_URI"]);
        $APPLICATION->reinitPath();
    }

    public static function getOptions($SITE_ID = SITE_ID)
    {
        $params['MODULE_STATUS'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_MODULE_STATUS_' . $SITE_ID, "N");
        $params['DELETE_STATUS'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS_' . $SITE_ID, "N");
        $params['IBLOCK_ID'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE_ID, "N");
        return $params;
    }

    public static function getOption($PARAM, $SITE_ID = SITE_ID)
    {
        return \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_' . $PARAM . '_' . $SITE_ID, "N");
    }

}
