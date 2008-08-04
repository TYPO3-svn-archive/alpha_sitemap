<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Steffen Kamper <info@sk-typo3.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Alphabetic Sitemap' for the 'alpha_sitemap' extension.
 *
 * @author	Steffen Kamper <info@sk-typo3.de>
 * @package	TYPO3
 * @subpackage	tx_alphasitemap
 */
class tx_alphasitemap_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_alphasitemap_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_alphasitemap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'alpha_sitemap';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website (Menu)
	 */
	function main($content, $conf)	{
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
			// Get the PID from which to make the menu.
			// If a page is set as reference in the 'Startingpoint' field, use that
			// Otherwise use the page's id-number from TSFE
		$menuPid = intval($this->cObj->data['pages'] ? $this->cObj->data['pages'] : $GLOBALS['TSFE']->id);
	
			// Additional settings with TS
		if (intval($conf['includeNotInMenu']) == 0) {
			$addWhere = 'AND nav_hide=0';
		}	
		if ($conf['addWhere']) {
			$addWhere .= ' ' . trim(htmlspecialchars($conf['addWhere']));
		}
			
			// Now, get an array with all the subpages to this pid:
			// (Function getMenu() is found in class.t3lib_page.php)
		$menuItems_level1 = $GLOBALS['TSFE']->sys_page->getMenu($menuPid, $fields='*', $sortField='sorting', $addWhere, $checkShortcuts=1);
	
			// Prepare vars:
		$items = array();
		$aChar = array();
		
		
		foreach ($menuItems_level1 as $uid => $rec) {
			if (t3lib_div::compat_version('4.2')) {
				$char = t3lib_div::strtoupper(substr($rec['title'],0,1));
			} else {
				$char = $this->strtoupper(substr($rec['title'],0,1));
			}
			if (ord($char)<65) {
				$char = '0';
			}
			$aChar[$char] = 1;
			$title = $rec['nav_title'] ? $rec['nav_title'] : $rec['title'];
			$items[$char][$title] = $this->cObj->stdWrap($this->pi_linkToPage(
				$title,  
				$rec['uid'],
				$rec['target']
			), $conf['pages.']);
				
		}
		ksort($items);
	   
		
		// alphabetic chars
		//all
		$charlinks = $this->cObj->stdWrap($this->pi_linkTP($this->pi_getLL('all'), array(), 1), $conf['chars.']);
		//0-9
		$charlinks .= $this->cObj->stdWrap($aChar['0'] ? $this->pi_linkTP('0-9', array($this->prefixId.'[char]' => 0), 1) : '0-9', $conf['chars.']);
		//chars
		for ($i = 65; $i < 91; $i++) {
			$charlinks .= $this->cObj->stdWrap($aChar[chr($i)] ? $this->pi_linkTP(chr($i), array($this->prefixId.'[char]' => chr($i)), 1) : chr($i), $conf['chars.']);
		}
		
		
		$totalMenu = $this->cObj->stdWrap($charlinks, $conf['charMenu.']);
		
		if ($this->piVars['char']) {
			$totalMenu .= $this->cObj->stdWrap($this->piVars['char'], $conf['titleChar.']);   
			$p = $items[$this->piVars['char']];
			ksort($p, SORT_LOCALE_STRING);
			$totalMenu .= $this->cObj->stdWrap(implode('', $p), $conf['pageMenu.']);   
		} else {
			foreach($items as $ch => $val) {
				if ($val) {
					$totalMenu .= $this->cObj->stdWrap($ch, $conf['titleChar.']); 
					ksort($val, SORT_LOCALE_STRING);
					$totalMenu .= $this->cObj->stdWrap(implode('', $val), $conf['pageMenu.']);               
				}   
			}
		}
	
		return $this->pi_wrapInBaseClass($totalMenu);
	}
	
	private function strtoupper($str) {
		return strtr((string)$str, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/alpha_sitemap/pi1/class.tx_alphasitemap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/alpha_sitemap/pi1/class.tx_alphasitemap_pi1.php']);
}

?>
