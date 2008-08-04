<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_alphasitemap_pi1.php','_pi1','menu_type',1);
?>