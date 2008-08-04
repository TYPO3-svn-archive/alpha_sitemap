<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPlugin(array('LLL:EXT:alpha_sitemap/locallang_db.xml:tt_content.menu_type_pi1', $_EXTKEY.'_pi1'),'menu_type');
?>