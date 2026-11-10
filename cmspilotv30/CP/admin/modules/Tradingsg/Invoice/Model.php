<?
class CP_Admin_Modules_Tradingsg_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT i.*
            ,c.company_id
            ,c.company_name
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        if (!$this->getGenerateInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $orderItemIds       = $fn->getReqParam('orderItemId', array());
        $invoice_amount     = $fn->getPostParam('invoice_amount');
        $invoice_date       = $fn->getPostParam('invoice_date');
        $invoice_due_date   = $fn->getPostParam('invoice_due_date');
        $invoice_terms      = $fn->getPostParam('invoice_terms');
        $notes              = $fn->getPostParam('notes');
        $order_id           = $fn->getReqParam('order_id');
        $qty_arr            = $fn->getReqParam('qty', array());
        $qty_balance        = $fn->getReqParam('qty_balance');

        $gsttaxperc = $cpCfg['amtForGSTCalc'] ;
        $invoice_amount = $invoice_amount + ($invoice_amount * $gsttaxperc/100);
        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa = array();
        $fa['invoice_code']     = 'INV - ' . $invoice_code;
        $fa['invoice_amount']   = $invoice_amount;
        $fa['invoice_date']     = $invoice_date;
        $fa['invoice_due_date'] = $invoice_due_date;
        $fa['invoice_terms']    = $invoice_terms;
        $fa['notes']            = $notes;
        $fa['order_id']         = $order_id;
        $fa['status']           = 'Due';
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        $fa['invoice_type']     = 'Client';

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        $count = count($orderItemIds);
        $recCount = 0;
        foreach ($orderItemIds as $key=>$value){
            $orderItemRec = $fn->getRecordRowByID('order_item', 'order_item_id', $value);
            $pfx  = $value . '_' ;
            $qty  = $fn->getPostParam("{$pfx}qty");

            if ($invoice_id > 0){
                $fa = array();
                $fa['invoice_id']   = $invoice_id;
                $fa['record_id']    = $orderItemRec['record_id'];
                $fa['qty']          = $qty;
                $fa['unit_price']   = $orderItemRec['unit_price'];
                $fa['cost_price']   = $orderItemRec['cost_price'];
                $fa['item_title']   = $orderItemRec['item_title'];
                $fa['module']       = $orderItemRec['module'];
                $fa['supplier_id']  = $orderItemRec['supplier_id'];
                $fa['order_item_id']  = $value;

                $invoice_item_id = $fn->addRecord($fa, 'invoice_item');
                //print_r ($fa);
                $recCount++;
            }
        }

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

        //$this->getGenerateInvoiceForMedia($invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateInvoiceForMedia($invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT it.*
        FROM invoice_item it
        WHERE it.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $count = 0;
        $total_invoice_sum = 0;
        $item_no = 1;

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}

        //=================================== FIRST TABLE ============================= //
        /* Logo of the institution */
        $pdf->Image('images/logo-print.gif', 10, 5, 45);

        /* Institute company address */
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(140,1);
        $pdf->Cell(20, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
        $pdf->SetXY(140,5);
        $pdf->Cell(24, 20, $cpCfg['printTelephoneAndFax']);
        $pdf->SetXY(140,10);
        $pdf->Cell(28, 20, $cpCfg['printEmailAddress']);
        $pdf->SetXY(140,15);
        $pdf->Cell(20, 20, $cpCfg['printWebAddress']);
        $pdf->SetXY(140,20);
        $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
        $pdf->Ln(15);

        $pdf->SetX(100);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 20, 'INVOICE');
        $pdf->Ln(10);

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $invoiceRec     = $fn->getRecordRowByID('invoice', 'invoice_id', $row['invoice_id']);
            $orderItemRec   = $fn->getRecordRowByID('order_item', 'invoice_id', $row['invoice_id']);
            $productRec     = $fn->getRecordRowByID('product', 'product_id', $orderItemRec['record_id']);
            $orderRec       = $fn->getRecordRowByID('order', 'order_id', $invoiceRec['order_id']);
            $quoteRec       = $fn->getRecordRowByID('quote', 'quote_id', $orderRec['quote_id']);
            $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);

            $quote_currency = $quoteRec['currency'];

            if ($count == 0) {
                //=================================== SECOND TABLE ============================= //
                /* Hard coded text of Master and Owner */
                if ($cpCfg['hasMasterAndOwnerTextInInvoicePdf']) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetXY(10, 50);
                    $pdf->SetFillColor(255,255,255);
                    $toText = $cpCfg['printMasterAndOwnerText'];
                    $pdf->Cell(100, 8, $toText, 1, 0, 'L', 1);
                }

                $invoice_code = $invoiceRec['invoice_code'];
                /* Invoice code*/
                $code = 'Invoice No : '. $invoice_code;
                $pdf->SetXY(145, 44);
                $pdf->Cell(50, 20, $code);
                $pdf->Ln(14);

                /* Company Name */
                $pdf->SetX(10);
                $companyName = 'Name: ' . $companyRec['company_name'];
                $pdf->Cell(100, 8, $companyName, 1, 0, 'L', 1);

                /* Invoice Date*/
                $date = $fn->getCPDate($invoiceRec['invoice_date'], 'dS F Y');
                $invoiceDate = 'Invoice Date : '. $date;
                $pdf->SetXY(145, 52);
                $pdf->Cell(50, 20, $invoiceDate);
                $pdf->Ln(14);

                if ($cpCfg['hasCompanyAddressInInvoicePdf']) {
                    $pdf->SetX(10);
                    $pdf->SetFont('Arial', 'B', 10);
                    $companyAddressText = 'Address of the company comes here';
                    $pdf->Cell(70, 8, $companyAddressText, 1, 0, 'L', 1);
                    $pdf->Ln(5);
                }
                $pdf->Ln(10);

                //=================================== THIRD TABLE ============================= //
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(15, 8, "Item No",1,0, 'C', 1);
                $pdf->Cell(20, 8, "Item Code",1,0, 'C', 1);
                $pdf->Cell(80, 8, "Name of the Item",1,0, 'C', 1);
                $pdf->Cell(10, 8, "Qty",1,0, 'C', 1);
                $pdf->Cell(18, 8, "Unit Size",1,0, 'C', 1);
                $pdf->Cell(30, 8, "Unit Price",1,0, 'C', 1);
                $pdf->Cell(25, 8, "Amount",1,0, 'C', 1);
                $pdf->Ln();
            }

            $item_amount_price = $row['qty'] * $row['unit_price'];
            $item_amount_formatted = number_format($item_amount_price, 2);
            $total_invoice_sum += $item_amount_price;

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(15, 8, $item_no, 1, 0, 'C', 1);
            $pdf->Cell(20, 8, $productRec['item_code'], 1, 0, 'L', 1);
            $pdf->Cell(80, 8, $row['item_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(18, 8, $productRec['unit'], 1, 0, 'R', 1);
            $pdf->Cell(30, 8, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $item_amount_formatted, 1, 0, 'R', 1);
            $pdf->Ln(7);

            $count++;
            $item_no++;
        }

        /* SubTotal */
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Sub Total ' . $quote_currency, 'B', 0, 'R');
        $pdf->Cell(25, 8, number_format($total_invoice_sum, 2), 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* GST */
        $gst_amount = 0;
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'ADD: ' . $cpCfg['printTaxName'] . ' ' . $cpCfg['printTaxValue'], 'B', 0, 'R');
        $pdf->Cell(25, 8, $gst_amount, 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* Total */
        $total_amount = number_format($total_invoice_sum + $gst_amount, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Total', 'B', 0, 'R');
        $pdf->Cell(25, 8, $total_amount, 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'tradingsg_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateInvoiceFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $qty = $fn->getReqParam('qty');
        $qty_balance = $fn->getReqParam('qty_balance');

        $validate->resetErrorArray();
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
    function getEditInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $invoiceItemIds     = $fn->getPostParam('invoiceItemId', array());
        $invoice_amount     = $fn->getPostParam('invoice_amount');
        $invoice_date       = $fn->getPostParam('invoice_date');
        $invoice_due_date   = $fn->getPostParam('invoice_due_date');
        $invoice_terms      = $fn->getPostParam('invoice_terms');
        $notes              = $fn->getPostParam('notes');
        $order_id           = $fn->getReqParam('order_id');
        $invoice_id         = $fn->getReqParam('invoice_id');
        $qty_arr            = $fn->getReqParam('qty', array());
        $qty_balance        = $fn->getReqParam('qty_balance');

        $fa = array();
        $fa['invoice_amount']   = $invoice_amount;
        $fa['invoice_date']     = $invoice_date;
        $fa['invoice_due_date'] = $invoice_due_date;
        $fa['invoice_terms']    = $invoice_terms;
        $fa['notes']            = $notes;
        $fa['order_id']         = $order_id;
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['modification_date']= date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $gsttaxperc = $cpCfg['amtForGSTCalc'] ;
        $invoice_amount = $invoice_amount + ($invoice_amount * $gsttaxperc/100);

        $whereCondition = "WHERE invoice_id = {$invoice_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        $count = count($invoiceItemIds);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $invoice_item_id = $invoiceItemIds[$i];
            $qty = $qty_arr[$i];

            $fa = array();
            $fa['qty']          = $qty;

            $whereCondition = "WHERE invoice_item_id = {$invoice_item_id}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice_item", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);

            $recCount++;
        }

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

        return $validate->getSuccessMessageXML();
    }

}