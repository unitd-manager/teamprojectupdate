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
				<td width="28%" style="height: 35px;"><img src="images/logo.jpg" width="175"/></td>
				<td width="42%" style="font-size:18px;color:#14213d;font-weight:bold;line-height:24px;">'.strtoupper($cpCfg['cp.companyName']).'<br/><font style="font-size:10px;color:#14213d;font-weight:bold;">'.$company_address.'</font>
				</td>
				<td width="30%" style="height: 35px;" align="right"><img src="images/logo-right.jpg" /></td>
			</tr>
		</table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(40);
	}

	public function Footer() {
		$this->SetFont('Courier','',9);
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');

      	/*$footer='
      	<table border="0" width="100%">
	        <tr>
	            <td align="center">'.$cpCfg['pdfFooterAddress1'].' '.$cpCfg['pdfFooterAddress2'].' '.$cpCfg['pdfFooterAddress3'].' '.$cpCfg['pdfFooterAddress4'].'.
	            </td>
	        </tr>
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
		</table>';*/

		$footer='
      	<table border="0" width="100%">
			<tr>
				<td width="100%" align="center">(This is computer generated document, and does not require a signature)</td>
				<!--<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>-->
			</tr>
		</table>';
		$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>