<?
class CP_Admin_Modules_AceIms_Company_Functions extends CP_Common_Modules_AceIms_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_company');
        $modules->registerModule($modObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('aceIms_company', 'aceIms_orderLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'order'
           ,'displayTitleFieldName' => "c.title"
           ,'historyTableKeyField'  => 'order_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 650
           ,'portalDialogHeight'    => 350
           ,'fieldlabel'            => array('Date')
        ));
		
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('aceIms_company', 'aceIms_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => array('first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id'))
           ,'fieldlabel'            => array('Name'
                                            , 'Email'
                                            , 'Reg No'
                                            , 'NRIC / Passport No. / FIN'
                                            , 'Phone'
                                            , 'Mobile'
                                       )
        ));

    }
    
    /**
     *
     */
    function getPrintVoucher() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $company_id = $fn->getReqParam('id');
        $order_id   = $fn->getReqParam('order_id');
        
		$invoice_terms = '';
		$notes  = '';
        $total = '';

        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,c.title AS company_name
              ,c.address1 
              ,c.address2 
              ,c.address_po_code
              ,CONCAT_WS(' ', co.first_name, co.last_name) AS contact_name
              ,oi.qty
              ,oi.item_title
              ,oi.unit_price
              ,oi.module
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        LEFT JOIN contact co ON (co.contact_id = oi.contact_id)
        LEFT JOIN geo_country gc1 ON (c.address_country_code = gc1.country_code)
        WHERE O.company_id = {$company_id}
            AND O.order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                $pdf->Image('images/logo-print.jpg',10,5,45);
                $pdf->SetX(100);
                $pdf->SetFont('Arial','B',14);
                $pdf->Cell(40, 20, "Invoice");

                $date = $fn->getCPDate($today, 'd M Y');
                $code = 'Invoice # : '. $row['order_code'];
                $company_name = strtoupper ($cpCfg['printCompanyNamePvt']);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(165, 5);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "Date : " . $date);

                $pdf->SetXY(10, 25);
                //$pdf->SetFillColor(142,182,212);
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10 , 28, 80, 38, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'EDUQUEST INTERNATIONAL INSTITUTE');
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #07-13');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : +65 6338 7151');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Fax : +65 6338 7151');
                $pdf->Ln(15);
                
                $pdf->SetXY(127, 25);
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(125 , 28, 75, 38, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetXY(127, 30);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'Attn : ' . $row['company_name']);
                $pdf->SetXY(127, 35);
                $pdf->Cell(50, 20, $row['address1']);
                $pdf->SetXY(127, 40);
                $pdf->Cell(50, 20, $row['address2']);
                $pdf->SetXY(127, 45);
                $pdf->Cell(60, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);
                
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(40, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(40, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$row['item_title'],1);
                $pdf->Cell(30,10,"Program Code",1);
                $pdf->Cell(40,10,"Training Date(s)",1);
                $pdf->Cell(40,10,"Immediate",1);
                $pdf->Ln(20);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                //$pdf->Cell(35,8,"Price",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'C', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            
            if($row['module'] == 'aceIms_course'){
                $courseTxt = 'Programme Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'aceIms_subsidy'){
                $courseTxt = 'Rebate for Tuition Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'aceIms_discount'){
                $courseTxt = 'Discount';
                $discount_price = $row['unit_price'];
            }
            else{
                $courseTxt = ''; 
            }
            
            $unit_price = $row['unit_price'];
            
            if($row['module'] != 'aceIms_discount'){
                $pdf->SetFillColor(224,235,255);
                $pdf->Cell(20, 10, $row['qty'],1, 0, 'L', 1);
                $pdf->Cell(135, 10, $courseTxt,1, 0, 'L', 1);
                $pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
                //$pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
            }
            $total += $row['unit_price'];

            if($row['module'] != 'aceIms_discount'){
                $pdf->Ln();
            }
            $count++;
        } 

        if ($discount_price) {
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $pdf->SetFillColor(255,191,161);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);

        $pdf->Ln(40);
        $pdf->Cell(70, 8, 'Cheque should be Crossed and Issued to :');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'EDUQUEST INTERNATIONAL INSTITUTE');
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        $pdf->Output();
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_company', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_company', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}