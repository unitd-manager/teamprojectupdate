<?php

include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	/*//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$this->SetFont('helvetica', 'B', 8);
		$sample='<table border="0" width="100%">';
		$sample= $sample.'
		<tr>
		<td width="60%"><p style="line-height:160%;"><img src="./images/logo-print.gif" width="130" height="35" alt=""><br/>
		'.$cpCfg['cp.companyName'].'<br/>
		TIN: 33781027038<br/>
		CST: 1211147, Dated13.05.2014</p>
		</td>
		<td width="40%" align="right"><p style="line-height:160%;">LKS Plaza AC6, 2nd Avenue Road,<br/>
		Anna Nagar, Chennai-600040.<br/>
		Tel: +91 44 4359 0303<br/>
		'.$cpCfg['printEmailAddress'].'<br/>
		WEB: www.blossomsin.com</p>
		</td>
		</tr>
		';
		$sample=$sample.'</table>';
		$this->writeHTML($sample, true, false, false, false, '');
	}
	
	public function Footer() {
      $this->SetFont('helvetica', '', 8);
      // Page number
      //$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');
      $footer='<table border="0" width="100%">';
			$footer= $footer.'
			<tr>
			<td width="80%">(This is computer generated document, and does not require a signature)</td>
			<td width="20%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>';
			$footer=$footer.'</table>';
			$this->writeHTML($footer, true, false, false, false, '');
    }*/
}
?>
