<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');

		$header='
		<table border="0" width="100%">
			<tr>
				<td width="80%"><img src="images/logo_text.jpg" width="250px"/><br/><font style="font-size:11px; font-weight:bold; color: #000; line-height:25px;">'.$cpCfg['cp.companyUEN'].'</font></td>
				<td width="20%" align="right"><img src="images/logo.jpg" width="80px"/></td>
			</tr>
		</table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(25);
	}

	public function Footer() {
        $cpCfg = Zend_Registry::get('cpCfg');

		/* Showing footer images in right bottom */
		$image_file = $cpCfg['cp.localPath']."images/footer_logos.jpg";
        $this->Image($image_file, 135, 272, 60, 24, 'JPG', '', 'T', false, 90, '', false, false, 0, false, false, false);
     	
		$this->SetY(-27);
      	$footer='
      	<table border="0" width="100%" style="border-top: 1px solid #0094DC;">
			<tr>
				<td width="45%" style="font-size:11px; height: 35px;letter-spacing:1.2px;"><br/><br/>
					' . $cpCfg['cp.footerManpowerInvoicecompanyAddress1'].'<br/>
					' . $cpCfg['cp.footerManpowerInvoicecompanyAddress2'].'<br/>
					' . $cpCfg['cp.companyEmail'].'<br/>
					' . $cpCfg['cp.footerManpowerInvoiceFooter4'].'<br/>
					' . $cpCfg['cp.footerManpowerInvoiceFooter5'].'<br/>
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