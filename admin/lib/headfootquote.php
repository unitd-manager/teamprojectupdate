<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');

        $company_address = $cpCfg['cp.gstNumber'].'<br/> Tel : '.$cpCfg['cp.companyPhone'] . ' &nbsp;&nbsp; Fax : ' . $cpCfg['cp.companyFax'] . ' <br/>'. $cpCfg['cp.companyEmail'] . '&nbsp;&nbsp;' . $cpCfg['cp.companyWebsite'] . '<br/>' . $cpCfg['cp.companyAddress1'] . ' ' . $cpCfg['cp.companyAddress2'] . ' ' . $cpCfg['cp.companyAddress3'];

		$header='
		<table border="0"  >
			<tr>
				<td ><img src="images/Team.jpg" /></td>
				
			</tr>
		</table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(39);
	}

	public function Footer() {
        $cpCfg = Zend_Registry::get('cpCfg');

		/* Showing footer images in right bottom */
		/*$image_file = $cpCfg['cp.localPath']."images/footer_logos.jpg";
        $this->Image($image_file, 135, 272, 60, 24, 'JPG', '', 'T', false, 90, '', false, false, 0, false, false, false);*/
		//Image ($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())

	    $andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);
		//$this->SetFont($andalus,'',13);
		$current_page = $this->getAliasNumPage();
		$total_page = $this->getAliasNbPages();
      	
		$this->SetY(-12);
      	$footer='
		  <table border="0" width="100%" style="border-top: 3px solid #0094DC;">
		  <tr>
			  <td width="100%" style="font-size:11px; font-weight:bold; height: 35px;letter-spacing:1.2px;"><br/><br/>
				  ' . $cpCfg['cp.quoteFooter'].'<br/>
				<font style="text-align:center;">  ' . $cpCfg['cp.quoteFooter1'].'</font>
				 
			  </td>
			</tr>
		</table>
		';
		$this->writeHTML($footer, true, false, false, false, '');

		
    }
}
?>