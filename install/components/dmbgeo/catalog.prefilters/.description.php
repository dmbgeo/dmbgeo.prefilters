<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("DMBGEO_CATALOG_LIST_NAME"),
	"DESCRIPTION" => GetMessage("DMBGEO_CATALOG_LIST_DESCRIPTION"),	
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "dmbgeo",
		"NAME" => GetMessage("DMBGEO_COMPONENTS_GROUP_NAME"),
		"CHILD" => array(
			"ID" => "DMBGEO_CATALOG",
			"NAME" => GetMessage("DMBGEO_CATALOG_GROUP_NAME"),
		)
	),	
);
?>