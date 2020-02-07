<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

$module_id = 'dmbgeo.prefilters';
$module_path = str_ireplace($_SERVER["DOCUMENT_ROOT"], '', __DIR__) . $module_id . '/';
$ajax_path = '/bitrix/tools/' . $module_id . '/' . 'ajax.php';
CModule::IncludeModule('main');
CModule::IncludeModule($module_id);
CModule::IncludeModule('iblock');

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);
if ($APPLICATION->GetGroupRight($module_id) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
//получим инфоблоки пользователей на сайте, чтоб добавить в настройки

$SITES = \Prefilters::getSites();

$arSections = [];
foreach ($SITES as $SITE) {
    $aTabs[] = array(
        'DIV' => $SITE['LID'],
        'TAB' => $SITE['NAME'],
        'OPTIONS' => array(
            array('DMBGEO_PREFILTERS_OPTION_MODULE_STATUS_' . $SITE['LID'], Loc::getMessage('DMBGEO_PREFILTERS_OPTION_MODULE_STATUS'), '', array('checkbox', "Y")),
            array('DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS_' . $SITE['LID'], Loc::getMessage('DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS'), '', array('checkbox', "Y")),
        ),
    );
    $params[] = 'DMBGEO_PREFILTERS_OPTION_MODULE_STATUS_' . $SITE['LID'];
    $params[] = 'DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS_' . $SITE['LID'];
    $params[] = 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE['LID'];

}

if ($request->isPost() && $request['Apply'] && check_bitrix_sessid()) {

    foreach ($params as $param) {
        if (array_key_exists($param, $_POST) === true) {
            Option::set($module_id, $param, is_array($_POST[$param]) ? implode(",", $_POST[$param]) : $_POST[$param]);
        } else {
            Option::set($module_id, $param, "N");
        }
    }

}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<?$tabControl->Begin();?>

<form method='post' action='<?echo $APPLICATION->GetCurPage() ?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='DMBGEO_PREFILTERS_settings'>

<?$n = count($aTabs);?>
<?foreach ($aTabs as $key => $aTab):
    if($aTab['OPTIONS']): ?>
		<?$tabControl->BeginNextTab();?>
        <? $DMBGEO_PREFILTERS_OPTION_IBLOCK_ID = \COption::GetOptionString($module_id, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $aTab['DIV']); ?>
		<?__AdmSettingsDrawList($module_id, $aTab['OPTIONS']);?>
        <tr>
			<td><?echo Loc::getMessage('DMBGEO_PREFILTERS_OPTION_IBLOCK_ID') ?></td>
			<td><?echo GetIBlockDropDownListEx($DMBGEO_PREFILTERS_OPTION_IBLOCK_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_TYPE_ID_' . $aTab['DIV'], 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $aTab['DIV'], false, "DMBGEO_PREFILTERS_OPTION_IBLOCK_TYPE_ID('DMBGEO_PREFILTERS_OPTION_SECTION_LIST_" . $aTab['DIV'] . "')", "DMBGEO_PREFILTERS_OPTION_IBLOCK_ID(this,'$ajax_path','DMBGEO_PREFILTERS_OPTION_SECTION_LIST_" . $aTab['DIV'] . "')"); ?></td>
		</tr>
	<?endif?>
<?endforeach;?>
	<?

$tabControl->Buttons();?>

	<input type="submit" name="Apply" value="<?echo GetMessage('MAIN_SAVE') ?>">
	<input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET') ?>">
	<?=bitrix_sessid_post();?>
</form>
<?$tabControl->End();?>
