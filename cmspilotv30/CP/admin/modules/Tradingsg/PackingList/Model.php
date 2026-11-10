<?
class CP_Admin_Modules_Tradingsg_PackingList_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT pl.*
        FROM packing_list pl
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('status', 'Please choose the Status');
        $validate->validateData('title', 'Please enter the Title');
        $validate->validateData('packing_list_date', 'Please choose Date');
        $validate->validateData('terms', 'Please enter the Terms');
        $validate->validateData('port_of_loading', 'Please enter Port of Loading');
        $validate->validateData('port_of_discharge', 'Please enter Port of Discharge');
        $validate->validateData('final_destination_country', 'Please choose Country of Final Destination');
        $validate->validateData('vessel', 'Please enter the Vessel');
        $validate->validateData('departure_date', 'Please choose Departure Date');
        $validate->validateData('origin_goods_country', 'Please choose Origin Goods Country');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'packing_list_date');
        $fa = $fn->addToFieldsArray($fa, 'port_of_loading');
        $fa = $fn->addToFieldsArray($fa, 'port_of_discharge');
        $fa = $fn->addToFieldsArray($fa, 'final_destination_country');
        $fa = $fn->addToFieldsArray($fa, 'exporters_reference_no');
        $fa = $fn->addToFieldsArray($fa, 'buyers_order_no');
        $fa = $fn->addToFieldsArray($fa, 'terms');
        $fa = $fn->addToFieldsArray($fa, 'show_bank_name');      
        $fa = $fn->addToFieldsArray($fa, 'vessel');      
        $fa = $fn->addToFieldsArray($fa, 'departure_date');
        $fa = $fn->addToFieldsArray($fa, 'origin_goods_country');
        $fa = $fn->addToFieldsArray($fa, 'container_size');
        $fa = $fn->addToFieldsArray($fa, 'no_of_cartons');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'net_wt');
        $fa = $fn->addToFieldsArray($fa, 'gross_wt');
        $fa = $fn->addToFieldsArray($fa, 'cube_m3');

        return $fa;
    }

    
    /**
     *
     */
    function getGeneratePackingListFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        if (!$this->getGeneratePackingListFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $invoiceItemIds       		= $fn->getReqParam('invoiceItemId', array());
        $status     		        = $fn->getPostParam('status');
        $title     		            = $fn->getPostParam('title');
        $packing_list_date     		= $fn->getPostParam('packing_list_date');

        $terms      		        = $fn->getPostParam('terms');
        $port_of_loading    		= $fn->getPostParam('port_of_loading');
        $port_of_discharge    		= $fn->getPostParam('port_of_discharge');
        $final_destination_country  = $fn->getPostParam('final_destination_country');
        $exporters_reference_no    	= $fn->getPostParam('exporters_reference_no');
        $buyers_order_no    		= $fn->getPostParam('buyers_order_no');
        $vessel    					= $fn->getPostParam('vessel');
        $departure_date    			= $fn->getPostParam('departure_date');
        $origin_goods_country    	= $fn->getPostParam('origin_goods_country');
        
        $show_bank_name    	        = $fn->getPostParam('show_bank_name');
        $container_size    	        = $fn->getPostParam('container_size');
        $no_of_cartons    	        = $fn->getPostParam('no_of_cartons');
        $description    	        = $fn->getPostParam('description');
        $net_wt    	                = $fn->getPostParam('net_wt');
        $gross_wt    	            = $fn->getPostParam('gross_wt');
        $cube_m3    	            = $fn->getPostParam('cube_m3');
        $packing_list_in_next_page  = $fn->getPostParam('packing_list_in_next_page');

        $order_id           		= $fn->getReqParam('order_id');
        $company_id           		= $fn->getReqParam('company_id');
        $qty_arr            		= $fn->getReqParam('qty', array());
        $no_of_carton_arr           = $fn->getReqParam('no_of_carton', array());
        $qty_balance        		= $fn->getReqParam('qty_balance');
        
        //To update invoice code
        $SQLUpdate          = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPackingListCode'";
        $resultUpdate       = $db->sql_query($SQLUpdate);

        $packingListCodePfx = $fn->getSettingsValueByKey("packingListCodePrefix");
        $packingListCode    = $fn->getSettingsValueByKey("nextPackingListCode");

        if($packingListCode < 10) {
            $packing_list_code = $packingListCodePfx . '0000' . $packingListCode;
        } else if($packingListCode < 99) {
            $packing_list_code = $packingListCodePfx . '000' . $packingListCode;
        } else if($packingListCode < 999) {
            $packing_list_code = $packingListCodePfx . '00' . $packingListCode;
        } else if($packingListCode < 9999) {
            $packing_list_code = $packingListCodePfx . '0' . $packingListCode;
        } else {
            $packing_list_code = $packingListCodePfx . $packingListCode;
        }
        
        $fa = array();
        $fa['packing_list_code']     	 = $packing_list_code;
        /*
        $fa['invoice_amount']   		 = $invoice_amount;
        $fa['invoice_date']     		 = $invoice_date;
        $fa['invoice_due_date'] 		 = $invoice_due_date;
        */

        $fa['status']    		         = $status;
        $fa['title']    		         = $title;
        $fa['packing_list_date']         = $packing_list_date;
        $fa['terms']    		         = $terms;
        $fa['port_of_loading']  		 = $port_of_loading;
        $fa['port_of_discharge']  		 = $port_of_discharge;
        $fa['final_destination_country'] = $final_destination_country;
        $fa['exporters_reference_no']  	 = $exporters_reference_no;
        $fa['buyers_order_no']  		 = $buyers_order_no;
        $fa['show_bank_name']    		 = $show_bank_name;
        $fa['vessel']  					 = $vessel;
        $fa['departure_date']  			 = $departure_date;
        $fa['origin_goods_country']  	 = $origin_goods_country;

        $fa['container_size']    		 = $container_size;
        $fa['no_of_cartons']    		 = $no_of_cartons;
        $fa['description']    		     = $description;

        $fa['net_wt']    		         = $net_wt;
        $fa['gross_wt']    		         = $gross_wt;
        $fa['cube_m3']    		         = $cube_m3;
        $fa['packing_list_in_next_page'] = $packing_list_in_next_page;

        $fa['order_id']         		 = $order_id;
        $fa['company_id']         		 = $company_id;
        $fa['staff_id']         		 = $_SESSION['staff_id'];
        $fa                              = $fn->addCreationDetailsToFieldsArray($fa, 'packing_list');
        
        $insertPackingListSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'packing_list');
        $resultSQL              = $db->sql_query($insertPackingListSQL);
        $packing_list_id        = $db->sql_nextid();

        $count = count($invoiceItemIds);
        $recCount = 0;
        foreach ($invoiceItemIds as $key=>$value){
            $orderItemRec = $fn->getRecordRowByID('order_item', 'order_item_id', $value);
            $pfx          = $value . '_' ;
            $qty          = $fn->getPostParam("{$pfx}qty");
            $no_of_carton = $fn->getPostParam("{$pfx}no_of_carton");

            $fa = array();
            $fa['packing_list_id']   = $packing_list_id;
            $fa['order_id']          = $order_id;
            $fa['invoice_item_id']   = $value;
            $fa['qty']               = $qty;
            $fa['no_of_cartons']     = $no_of_carton;
            $fa['created_by']        = $fn->getSessionParam('userName');
            
            $invoice_item_id = $fn->addRecord($fa, 'packing_list_history');
            //print_r ($fa);
            $recCount++;
        }

        /*
        $sql ="
        SELECT SUM(it.qty * it.unit_price) As amount
        FROM invoice_item it
        WHERE it.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($sql);  
        $row = $db->sql_fetchrow($result);

        $fa2 = array();
        $fa2['invoice_amount']  = $row['amount'];
        
        $gsttaxperc = $cpCfg['amtForGSTCalc'] ;
        $fa2['invoice_amount'] =  $fa2['invoice_amount'] + ($fa2['invoice_amount'] * $gsttaxperc/100);
        
        $whereCondition = "
        WHERE invoice_id = {$invoice_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'invoice', $whereCondition);
        $db->sql_query($SQLInvoice);
        */

        //$this->getGenerateInvoiceForMedia($invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGeneratePackingListFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $qty          = $fn->getReqParam('qty');
        $qty_balance  = $fn->getReqParam('qty_balance');
        $invoiceItemIds = $fn->getPostParam('invoiceItemId', array());
        
        $validate->resetErrorArray();
        
        if (count($invoiceItemIds) == 0){
            $msg = 'Please check atleast one item for generating Packing List';
            $validate->validateData('error_box', $msg);
		}

        $validate->validateData('status', 'Please choose the Status');
        $validate->validateData('title', 'Please enter the Title');
        $validate->validateData('packing_list_date', 'Please choose Date');
        $validate->validateData('terms', 'Please enter the Terms');
        $validate->validateData('port_of_loading', 'Please enter Port of Loading');
        $validate->validateData('port_of_discharge', 'Please enter Port of Discharge');
        $validate->validateData('final_destination_country', 'Please choose Country of Final Destination');
        $validate->validateData('vessel', 'Please enter the Vessel');
        $validate->validateData('departure_date', 'Please choose Departure Date');
        $validate->validateData('origin_goods_country', 'Please choose Origin Goods Country');
        //$validate->validateData('qty', 'Please enter the qty');
        
        /*if($qty_balance < $qty){
            $validate->errorArray['qty']['name'] = "qty";
            $validate->errorArray['qty']['msg']  = 'Please enter less qty';
        }*/

        if (count($validate->errorArray) == 0) {
            return true;  
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditPackingListFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditPackingListFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $packingListHistIdIds       = $fn->getReqParam('packingListHistId', array());
        $title     		            = $fn->getPostParam('title');
        $status     		        = $fn->getPostParam('status');
        $packing_list_date     		= $fn->getPostParam('packing_list_date');

        $port_of_loading    		= $fn->getPostParam('port_of_loading');
        $port_of_discharge    		= $fn->getPostParam('port_of_discharge');
        $final_destination_country  = $fn->getPostParam('final_destination_country');
        $exporters_reference_no    	= $fn->getPostParam('exporters_reference_no');
        $buyers_order_no    		= $fn->getPostParam('buyers_order_no');
        $terms      		        = $fn->getPostParam('terms');
        $show_bank_name    	        = $fn->getPostParam('show_bank_name');
        $vessel    					= $fn->getPostParam('vessel');
        $departure_date    			= $fn->getPostParam('departure_date');
        $origin_goods_country    	= $fn->getPostParam('origin_goods_country');        
        $container_size    	        = $fn->getPostParam('container_size');
        $no_of_cartons    	        = $fn->getPostParam('no_of_cartons');
        $description    	        = $fn->getPostParam('description');
        $net_wt    	                = $fn->getPostParam('net_wt');
        $gross_wt    	            = $fn->getPostParam('gross_wt');
        $cube_m3    	            = $fn->getPostParam('cube_m3');
        $packing_list_in_next_page  = $fn->getPostParam('packing_list_in_next_page');

        $order_id           		= $fn->getReqParam('order_id');
        $packing_list_id           	= $fn->getReqParam('packing_list_id');
        $qty_arr            		= $fn->getReqParam('qty', array());
        $no_of_carton_arr           = $fn->getPostParam('no_of_carton', array());
        $qty_balance        		= $fn->getReqParam('qty_balance');
        
        $fa = array();
        $fa['title']    		         = $title;
        $fa['status']    		         = $status;
        $fa['packing_list_date']         = $packing_list_date;
        $fa['port_of_loading']  		 = $port_of_loading;
        $fa['port_of_discharge']  		 = $port_of_discharge;
        $fa['final_destination_country'] = $final_destination_country;
        $fa['exporters_reference_no']  	 = $exporters_reference_no;
        $fa['buyers_order_no']  		 = $buyers_order_no;
        $fa['terms']    		         = $terms;
        $fa['show_bank_name']    		 = $show_bank_name;
        $fa['vessel']  					 = $vessel;
        $fa['departure_date']  			 = $departure_date;
        $fa['origin_goods_country']  	 = $origin_goods_country;
        $fa['container_size']    		 = $container_size;
        $fa['no_of_cartons']    		 = $no_of_cartons;
        $fa['description']    		     = $description;
        $fa['net_wt']    		         = $net_wt;
        $fa['gross_wt']    		         = $gross_wt;
        $fa['cube_m3']    		         = $cube_m3;
        $fa['packing_list_in_next_page'] = $packing_list_in_next_page;
        $fa                      		 = $fn->addModificationDetailsToFieldsArray($fa, 'packing_list');
        
        $whereCondition = "WHERE packing_list_id = {$packing_list_id}";
        $sqlUpdate      = $dbUtil->getUpdateSQLStringFromArray($fa, "packing_list", $whereCondition);
        $resultUpdate   = $db->sql_query($sqlUpdate);
        
        $count = count($packingListHistIdIds);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $packing_list_hist_id = $packingListHistIdIds[$i];
            $qty                  = $qty_arr[$i];
            $no_of_cartons        = $no_of_carton_arr[$i];

            $fa                  = array();
            $fa['qty']           = $qty;
            $fa['no_of_cartons'] = $no_of_cartons;

            $whereCondition = "WHERE packing_list_history_id = {$packing_list_hist_id}";
            $sqlUpdate      = $dbUtil->getUpdateSQLStringFromArray($fa, "packing_list_history", $whereCondition);
            $resultUpdate   = $db->sql_query($sqlUpdate);

            $recCount++;
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPackingListFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $qty                  = $fn->getReqParam('qty');
        $qty_balance          = $fn->getReqParam('qty_balance');
        $packingListHistIdIds = $fn->getReqParam('packingListHistId', array());
        
        $validate->resetErrorArray();
        
        if (count($packingListHistIdIds) == 0){
            $msg = 'Please check atleast one item for generating Packing List';
            $validate->validateData('error_box', $msg);
		}

        $validate->validateData('status', 'Please choose the Status');
        $validate->validateData('title', 'Please enter the Title');
        $validate->validateData('packing_list_date', 'Please choose Date');
        $validate->validateData('terms', 'Please enter the Terms');
        $validate->validateData('port_of_loading', 'Please enter Port of Loading');
        $validate->validateData('port_of_discharge', 'Please enter Port of Discharge');
        $validate->validateData('final_destination_country', 'Please choose Country of Final Destination');
        $validate->validateData('vessel', 'Please enter the Vessel');
        $validate->validateData('departure_date', 'Please choose Departure Date');
        $validate->validateData('origin_goods_country', 'Please choose Origin Goods Country');
        //$validate->validateData('qty', 'Please enter the qty');
        
        /*if($qty_balance < $qty){
            $validate->errorArray['qty']['name'] = "qty";
            $validate->errorArray['qty']['msg']  = 'Please enter less qty';
        }*/

        if (count($validate->errorArray) == 0) {
            return true;  
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintPackingListAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');    

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        //$pdf = new MYPDF();
        //$pdf = new PDF_HTML();
        $pdf = new PDF_MC_Table();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);

        $packing_list_id = $fn->getReqParam('id');

        $SQL = "
        SELECT pl.*
              ,o.shipping_first_name
              ,o.shipping_address1
              ,o.shipping_address2
              ,o.shipping_address_city
              ,o.shipping_address_state
			  ,(SELECT gc.name FROM geo_country gc
			  	WHERE gc.country_code = o.shipping_address_country)AS
			  	shipping_country
              ,c.bank_name
			  ,(SELECT gc.name FROM geo_country gc
			  	WHERE gc.country_code = pl.origin_goods_country)AS
			  	origin_goods_country
			  ,(SELECT gc.name FROM geo_country gc
			    WHERE gc.country_code =	pl.final_destination_country)AS
			    final_destination_country  	
        FROM packing_list pl
        LEFT JOIN (`order` o) ON (pl.order_id   = o.order_id)
        LEFT JOIN (company c) ON (pl.company_id = c.company_id)
        WHERE pl.packing_list_id = {$packing_list_id} 
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        
        $count = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        //syed:multi text code to set width of each column and alignment
        $pdf->SetWidths(array(10, 62, 17, 16, 41, 44));
        $pdf->SetAligns(array('L', 'L', 'L', 'L', 'R', 'R'));
        
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0) {
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',5,5);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',19.5);
                $pdf->SetXY(30,5);
	            $pdf->Cell(176, 8, $cpCfg['cp.companyName'] ,1,0, 'L');
                $pdf->SetXY(158,5);
                $pdf->SetFont('Courier','',7);
                $pdf->Cell(50, 10, $cpCfg['cp.addressPdf1']);

                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(28.5,8);
                $pdf->Cell(50, 15, $cpCfg['cp.addressPdf10']);
                $pdf->SetXY(28.5,11);
                $pdf->Cell(50, 15, $cpCfg['cp.addressPdf4']);
                $pdf->SetXY(28.5,14);
                $pdf->Cell(50, 15, $cpCfg['cp.addressPdf7']);
                $pdf->SetXY(28.5,17);
                $pdf->Cell(50, 15, $cpCfg['printEmailAddress']); 
                $pdf->Ln();

                $pdf->SetFont('Courier','B',12);
                $pdf->SetXY(174, 24);
                $pdf->Cell(5, 10, 'Packing List');
                $pdf->Ln();

                /* CONSIGNEE BOX */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetX(10);
                $pdf->Rect(5, 33, 118, 28);
                $pdf->SetX(5);
                $pdf->SetFont('Courier','',6);
                $pdf->Cell(100,4,"Consignee");
                
                /* INVOICE NO TO COUNTRY OF FINAL DESTINATION */
                $pdf->SetXY(123, 33);
                $pdf->Cell(41,4,"Invoice No.", 'TLR', 'L', 1);
                $pdf->Cell(41,4,"Date", 'TR');
                $pdf->Ln();

                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 36);
                $pdf->Cell(41, 6, $row['exporters_reference_no'],'B', '', 'C');
                $pdf->Cell(41, 6, $fn->getCPDate($row['packing_list_date'], 'd/m/Y'),'LBR', '', 'C');
                
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(123, 42);
             	$pdf->Cell(82,4,"Port of Loading",'R', 'L');
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 46);
                $pdf->Cell(82, 4, $row['port_of_loading'], 'LBR', '', 'C');
                
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(123, 50);
                $pdf->Cell(82,4,"Port of Discharge",'R', 'L');
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 52);
                $pdf->Cell(82, 6, $row['port_of_discharge'], 'LBR', '', 'C');

                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(123, 58);
                $pdf->Cell(82,4,"Country of Final Destination",'R', 'L');
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 60);
                $pdf->Cell(82, 6, strtoupper($row['final_destination_country']), 'LBR', '', 'C');
                
                /* BUYER BOX */
                $pdf->Rect(5, 61, 118, 28);
                $pdf->SetX(5);
                $pdf->SetFont('Courier','',6);
                $pdf->Cell(100,6,"Buyer (if not stated above)");
                $pdf->Ln(4);
                
                if ($row['show_bank_name'] == 1) {
                    $pdf->SetFont('Courier','B',8);
                    $pdf->SetX(5);
                    $pdf->Cell(100,5,$row['bank_name']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_first_name'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,5,$row['shipping_first_name']);
                    $pdf->Ln(3);
                }
               	
                if ($row['shipping_address1'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,5,$row['shipping_address1']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_address2'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,6,$row['shipping_address2']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_address_city'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,6,$row['shipping_address_city']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_address_state'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,6,$row['shipping_address_state']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_address_state'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,6,$row['shipping_address_state']);
                    $pdf->Ln(3);
                }

                if ($row['shipping_country'] != '') {
                    $pdf->SetX(5);
                    $pdf->Cell(100,6,strtoupper($row['shipping_country']));
                    $pdf->Ln(3);
                }

                /* EXPORTERS REF NO & TERMS OF DELIVERY */
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(123, 66);
                $pdf->Cell(41,4,"Exporter's Reference NO.", 'TR', 'L', 1);
                $pdf->Cell(41,4,"Buyer's Order NO.", 'TR');
                $pdf->Ln();

                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 68);
                $pdf->Cell(41, 6, $row['exporters_reference_no'],'B', '', 'C');
                $pdf->Cell(41, 6, $row['buyers_order_no'],'LBR', '', 'C');
                
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(123, 72);
                $pdf->Rect(123, 74, 82, 31);
                $pdf->Cell(5,10,"Terms of Delivery Payment");
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(123, 79);
                $pdf->drawTextBox($row['terms'], 98, 100, 'L', 'T', 0);
               	
                /* VESSEL, DEP DATE & COUNTRY OF ORIGIN OF GOODS */
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(5, 84);
                $pdf->Cell(118,14,"Vessel/Aircraft etc." ,'LR', 'L');
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(5, 91);
                $pdf->Cell(118, 6, $row['vessel'],'B', '', 'C');
                
                $pdf->SetFont('Courier','',6);
                $pdf->SetXY(5, 97);
                $pdf->Cell(59,5,"Departure Date" ,'LR', 'L');
                $pdf->Cell(59,5,"Country of Origin of Goods" ,'LR', 'L');
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetXY(5, 100);
                $pdf->Cell(59,5,$fn->getCPDate($row['departure_date'], 'd/m/Y'),'LBR', '', 'C');
                $pdf->Cell(59,5,strtoupper($row['origin_goods_country']),'LBR', '', 'C');

                /* DESCRIPTION OF GOODS */
                $pdf->Rect(5, 105, 132, 125);
                $pdf->SetX(5);
                $pdf->SetFont('Courier','',6);
                $pdf->Cell(100,14,"Marks Nos and Container No.      No and Kind of Packages       Description of Goods");

                /* QUANTITY */
                $pdf->Rect(137, 105, 30, 125);
                $pdf->SetXY(102, 100);
                $pdf->SetFont('Courier','',6);
                $pdf->Cell(100,14,"Quantity", '', '', 'C');
                $pdf->Ln(20);
        
                $pdf->SetX(40);
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(100,5,$row['container_size']);
                $pdf->Ln(10);
                
                $pdf->SetX(40);
                $pdf->Cell(40,5,$row['no_of_cartons'] . ' CARTONS');
                $pdf->Ln(10);
            
            }

            $packing_list_id = $row['packing_list_id'];
        }
        
        //===================================MAIN TABLE============================= //
        $packingListRec = $fn->getRecordRowById('packing_list', 'packing_list_id' , $packing_list_id);

        $total_quantity = 0;
        if ($packingListRec['packing_list_in_next_page'] == 0) {
            $sqlPackingListItems = "
            SELECT plh.*
                  ,ii.item_title
            FROM packing_list_history plh
            LEFT JOIN (invoice_item ii) ON (plh.invoice_item_id = ii.invoice_item_id)
            WHERE plh.packing_list_id = {$packing_list_id}        
            ";
            $resultPackingListItems     = $db->sql_query($sqlPackingListItems);
            while ($rowPackingListItems = $db->sql_fetchrow($resultPackingListItems)) {
                
                $qty = number_format($rowPackingListItems['qty']);
            
                $pdf->SetX(40);
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(100,5,$rowPackingListItems['item_title']);
                $pdf->Cell(23,5,$qty, '', '', 'C');
                $pdf->Ln(5);
                
                $total_quantity += $rowPackingListItems['qty'];
            }
        }
        
        /* PACKING LIST DESCRIPTION */
        $pdf->SetFont('Courier','B',8);
        $pdf->Cell(100,5,$packingListRec['description']);
        $pdf->Ln(5);

        /* EMPTY RECTANGLE WITH COLOR */
        $pdf->SetFillColor(224,235,255);
        $pdf->Rect(167, 105, 38, 135, 'DF');

        //=================================== BOTTOM TABLE ============================= //
        $pdf->SetFont('Courier','',6);
        $pdf->SetXY(5, 230);
        $pdf->Cell(31,5,"Total Packages", 'LTR', '', 'L');
        $pdf->Cell(31,5,"Net Wt (Kg)", 'LTR', '', 'L');
        $pdf->Cell(31,5,"Gross Wt (Kg)", 'LTR', '', 'L');
        $pdf->Cell(29,5,"Cube M3", 'LTR', '', 'L');
        $pdf->Cell(40,5,"Total Quantity", 'LTR', '', 'L');
        $pdf->Ln();
        $pdf->SetFont('Courier','B',8);
        $pdf->SetXY(5, 235);
        $pdf->Cell(31,5,$packingListRec['no_of_cartons'] . ' CARTONS','LBR', '', 'C');
        $pdf->Cell(31,5,number_format($packingListRec['net_wt'], 2) . ' KGS','LBR', '', 'C');
        $pdf->Cell(31,5,number_format($packingListRec['gross_wt'], 2) . ' KGS','LBR', '', 'C');
        $pdf->Cell(29,5,$packingListRec['cube_m3'],'LBR', '', 'C');
        $pdf->Cell(40,5,number_format($total_quantity),'LBR', '', 'C');
        
        /* EMPTY RECTANGLE */
        $pdf->Rect(5, 240, 200, 35);
        $pdf->SetXY(5, 240);

        if ($packingListRec['packing_list_in_next_page'] == 1) {
            $pdf->AddPage();
            $pdf->SetFont('Courier','B',12);
            $pdf->SetXY(74, 20);
            $pdf->Cell(68,5,"PACKING LIST ATTACHED SHEET", 'B', '', 'C');
            $pdf->Ln(10);

            $pdf->SetFont('Courier','B',10);
            $pdf->SetX(10);
            $pdf->Cell(32,5,"L/C NO        :");
            $pdf->Cell(68,5,"");
            $pdf->Ln(7);

            $pdf->SetX(10);
            $pdf->Cell(32,5,"DATED         :");
            $pdf->Cell(68,5,"");
            $pdf->Ln(7);

            $pdf->SetX(10);
            $pdf->Cell(32,5,"INVOICE NO    :");
            $pdf->Cell(68,5,"");
            $pdf->Ln(7);

            $pdf->SetX(10);
            $pdf->Cell(32,5,"DATED         :");
            $pdf->Cell(68,5,$dateUtil->formatDate($packingListRec['packing_list_date'], 'DD/MM/YY'));
            $pdf->Ln(4);

            $pdf->SetFont('Courier','B',14);
            $pdf->SetX(74);
            $pdf->Cell(68,5,"GENERAL KITCHENWARE ITEMS", '', '', 'C');
            $pdf->Ln(5);

            $pdf->SetFont('Courier','B',12);
            $pdf->SetX(74);
            $pdf->Cell(68,5,"ALL OTHER DETAILS AS PER INDENT NO.NAT/7/379/13", '', '', 'C');
            $pdf->Ln(10);

            /* LABEL */
            $pdf->SetX(10);
            $pdf->Cell(15,5,"NO.", 'TR', '', 'C');
            $pdf->Cell(25,5,"CRTN", 'TR', '', 'C');
            $pdf->Cell(28,5,"PCS, SETS", 'TR', '', 'C');
            $pdf->Cell(25,5,"TOTAL NO.", 'TR', '', 'C');
            $pdf->Cell(65,5,"DESCRIPTION OF", 'TR', '', 'C');
            $pdf->Cell(32,5,"TOTAL NO. OF", 'TR', '', 'C');
            $pdf->Ln();

            $pdf->Cell(15,5,"", 'RB', '', 'C');
            $pdf->Cell(25,5,"NO.", 'RB', '', 'C');
            $pdf->Cell(28,5,"IN ONE CRTN", 'RB', '', 'C');
            $pdf->Cell(25,5,"OF CRTNS", 'RB', '', 'C');
            $pdf->Cell(65,5,"GOODS", 'RB', '', 'C');
            $pdf->Cell(32,5,"PCS", 'RB', '', 'C');
            $pdf->Ln();

            /* ITEMS IN PACKING LIST */
            $sqlPackingListItems = "
            SELECT plh.*
                  ,ii.item_title
            FROM packing_list_history plh
            LEFT JOIN (invoice_item ii) ON (plh.invoice_item_id = ii.invoice_item_id)
            WHERE plh.packing_list_id = {$packing_list_id}        
            ";
            $resultPackingListItems = $db->sql_query($sqlPackingListItems);
            $counter = 1;
            $total_carton = 0;
            while ($rowPackingListItems = $db->sql_fetchrow($resultPackingListItems)) {                
                $qty = number_format($rowPackingListItems['qty']);
            
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(15,5,$counter, 'RB', '', 'C');
                $pdf->Cell(25,5,"1-" . $rowPackingListItems['no_of_cartons'], 'RB', '', 'C');
                $pdf->Cell(28,5,"IN ONE CRTN", 'RB', '', 'C');
                $pdf->Cell(25,5,$rowPackingListItems['no_of_cartons'], 'RB', '', 'C');
                $pdf->Cell(65,5,$rowPackingListItems['item_title'], 'RB', '', 'C');
                $pdf->Cell(32,5,$qty, 'RB', '', 'R');
                $pdf->Ln();
                
                $total_quantity += $rowPackingListItems['qty'];
                $total_carton += $rowPackingListItems['no_of_cartons'];
                $counter++;
            }

            $pdf->SetFont('Courier','B',9);
            $pdf->Cell(15,5,'', 'RB', '', 'C');
            $pdf->Cell(25,5,'', 'RB', '', 'C');
            $pdf->Cell(28,5,'', 'RB', '', 'C');
            $pdf->Cell(25,5,$total_carton . ' CARTON', 'RB', '', 'C');
            $pdf->Cell(65,5,'', 'RB', '', 'C');
            $pdf->Cell(32,5,number_format($total_quantity), 'RB', '', 'R');
            $pdf->Ln();

        }

		$pdf->Output();
    }
}
