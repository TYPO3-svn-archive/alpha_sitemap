<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPlugin(array('LLL:EXT:alpha_sitemap/locallang_db.xml:tt_content.menu_type_pi1', $_EXTKEY.'_pi1'),'menu_type');
t3lib_div::loadTCA('tt_content'); 
$TCA['tt_content']['types']['menu']['showitem'] = 'CType;;4;button,hidden,1-1-1, header;;3;;2-2-2,  linkToTop;;;;3-3-3,
							--div--;LLL:EXT:cms/locallang_ttc.xml:CType.I.12, menu_type;;;;4-4-4, pages;;12,
							--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,starttime, endtime';
?>