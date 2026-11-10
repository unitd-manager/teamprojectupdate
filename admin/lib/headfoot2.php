<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$this->SetFont('Courier','',10);

        /* Watermark code start */
        //$ImageW = 130; //WaterMark Size
        //$ImageH = 150;
		//
        //$myPageWidth = $this->getPageWidth();
        //$myPageHeight = $this->getPageHeight();
        //$myX = ( $myPageWidth / 2 ) - 60;  //210 WaterMark Positioning
        //$myY = ( $myPageHeight / 2 ) - 95; //297
		//
        //$this->SetAlpha(0.60); //opacity of bg image
		//
        //$bg_image = $cpCfg['cp.localPath']."images/logo_bg.jpg";
        ////$bg_image = $pdf->Image('images/logo_bg.jpg');
        ////Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
        //$this->Image($bg_image, $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
        //$this->SetAlpha(1);
        ///* Watermark code end */

		$images = '<img src="images/logo.jpg" height="45.7px"/>';

		$this->SetFont('Courier','B',5);
		$header='
		<table border="0" width="100%">
			<tr>
				<td width="18%" align="left">
					<table border="0" width="100%">
						<tr>
							<td>'. $images .'</td>
						</tr>
						<tr>
							<td width="100%" style="border-bottom:2px solid black"></td>
						</tr>
					</table>
				</td>
				<td width="82%">
					<table border="0" width="100%">
						<tr>
							<td width="100%">
								<font style="font-size:18px; font-weight:bold;">'.$cpCfg['cp.companyName'].'</font>
							</td>
						</tr>
						<tr>
							<td width="100%"><font style="font-size:10px;">'.$cpCfg['cp.companyAddress1'].'
								'.$cpCfg['cp.companyAddress2'].'
								'.$cpCfg['cp.companyAddress3'].'<br/>
								'.$cpCfg['cp.companyPhone'].'
								'.$cpCfg['cp.companyFax'].'
								'.$cpCfg['cp.companyEmail'].'</font>
							</td>
						</tr>
						<tr>
							<td width="100%" style="border-bottom:2px solid black"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(26);
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