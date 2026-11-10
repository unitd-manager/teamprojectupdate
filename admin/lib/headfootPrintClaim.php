<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');

        $company_address = $cpCfg['cp.gstNumber'].'<br/> Tel : '.$cpCfg['cp.companyPhone'] . ' &nbsp;&nbsp; Fax : ' . $cpCfg['cp.companyFax'] . ' <br/>'. $cpCfg['cp.companyEmail'] . '&nbsp;&nbsp;' . $cpCfg['cp.companyWebsite'] . '<br/>' . $cpCfg['cp.companyAddress1'] . ' ' . $cpCfg['cp.companyAddress2'] . ' ' . $cpCfg['cp.companyAddress3'];

		$header='
		<table border="0" width="100%" style="border-bottom: 1px solid #0e502a;">
			<tr>
				<td width="21%" style="height: 35px;"><img src="images/logo.jpg" width="180"/></td>
				<td width="49%" style="font-size:18px;color:#14213d;font-weight:bold;line-height:24px;">'.strtoupper($cpCfg['cp.companyName']).'<br/><font style="font-size:10px;color:#14213d;font-weight:bold;">'.$company_address.'</font>
				</td>
				<td width="30%" style="height: 35px;" align="right"><img src="images/logo-right.jpg" width="230" /></td>
			</tr>
		</table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(29);
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
      	
		$this->SetY(-20);
      	$footer='
      	<table border="0" width="100%">
			<tr>
				<td width="45%" style="font-size:10px; height: 35px;">
				</td>
				<td width="55%" align="center" style="color:#2F71AD;text-align:left">
				</td>
			</tr>
		</table>
		';
		$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>