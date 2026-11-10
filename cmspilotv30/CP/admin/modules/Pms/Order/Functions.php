<?
class CP_Admin_Modules_Pms_Order_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pms_order');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'printOrder')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
        ));
    }

    /**
     *
     */
    function setActionsArray($actArrayObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        
        //=============== Print Order =================//
        $actObj = $actArrayObj->getActionObj('printOrder');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "index.php?module=pms_order&_spAction=printOrder"
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('pms_order', 'ecommerce_orderItemLink');
        $productArr = $fn->getDdDataAsArray($cpCfg['m.ecommerce.order.itemsMainModule']);
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'order_item'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'order_item_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
            ,'fieldlabel'             => array('Product', 'Unit Price', 'Qty')
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $productArr)
            )
            ,'additionalFieldsArray' => array(
                 'b.item_title'
                ,'b.unit_price'
                ,'b.qty'
            )
        ));

        //------------------------------------------------------------------------------//
        /*$linkObj = $inst->getLinksArrayObj('pms_order', 'pms_paymentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'payment'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'      => array('Date'
                                      ,'Amount'
                                 )
        ));*/

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_order', 'pms_insuranceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'student_insurance'
           ,'historyTableKeyField'  => 'student_insurance_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'showAnchorInLinkPortal'=> 0
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 600
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array('Course Name'
                                            ,'Insurance Company'
                                            ,'Certificate of Insurance'
                                            ,'Protected Amount'
                                            ,'Start Date'
                                            ,'End Date'
                                       )
        ));  
		
    }

    /**
     *
     */
    function getPrintOrderIndividual($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $SERVER = $_SERVER['HTTP_HOST'];

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        //$order_id  = $fn->getReqParam('order_id', '', true);

        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,gc2.name AS shipping_country_name
              ,c.title AS company_name
              ,CONCAT_WS(' ', co.first_name, co.last_name) AS contact_name
              ,co.phone
              ,co.email
              ,co.address_flat
              ,co.address_street
              ,co.address_country
              ,co.address_state
              ,co.address_po_code
              ,oi.qty
              ,oi.item_title
              ,oi.unit_price
              ,oi.module
              ,oi.record_id
              ,oi.contact_id
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN geo_country gc1 ON (co.address_country = gc1.country_code)
        LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        WHERE o.order_id = '{$order_id}'
        ";
        $result = $db->sql_query($SQL);
        
        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
        $count = 0;
        $total = 0; 
        $start_date = '';
        $end_date   = '';       
        $expbatch   = '';       
        //============================================================================= //
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your invoice and print the PDF");
			$pdf->Output();
			return;
		}
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                // Framed                 
                $pdf->Image('images/logo-print.jpg',10,5,45);
                $pdf->SetX(100);
                $pdf->SetFont('Arial','B',14);
                $pdf->Cell(40, 20, "Invoice");

                $date = $fn->getCPDate($today, 'd M Y');
                $code = 'Invoice # : '. $row['order_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(165, 5);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "Date : " . $date);

                $pdf->SetXY(10, 25);
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
                $pdf->Cell(50, 20, 'Attn : ' . $row['contact_name']);
                $pdf->SetXY(127, 35);
                $pdf->Cell(50, 20, $row['address_flat']);
                $pdf->SetXY(127, 40);
                $pdf->Cell(50, 20, $row['address_street']);
                $pdf->SetXY(127, 45);
                $pdf->Cell(60, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);
                /*
                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Rect(10 , 70, 80, 35, 'FD');
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->Ln(7);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, "Attn : " . $row['contact_name']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address_flat']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address_street']);
                $pdf->Ln(5);
                $pdf->Cell(20, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->Ln(25);
                */
                $expBatch = array('condn' => " 
                AND course_id  = {$row['record_id']}
                AND contact_id = {$row['contact_id']}
                ");
                $courceContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expbatch);
                
                if($courceContactRec['batch_id']){
                    $batchRec = $fn->getRecordRowByID('batch', 'batch_id',
                    $courceContactRec['batch_id']);
                    $start_date = $batchRec['start_date'];
                    $end_date   = $batchRec['end_date'];
                    $start_date = $fn->getCPDate($start_date, 'd M Y');
                    $end_date   = $fn->getCPDate($end_date, 'd M Y');
                }
                
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(77, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(28, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(60, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(25, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(77,10,$row['item_title'],1);
                $pdf->Cell(28,10,"Program Code",1);
                $pdf->Cell(60,10,$start_date . ' to ' . $end_date,1);
                $pdf->Cell(25,10,"Immediate",1);
                $pdf->Ln(20);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                //$pdf->Cell(35,8,"Price",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'C', 1);
                $pdf->Ln();
            }
            //to get batch time using the course , contact and order ids

            $pdf->SetFont('Arial','',10);
            
            if($row['module'] == 'pms_course'){
                $courseTxt = 'Programme Fee';
            }
            else if($row['module'] == 'pms_subsidy'){
                $courseTxt = 'Rebate for Tuition Fee';
            }
            else if($row['module'] == 'pms_discount'){
                $courseTxt = 'Discount';
            }
            else{
                $courseTxt = ''; 
            }
            
            $unit_price = $row['unit_price'];
            
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10, $row['qty'],1, 0, 'L', 1);
            $pdf->Cell(135, 10, $courseTxt,1, 0, 'L', 1);
            $pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
            $total += $row['unit_price'];

            $pdf->Ln();
            $count++;
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
        
        //print "dsfds";
    }    
    /**
     *
     */
    function getPrintOrderCompany($order_id, $company_id) {
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

        //$company_id  = $fn->getReqParam('id');
        //$order_id    = $fn->getReqParam('order_id');
        
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
            $pdf->SetXY(30,30);
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
                $company_name = strtoupper ($cpCfg['cp.companyName']);

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
                /*
                $pdf->SetFont('Arial','B',10);
                $pdf->Rect(10 , 70, 80, 35, 'FD');
                $pdf->SetX(10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->Ln(7);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, "Attn : " . $row['company_name']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address1']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address2']);
                $pdf->Ln(5);
                $pdf->Cell(20, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->Ln(25);
                */
                
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
            
            if($row['module'] == 'pms_course'){
                $courseTxt = 'Programme Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'pms_subsidy'){
                $courseTxt = 'Rebate for Tuition Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'pms_discount'){
                $courseTxt = 'Discount';
                $discount_price = $row['unit_price'];
            }
            else{
                $courseTxt = ''; 
            }
            
            $unit_price = $row['unit_price'];
            
            if($row['module'] != 'pms_discount'){
                $pdf->SetFillColor(224,235,255);
                $pdf->Cell(20, 10, $row['qty'],1, 0, 'L', 1);
                $pdf->Cell(135, 10, $courseTxt,1, 0, 'L', 1);
                $pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
                //$pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
            }
            $total += $row['unit_price'];

            if($row['module'] != 'pms_discount'){
                $pdf->Ln();
            }
            $count++;
        } 

        if($discount_price){
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
    function getPrintOrder() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        //$order_id = $fn->getReqParam('order_id');
        $order_id     = $fn->getReqParam('record_id');
        if($order_id == ''){
            //$order_id = 14;
        }

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        
        $companyId = $orderRec['company_id'];
        
        if ($companyId != '') {
            return $this->getPrintOrderCompany($order_id, $companyId);
        } else {
            return $this->getPrintOrderIndividual($order_id);
        }
    }    

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_order', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}