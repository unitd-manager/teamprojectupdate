<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$fn    = Zend_Registry::get('fn');

		$quote_id = $fn->getReqParam('quote_id');
		$quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        
        $company_address = $cpCfg['cp.gstNumber'].'<br/> Tel : '.$cpCfg['cp.companyPhone'] . ' &nbsp;&nbsp; Fax : ' . $cpCfg['cp.companyFax'] . ' <br/>'. $cpCfg['cp.companyEmail'] . '&nbsp;&nbsp;' . $cpCfg['cp.companyWebsite'] . '<br/>' . $cpCfg['cp.companyAddress1'] . ' ' . $cpCfg['cp.companyAddress2'] . ' ' . $cpCfg['cp.companyAddress3'];

		$header='
		<table border="0" width="100%" style="border-bottom: 1px solid #0e502a;">
			<tr>
				<td width="28%" style="height: 35px;"><img src="images/logo.jpg" width="175"/></td>
				<td width="42%" style="font-size:18px;color:#078205;font-weight:bold;line-height:24px;">'.strtoupper($cpCfg['cp.companyName']).'<br/><font style="font-size:10px;color:#078205;font-weight:bold;">'.$company_address.'</font>
				</td>
				<td width="30%" style="height: 35px;" align="right"><img src="images/logo-right.jpg" /></td>
			</tr>
		</table>
		';

		$quote_date = $fn->getCPDate($quoteRec['quote_date'], 'd/M/Y');

		$tbl1 = '
        <table border="0" width="100%" style="border-bottom: 1px solid #0e502a;" cellpadding="0">
            <tr>
                <td></td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px; line-height:26px;" width="70%">Date : '.$quote_date.'</td>
                <td style="font-size:10px; line-height:26px;" width="30%">Our Ref : '.$quoteRec['our_reference'].'</td>
            </tr>
        </table>
        ';

		$this->writeHTML($header, true, false, false, false, '');
		//$this->ln(2);
		//$this->writeHTML($tbl1, true, false, false, false, '');
		//$this->ln(-2);
		$this->writeHTML($tbl2, true, false, false, false, '');

		$this->SetTopMargin(45);
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