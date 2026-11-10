<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$this->SetFont('Courier','',10);

		if (count($this->pages) == 1 ) {
			$images = '<img src="images/logo.jpg" width="180px" height="70px"/>';

			$header='
			<table border="0" width="100%">
				<tr>
					<td width="30%">'.$images.'</td>
					<td width="70%"><br/><br/><font style="font-size:22px; font-weight:bold; color:#1242AB;">'.$cpCfg['cp.companyName'].'</font><br/>
						Email: '.$cpCfg['cp.adminEmail'].'
					</td>
				</tr>
				<tr>
					<td width="100%" style="border-bottom:2px solid black"></td>
				</tr>
				</table>
			';

			$this->writeHTML($header, true, false, false, false, '');
			$this->SetTopMargin(40);
		} else {
			$this->SetTopMargin(6);
		}
	}

	public function Footer() {
		$this->SetFont('Courier','',9);
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');

      	$footer='
      	<table border="0" width="100%">
	        <tr>
	            <td align="center">'.$cpCfg['pdfFooterAddress1'].' '.$cpCfg['pdfFooterAddress2'].' '.$cpCfg['pdfFooterAddress3'].' '.$cpCfg['pdfFooterAddress4'].'.
	            </td>
	        </tr>
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
		</table>';
		$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>