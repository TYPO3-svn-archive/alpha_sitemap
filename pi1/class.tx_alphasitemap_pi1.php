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
	var $addWhere;
    var $menu;
    	
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
		$this->conf = $conf;
		
			// Get the PID from which to make the menu.
			// If a page is set as reference in the 'Startingpoint' field, use that
			// Otherwise use the page's id-number from TSFE
		$menuPid = intval($this->cObj->data['pages']) ? $this->cObj->data['pages'] : $GLOBALS['TSFE']->id;
	    $this->maxLevels = intval($this->cObj->data['recursive']) ? $this->cObj->data['recursive'] : 1;
	    $this->menu = array();
	    
			// Additional settings with TS
		if (intval($this->conf['includeNotInMenu']) == 0) {
			$this->addWhere = 'AND nav_hide=0';
		}	
		if ($this->conf['addWhere']) {
			$this->addWhere .= ' ' . trim(htmlspecialchars($this->conf['addWhere']));
		}

			// Now, get an array with all the subpages to this pid:
		
		$this->recursiveMenu($menuPid);	
		
			// Prepare vars:
		$items = array();
		$aChar = array();
		
		
		foreach ($this->menu as $uid => $page) { 
			if (t3lib_div::compat_version('4.2')) {
				$char = t3lib_div::strtoupper(substr($page['title'],0,1));
			} else {
				$char = $this->strtoupper(substr($page['title'],0,1));
			}
			if (ord($char)<65) {
				$char = '0';
			}
			$aChar[$char] = 1;
			$title = $page['nav_title'] ? $page['nav_title'] : $page['title'];
			if ($this->conf['titleField']) {
				$title = $page[$this->conf['titleField']];
			}
			// complete record to data to enable access to other fields in TS
			$this->cObj->data = $page;
			$items[$char][$title] = $this->cObj->stdWrap($this->pi_linkToPage(
				$title,  
				$page['uid'],
				$page['target']
			), $this->conf['pages.']);
			
		}
		ksort($items);
	   
		
		// alphabetic chars
		//all
		$charlinks = $this->cObj->stdWrap($this->pi_linkTP($this->pi_getLL('all'), array(), 1), $this->conf['chars.']);
		//0-9
		$charlinks .= $this->cObj->stdWrap($aChar['0'] ? $this->pi_linkTP('0-9', array($this->prefixId.'[char]' => 0), 1) : '0-9', $this->conf['chars.']);
		//chars
		for ($i = 65; $i < 91; $i++) {
			if ($this->piVars['char'] == chr($i)) {
				if ($this->conf['activeChar.']['doNotLink']) { 
					$charlinks .= $this->cObj->stdWrap(chr($i), $this->conf['activeChar.']);				 	
				} else {
					$ATagParams = $GLOBALS['TSFE']->ATagParams;
					$GLOBALS['TSFE']->ATagParams = $this->conf['activeChar.']['ATagParams'];					
					$charlinks .= $this->cObj->stdWrap($this->pi_linkTP(chr($i), array($this->prefixId.'[char]' => chr($i)), 1), $this->conf['activeChar.']);		
					$GLOBALS['TSFE']->ATagParams = $ATagParams;
				}
			} else {
			    $charlinks .= $this->cObj->stdWrap($aChar[chr($i)] ? $this->pi_linkTP(chr($i), array($this->prefixId.'[char]' => chr($i)), 1) : chr($i), $this->conf['chars.']);
			} 
		}
		
		
		$totalMenu = $this->cObj->stdWrap($charlinks, $this->conf['charMenu.']);
		
		if ($this->piVars['char']) {
			$totalMenu .= $this->cObj->stdWrap($this->piVars['char'], $this->conf['titleChar.']);   
			$p = $items[$this->piVars['char']];
			ksort($p, SORT_LOCALE_STRING);
			$totalMenu .= $this->cObj->stdWrap(implode('', $p), $this->conf['pageMenu.']);   
		} else {
			foreach($items as $ch => $val) {
				if ($val) {
					$totalMenu .= $this->cObj->stdWrap($ch, $this->conf['titleChar.']); 
					ksort($val, SORT_LOCALE_STRING);
					$totalMenu .= $this->cObj->stdWrap(implode('', $val), $this->conf['pageMenu.']);               
				}   
			}
		}
	
		return $this->pi_wrapInBaseClass($totalMenu);
	}
	
	/**
    *
    * @param string $str
    * @return string
    */
    private function strtoupper($str) {
		return strtr((string)$str, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}
	
	/**
	* recursive functions to catch all pages for menu
	* 
	* @param int $pid	starting pid for the menu  
	* @param int $level	don't use it with the call of the function, it is used by the function for recursive calls
	* @return
	*/
	function recursiveMenu($pid, $level = 1) {
		// (Function getMenu() is found in class.t3lib_page.php)        
		$menu = $GLOBALS['TSFE']->sys_page->getMenu($pid, $fields='*', $sortField='sorting', $this->addWhere, $checkShortcuts=1);
		$this->menu = array_merge($menu, $this->menu);
		foreach($menu as $uid => $page) {
		    if ($level < $this->maxLevels) {
				$this->recursiveMenu($uid, $level + 1);
		    }
		}
    }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/alpha_sitemap/pi1/class.tx_alphasitemap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/alpha_sitemap/pi1/class.tx_alphasitemap_pi1.php']);
}

?>
