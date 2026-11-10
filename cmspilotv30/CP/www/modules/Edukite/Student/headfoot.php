<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$this->SetFont('Arial','B',10);

        $hostName   = $_SERVER['HTTP_HOST'];
        $fromEmailId = '';
        $schoolTitle = '';

        if(strpos($hostName, 'masad') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
            $schoolTitle = "Masada College";
        }
        else if(strpos($hostName, 'queenwood') !== false){
            $fromEmailId = "autonotification3@kitesonacloud.com";
            $schoolTitle = "Queenwood Edukite";
        }
        else if(strpos($hostName, 'waves') !== false){
            $fromEmailId = "autonotification4@kitesonacloud.com";
            $schoolTitle = "Waves Childcare Centre";
        }
        else if(strpos($hostName, 'weecare') !== false){
            $fromEmailId = "autonotification5@kitesonacloud.com";
            $schoolTitle = "Wee Care Early Childhood Education";
        }
        else if(strpos($hostName, 'rosebay') !== false){
            $fromEmailId = "autonotification6@kitesonacloud.com";
            $schoolTitle = "Ballykin Early Learning Centres";
        }
        else if(strpos($hostName, 'essington') !== false){
            $fromEmailId = "autonotification7@kitesonacloud.com";
            $schoolTitle = "Essington";
        }
        else if(strpos($hostName, 'kincoppal') !== false){
            $fromEmailId = "autonotification8@kitesonacloud.com";
            $schoolTitle = "Kincoppal - Rosebay school of the sacred Heart joigny centre & sophie's cottage";
            $schoolTitle = "Kincoppal - Rosebay school";
        }
        else if(strpos($hostName, 'marli') !== false){
            $fromEmailId = "autonotification9@kitesonacloud.com";
            $schoolTitle = "Marli Marli";
        }
        else if(strpos($hostName, 'sand') !== false){
            $fromEmailId = "autonotification10@kitesonacloud.com";
            $schoolTitle = "Sand & Stories";
        }
        else if(strpos($hostName, 'step') !== false){
            $fromEmailId = "autonotification1@kitesonacloud.com";
            $schoolTitle = "";
        }
        else if(strpos($hostName, 'stpauls') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
            $schoolTitle = "Little Saints";
        }
        else if(strpos($hostName, 'scbc') !== false){
            $fromEmailId = "autonotification3@kitesonacloud.com";
            $schoolTitle = "South Coast Baptist College School of Early Learning Childcare";
        }
        else if(strpos($hostName, 'mary') !== false){
            $fromEmailId = "autonotification4@kitesonacloud.com";
            $schoolTitle = "Mary's Mount Primary School";
        }
        else if(strpos($hostName, 'gumnut') !== false){
            $fromEmailId = "autonotification5@kitesonacloud.com";
            $schoolTitle = "Gumnut Gardens";
        }
        else if(strpos($hostName, 'silverseeds') !== false){
            $fromEmailId = "autonotification4@kitesonacloud.com";
            $schoolTitle = "Silver seeds Child care - Paddington - NSW";
        }
        else if(strpos($hostName, 'localhost') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
            $schoolTitle = "";
        }
        else if(strpos($hostName, 'miracles') !== false){
            $fromEmailId = "autonotification6@kitesonacloud.com";
            $schoolTitle = "Miracles On Russell";
        }
        else if(strpos($hostName, 'edukiteweb') !== false){
            $fromEmailId = "autonotification7@kitesonacloud.com";
            $schoolTitle = "";
        }
        else if(strpos($hostName, 'edukitedev') !== false){
            $fromEmailId = "autonotification8@kitesonacloud.com";
            $schoolTitle = "Edukite Development Site";
        }
        else{
            $fromEmailId = "autonotification9@kitesonacloud.com";
        }

		if (count($this->pages) === 1) {

			$header='
			<table border="0" width="100%">
				<tr>
					<td width="80%" align="centre" style="font-size:23px; font-weight:bold; font-family:arial;"><br/><br/>&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IMS OUTCOMES
                    </td>
					<td width="20%" align="right">
                        <img src="/admin/images/logo.jpg" width="100" />
					</td>
				</tr>
                <tr>
                    <td></td>
                    <td align="right" >www.cubosale.com</td>
                </tr><br/>
				<tr>
					<td width="100%" style="font-size:16px; font-weight:bold; border-bottom:2px solid #005186;">
						'.$schoolTitle.'
					</td>
				</tr>
			</table>
			';

			$this->writeHTML($header, true, false, false, false, '');

		}else{
			$this->SetTopMargin(6);
		}
	}

	public function Footer() {
	  $this->SetFont('Courier','B',9);

	  //Page Footer

      $footer='
      <table border="0" width="100%">
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
	  </table>';
	  $this->writeHTML($footer, true, false, false, false, '');
    }
}
?>
