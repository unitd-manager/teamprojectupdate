<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');

	    $andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);

		$header='';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(26);
	}

	public function Footer() {
        $cpCfg = Zend_Registry::get('cpCfg');

	    $andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);
		$current_page = $this->getAliasNumPage();
		$total_page = $this->getAliasNbPages();
      	
		$this->SetY(-20);
      	$footer='';
		$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>