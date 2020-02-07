<?
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class dmbgeo_prefilters extends CModule
{
    public $MODULE_ID = 'dmbgeo.prefilters';
    public $COMPANY_ID = 'dmbgeo';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function dmbgeo_prefilters()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("DMBGEO_PREFILTERS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DMBGEO_PREFILTERS_MODULE_DESC");
        $this->PARTNER_NAME = getMessage("DMBGEO_PREFILTERS_PARTNER_NAME");
        $this->PARTNER_URI = getMessage("DMBGEO_PREFILTERS_PARTNER_URI");
        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php',
        );
    }

    public function InstallDB()
    {
        global $DB;
        $strSql = "CREATE TABLE `dmbgeo_prefilters` ( `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT , `ORDER_ID` INT UNSIGNED NOT NULL , `SUCCESS` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`ID`)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;";
        $DB->Query($strSql, false, "File: " . __FILE__ . "Line: " . __LINE__);
    }

    public function UnInstallDB()
    {
        global $DB;
        $strSql = "DROP TABLE `dmbgeo_prefilters`";
        $DB->Query($strSql, false, "File: " . __FILE__ . "Line: " . __LINE__);
    }

    public function InstallIblock()
    {
        CModule::IncludeModule('main');
        global $DB;
        $MODULE_ID = str_replace(".", "_", $this->MODULE_ID);
        $SITES = array();
        $rsSites = \CSite::GetList($by = "sort", $order = "desc");
        while ($arSite = $rsSites->Fetch()) {
            $SITES[] = $arSite;
        }
        if (\CModule::IncludeModule('iblock')) {
            foreach ($SITES as $SITE) {
                if (!\CIBlockType::GetByID($MODULE_ID)->Fetch()) {
                    $arFields = array(
                        'ID' => $MODULE_ID,
                        'SECTIONS' => 'Y',
                        'IN_RSS' => 'N',
                        'SORT' => 100,
                        'LANG' => array(
                            'en' => array(
                                'NAME' => 'Prefilter',
                                'SECTION_NAME' => 'Types',
                                'ELEMENT_NAME' => 'Prefilters',
                            ),
                            'ru' => array(
                                'NAME' => 'Предфильтры',
                                'SECTION_NAME' => 'Типы',
                                'ELEMENT_NAME' => 'Предфильтры',
                            ),
                        ),
                    );
                    $obBlocktype = new \CIBlockType;
                    $DB->StartTransaction();
                    $res = $obBlocktype->Add($arFields);
                    if (!$res) {
                        $DB->Rollback();
                    } else {
                        $DB->Commit();
                    }
                }
                if (\CIBlockType::GetByID($MODULE_ID)->Fetch()) {
                    $IBLOCK = \CIBlock::GetList(array(), array("TYPE" => $MODULE_ID, "SITE_ID" => $SITE['LID'], 'CODE' => $MODULE_ID . "_" . $SITE['LID']))->Fetch();
                    if (!$IBLOCK) {
                        $ib = new \CIBlock;
                        $arFields = array(
                            "ACTIVE" => "Y",
                            "NAME" => $SITE['NAME'],
                            "CODE" => $MODULE_ID . "_" . $SITE['LID'],
                            "IBLOCK_TYPE_ID" => $MODULE_ID,
                            "SITE_ID" => array($SITE['LID']),
                            "SORT" => 100,
                        );
                        $arFields['FIELDS']['CODE']['IS_REQUIRED']="Y";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['UNIQUE']="Y";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANSLITERATION']="Y";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANS_LEN']="100";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANS_CASE']="L";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANS_SPACE']="-";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANS_OTHER']="-";
                        $arFields['FIELDS']['CODE']['DEFAULT_VALUE']['TRANS_EAT']="Y";
                        $ID = $ib->Add($arFields);
                        \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE['LID'], $ID);
                        $arFields = array(
                            "NAME" => "Ссылка фильтра",
                            "ACTIVE" => "Y",
                            "SORT" => "100",
                            "CODE" => "FILTER_URL",
                            "PROPERTY_TYPE" => "S",
                            "IBLOCK_ID" => $ID,
                        );

                        $ibp = new \CIBlockProperty;
                        $PropID = $ibp->Add($arFields);
                        $arFields = array(
                            "NAME" => "Параметры массива фильтра",
                            "ACTIVE" => "Y",
                            "SORT" => "100",
                            "CODE" => "FILTER_PARAM",
                            "PROPERTY_TYPE" => "S",
                            "WITH_DESCRIPTION" => 'Y',
                            "MULTIPLE" => 'Y',
                            "IBLOCK_ID" => $ID,
                        );

                        $ibp = new \CIBlockProperty;
                        $PropID = $ibp->Add($arFields);
                    } else {
                        \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE['LID'], $IBLOCK['ID'] ?? "");
                    }
                }
            }
        }
    }

    public function UnInstallIblock()
    {
        CModule::IncludeModule('main');
        global $DB;
        $MODULE_ID = str_replace(".", "_", $this->MODULE_ID);
        $SITES = array();
        $rsSites = \CSite::GetList($by = "sort", $order = "desc");
        while ($arSite = $rsSites->Fetch()) {
            $SITES[] = $arSite;
        }
        if (\CModule::IncludeModule('sale')) {
            foreach ($SITES as $SITE) {
                $OPTION_DATA_DELETE_STATUS = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS_' . $SITE['LID']);
                if ($OPTION_DATA_DELETE_STATUS === 'Y') {
                    $IBLOCK_ID = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE['LID']);
                    $DB->StartTransaction();
                    if (!\CIBlock::Delete($IBLOCK_ID)) {
                        $DB->Rollback();
                    } else {
                        $DB->Commit();
                    }
                }
            }
            $IBLOCK = \CIBlock::GetList(array(), array("TYPE" => $MODULE_ID))->Fetch();
            if (!$IBLOCK) {
                $DB->StartTransaction();
                if (!\CIBlockType::Delete($MODULE_ID)) {
                    $DB->Rollback();
                }
                $DB->Commit();
            }
        }
    }

    public function InstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("sale", "OnOrderAdd", $this->MODULE_ID, '\dmbgeo\orderSplit\EventHandlers\SplitOrderAdd', "handler");

    }

    public function UnInstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("sale", "OnOrderAdd", $this->MODULE_ID, '\dmbgeo\orderSplit\EventHandlers\SplitOrderAdd', "handler");

    }

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function InstallFiles($arParams = array())
    {
        $path = $this->GetPath() . "/install/components";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        }

        $path = $this->GetPath() . "/install/tools";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools", true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            CopyDirFiles($this->GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles)) {
                        continue;
                    }

                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/install/files')) {
            $this->copyArbitraryFiles();
        }

        return true;
    }

    public function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/' . $this->COMPANY_ID . '/catalog.prefilters/');

        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/tools/' . $this->MODULE_ID . '/');

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->GetPath() . '/install/admin/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles)) {
                        continue;
                    }

                    \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/install/files')) {
            $this->deleteArbitraryFiles();
        }

        return true;
    }

    public function copyArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            $destPath = $rootPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            ($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
        }
    }

    public function deleteArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            if (!$object->isDir()) {
                $file = str_replace($localPath, $rootPath, $object->getPathName());
                \Bitrix\Main\IO\File::deleteFile($file);
            }
        }
    }

    public function UnInstallOptions()
    {
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
    }

    public function DoInstall()
    {

        global $APPLICATION;
        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallIblock();
            // $this->InstallEvents();
            $this->InstallFiles();
            // $this->InstallDB();

        } else {
            $APPLICATION->ThrowException(Loc::getMessage("DMBGEO_PREFILTERS_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DMBGEO_PREFILTERS_INSTALL"), $this->GetPath() . "/install/step.php");
    }

    public function DoUninstall()
    {

        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $this->UnInstallIblock();
        // $this->UnInstallDB();
        $this->UnInstallFiles();
        // $this->UnInstallEvents();
        $this->UnInstallOptions();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DMBGEO_PREFILTERS_UNINSTALL"), $this->GetPath() . "/install/unstep.php");
    }
}
