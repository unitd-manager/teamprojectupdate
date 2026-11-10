<?
class CP_Admin_Modules_AceIms_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,gc2.name AS shipping_country_name
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,(SELECT (SUM(i.invoice_amount))
               FROM invoice i
               WHERE i.order_id = o.order_id
               ) AS order_amount
        FROM `order` o
        LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
        LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        #$searchVar->mainTableAlias = 'o';

        $business_id            = $fn->getReqParam('business_id');
	    $organization_id        = $fn->getReqParam('organization_id');
        $business_contact_id    = $fn->getReqParam('business_contact_id');
        $order_date1            = $fn->getReqParam('order_date_1');
        $order_date2            = $fn->getReqParam('order_date_2');
        $order_status           = $fn->getReqParam('order_status');
        $order_type             = $fn->getReqParam('order_type');
        $order_id               = $fn->getReqParam('order_id');
        $payment_method         = $fn->getReqParam('payment_method');

        $searchVar->sqlSearchVar[] = "o.module = 'aceIms_course'";

        if ($_SESSION['userGroupType'] != 'Super Administrator') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$_SESSION['cp_site_id']}";
        }

        if ($order_id != "") {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'o.order_id');

            if ($order_id != '') {
                $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
            }

            if ($payment_method != '') {
                $searchVar->sqlSearchVar[] = "o.payment_method = '{$payment_method}'";
            }

            if ($business_id != '') {
                $searchVar->sqlSearchVar[] = "o.business_id = '{$business_id}'";
            }

            if ($organization_id != '') {
                $searchVar->sqlSearchVar[] = "org.organization_id = '{$organization_id}'";
            }

            if ($business_contact_id != '') {
                $searchVar->sqlSearchVar[] = "o.business_contact_id = '{$business_contact_id}'";
            }

            if ($order_date1 != "" && $order_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.order_date BETWEEN '{$order_date1}' AND '{$order_date2}')";
            }

            if ($order_status != '') {
                $searchVar->sqlSearchVar[] = "o.order_status = '{$order_status}'";
            }

            if ($order_type != '') {
                $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.cust_first_name       LIKE '%{$tv['keyword']}%'  OR
                    o.order_id              LIKE '%{$tv['keyword']}%'  OR
                    o.cust_last_name        LIKE '%{$tv['keyword']}%'  OR
                    o.order_code            LIKE '%{$tv['keyword']}%'  OR
                    o.memo                  LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_first_name   LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_last_name    LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "o.creation_date DESC";
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();
        $validate->validateData('order_date', 'Please enter the order date');

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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['order_status'] = 'New';
        $id = $fn->addRecord($fa);

        $order_code = $cpCfg['m.ecommerce.order.codePrefix'] . $id;

        $SQL = "
        UPDATE `order`
        SET order_code = '{$order_code}'
        WHERE order_id = {$id}
        ";
        $result = $db->sql_query($SQL);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('order_id', 'Please enter the title');

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

        $fa = $fn->addToFieldsArray($fa, 'order_date');
        $fa = $fn->addToFieldsArray($fa, 'order_status');
        $fa = $fn->addToFieldsArray($fa, 'order_code');
        $fa = $fn->addToFieldsArray($fa, 'memo');
        $fa = $fn->addToFieldsArray($fa, 'shipping_charge');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');

        $fa = $fn->addToFieldsArray($fa, 'cust_first_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_last_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_email');
        $fa = $fn->addToFieldsArray($fa, 'cust_phone');
        $fa = $fn->addToFieldsArray($fa, 'cust_address1');
        $fa = $fn->addToFieldsArray($fa, 'cust_address2');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_city');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_area');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_state');
        $fa = $fn->addToFieldsArray($fa, 'cust_po_code');
        $fa = $fn->addToFieldsArray($fa, 'cust_country_code');

        $fa = $fn->addToFieldsArray($fa, 'shipping_first_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_last_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_email');
        $fa = $fn->addToFieldsArray($fa, 'shipping_phone');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address1');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address2');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_area');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_city');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_state');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_country_code');

        return $fa;
    }

    /**
     *
     */
    function getAceImsOrderAceImsInsuranceLinkSQL($id) {

        $SQL = "
        SELECT si.student_insurance_id
              ,co.title AS course_title
              ,i.title AS insurance_title
              ,si.code
              ,si.premium_amount
              ,si.insurance_start_date
              ,si.insurance_end_date
        FROM student_insurance si
        LEFT JOIN `order` o ON (o.order_id = si.order_id)
        LEFT JOIN course co ON (co.course_id = si.course_id)
        LEFT JOIN insurance i ON (i.insurance_id = si.insurance_id)
        WHERE si.order_id = '{$id}'
        ORDER BY si.student_insurance_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getUpdateSubsidyStatus() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $order_item_id = $fn->getReqParam('order_item_id');
        $subsidy_paid_status = $fn->getReqParam('subsidy_paid_status');

        $SQL = "
        UPDATE `order_item`
        SET subdidy_paid = {$subsidy_paid_status}
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

        return $text;
    }

    /**
     *
     */
    function getUpdateInvoiceStatus() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $invoice_id = $fn->getReqParam('invoice_id');
        $invoice_status = $fn->getReqParam('invoice_status');

        $SQL = "
        UPDATE invoice
        SET status = '{$invoice_status}'
        WHERE invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        return $text;
    }

    /**
     *
     */
    function getAceImsOrderAceImsPaymentLinkSQL($id) {

        return "
        SELECT p.payment_id
              ,DATE_FORMAT(p.payment_date, '%d-%M-%Y') AS payment_date
              ,p.amount
        FROM payment p
        JOIN `order` o
        WHERE p.order_id = o.order_id
          AND p.order_id = '{$id}'
        ";

    }

    /**
     *
     */
    function getGenerateInvoice() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        set_time_limit(50000);

        $site_id = $fn->getSessionParam('cp_site_id');
        $order_id = $fn->getPostParam('order_id');
        $order_item_ids = $fn->getPostParam('orderItemId');
        //print_r($order_item_ids);
        if (!is_array($order_item_ids)){
            $cpUtil->redirect("index.php?_topRm=finance&module=aceIms_order&order_id={$order_id}&_action=edit");
            return;
        }

        $order_item_id_values = '';
        $total= '';

        foreach($order_item_ids AS $order_item_id){
            $order_item_id_values .= $order_item_id . ',';
        }
        $order_item_id_values = substr($order_item_id_values,0,-1);

        //to create invoice
        $SQL = "
        SELECT SUM(qty * unit_price) AS total_amount
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
          AND oi.contact_id IN(
             SELECT contact_id
             FROM order_item
             WHERE order_item_id IN ($order_item_id_values)
          )
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if ($site_id) {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa = array();
        $fa['order_id']       = $order_id;
        $fa['invoice_amount'] = $row['total_amount'];
        $fa['invoice_code']   = $nextInvoiceCode;
        $fa['status']         = 'Due';
        $fa['invoice_date']   = date("Y-m-d H:i:s");
        $fa['creation_date']  = date("Y-m-d H:i:s");

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        //to create invoice item
        $SQL = "
        SELECT oi.*
        FROM order_item oi
        WHERE oi.order_item_id IN ($order_item_id_values)
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            // To get subsidy and discount for related contacts

            $expSubsidy = array('condn' => " AND module='aceIms_subsidy' AND order_id = {$order_id}");
            $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'contact_id',
            $row['contact_id'], $expSubsidy);

            $subsidy_cost = $orderItemRecSubsidy['unit_price'];

            $expDiscount = array('condn' => " AND module='aceIms_discount' AND order_id = {$order_id}");
            $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'contact_id', $row['contact_id'], $expDiscount);

            $discount_cost = $orderItemRecSubsidy['unit_price'];

            $fa = array();
            $fa['invoice_id']     = $invoice_id;
            $fa['qty']            = $row['qty'];
            $fa['unit_price']     = $row['unit_price'];
            $fa['item_title']     = $row['item_title'];
            $fa['contact_id']     = $row['contact_id'];
            $fa['contact_name']   = $row['contact_name'];
            $fa['subsidy']        = $subsidy_cost;
            $fa['discount']       = $discount_cost;

            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_item');
            $resultInsert       = $db->sql_query($insertInvoiceSQL);
        }
        //to update order_item with invoice id
        foreach($order_item_ids AS $order_item_id){
            $fa = array();
            $fa['invoice_id'] = $nextInvoiceCode;
            $fa['invoice_clear_status'] = '';

            $whereCondition = "
            WHERE order_item_id = {$order_item_id}
            ";

            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'order_item', $whereCondition);
            $db->sql_query($SQL);
        }

        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$order_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateInvoiceForMedia($invoice_id);
        } else {
            $this->getGenerateInvoiceIndividualForMedia($invoice_id);
        }

        $cpUtil->redirect("index.php?_topRm=finance&module=aceIms_order&order_id={$order_id}&_action=edit");
    }

    /**
     * Finding Receipt Code according to Site Id
     */
    function getFindReceiptCodeWithSite($site_id = '') {
        $db = Zend_Registry::get('db');

        $appendSql = '';
        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }

        $sqlSetting = "
        SELECT value
        FROM setting
        WHERE key_text = 'nextReceiptCode'
        {$appendSql}
        ";
        $resultSetting  = $db->sql_query($sqlSetting);
        $rowSetting     = $db->sql_fetchrow($resultSetting);
        $receiptCode    = $rowSetting['value'];

        return $receiptCode;
    }

    /**
     * Updation of Receipt Code according to Site Id
     */
    function getReceiptCodePrefixWithSite($site_id = '') {
        $db = Zend_Registry::get('db');

        $appendSql = '';
        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }

        $sqlRecCode = "
        SELECT value
        FROM setting
        WHERE key_text = 'receiptCodePrefix'
        {$appendSql}
        ";
        $resultRecCode  = $db->sql_query($sqlRecCode);
        $rowRecCode     = $db->sql_fetchrow($resultRecCode);
        $receiptCodePfx = $rowRecCode['value'];

        return $receiptCodePfx;
    }

    /**
     * Separation of Invoice Codes
     */
    function getSeparationOfInvoiceCodes($invoiceCodes) {

        $invoiceCodesArr = join(',', $invoiceCodes);
        $sessionExplode  = explode(',', $invoiceCodesArr);

        $counter = 1;
        $count = count($sessionExplode);

        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        return $invoice_code;
    }

    /**
     *
     */
    function getGenerateCreditNoteFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getGenerateCreditNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoice_id      = $fn->getPostParam('invoice_id');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $order_id        = $fn->getReqParam('order_id');

        //To update credit note codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCreditNoteCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $credit_note_code = $fn->getSettingsValueByKey("nextCreditNoteCode");

        $fa = array();
        $fa['amount']           = $amount;
        $fa['order_id']         = $order_id;
        $fa['invoice_id']       = $invoice_id;
        $fa['credit_note_code'] = $credit_note_code;
        $fa['remarks']          = $remarks;
        $fa['date']             = date("Y-m-d H:i:s");
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insertCreditNoteSQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'credit_note');
        $resultSQL              = $db->sql_query($insertCreditNoteSQL);
        $credit_note_id         = $db->sql_nextid();

        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$order_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateCreditNoteForMedia($credit_note_id);
        } else {
            $this->getGenerateCreditNoteForMedia($credit_note_id);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateCreditNoteFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('invoice_id' , 'Please select the invoice');
        $validate->validateData('amount' , 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getClearInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $order_item_id = $fn->getReqParam('order_item_id');

        $SQL = "
        UPDATE `order_item`
        SET invoice_clear_status = 'Cancelled'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

        //To change invoice status to cancelled
        $orderRec = $fn->getRecordRowByID('order_item', 'order_item_id', $order_item_id);

        $invoice_code = $orderRec['invoice_id'];

        if($invoice_code){
            $SQL = "
            UPDATE `invoice`
            SET status = 'Cancelled'
            WHERE invoice_code = {$invoice_code}
            ";
            $result = $db->sql_query($SQL);

            $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_code', $invoice_code);

            $invoice_id   = $invoiceRec['invoice_id'];

            $SQLReceipt = "
            SELECT receipt_id
            FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            ";
            $resultReceipt = $db->sql_query($SQLReceipt);
            while ($rowReceipt = $db->sql_fetchrow($resultReceipt)) {
                $UpdateSQL = "
                UPDATE `receipt`
                SET receipt_status = 'Cancelled'
                WHERE receipt_id = {$rowReceipt['receipt_id']}
                ";
                $result = $db->sql_query($UpdateSQL);
            }
        }
        return $text;
    }

    /**
     *
     */
    function getPrintInvoice() {
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

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_code = {$invoice_code}
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
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $SQLInvoiceItem = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = {$row['invoice_id']}
            ";
            $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
            $numRowsInvoiceItem  = $db->sql_numrows($resultInvoiceItem);

            while ($invoiceItemRec = $db->sql_fetchrow($resultInvoiceItem)) {
                if ($count == 0){

                    $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                    $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                    $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                    $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                    $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

                    $pdf->SetXY(10,1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Rect(10 , 5, 80, 38, 'F');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                    $pdf->SetFont('Arial','',7);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                    $pdf->Ln(15);

                    $pdf->Image('images/logo-print.jpg',157,5,45);

                    $pdf->SetFont('Arial', 'B', 22);
                    $pdf->SetXY(157, 35);
                    $pdf->Cell(40, 20, "Invoice");
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(157, 45);
                    $pdf->Cell(21, 20, "Invoice No : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $row['invoice_code']);
                    $pdf->Ln(5);

                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                    $pdf->Cell(11, 20, "Date : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $date);

                    $pdf->SetXY(10, 40);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, "Bill To :");
                    $pdf->SetFillColor(224,235,255);
                    $pdf->Rect(10, 53, 75, 30, 'D');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(10, 45);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                    $pdf->SetXY(10, 50);
                    $pdf->Cell(50, 20, $companyRec['title']);
                    $pdf->SetXY(10, 55);
                    $pdf->Cell(50, 20, $companyRec['address1']);
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(50, 20, $companyRec['address2']);
                    $pdf->SetXY(10, 65);
                    $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                    $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                    $pdf->Ln(35);

                    $pdf->SetXY(105, 85);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(69, 5, $companyRec['phone'],1 ,0, 'L', 1);
                    $pdf->Ln(4);

                    $pdf->SetXY(10, 90);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(70, 5, 'ACCOUNTS DEPARTMENT',1 ,0, 'L', 1);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(69, 5, $companyRec['fax'],1 ,0, 'L', 1);
                    $pdf->Ln(10);

                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                    $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                    $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                    $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                    $pdf->Ln();
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(80,10,$courseRec['course_code'] . ' ' . $courseRec['title'],1 ,0, 'C', 0);
                    $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                    $date_from = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                    $date_to = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                    $date = $date_from . ' - ' . $date_to;
                    $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                    $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                    $pdf->Ln(14);

                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                    $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                    $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                    $pdf->Ln();
                }

                $invoiceItemQty += $invoiceItemRec['qty'];

                $unitPriceAmt += $invoiceItemRec['unit_price'];
                $unitPrice = number_format($unitPriceAmt, 2);

                $subsidyAmt += $invoiceItemRec['subsidy'];
                $subsidy = number_format($subsidyAmt, 2);

                if ($numrowsInvoiceItem == $numRowsInvoiceItem) {

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(20, 10, $invoiceItemQty,1, 0, 'C', 1);
                    $pdf->Cell(135, 10, $invoiceItemRec['item_title'],1, 0, 'L', 1);
                    $pdf->Cell(35, 10, $unitPrice, 1, 0, 'R', 1);
                    $pdf->Ln(7);

                    $pdf->Cell(20, 10, $invoiceItemQty, 'L', 0, 'C', 1);
                    $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
                    $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
                    $pdf->Ln(10);
                }

                $numrowsInvoiceItem++;
            }
            $YVariable = 145;

            $total = $unitPrice + $subsidy;
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

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(150, 8, 'Remarks');
        $pdf->Ln(4);

        /*$pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();*/

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(67, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
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
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
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
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $SQLInvoiceItem = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = {$row['invoice_id']}
            ";
            $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
            $numRowsInvoiceItem  = $db->sql_numrows($resultInvoiceItem);

            while ($invoiceItemRec = $db->sql_fetchrow($resultInvoiceItem)) {
                if ($count == 0){

                    $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                    $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                    $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                    $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                    $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

                    $pdf->SetXY(10,1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Rect(10 , 5, 80, 38, 'F');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, 'MASS EDUCATION INSTITUTE');
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, '1 Sophia Road, #06-24 Peace Centre');
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, 'Singapore 228149');
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, 'Tel : (65) 6336 6546 Fax : (65) 6337 7547');
                    $pdf->Ln(15);

                    $pdf->Image('images/logo-print.jpg',157,5,45);

                    $pdf->SetFont('Arial', 'B', 22);
                    $pdf->SetXY(157, 35);
                    $pdf->Cell(40, 20, "Invoice");
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(157, 45);
                    $pdf->Cell(21, 20, "Invoice No : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $row['invoice_code']);
                    $pdf->Ln(5);

                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                    $pdf->Cell(11, 20, "Date : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $date);

                    $pdf->SetXY(10, 40);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, "Bill To :");
                    $pdf->SetFillColor(224,235,255);
                    $pdf->Rect(10, 53, 75, 30, 'D');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(10, 45);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                    $pdf->SetXY(10, 50);
                    $pdf->Cell(50, 20, $companyRec['title']);
                    $pdf->SetXY(10, 55);
                    $pdf->Cell(50, 20, $companyRec['address1']);
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(50, 20, $companyRec['address2']);
                    $pdf->SetXY(10, 65);
                    $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                    $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                    $pdf->Ln(35);

                    $pdf->SetXY(105, 85);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(69, 5, $companyRec['phone'],1 ,0, 'L', 1);
                    $pdf->Ln(4);

                    $pdf->SetXY(10, 90);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(70, 5, 'ACCOUNTS DEPARTMENT',1 ,0, 'L', 1);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(69, 5, $companyRec['fax'],1 ,0, 'L', 1);
                    $pdf->Ln(10);

                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                    $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                    $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                    $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                    $pdf->Ln();
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(80,10,$courseRec['course_code'] . ' ' . $courseRec['title'],1 ,0, 'C', 0);
                    $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                    $date_from = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                    $date_to = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                    $date = $date_from . ' - ' . $date_to;
                    $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                    $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                    $pdf->Ln(14);

                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                    $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                    $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                    $pdf->Ln();
                }

                $invoiceItemQty += $invoiceItemRec['qty'];

                $unitPriceAmt += $invoiceItemRec['unit_price'];
                $unitPrice = number_format($unitPriceAmt, 2);

                $subsidyAmt += $invoiceItemRec['subsidy'];
                $subsidy = number_format($subsidyAmt, 2);

                if ($numrowsInvoiceItem == $numRowsInvoiceItem) {

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(20, 10, $invoiceItemQty,1, 0, 'C', 1);
                    $pdf->Cell(135, 10, $invoiceItemRec['item_title'],1, 0, 'L', 1);
                    $pdf->Cell(35, 10, $unitPrice, 1, 0, 'R', 1);
                    $pdf->Ln(7);

                    $pdf->Cell(20, 10, $invoiceItemQty, 'L', 0, 'C', 1);
                    $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
                    $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
                    $pdf->Ln(10);
                }

                /* SQL for more than one contact for the invoice*/
                $contactPerson = '';
                $SQLContact = "
                SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                      ,c.id_card_no
                FROM contact c
                WHERE c.contact_id = {$invoiceItemRec['contact_id']}
                ";
                $resultContact = $db->sql_query($SQLContact);
                while ($rowContact = $db->sql_fetchrow($resultContact)) {
                    $contactPerson .= $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'] . ', ';

            /*$pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();*/
                }

                $numrowsInvoiceItem++;
            }
            $YVariable = 145;
            /*$pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'123',1 ,0, 'L', 0);
            $YVariable = $YVariable + 5;
            $pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'456','LR' ,0, 'L', 0);
            $pdf->Ln();*/

            /*if ($count == 0){
                $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
                $pdf->Cell(135, 10, 'Name of Trainee(s):', 'L', 0, 'L', 1);
                $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            }

            $pdf->Ln(7);
            $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();*/

            $total = $unitPrice + $subsidy;
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        }

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $total = number_format($total, 2);
        //$pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 10, '', 'L', 0, 'C', 1);
        $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
        $pdf->Cell(35, 10, '', 'LR', 0, 'R', 1);
        $pdf->Ln(10);

        /*
        $pdf->Cell(155, 8,$contactPerson ,1, 0, 'L', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,'',1,  0, 'R', 1);
        $pdf->Ln(10);
        */

        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(150, 8, 'Remarks');
        $pdf->Ln(4);

        /*$pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();*/

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(67, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, '"MASS EDUCATION INSTITUTE"');
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');

        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        if ($cpCfg['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'pms_invoice';
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

        /* Condition for folder path with regards to local and other sites */
        if ($cpCfg['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintReceipt() {
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

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_code = {$receipt_code}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(7);

                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(7);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : " . $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);

            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();

            $lineItemNumber++; // To increment the line item in receipt

            if ($count == 0){
                $pdf->Cell(135, 10, 'Name of Trainee(s):');
                $pdf->Cell(35, 10, '');
            }

            /* SQL for more than one contact for the receipt*/
            $SQLContact = "
            SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                  ,c.id_card_no
            FROM contact c
            WHERE c.contact_id = {$invoiceItemRec['contact_id']}
            ";
            $resultContact = $db->sql_query($SQLContact);

            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $pdf->Ln(6);
                $contactPerson = $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'];
                $pdf->Cell(135, 10, $contactPerson);
                $pdf->Cell(35, 10, '');
                $pdf->Ln();
            }

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $remarks = $row['remarks'];

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

        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);

        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);

        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $total;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateReceiptForMedia($receipt_id) {
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
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);

                /* Company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'MASS EDUCATION INSTITUTE');
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #06-24 Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : (65) 6336 6546 Fax : (65) 6337 7547');
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(7);

                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(7);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : " . $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);

            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();

            $lineItemNumber++; // To increment the line item in receipt

            if ($count == 0){
                $pdf->Cell(135, 10, 'Name of Trainee(s):');
                $pdf->Cell(35, 10, '');
            }

            /* SQL for more than one contact for the receipt*/
            $contactPerson = '';
            $SQLContact = "
            SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                  ,c.id_card_no
            FROM contact c
            WHERE c.contact_id = {$invoiceItemRec['contact_id']}
            ";
            $resultContact = $db->sql_query($SQLContact);

            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $pdf->Ln(6);
                $contactPerson .= $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'] . ' ';
            }
                $pdf->Cell(135, 10, $contactPerson);
                $pdf->Cell(35, 10, '');
                $pdf->Ln();

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $invoice_amt = $invoiceRec['invoice_amount'];
            $invoice_amt = number_format($invoice_amt, 2);
            $remarks = $row['remarks'];
            $order_id = $row['order_id'];
            $receipt_code = $row['receipt_code'];

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

        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();

        /* Balance to be given */
        $change = $amount_paid - $total;
        $change = number_format($change, 2);
        $change = 0;
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);

        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);

        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $invoice_amt;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'pms_receipt';
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

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintInvoiceIndividual() {
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

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT it.*
              ,i.order_id
              ,i.invoice_date
              ,i.invoice_code
              ,ot.record_id
              ,o.contact_id
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (it.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (order_item ot) ON (o.order_id = ot.order_id)
        WHERE i.invoice_code = {$invoice_code}
          AND ot.invoice_id = {$invoice_code}
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

                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $row['record_id']);
                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$contactRec['address_country']}'");

                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Invoice");
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(21, 20, "Invoice No : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['invoice_code']);
                $pdf->Ln(5);

                $pdf->SetX(157);
                $pdf->SetFont('Arial','B',10);
                $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                $contact_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $contactRec['address_flat']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $contactRec['address_street']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' .  $contactRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);

                $pdf->SetXY(105, 85);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(69, 5, $contactRec['phone'],1 ,0, 'L', 1);
                $pdf->Ln(4);

                $pdf->SetXY(10, 90);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(70, 5, $contact_name,1 ,0, 'L', 1);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                /*$pdf->Cell(69, 5, $row['company_fax'],1 ,0, 'L', 1);*/
                $pdf->Ln(10);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$courseRec['title'],1 ,0, 'C', 0);
                $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $date = $from_date . ' to ' . $to_date;
                $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                $pdf->Ln(12);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $row['qty'],1, 0, 'C', 1);
            $pdf->Cell(135, 10, $row['item_title'],1, 0, 'L', 1);
            $pdf->Cell(35, 10, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Ln(7);

            $subsidy = number_format($row['subsidy'], 2);
            $pdf->Cell(20, 10, $row['qty'], 'L', 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
            $pdf->Ln(10);

            $total += $row['unit_price'] + $subsidy;
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

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'Remarks');

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(66, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');

        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateInvoiceIndividualForMedia($invoice_id) {
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

        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $invoice_code = $invoiceRec['invoice_code'];

        $SQL = "
        SELECT it.*
              ,i.order_id
              ,i.invoice_date
              ,i.invoice_code
              ,ot.record_id
              ,o.contact_id
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (it.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (order_item ot) ON (o.order_id = ot.order_id)
        WHERE i.invoice_code = {$invoice_code}
          AND ot.invoice_id = {$invoice_code}
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

                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $row['record_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$orderRec['cust_address_country_code']}'");

                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'MASS EDUCATION INSTITUTE');
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #06-24 Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : (65) 6336 6546 Fax : (65) 6337 7547');
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Invoice");
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(21, 20, "Invoice No : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['invoice_code']);
                $pdf->Ln(5);

                $pdf->SetX(157);
                $pdf->SetFont('Arial','B',10);
                $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                $contact_name = $orderRec['cust_first_name'] . ' ' . $orderRec['cust_last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $orderRec['cust_address1']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $orderRec['cust_address2']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' .  $orderRec['cust_address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);

                $pdf->SetXY(105, 85);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(69, 5, $orderRec['cust_phone'],1 ,0, 'L', 1);
                $pdf->Ln(4);

                $pdf->SetXY(10, 90);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(70, 5, $contact_name,1 ,0, 'L', 1);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                /*$pdf->Cell(69, 5, $row['company_fax'],1 ,0, 'L', 1);*/
                $pdf->Ln(10);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$courseRec['title'],1 ,0, 'C', 0);
                $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $date = $from_date . ' to ' . $to_date;
                $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                $pdf->Ln(12);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $row['qty'],1, 0, 'C', 1);
            $pdf->Cell(135, 10, $row['item_title'],1, 0, 'L', 1);
            $pdf->Cell(35, 10, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Ln(7);

            $subsidy = number_format($row['subsidy'], 2);
            $pdf->Cell(20, 10, $row['qty'], 'L', 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
            $pdf->Ln(10);

            $total += $row['unit_price'] + $subsidy;
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        }

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'Remarks');

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(66, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, '"MASS EDUCATION INSTITUTE"');
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');

        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'pms_invoice';
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

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintReceiptIndividual() {
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

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_code = {$receipt_code}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $orderRec['order_id']);
                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceRec     = $fn->getRecordRowByID('invoice', 'order_id', $orderRec['order_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);
                $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $invoiceItemRec['contact_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$contactRec['address_country']}'");

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(147, 45);
                $pdf->Cell(50, 20, $code);
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Individual */
                $contact_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $contactRec['address_flat']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $contactRec['address_street']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $contactRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();

                $pdf->Cell(180, 10, 'General');
                $pdf->Ln(5);

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $contact_name);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, 'Course Code: ' . $courseRec['course_code']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(5);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : ". $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);

            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();

            $lineItemNumber++; // To increment the line item in receipt

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $remarks = $row['remarks'];

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

        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);

        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);

        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $total;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateReceiptIndividualForMedia($receipt_id) {
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

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $orderRec['order_id']);
                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceRec     = $fn->getRecordRowByID('invoice', 'order_id', $orderRec['order_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$orderRec['cust_address_country_code']}'");

                /* Company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'MASS EDUCATION INSTITUTE');
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #06-24 Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : (65) 6336 6546 Fax : (65) 6337 7547');
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(147, 45);
                $pdf->Cell(50, 20, $code);
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Individual */
                $contact_name = $orderRec['cust_first_name'] . ' ' . $orderRec['cust_last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $orderRec['cust_address1']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $orderRec['cust_address2']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $orderRec['cust_address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();

                $pdf->Cell(180, 10, 'General');
                $pdf->Ln(5);

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $contact_name);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, 'Course Code: ' . $courseRec['course_code']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(5);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : ". $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);

            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();

            $lineItemNumber++; // To increment the line item in receipt

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $invoice_amt = $invoiceRec['invoice_amount'];
            $invoice_amt = number_format($invoice_amt, 2);
            $remarks = $row['remarks'];
            $order_id = $row['order_id'];
            $receipt_code = $row['receipt_code'];

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

        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();

        /* Balance to be given */
        $change = $amount_paid - $total;
        $change = number_format($change, 2);
        $change = 0;
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);

        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);

        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $invoice_amt;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'pms_receipt';
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

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateRefundFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getGenerateRefundFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $receiptCodes    = $fn->getPostParam('receiptCode', array());
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $receipt_id      = $fn->getReqParam('receipt_id');

        $count = count($receiptCodes);

        //To update refund code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRefundCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $refund_code  = $fn->getSettingsValueByKey("nextRefundCode");

        foreach ($receiptCodes AS $receipt_code) {

            $receiptRec = $fn->getRecordByCondition('receipt', "receipt_code = '{$receipt_code}'");

            $fa = array();
            $fa['amount']         = $amount;
            $fa['receipt_id']     = $receiptRec['receipt_id'];
            $fa['refund_code']    = $refund_code;
            $fa['remarks']        = $remarks;
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');;

            $insertRefundSQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'refund');
            $resultSQL          = $db->sql_query($insertRefundSQL);
            $refund_id          = $db->sql_nextid();
        }

        $receiptRecord = $fn->getRecordByCondition('receipt', "receipt_code = '{$receipt_code}'");
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRecord['order_id']);
        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$receiptRecord['order_id']}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateRefundForMedia($refund_id);
        } else {
            $this->getGenerateRefundForMedia($refund_id);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateRefundFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('receiptCode' , 'Please check receipt code');
        $validate->validateData('amount' , 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintRefund() {
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

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $refund_code = $fn->getReqParam('refund_code');

        $SQL = "
        SELECT r.*
        FROM refund r
        WHERE r.refund_code = {$refund_code}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $receiptRec = $fn->getRecordRowByID('receipt', 'receipt_id', $row['receipt_id']);
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $receiptRec['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRec['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $receiptRec['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Refund Slip");
                $code = 'Refund No : '. $row['refund_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['creation_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Payer");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln(10);
            }

            /* List of invoice items for the refund */
            $invoiceItemSQL ="
            SELECT * FROM invoice_item
            WHERE invoice_id = {$invoiceRec['invoice_id']}
            ";
            $invoiceItemresult  = $db->sql_query($invoiceItemSQL);
            $invoiceItemNumRows = $db->sql_numrows($invoiceItemresult);
            $lineItemNumber = 1;
            while ($rowInvoiceItem = $db->sql_fetchrow($invoiceItemresult)) {

                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $rowInvoiceItem['contact_id']);

                $contact_details = $contactRec['first_name'] . ' ' . $contactRec['last_name'] . '(ID Card NO: ' . $contactRec['id_card_no'] . '), ';
                $program_details = 'Programme Fee, Paid: $' . $rowInvoiceItem['unit_price'];
                $receipt_details = 'Receipt No.:' . $receiptRec['receipt_code'] . ' (Valid)';
                $description = $contact_details . $program_details;

                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
                $pdf->Cell(135, 10, $description);
                $pdf->Cell(35, 10, $rowInvoiceItem['unit_price'], 0, 0, 'R', 1);
                $pdf->Ln(5);

                $pdf->Cell(20, 10, '');
                $pdf->Cell(135, 10, $receipt_details);
                $pdf->Cell(35, 10, '');
                $pdf->Ln(7);

                $lineItemNumber++; // To increment the line item in receipt

                $total += $rowInvoiceItem['unit_price'];
            }

            $amount_paid = $row['amount'];
            $remarks = $row['remarks'];

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

        /* Sub Total */
        $pdf->Ln(7);
        $total = number_format($total, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 8,'All prices stated are inclusive of 7% GST', 'T');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(105, 8, 'Sub Total', 'T', 0, 'R');
        $pdf->Cell(35, 8, $total, 'T', 0, 'R');
        $pdf->Ln();

        /* Refund Amount */
        $pdf->SetX(115);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(50, 8,'TOTAL REFUND AMOUNT',0, 0, 'R');
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Remarks */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln(25);

        /* Signature */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, $cpCfg['printRefundSignatureName'], 'T', 0 , 'C');
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateRefundForMedia($refund_id) {
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

        $refund_code = $fn->getReqParam('refund_code');

        $SQL = "
        SELECT r.*
        FROM refund r
        WHERE r.refund_id = {$refund_id}
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
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $receiptRec = $fn->getRecordRowByID('receipt', 'receipt_id', $row['receipt_id']);
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $receiptRec['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRec['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $receiptRec['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);

                /* Company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'MASS EDUCATION INSTITUTE');
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #06-24 Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : (65) 6336 6546 Fax : (65) 6337 7547');
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Refund Slip");
                $code = 'Refund No : '. $row['refund_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['creation_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Payer");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln(10);
            }

            /* List of invoice items for the refund */
            $invoiceItemSQL ="
            SELECT * FROM invoice_item
            WHERE invoice_id = {$invoiceRec['invoice_id']}
            ";
            $invoiceItemresult  = $db->sql_query($invoiceItemSQL);
            $invoiceItemNumRows = $db->sql_numrows($invoiceItemresult);
            $lineItemNumber = 1;
            while ($rowInvoiceItem = $db->sql_fetchrow($invoiceItemresult)) {

                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $rowInvoiceItem['contact_id']);

                $contact_details = $contactRec['first_name'] . ' ' . $contactRec['last_name'] . '(ID Card NO: ' . $contactRec['id_card_no'] . '), ';
                $program_details = 'Programme Fee, Paid: $' . $rowInvoiceItem['unit_price'];
                $receipt_details = 'Receipt No.:' . $receiptRec['receipt_code'] . ' (Valid)';
                $description = $contact_details . $program_details;

                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
                $pdf->Cell(135, 10, $description);
                $pdf->Cell(35, 10, $rowInvoiceItem['unit_price'], 0, 0, 'R', 1);
                $pdf->Ln(5);

                $pdf->Cell(20, 10, '');
                $pdf->Cell(135, 10, $receipt_details);
                $pdf->Cell(35, 10, '');
                $pdf->Ln(7);

                $lineItemNumber++; // To increment the line item in receipt

                $total += $rowInvoiceItem['unit_price'];
            }

            $amount_paid = $row['amount'];
            $remarks = $row['remarks'];
            $order_id = $receiptRec['order_id'];
            $refund_code = $row['refund_code'];

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

        /* Sub Total */
        $pdf->Ln(7);
        $total = number_format($total, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 8,'All prices stated are inclusive of 7% GST', 'T');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(105, 8, 'Sub Total', 'T', 0, 'R');
        $pdf->Cell(35, 8, $total, 'T', 0, 'R');
        $pdf->Ln();

        /* Refund Amount */
        $pdf->SetX(115);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(50, 8,'TOTAL REFUND AMOUNT',0, 0, 'R');
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Remarks */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln(25);

        /* Creation of media record of the invoice */
        $file_name = 'Refund_REF_' . $refund_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $refund_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'pms_refund';
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

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPopulateReceiptAmountPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_receipt_history_id = $fn->getReqParam('invoice_hist_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_receipt_history_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_receipt_history_id, $s)) !== false){
                unset($s[$key]);
            }
        }
        if(count($_SESSION['selectedInvoiceIds']) == 0){
            return 0;
        }
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);

        $SQLPaid = "
        SELECT SUM(amount) AS invoice_selected_sum
        FROM installment
        WHERE installment_id IN ({$selectInvoiceIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (installment i) ON (irh.installment_id = i.installment_id)
        WHERE i.installment_id IN ({$selectInvoiceIds})
          AND i.invoice_paid_status = 'Partial Payment'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            $receipt_amount = $rowPaid['invoice_selected_sum'];
        } else {
            $receipt_amount = $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }

        return number_format($receipt_amount, 2, '.', '');

    }

    /**
     * Amount population when invoice is clicked for misc receipt. Called from Jquery
     */
    function getPopulateReceiptAmountMiscPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_receipt_history_id = $fn->getReqParam('invoice_hist_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_receipt_history_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_receipt_history_id, $s)) !== false){
                unset($s[$key]);
            }
        }
        if(count($_SESSION['selectedInvoiceIds']) == 0){
            return 0;
        }
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);

        $SQLPaid = "
        SELECT SUM(amount) AS invoice_selected_sum
        FROM installment
        WHERE installment_id IN ({$selectInvoiceIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        return $rowPaid['invoice_selected_sum'];

    }

    /**
     *
     */
    function getPopulateRefundAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_id = $fn->getReqParam('receipt_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if ($checkedVal == 1) {
            $_SESSION['selectedReceiptIds'][] = $receipt_id;
        } else if ($checkedVal == 0) {
            $s = &$_SESSION['selectedReceiptIds'];
            if (($key = array_search($receipt_id, $s)) !== false) {
                unset($s[$key]);
            }
        }

        if (count($_SESSION['selectedReceiptIds']) == 0){
            return 0;
        }

        $selectedReceiptIds = join(',', $_SESSION['selectedReceiptIds']);

        $SQLPaid = "
        SELECT SUM(amount) AS invoice_receipt_sum
        FROM receipt
        WHERE receipt_id IN ({$selectedReceiptIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        return $rowPaid['invoice_receipt_sum'];
    }

    /**
     *
     */
    function getPopulateCreditNoteAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_id = $fn->getReqParam('invoice_id');

        $SQL = "
        SELECT invoice_amount
        FROM invoice
        WHERE invoice_id = {$invoice_id}
        ";
        $result  = $db->sql_query($SQL);
        $row     = $db->sql_fetchrow($result);

        return $row['invoice_amount'];
    }

    /* New functionality for Private Institutions */
    /**
     *
     */
     function getGenerateInvoiceFormPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id= $fn->getReqParam('order_id');

        $numRows = 0;
        $rows    = '';
        $expEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $no_of_installment = $orderRec['no_of_installment'];
        $registration_type = $orderRec['registration_type'];

        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,o.registration_type
              ,o.medical_insurance
              ,o.add_registration_fee
              ,o.full_time
              ,cc.no_of_months
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt = $db->sql_query($SQL);
        $total_invoice_amount = $this->view->getTotalForPvtInst($resultForPvt);

        if($registration_type == 'Only Registration'){
            $pfx = 1 . '_' ;
            $invoice_installment_amount = $fn->getSettingsValueByKey("registrationFeePvt");
            $rows .= "
            <div class='floatbox'>
                <div class='float_right'>
                {$formObj->getTBRow('Invoice Amount', "{$pfx}invoice_amount", $invoice_installment_amount)}
                </div>
                <div>
                {$formObj->getDateRow('Date', "{$pfx}invoice_date")}
                </div>
            </div>
            ";
        }
        else if($registration_type == 'Registration & Enrollment'){
            if($no_of_installment == ''){
                $no_of_installment = 1;
            }

            $invoice_installment_amount = $total_invoice_amount/$no_of_installment;
            //$invoice_installment_amount  = number_format($invoice_installment_amount, 2);

            for($i=$no_of_installment; $i>0; $i--){
                $pfx = $i . '_' ;
                $rows .= "
                <div class='floatbox'>
                    <div class='float_right'>{$formObj->getTBRow('Invoice Amount', "{$pfx}invoice_amount", $invoice_installment_amount)}</div>
                    <div>{$formObj->getDateRow('Date', "{$pfx}invoice_date")}</div>
                </div>
                ";
            }
        }

        $formAction = "index.php?_topRm=finance&module=aceIms_order&_spAction=generateInvoiceFormSubmitPvt&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar invoiceFormPvt' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$rows}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='total_invoice_amount' value='{$total_invoice_amount}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateInvoiceFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getInvoiceFormSubmitPvtValidate()){
            return $validate->getErrorMessageXML();
        }

        $order_id = $fn->getPostParam('order_id');
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $no_of_installment = $orderRec['no_of_installment'];
        $registration_type = $orderRec['registration_type'];
        $medical_insurance = $orderRec['medical_insurance'];
        $add_registration_fee = $orderRec['add_registration_fee'];

        //$invoice_id = 1;
        //$this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);
        //return $validate->getSuccessMessageXML();

        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,o.registration_type
              ,o.medical_insurance
              ,o.add_registration_fee
              ,o.full_time
              ,cc.no_of_months
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQL);
        $total    = $this->view->getTotalForPvtInst($resultForPvt);

        if($no_of_installment == ''){
            $no_of_installment = 1;
        }

        //to create invoice item
        $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'invoiceCodePrefix'");
        $current_year = date('Y');

        $SQLUpdate       = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate    = $db->sql_query($SQLUpdate);
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");
        if($nextInvoiceCode < 10){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode < 100){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode > 99 && $nextInvoiceCode < 1000){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode > 999 && $nextInvoiceCode < 10000){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextInvoiceCode;
        }
        else{
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-' . $nextInvoiceCode;
        }

        $pfx = $no_of_installment . '_' ;
        $invoice_date_main   = $fn->getPostParam("{$pfx}invoice_date");

        $fa = array();
        $fa['order_id']         = $order_id;
        $fa['invoice_code']     = $nextInvoiceCode;
        $fa['invoice_date']     = $invoice_date_main;
        $fa['invoice_amount']   = $total;
        $fa['registration_type']= $registration_type;
        $fa['medical_insurance']= $medical_insurance;
        $fa['add_registration_fee']= $add_registration_fee;
        $fa['status']           = 'Due';
        $fa['creation_date']    = date("Y-m-d H:i:s");

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        if($add_registration_fee == 1){
            $today = date("Y-m-d");
            $fa = array();
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $fn->getSettingsValueByKey("registrationFeePvt");;
            $fa['invoice_date']  = $invoice_date_main;
            $fa['title']         = 'Registration';
            //$histId              = $fn->addRecord($fa, 'invoice_receipt_history');
            $histId              = $fn->addRecord($fa, 'installment');
        }

        $count = 1;
        // To add Installment
        if($registration_type != 'Only Registration'){
            for($i=$no_of_installment; $i>0; $i--){
                $pfx = $i . '_' ;
                $invoice_amount = $fn->getPostParam("{$pfx}invoice_amount");
                $invoice_date   = $fn->getPostParam("{$pfx}invoice_date");
                //Inserting installments to history table
                $fa = array();
                $fa['invoice_id']    = $invoice_id;
                $fa['amount']        = $invoice_amount;
                $fa['invoice_date']  = $invoice_date;
                $fa['title']         = 'Installment' . $count;
                //$histId              = $fn->addRecord($fa, 'invoice_receipt_history');
                $histId              = $fn->addRecord($fa, 'installment');
                $count++;
            }
        }

        $SQL = "
        SELECT oi.*
        FROM order_item oi
        WHERE oi.order_id = ($order_id)
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            // To get discount for related contacts

            $expDiscount = array('condn' => " AND module='aceIms_discount' AND order_id = {$order_id}");
            $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'contact_id', $row['contact_id'], $expDiscount);

            $discount_cost = $orderItemRecDiscount['unit_price'];

            $fa = array();
            $fa['invoice_id']     = $invoice_id;
            $fa['qty']            = $row['qty'];
            $fa['unit_price']     = $row['unit_price'];
            $fa['item_title']     = $row['item_title'];
            $fa['contact_id']     = $row['contact_id'];
            $fa['contact_name']   = $row['contact_name'];
            $fa['discount']       = $discount_cost;

            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_item');
            $resultInsert       = $db->sql_query($insertInvoiceSQL);
        }

        $this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);

        return $validate->getSuccessMessageXML();
        //$cpUtil->redirect("index.php?_topRm=finance&module=aceIms_order&order_id={$order_id}&_action=edit");
    }

    /**
     *
     */
    function getInvoiceFormSubmitPvtValidate() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $total_invoice_amount = $fn->getReqParam('total_invoice_amount');
        $order_id             = $fn->getPostParam('order_id');

        $orderRec             = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $no_of_installment    = $orderRec['no_of_installment'];

        //==================================================================//
        $validate->resetErrorArray();

        foreach($_POST as $fldName => $value){
            if ($fldName != 'order_id'){
                $actualFld = substr($fldName, strpos($fldName, '_') + 1 , strlen($fldName));
                if ($value == '' && $actualFld == 'invoice_amount'){
                    $validate->validateData($fldName, 'Please enter the amount');
                } else if ($value == '' && $actualFld == 'invoice_date'){
                    $validate->validateData($fldName, 'Please select the date');
                }
            }
        }

        $updated_total_invoice_amount = 0;
        $pfx = $no_of_installment . '_' ;
        for($i= $no_of_installment; $i>0; $i--) {
            $invoice_amount = $fn->getPostParam("{$pfx}invoice_amount");
            $updated_total_invoice_amount += $invoice_amount;
        }

        if ($orderRec['registration_type'] == 'Registration & Enrollment') {
            if ($updated_total_invoice_amount > $total_invoice_amount) {
                $msg = 'Please enter the total amount not greater than ' . $total_invoice_amount;
                $validate->validateData('error_box', $msg);
            } else if ($updated_total_invoice_amount < $total_invoice_amount) {
                $msg = 'Please enter the total amount equal to ' . $total_invoice_amount;
                $validate->validateData('error_box', $msg);
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditInvoiceFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $order_id   = $fn->getReqParam('order_id');
        $invoice_id = $fn->getReqParam('invoice_id');

        if (!$this->getEditInvoiceFormSubmitPvtValidate($invoice_id)){
            return $validate->getErrorMessageXML();
        }

        /*$total    = $this->view->getTotalForPvtInst($resultForPvt);*/

        $fa = array();
        $fa['status']           = 'Due';
        $fa['modification_date']= date("Y-m-d H:i:s");

        $histId = $fn->saveRecord($fa, 'invoice', 'invoice_id', $invoice_id);

        foreach($_POST AS $fldName => $value) {
            $fldArr = explode('__', $fldName);
            if($fldArr[0] == 'invoice_amount') {
                $invoice_amount = $value;
                $installment_id = $fldArr[1];
                $dateFldName = "invoice_date__{$installment_id}";
                $invoice_date   = $_POST[$dateFldName];

                $fa = array();
                $fa['amount']        = $invoice_amount;
                $fa['invoice_date']  = $invoice_date;

                $instId = $fn->saveRecord($fa, 'installment', 'installment_id', $installment_id);
            }
        }

        $SqlMediaDelete = "DELETE FROM media
        WHERE room_name = 'aceIms_invoice'
          AND record_id = {$invoice_id}
          AND record_type = 'attachment'
        ";
        $resultMediaDelete = $db->sql_query($SqlMediaDelete);

        $this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);

        return $validate->getSuccessMessageXML();
        //$cpUtil->redirect("index.php?_topRm=finance&module=aceIms_order&order_id={$order_id}&_action=edit");
    }

    /**
     *
     */
    function getEditInvoiceFormSubmitPvtValidate($invoice_id) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();

        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);

        $invoice_amount = 0;
        foreach($_POST as $fldName => $value){
            $fldArr = explode('__', $fldName);

            if ($value == '' && $fldArr[0] == 'invoice_amount'){
                $validate->validateData($fldName, 'Please enter the amount');
            } else if ($value == '' && $fldArr[0] == 'invoice_date'){
                $validate->validateData($fldName, 'Please select the date');
            }

            if($fldArr[0] == 'invoice_amount') {
                $installment_id = $fldArr[1];
                $invoice_amount += $value;
            }
        }

        $invoice_amount = round($invoice_amount, 2);
        if($invoiceRec['add_registration_fee'] == 1){
            $invoiceRec['invoice_amount'] = $invoiceRec['invoice_amount'] +
            $fn->getSettingsValueByKey("registrationFeePvt");;
        }

        if ($invoice_amount > $invoiceRec['invoice_amount']) {
            $msg = 'Total entered amount exceeds total invoice amount.<br> Invoice amount entered =  ' . $invoice_amount . '. Total invoice amount ' . $invoiceRec['invoice_amount'];
            $validate->validateData('error_box', $msg);
        } else if ($invoice_amount < $invoiceRec['invoice_amount']) {
            $msg = 'Total entered amount is below total invoice amount.<br> Invoice amount entered =  ' . $invoice_amount . '. Total invoice amount ' . $invoiceRec['invoice_amount'];
            $validate->validateData('error_box', $msg);
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getGenerateInvoiceForMediaPvt($invoice_id, $order_id) {
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

        $sqlAppend = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        /* To be amended later */
        $registration_fee = '';
        if($cpCfg['m.aceIms.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = '
            ,o.registration_type
            ,o.medical_insurance
            ,o.add_registration_fee
            ,o.full_time
            ,cc.no_of_months
            ';
        }

        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQLPvt);
        $resultForPvt1 = $db->sql_query($SQLPvt);
        $total    = $this->view->getTotalForPvtInst($resultForPvt);
        $netTotal = $this->view->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');

        $expDiscount = array('condn' => " AND module='aceIms_discount'");
        $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id',
        $order_id, $expDiscount);
        $discountTotal = '';
        $discountPer   = '';

        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

        $registration_type    = $orderRec['registration_type'];
        $add_registration_fee = $orderRec['add_registration_fee'];

        if($orderRec['medical_insurance'] == 1){
            $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");
            $netTotal = $netTotal + $medical_insurance_value;
        }

        if($orderItemRecDiscount['unit_price'] > 0){
            $discountPer   = $orderItemRecDiscount['unit_price'];
            $discountTotal = ($netTotal *  $discountPer)/100;
            $discountTotal = round($discountTotal, 2);
        }

        if($add_registration_fee == 1){
            $add_registration_fee = $fn->getSettingsValueByKey("registrationFeePvt");
        }

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
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
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $pdf->Image('images/logo-print.jpg',10,5,45);

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY(80,1);
            $pdf->Cell(30, 20, $cpCfg['addressFlatPvt']);
            $pdf->Cell(30, 20, $cpCfg['addressStreetPvt']);
            $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
            $pdf->Ln(5);
            $pdf->SetX(94);
            $pdf->Cell(28, 20, 'Tel : ' . $cpCfg['contactNoPvt']);
            $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
            $pdf->Ln(5);
            $pdf->SetX(104);
            $pdf->Cell(50, 20, $cpCfg['printCompanyWebsitePvt']);
            $pdf->Ln(15);

            /* Amendment */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');
            $pdf->Ln(20);

            $pdf->SetX(10);
            $registration_data = 'Registration No: ' . $contactRec['registration_no'];
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(25, 5, $registration_data);
            $pdf->SetX(125);
            $invoice_data = 'Invoice Number: ' . $row['invoice_code'];
            $pdf->Cell(175, 5, $invoice_data);
            $pdf->Ln();

            $pdf->SetXY(10, 54);
            $date = $fn->getCPDate($row['invoice_date'], 'dS F Y');
            $invoice_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $invoice_date);
            $pdf->Ln(6);

            $pdf->SetX(10);
            $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $student_name = 'Student Name: ' . $student_full_name;
            $pdf->Cell(50, 20, $student_name);
            $pdf->Ln(6);

            $pdf->SetX(10);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(27, 20, 'Course Enrolled: ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(100, 20, $courseRec['title']);
            $pdf->SetX(125);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(23, 20, 'Course Code: ');
            $pdf->Cell(50, 20, $courseRec['course_code']);
            $pdf->Ln(6);

            $pdf->SetXY(10, 80);
            $pdf->SetFont('Arial','',10);
            $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
            $from_date_format = 'Commencement Date: ' . $from_date;
            $pdf->Cell(25, 5, $from_date_format);
            $pdf->SetXY(125, 80);
            $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
            $to_date_format = 'End Date: ' . $to_date;
            $pdf->Cell(175, 5, $to_date_format);
            $pdf->Ln();

            $pdf->SetXY(10, 79);
            $pdf->SetFont('Arial','',10);
            $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
            $pdf->Cell(50, 20, $course_title);
            $pdf->Ln();

            $pdf->SetX(10);
            $pdf->SetFont('Arial','B', 13);
            $pdf->Cell(190, 7, 'Details of Payment', 1);
            $pdf->Ln(7);

            $pdf->SetFont('Arial','B', 10);
            $pdf->Cell(100, 8, "Description of Items",1);
            $pdf->Cell(55, 8, "No of Subject / Scheme",1);
            $pdf->Cell(35, 8, "S$ Amount",1, 0, 'R');
            $pdf->Ln(8);

            if ($add_registration_fee == '' || $add_registration_fee == 0 ){
                $add_registration_fee = '-';
            }

            $pdf->SetY(114);
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '1. Registration fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(35, 8, $add_registration_fee , 'R', 0, 'R');
            $pdf->Ln(8);

            /* To show no of subjects */
            $SQLSubject = "SELECT count(module) AS no_of_subjects
            FROM order_item
            WHERE order_id = {$row['order_id']}
            AND module = 'aceIms_subject'
            AND item_title != 'Science Lab'
            ";
            $resultSubject = $db->sql_query($SQLSubject);
            $rowSubject = $db->sql_fetchrow($resultSubject);

            $no_of_subjects = '';
            if ($rowSubject['no_of_subjects'] == 1){
                $no_of_subjects = '[ ' . $rowSubject['no_of_subjects'] . ' Subject ]';
            } else if ($rowSubject['no_of_subjects'] > 1){
                $no_of_subjects = '[ ' . $rowSubject['no_of_subjects'] . ' Subjects ]';
            }

            $rowSubjectTotal = '-';
            if ($netTotal > 1){
                $rowSubjectTotal = $netTotal;
            }

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '2. Course fees', 'LR');
            $pdf->Cell(55, 8, $no_of_subjects, 'LR', 0, 'C');
            $pdf->Cell(35, 8, number_format($rowSubjectTotal,2), 'R', 0, 'R');
            $pdf->Ln(4);

            $pdf->SetFont('Arial','', 7);
            $courseFeesInc = '(inclusive of material fees, internal exam fees & science practical lab fees)';
            $courseFeesInc ='';
            $pdf->Cell(100, 8, $courseFeesInc, 'LR');
            $pdf->Cell(55, 8, '', 'LR');
            $pdf->Cell(35, 8, '', 'R', 0, 'R');
            $pdf->Ln(5);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, 'Less', 'LR');
            $pdf->Cell(55, 8, '', 'LR');
            $pdf->Cell(35, 8, '', 'R');
            $pdf->Ln(8);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '3. Discount/School Grant', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(35, 8, $discountTotal, 'R', 0, 'R');
            $pdf->Ln(8);

            if($add_registration_fee != '-'){
                //$total += $add_registration_fee;
            }

            $pdf->Cell(100, 8, 'Total course fees payable(2-3)', 'LRB');
            $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
            $pdf->Cell(35, 8, number_format($total,2), 'RB', 0, 'R');
            $pdf->Ln(40);

            $total = '';
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        }

        //=========================INSTALLMENT======================================== //
        $SQL = "
        SELECT i.*
        FROM invoice_receipt_history i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $SQL = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id}
        ORDER BY i.installment_id
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            if($count == 1){
                $pdf->SetXY(10, 165);
                $pdf->SetFont('Arial','B', 13);
                $pdf->Cell(190, 7, 'Payment Advice', 1);
                $pdf->Ln(7);

                $pdf->SetFont('Arial','B', 10);
                $pdf->Cell(100, 8, "Installment Schedules",1);
                $pdf->Cell(55, 8, "Date Due" ,1);
                $pdf->Cell(35, 8, "S$ Amount" ,1, 0, 'R');
                $pdf->Ln(8);
            }

            if ($registration_fee == ''){
                $registration_fee = '-';
            }

            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            if($row['title'] == 'Registration'){
                $invoice_date = '';
            }
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8,$row['title'] ,1);
            $pdf->Cell(55, 8, $invoice_date ,1);
            $pdf->Cell(35, 8, number_format($row['amount'],2),1 ,0, 'R');
            $pdf->Ln(8);

            $count++;
        }
        //===================END OF TABLE========================================== //

        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(190, 8, 'This is a computer-generated Invoice. No signature is required');
        $pdf->Ln(15);

        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, 'Kindly make the above mentioned payment by due date to avoid late fee charges.');
        $pdf->Ln(5);

        $pdf->Cell(130, 5, 'Please refer to our student handbook and website for refund/withdrawal/transfer policies');
        $pdf->Ln(5);

        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(5);

        /* Creation of media record of the invoice */
        $file_name = $invoice_code .'.pdf';

        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'aceIms_invoice';
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
    function getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present) {
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

        $sqlAppend = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;
        $material_fee = '';
        $assesment_fee = '';
        $science_lab_fee = '';
        $medical_insurance_value = '';

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        /* To be amended later */
        $registration_fee = '';
        if($cpCfg['m.aceIms.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = '
            ,o.registration_type
            ,o.medical_insurance
            ,o.add_registration_fee
            ,o.full_time
            ,cc.no_of_months
            ';
        }

        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQLPvt);
        $resultForPvt1 = $db->sql_query($SQLPvt);
        $total    = $this->view->getTotalForPvtInst($resultForPvt);
        $netTotal = $this->view->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');

        $expDiscount = array('condn' => " AND module='aceIms_discount'");
        $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id',
        $order_id, $expDiscount);
        $discountTotal = '';
        $discountPer   = '';

        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

        $registration_type    = $orderRec['registration_type'];
        $add_registration_fee = number_format($orderRec['add_registration_fee'], 2);
        $no_of_installment    = $orderRec['no_of_installment'];
        $full_time            = $orderRec['full_time'];

        if($orderRec['medical_insurance'] == 1){
            $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");
            $netTotal = $netTotal + $medical_insurance_value;
        }

        if($orderItemRecDiscount['unit_price'] > 0){
            //$discountPer   = number_format($orderItemRecDiscount['unit_price']);
            //$discountTotal = ($netTotal *  $discountPer)/100;
            $discountTotal  = ($netTotal * $orderItemRecDiscount['unit_price'])/100;
            $discountTotal  = round($discountTotal, 2);
        }

        $unitPrice = '-';
        if($registration_type == 'Only Registration'){
            $registration_fee = $fn->getSettingsValueByKey("registrationFeePvt");
            //$netTotal = '-';
            $discountTotal = '-';
            $total = $registration_fee;
        }

        if($add_registration_fee == 1){
            $add_registration_fee = $fn->getSettingsValueByKey("registrationFeePvt");
            $add_registration_fee = number_format($add_registration_fee, 2);
        }

        /* To show no of subjects */
        $SQLSubject = "
        SELECT count(module) AS no_of_subjects
        FROM order_item
        WHERE order_id = {$order_id}
        AND module = 'aceIms_subject'
        AND item_title != 'Science Lab'
        ";
        $resultSubject  = $db->sql_query($SQLSubject);
        $rowSubject     = $db->sql_fetchrow($resultSubject);
        $no_of_subjects = $rowSubject['no_of_subjects'];
        if($no_of_subjects > 4){
            $no_of_subjects = '[' . $no_of_subjects . ' Subjects' .']';
        }

        if($no_of_subjects == 1){
            $no_of_subjects = '[' . $no_of_subjects . ' Subject' .']';
        }


        $SQLInstlmt = "
        SELECT count(amount) as first_installment_paid
        FROM installment
        WHERE invoice_id = {$invoice_id}
            AND invoice_paid_status = 'Paid'
        ";
        $resultInstlmt          = $db->sql_query($SQLInstlmt);
        $rowInstlmt             = $db->sql_fetchrow($resultInstlmt);
        $first_installment_paid = $rowInstlmt['first_installment_paid'];

        $SQLPaidHistory = "
        SELECT
        (SELECT SUM(amount) FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            AND invoice_paid_status IS NULL
            ) as invoice_balance_amount
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE receipt_id = {$receipt_id}
              AND title != 'Registration'
            ) as last_invoice_paid
        ,(SELECT unit_price FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            AND item_title = 'Science Lab'
            ) as science_lab_fees
        ";
        $SQLPaidHistory = "
        SELECT
        (SELECT SUM(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            ) as total_invoice_amount
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            ) as total_invoice_paid
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE receipt_id = {$receipt_id}
            ) as last_invoice_paid
        ,(SELECT unit_price FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            AND item_title = 'Science Lab'
            ) as science_lab_fees
        ";
        $resultPaidHistory    = $db->sql_query($SQLPaidHistory);
        $rowPaidHistory       = $db->sql_fetchrow($resultPaidHistory);
        $last_invoice_paid    = $rowPaidHistory['last_invoice_paid'];
        $science_lab_fees     = $rowPaidHistory['science_lab_fees'];
        //$invoice_balance_amount  = $rowPaidHistory['invoice_balance_amount'];
        //$invoice_balance_amount_before_receipt = $invoice_balance_amount +  $last_invoice_paid ;
        $invoice_balance_amount_before_receipt =
        $rowPaidHistory['total_invoice_amount'] -
        $rowPaidHistory['total_invoice_paid'] +
        $rowPaidHistory['last_invoice_paid'];

        $amount_outstanding = $rowPaidHistory['total_invoice_amount'] -
        $rowPaidHistory['total_invoice_paid'];

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
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
        //===================================HEADER=================================== //
        $pdf->SetFont('Arial','',10);
        $row = $db->sql_fetchrow($result);
        $mode_of_payment = $row['mode_of_payment'];
        $cheque_no       = $row['cheque_no'];
        $bank            = $row['bank_name'];
        $issued_by       = $row['issued_by'];
        $coi_no          = $row['coi_no'];
        $approval_code   = $row['approval_code'];
        if($full_time == 1){
            $material_fee   = 200;
            $assesment_fee  = 200;
        }
        else{
            $material_fee   = 50;
            $assesment_fee  = 50;
        }
        if ($courseRec['course_type'] == 'Short Term') {
            $material_fee   = '';
            $assesment_fee  = '';
        }
        $science_lab_fee = $science_lab_fees;

        $netTotal = $netTotal - $science_lab_fee - $material_fee - $assesment_fee - $medical_insurance_value;

        if($science_lab_fee != ''){
            $science_lab_fee = number_format($science_lab_fee);
        }

        $pdf->Image('images/logo-print.jpg',10,5,45);

        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(80,1);
        $pdf->Cell(30, 20, $cpCfg['addressFlatPvt']);
        $pdf->Cell(30, 20, $cpCfg['addressStreetPvt']);
        $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
        $pdf->Ln(5);
        $pdf->SetX(94);
        $pdf->Cell(28, 20, 'Tel : (65)' . $cpCfg['contactNoPvt']);
        $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
        $pdf->Ln(5);
        $pdf->SetX(104);
        $pdf->Cell(50, 20, $cpCfg['printCompanyWebsitePvt']);
        $pdf->Ln(15);

        $pdf->Line(10,33,195,33);

        /* Amendment */
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY(100, 35);
        $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');
        $pdf->Ln(17);

        $pdf->SetX(10);
        $registration_data = 'Registration No: ' . $contactRec['registration_no'];
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(25, 5, $registration_data);

        $pdf->SetFont('Arial','',10);
        $pdf->SetX(129);
        $receipt_code = 'Receipt Number: ' . $row['receipt_code'];
        $pdf->Cell(175, 5, $receipt_code);
        $pdf->Ln();

        $pdf->SetXY(10, 50);
        $date = $fn->getCPDate($row['date'], 'dS F Y');
        $receipt_date = 'Date: ' . $date;
        $pdf->Cell(50, 20, $receipt_date);
        $pdf->Ln(6);

        $pdf->SetX(10);
        $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
        $student_name = 'Student Name: ' . $student_full_name;
        $pdf->Cell(50, 20, $student_name);
        $pdf->Ln(6);

        $pdf->SetX(10);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(27, 20, 'Course Enrolled: ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(100, 20, $courseRec['title']);
        $pdf->SetX(129);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(23, 20, 'Course Code: ');
        $pdf->Cell(50, 20, $courseRec['course_code']);
        $pdf->Ln(6);

        $pdf->SetXY(10, 75);
        $pdf->SetFont('Arial','',10);
        $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
        $from_date_format = 'Commencement Date: ' . $from_date;
        $pdf->Cell(25, 5, $from_date_format);
        $pdf->SetXY(129, 75);
        $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
        $to_date_format = 'End Date: ' . $to_date;
        $pdf->Cell(175, 5, $to_date_format);
        $pdf->Ln();

        $pdf->SetXY(10, 78);
        $pdf->SetFont('Arial','',10);
        $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
        $pdf->Cell(50, 10, $course_title);
        $pdf->Ln();
        //===================================FIRST TABLE============================= //

        $pdf->SetX(10);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Details of Payment', 1);
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "No of Subject / Scheme",1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        //$pdf->Cell(35, 8, "S$ Amount",1, 0, 'R');
        $pdf->Ln(8);

        if ($add_registration_fee == '' || $add_registration_fee == 0 ){
            $add_registration_fee = '-';
        }
        //if there is first receipt already generated then we need to make all the values to (-) for the first table in pdf.
        if($first_receipt_present > 0){
            $material_fee   = '-';
            $assesment_fee  = '-';
            $science_lab_fee = '-';
            $add_registration_fee = '-';
            $medical_insurance_value = '-';
            $discountTotal = '-';
            $netTotal = $invoice_balance_amount_before_receipt;
            $total    = $invoice_balance_amount_before_receipt;
        }
        else{
            if($material_fee){
                $material_fee = number_format($material_fee,2);
            }
            if($assesment_fee){
                $assesment_fee = number_format($assesment_fee,2);
            }

            if($science_lab_fee != ''){
                $science_lab_fee = number_format($science_lab_fee,2);
            }
            else{
                $science_lab_fee = '-';
            }

            if($medical_insurance_value != '' && $medical_insurance_value != '-'){
                $medical_insurance_value = number_format($medical_insurance_value,2);
            }
            else{
                $medical_insurance_value = '-';
            }

            if($discountTotal != '' && $discountTotal != '-' ){
                $discountTotal = number_format($discountTotal,2);
            }
            else{
                $discountTotal = '-';
            }

        }

        $pdf->SetFont('Arial','', 10);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '1. Registration fees', 'LR');
        } else {
            $pdf->Cell(100, 8, '1. Application fees', 'LR');
        }
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $add_registration_fee, 'R', 0, 'R');
        //$pdf->Cell(35, 8, $add_registration_fee, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '2. Course fees', 'LR');
        $pdf->Cell(55, 8, $no_of_subjects, 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, number_format($netTotal,2), 'R', 0, 'R');
        $pdf->Ln(6);

        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '3. Material fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $material_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $material_fee, 'R', 0, 'R');
            $pdf->Ln(6);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '4. Continual assessment & internal Exam fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $assesment_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8,  $assesment_fee , 'R', 0, 'R');
            $pdf->Ln(6);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '5. Science Lab fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $science_lab_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $science_lab_fee, 'R', 0, 'R');
            $pdf->Ln(6);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '6. Medical Insurance fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $medical_insurance_value, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $medical_insurance_value, 'R', 0, 'R');
            $pdf->Ln(6);
        }

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Less', 'LR');
        $pdf->Cell(55, 8, '', 'LR');
        $pdf->Cell(35, 8, '', 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '7. School Grant', 'LR');
        } else {
            $pdf->Cell(100, 8, '3. School Grant / Discount', 'LR');
        }
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $discountTotal, 'R', 0, 'R');
        //$pdf->Cell(35, 8, $discountTotal, 'R', 0, 'R');
        $pdf->Ln(6);

        if($add_registration_fee != '-'){
            //$total += $add_registration_fee;
        }
        $total = number_format($total, 2);
        if($registration_type == 'Only Registration'){
            $courseFeetext = 'Total Application fees payable';
        }
        else{
            $courseFeetext = 'Total course fees payable';
        }

        if($no_of_installment == 1){
            $installmenttext = '';
        }
        else{
            $installmenttext =  '(' . $no_of_installment . ' installments)';
        }

        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, 'Total course fees payable(item 2 to 6)', 'LRB');
        } else {
            $pdf->Cell(100, 8, $courseFeetext, 'LRB');
        }

        $pdf->Cell(55, 8, $installmenttext, 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $total, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $total, 'RB', 0, 'R');
        $pdf->Ln(12);

        $count++;
        $receipt_code = $row['receipt_code'];

        //=========================SECOND TABLE=================================== //

        $next_invoice_date = '';
        $expInvoice = array('condn' => " AND invoice_paid_status IS NULL");
        $invoiceHist = $fn->getRecordRowByID('installment', 'invoice_id',
        $invoice_id, $expInvoice);

        $next_invoice_date = $fn->getCPDate($invoiceHist['invoice_date'], 'd/m/Y');

        $SQL = "
        SELECT i.*

        ,(SELECT COUNT(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            AND (invoice_paid_status = 'Paid' || invoice_paid_status = 'Partial Payment')
            AND title != 'Registration'
            ) as total_no_paid_installment

        ,(SELECT COUNT(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title != 'Registration'
            ) as no_of_installment

        ,(SELECT amount FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title = 'Installment1'
            ) as course_total_after_installment

        ,(SELECT SUM(ir.amount) FROM invoice_receipt_history ir
            LEFT JOIN `installment` ins ON (ir.installment_id = ins.installment_id)
            WHERE ir.receipt_id = {$receipt_id}
            AND ins.title != 'Registration'
            ) as invoice_amount_paid

        ,(SELECT ir.amount FROM invoice_receipt_history ir
            LEFT JOIN `installment` ins ON (ir.installment_id = ins.installment_id)
            WHERE ir.receipt_id = {$receipt_id}
            AND ins.title = 'Registration'
            ) as registration_paid_now

        ,(SELECT amount FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title = 'Registration'
            ) as registration_applicable

        FROM invoice_receipt_history i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        $rowInvoice = $db->sql_fetchrow($result);

        $invoice_amount_paid       = $rowInvoice['invoice_amount_paid'];
        //$invoice_amount_paid = round($invoice_amount_paid, 3);

        $total_no_paid_installment = $rowInvoice['total_no_paid_installment'];
        $installment_no            = $rowInvoice['total_no_paid_installment'] ;
        //$installment_no            += 1;
        $registration_paid_now     = $rowInvoice['registration_paid_now'] ;
        $registration_applicable   = $rowInvoice['registration_applicable'] ;

        $pdf->SetFont('Arial','',10);
        $count = 1;
        //$row = $db->sql_fetchrow($result);
        $pdf->SetXY(10, 168);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Payment Received', 1);
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "Installments/due date" ,1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        //$pdf->Cell(35, 8, "S$ Amount" ,1, 0, 'R');
        $pdf->Ln(8);

        if ($registration_paid_now == ''){
            $registration_paid_now = '-';
        }
        else{
            $registration_paid_now     = round($registration_paid_now, 3);
            $registration_paid_now     = number_format($registration_paid_now, 2);
        }
        $registration_waived = '';
        if ($registration_applicable == ''){
            $registration_waived = 'Waived';
        }

        if ($registration_paid_now != '' && $invoice_amount_paid == ''){
            $invoice_amount_paid = $registration_paid_now;
            $invoice_amount_paid = '';
        }

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total course fees', 'LRB');
        $pdf->Cell(55, 8, '[' . $installment_no . '/' . $rowInvoice['no_of_installment'] .']', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        if($invoice_amount_paid != ''){
            $pdf->Cell(20, 8, number_format($invoice_amount_paid,2), 'RB', 0, 'R');
        }
        else{
            $pdf->Cell(20, 8, '', 'RB', 0, 'R');
        }
        $pdf->Ln(8);

        $pdf->Cell(100, 8, 'Application fees', 'LR');
        $pdf->Cell(55, 8, $registration_waived, 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $registration_paid_now, 'R', 0, 'R');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','', 7);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '(Non-refundable & not protected under FPS)', 'LRB');
        } else {
            $pdf->Cell(100, 8, '', 'LRB');
        }
        $pdf->Cell(55, 8,'', 'LRB', 0, 'C');
        $pdf->Cell(35, 8, '', 'RB', 0, 'R');
        $pdf->Ln(8);

        $total_paid = $invoice_amount_paid + $registration_paid_now;
        $total_paid = number_format($total_paid, 2);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total Amount Paid', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $total_paid, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $total_paid, 'RB', 0, 'R');
        $pdf->Ln(8);

        if($amount_outstanding != ''){
            $next_payment = 'Next payment on ' . $next_invoice_date;
            $amount_outstanding = number_format($amount_outstanding,2);
        }
        else{
            $next_payment = '';
            $amount_outstanding = '-';
        }

        $pdf->Cell(100, 8, 'Amount Outstanding', 'LRB');
        $pdf->Cell(55, 8, $next_payment , 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $amount_outstanding, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $amount_outstanding, 'RB', 0, 'R');
        $pdf->Ln(8);

        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, 'Certificate of Insurance(COI) NO:', 'LRB');
            $pdf->Cell(55, 8, $coi_no, 'LRB', 0, 'C');
            $pdf->Cell(35, 8, '', 'RB', 0, 'R');
            $pdf->Ln(8);
        }

        //================================ END OF TABLE===========================
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(50, 8, 'Mode of Payment : ');
        $pdf->SetX(110);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        //$pdf->Cell(13, 8, '[] Nets');
        //$pdf->Cell(20, 8, '[] Cheque');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Cheque No : ' . $bank .' '. $cheque_no);
        $pdf->Ln(5);
        $pdf->SetX(110);
        $pdf->Cell(13, 8, '[] Visa');
        $pdf->Cell(20, 8, '[] Mastercard');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Approval Code : ' . $approval_code);
        $pdf->Ln(5);


        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'This is a computer-generated Receipt. No signature is required');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, $cpCfg['printCompanyNamePvt'] . '  issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        $pdf->Cell(130, 5, 'Please refer to our student handbook and website for refund/withdrawal/transfer policies');
        $pdf->Ln(4);

        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = $receipt_code .'.pdf';

        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';
        $outputFileName = $outputPath . '/'. $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");

        /* Code for deleting old receipt during edit of receipt */
        $SQLReceipt = "
        SELECT * FROM media
        WHERE record_id = {$receipt_id}
        ";
        $resultReceipt  = $db->sql_query($SQLReceipt);
        $numRowsReceipt = $db->sql_numrows($resultReceipt);
		if ($numRows > 0){
            $SQLDelete = "DELETE FROM media WHERE record_id = {$receipt_id}";
            $resultDelete = $db->sql_query($SQLDelete);
		}

        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'aceIms_receipt';
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
    function getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges) {
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

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;
        $material_fee = '';
        $assesment_fee = '';
        $science_lab_fee = '';
        $medical_insurance_value = '';

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
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
        //===================================HEADER=================================== //
        $pdf->SetFont('Arial','',10);
        $row = $db->sql_fetchrow($result);
        $mode_of_payment = $row['mode_of_payment'];
        $cheque_no       = $row['cheque_no'];
        $bank            = $row['bank_name'];
        $issued_by       = $row['issued_by'];
        $coi_no          = $row['coi_no'];
        $approval_code   = $row['approval_code'];

        $pdf->Image('images/logo-print.jpg',10,5,45);

        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(80,1);
        $pdf->Cell(24, 20, $cpCfg['addressFlatPvt']);
        $pdf->Cell(28, 20, $cpCfg['addressStreetPvt']);
        $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
        $pdf->Ln(5);
        $pdf->SetX(94);
        $pdf->Cell(26, 20, 'Tel : (65)' . $cpCfg['contactNoPvt']);
        $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
        $pdf->Ln(5);
        $pdf->SetX(104);
        $pdf->Cell(50, 20, $cpCfg['printCompanyWebsitePvt']);
        $pdf->Ln(15);

        $pdf->Line(10,33,195,33);

        /* Amendment */
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY(100, 35);
        $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');
        $pdf->Ln(17);

        $pdf->SetX(10);
        $registration_data = 'Registration No: ' . $contactRec['registration_no'];
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(25, 5, $registration_data);

        $pdf->SetFont('Arial','',10);
        $pdf->SetX(129);
        $receipt_code = 'Receipt Number: ' . $row['receipt_code'];
        $pdf->Cell(175, 5, $receipt_code);
        $pdf->Ln();

        $pdf->SetXY(10, 50);
        $date = $fn->getCPDate($row['date'], 'dS F Y');
        $receipt_date = 'Date: ' . $date;
        $pdf->Cell(50, 20, $receipt_date);
        $pdf->Ln(6);

        $pdf->SetX(10);
        $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
        $student_name = 'Student Name: ' . $student_full_name;
        $pdf->Cell(50, 20, $student_name);
        $pdf->Ln(6);

        $pdf->SetX(10);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(27, 20, 'Course Enrolled: ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(100, 20, $courseRec['title']);
        $pdf->SetX(129);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(23, 20, 'Course Code: ');
        $pdf->Cell(50, 20, $courseRec['course_code']);
        $pdf->Ln(6);

        $pdf->SetXY(10, 75);
        $pdf->SetFont('Arial','',10);
        $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
        $from_date_format = 'Commencement Date: ' . $from_date;
        $pdf->Cell(25, 5, $from_date_format);
        $pdf->SetXY(129, 75);
        $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
        $to_date_format = 'End Date: ' . $to_date;
        $pdf->Cell(175, 5, $to_date_format);
        $pdf->Ln();

        $pdf->SetXY(10, 78);
        $pdf->SetFont('Arial','',10);
        $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
        $pdf->Cell(50, 10, $course_title);
        $pdf->Ln();
        //===================================FIRST TABLE============================= //

        $pdf->SetX(10);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Details of Payment', 1);
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Miscellaneous Items",1);
        $pdf->Cell(55, 8, "No of Subject / Scheme",1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        $pdf->Ln(8);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '1. ' . $cpCfg['printLateChargeFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $late_fee, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '2. ' . $cpCfg['printModuleSubjectChangeFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $change_fee, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '3. ' . $cpCfg['printReviewExamResultFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $review_fee, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '4. ' . $cpCfg['printNSDefermentFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $deferment_fees, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '5. ' . $cpCfg['printCreditCardChargeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $service_fees, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '6. ' . $cpCfg['printOtherFeeTxt'], 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $other_charges, 'RB', 0, 'R');
        $pdf->Ln(6);

        $count++;
        $receipt_code = $row['receipt_code'];

        //=========================SECOND TABLE=================================== //
        $pdf->SetFont('Arial','',10);
        $count = 1;
        $pdf->SetXY(10, 168);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Payment Received', 1);
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "Installments/due date" ,1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        $pdf->Ln(8);

        $total_fees = $late_fee + $change_fee + $review_fee + $deferment_fees + $service_fees + $other_charges;

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total fees', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, number_format($total_fees,2), 'RB', 0, 'R');
        $pdf->Ln(8);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total Amount Paid', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, number_format($total_fees,2), 'RB', 0, 'R');
        $pdf->Ln(8);

        $pdf->Cell(100, 8, 'Amount Outstanding', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, '-', 'RB', 0, 'R');
        $pdf->Ln(8);

        //================================ END OF TABLE===========================
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(50, 8, 'Mode of Payment : ');
        $pdf->SetX(110);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        //$pdf->Cell(13, 8, '[] Nets');
        //$pdf->Cell(20, 8, '[] Cheque');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Cheque No : ' . $bank .' '. $cheque_no);
        $pdf->Ln(5);
        $pdf->SetX(110);
        $pdf->Cell(13, 8, '[] Visa');
        $pdf->Cell(20, 8, '[] Mastercard');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Approval Code : ' . $approval_code);
        $pdf->Ln(5);


        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Signature:');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'MASS Education issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = $receipt_code .'.pdf';

        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';
        $outputFileName = $outputPath . '/'. $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'aceIms_receipt';
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
     */
    function getGenerateReceiptFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $receipt_type    = $fn->getPostParam('receipt_type');

        if ($receipt_type != 'misc receipt') {
            if (!$this->getGenerateReceiptFormValidatePvt($receipt_type)){
                return $validate->getErrorMessageXML();
            }
        }

        //$invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $invoiceHistId   = $fn->getPostParam('invoiceHistId', array());
        $invoice_id      = $fn->getPostParam('invoice_id');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $order_id        = $fn->getReqParam('order_id');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $bank_name       = $fn->getPostParam('bank_name');
        $receipt_date    = $fn->getPostParam('date');
        //$receipt_code    = $fn->getPostParam('receipt_code');
        $issued_by       = $fn->getPostParam('issued_by');
        $coi_no          = $fn->getPostParam('coi_no');
        $approval_code   = $fn->getPostParam('approval_code');
        $course_type     = $fn->getPostParam('course_type');

        if ($receipt_type == 'misc receipt') {
            $late_fee       = $fn->getPostParam('late_fees');
            $change_fee     = $fn->getPostParam('module_subject_change_fee');
            $review_fee     = $fn->getPostParam('exam_result_review_fee');
            $deferment_fees = $fn->getPostParam('ns_deferment_fees');
            $service_fees   = $fn->getPostParam('credit_card_service_fees');
            $other_charges  = $fn->getPostParam('other_charges');

            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;

            //if ($course_type == 'Short Term') {

            $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
            $current_year = date('Y');

            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

            if($nextReceiptCode < 10){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
            }
            else if($nextReceiptCode < 100){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 99){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 99 && $nextReceiptCode < 1000){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 999 && $nextReceiptCode < 10000){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextReceiptCode;
            }
            else{
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-' . $nextReceiptCode;
            }
            /*} else {
                $fa['receipt_code']   = $receipt_code;
            }*/

            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');

            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $amount;
            $fa['invoice_date']  = $receipt_date;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['created_by']    = $fn->getSessionParam('userName');
            $fa['receipt_type']  = 'misc receipt';
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');

            $this->getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges);
        } else {
            $count = count($invoiceHistId);
            // To check if there is any receipt paid earlier
            $SQLRecptPaid = "
            SELECT count(amount) AS first_receipt
            FROM installment
            WHERE invoice_paid_status = 'Paid'
            AND invoice_id = {$invoice_id}
            ";
            $resultRecptPaid  = $db->sql_query($SQLRecptPaid);
            $rowRecptPaid     = $db->sql_fetchrow($resultRecptPaid);
            $first_receipt_present = $rowRecptPaid['first_receipt'];

            /*
            $receipt_id =  151;
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
            return $validate->getSuccessMessageXML();
            */


            //To update receipt codes
            //$receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;

            //if ($course_type == 'Short Term') {
                //To update receipt code

            if ($receipt_type == 'misc receipt') {
                $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
            } else {
                $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'receiptCodePrefix'");
            }

            $current_year = date('Y');

            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

            if($nextReceiptCode < 10){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
            }
            else if($nextReceiptCode < 100){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 99){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
            }
            else{
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
            }

            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';

            if ($receipt_type == 'misc receipt') {
                $fa['receipt_type']   = $receipt_type;
            }

            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');

            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            $receipt_amount     = $amount;
            $invoice_status_due = '';
            $count = 0;

            // TO Change for Installment
            foreach($invoiceHistId AS $invoiceHistId){
                $invoiceRec = $fn->getRecordRowByID('installment', 'installment_id', $invoiceHistId);
                $invoice_amount = $invoiceRec['amount'];

                if ($invoiceRec['invoice_paid_status'] == 'Paid' ||
                $receipt_amount <= 0){
                    continue;
                }

                $SQLPaid = "
                SELECT SUM(amount) AS prev_sum
                FROM invoice_receipt_history
                WHERE installment_id = '{$invoiceHistId}'
                ";
                $resultPaid = $db->sql_query($SQLPaid);
                $rowPaid    = $db->sql_fetchrow($resultPaid);

                $invoice_amount = $invoice_amount - $rowPaid['prev_sum'];

                $faInv = array();
                $recpInvAmount = 0;
                if ($invoice_amount <= $receipt_amount){
                    $recpInvAmount = $invoice_amount;
                    $faInv['invoice_paid_status'] = 'Paid';
                } else if ($invoice_amount > $receipt_amount){
                    $recpInvAmount = $receipt_amount;
                    $faInv['invoice_paid_status'] = 'Partial Payment';
                }

                $receipt_amount = $receipt_amount - $recpInvAmount;
                $fn->saveRecord($faInv, 'installment', 'installment_id', $invoiceHistId);

                //Inserting receipt id in to history table ( one invoice can have multiple receipts)
                $fa = array();
                $fa['receipt_id']    = $receipt_id;
                $fa['invoice_id']    = $invoice_id;
                $fa['installment_id']= $invoiceHistId;
                $fa['amount']        = $recpInvAmount;
                $fa['invoice_date'] =  $receipt_date;
	            $fa['invoice_paid_status'] = 'Paid';
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']     = $fn->getSessionParam('userName');
                $histId = $fn->addRecord($fa, 'invoice_receipt_history');
             }


            $SQL = "
            SELECT i.*
            FROM installment i
            WHERE i.invoice_id = {$invoice_id}
            AND (i.invoice_paid_status != 'Paid'
                || i.invoice_paid_status = ''
                || i.invoice_paid_status IS NULL
                )
            ";
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
            $faInv = array();
            if ($numRows > 0){
                $faInv['status'] = 'Partial Payment';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            else{
                $faInv['status'] = 'Paid';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            //To create PDF related to receipt and save it in media
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormValidatePvt($receipt_type) {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount        = $fn->getPostParam('amount');
        $invoiceHistId = $fn->getPostParam('invoiceHistId', array());

        $validate->resetErrorArray();
        if ($receipt_type != 'misc receipt') {
            $validate->validateData('invoiceHistId' , 'Please check any one of the invoice');
        }

        $installment_array = $this->getSeparationOfInvoiceCodes($invoiceHistId);
        if ($invoiceHistId != ''){
            /* Finding total invoice amount of selected invoices */
            $SQL = "
            SELECT SUM(amount) as invoice_sum
            FROM installment
            WHERE installment_id IN ($installment_array)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            /* Finding total amount paid earlier of selected invoices */
            $SQLPaid = "
            SELECT SUM(amount) as prev_sum
            FROM invoice_receipt_history
            WHERE installment_id IN (
                SELECT installment_id
                FROM installment
                WHERE installment_id IN ($installment_array)
                )
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $invoice_amount = $total_invoice_amount - $prev_sum;

            $total_receipt_amount = $amount;
            if($total_receipt_amount > $invoice_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $invoice_amount . ' in amount for chosen invoices';
            }
        }

        $validate->validateData('mode_of_payment' , 'Please choose mode of payment');
        $validate->validateData('amount' , 'Please enter the amount');
        //$validate->validateData('mode_of_payment' , 'Please select mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPopulateMiscTotalAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $late_fees          = $fn->getReqParam('late_fees');
        $change_fees        = $fn->getReqParam('change_fees');
        $review_fees        = $fn->getReqParam('review_fees');
        $deferment_fees     = $fn->getReqParam('deferment_fees');
        $credit_card_fees   = $fn->getReqParam('credit_card_fees');
        $other_fees         = $fn->getReqParam('other_fees');

        return $total_misc_amount = $late_fees + $change_fees + $review_fees + $deferment_fees + $credit_card_fees + $other_fees;
    }

    /**
     */
    function getEditReceiptFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        /*if (!$this->getGenerateReceiptFormValidatePvt()){
            return $validate->getErrorMessageXML();
        }*/

        $amount          = $fn->getPostParam('receipt_Amount');
        $receipt_date    = $fn->getPostParam('date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $issued_by       = $fn->getPostParam('issued_by');
        $approval_code   = $fn->getPostParam('approval_code');
        $remarks         = $fn->getPostParam('remarks');
        $receipt_id      = $fn->getReqParam('receipt_id');
        $order_id        = $fn->getReqParam('order_id');
        $invoice_id      = $fn->getReqParam('invoice_id');

        /* How to save invoice id
        $invoiceHistId   = $fn->getPostParam('invoiceHistId', array());

        $count = count($invoiceHistId);*/

        //To update the existing receipt record with input values made in edit receipt.
        $fa = array();
        $fa['amount']         = $amount;
        $fa['date']           = $receipt_date;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['issued_by']      = $issued_by;
        $fa['approval_code']  = $approval_code;
        $fa['remarks']        = $remarks;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']     = $fn->getSessionParam('userName');

        $whereCondition = "WHERE receipt_id = {$receipt_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'receipt', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);

        $receipt_amount = $amount;

        /* Deleting the previously paid records in invoice receipt history related to this receipt
        $SQLDelete = "
        DELETE FROM invoice_receipt_history WHERE receipt_id = {$receipt_id}";
        $resultDelete = $db->sql_query($SQLDelete);*/

        /* Setting the amount & date empty for the previously paid records in invoice receipt history related to this receipt */
        $SQLIRHUpdate = "
        UPDATE invoice_receipt_history
        SET amount = ''
        AND invoice_date = ''
        WHERE receipt_id = {$receipt_id}";
        $resultIRHUpdate = $db->sql_query($SQLIRHUpdate);

        /* Setting the status to empty for all installments related to this receipt */
        $SQLUpdate = "
        UPDATE  installment i
        LEFT JOIN (invoice_receipt_history irh) ON (i.installment_id = irh.installment_id)
        SET i.invoice_paid_status = ''
        WHERE i.invoice_id = {$invoice_id}
          AND irh.receipt_id = {$receipt_id}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLOverall = "
        SELECT i.amount
              ,i.installment_id
              ,irh.installment_id
              ,irh.invoice_receipt_history_id
        FROM installment i
        LEFT JOIN (invoice_receipt_history irh) ON (i.installment_id = irh.installment_id)
        WHERE i.invoice_id = {$invoice_id}
          AND irh.receipt_id = {$receipt_id}
          ORDER BY i.installment_id
        ";
        $resultOverall = $db->sql_query($SQLOverall);

        while ($rowOverall = $db->sql_fetchrow($resultOverall)) {
            if ($receipt_amount > 0){

                //To get the previously paid amount for the isntallment from inv hisotry table
                $SQLInstPayment = "
                SELECT SUM(irechist.amount) AS previous_paid_amount
                FROM invoice_receipt_history irechist
                WHERE irechist.installment_id = {$rowOverall['installment_id']}
                ";
                $resultInstPayment = $db->sql_query($SQLInstPayment);
                $rowInstPayment = $db->sql_fetchrow($resultInstPayment);

                if ($rowInstPayment['previous_paid_amount'] > 0) {
                    $installment_amount = $rowOverall['amount'] - $rowInstPayment['previous_paid_amount'];
                } else {
                    $installment_amount = $rowOverall['amount'];
                }

                if ($receipt_amount >= $installment_amount) {
                    $faInst['invoice_paid_status'] = 'Paid';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowOverall['installment_id']);
                } else if ($receipt_amount > 0) {
                    $installment_amount = $receipt_amount;
                    $faInst['invoice_paid_status'] = 'Partial Payment';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowOverall['installment_id']);
                }

                if ($receipt_amount > 0) {
                    $fa = array();
                    $fa['amount']            = $installment_amount;
                    $fa['invoice_date']      = $receipt_date;
                    $fa['modification_date'] = date("Y-m-d H:i:s");
                    $fa['modified_by']       = $fn->getSessionParam('userName');

                    $histId = $fn->saveRecord($fa, 'invoice_receipt_history', 'invoice_receipt_history_id', $rowOverall['invoice_receipt_history_id']);
                }

                $receipt_amount = $receipt_amount - $installment_amount;
            }
        }

        //If balance amount($receipt_amount) is greater than 0 and there is no invoice history record,
        // we are running the below code to create inv history record.
        $SQLInst = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id}
          AND i.invoice_paid_status IS NULL OR i.invoice_paid_status = ''
        ORDER BY i.installment_id ASC
        ";
        $resultInst = $db->sql_query($SQLInst);

        while ($rowInst = $db->sql_fetchrow($resultInst)) {

            if ($receipt_amount) {
                $installment_amount = $rowInst['amount'];
                if ($receipt_amount >= $installment_amount) {
                    $faInst['invoice_paid_status'] = 'Paid';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowInst['installment_id']);
                } else if ($receipt_amount > 0) {
                    $installment_amount = $receipt_amount;
                    $faInst['invoice_paid_status'] = 'Partial Payment';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowInst['installment_id']);
                }

                $fa = array();
                $fa['receipt_id']    = $receipt_id;
                $fa['invoice_id']    = $invoice_id;
                $fa['installment_id']= $rowInst['installment_id'];
                $fa['amount']        = $installment_amount;
                $fa['invoice_date']  = $receipt_date;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']    = $fn->getSessionParam('userName');

                $histId = $fn->addRecord($fa, 'invoice_receipt_history');

                $receipt_amount = $receipt_amount - $installment_amount;
            }
        }

        //Updating the invoice record status.
        $SQL = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id}
        AND (i.invoice_paid_status != 'Paid'
            || i.invoice_paid_status = ''
            || i.invoice_paid_status IS NULL
            )
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        $faInv = array();
        if ($numRows > 0){
            $faInv['status'] = 'Partial Payment';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
        } else{
            $faInv['status'] = 'Paid';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
        }

        //Check the number of record in receipt related to the order id, to find if the receipt is first receipt or not
        $SQLRecpt = "
        SELECT count(*)
        FROM receipt
        WHERE order_id = {$order_id}
        ";
        $resultRecpt  = $db->sql_query($SQLRecpt);
        $numRows  = $db->sql_numrows($resultRecpt);
        if($numRows == 1) {
            $first_receipt_present = 0;
        } else {
            $first_receipt_present = 1;
        }

        $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);

        return $validate->getSuccessMessageXML();
        //check
    }

    /**
     *
     */
    function getDeleteInvoiceFormPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $invoice_id = $fn->getReqParam('invoice_id');
        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);

        /* Invoice Records */
        $SQLMediaInvoice = "
        DELETE FROM media
        WHERE record_id = {$invoice_id}
          AND room_name = 'aceIms_invoice'
          AND record_type = 'attachment'
        ";
        $resultMediaInvoice = $db->sql_query($SQLMediaInvoice);

        $SQLInvoice = "
        DELETE FROM invoice
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoice = $db->sql_query($SQLInvoice);

        $SQLInvoiceItem = "
        DELETE FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);

        $SQLInstallment = "
        DELETE FROM installment
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInstallment = $db->sql_query($SQLInstallment);

        /* Receipt Records */
        $SQLMediaReceipt = "
        DELETE FROM media
        WHERE record_id IN (
           SELECT receipt_id FROM receipt
           WHERE order_id = {$invoiceRec['order_id']}
          )
          AND room_name = 'aceIms_receipt'
          AND record_type = 'attachment'
        ";
        $resultMediaReceipt = $db->sql_query($SQLMediaReceipt);

        $SQLReceipt = "
        DELETE FROM receipt
        WHERE order_id = {$invoiceRec['order_id']}
        ";
        $resultReceipt = $db->sql_query($SQLReceipt);

        $SQLInvReceiptHist = "
        DELETE FROM invoice_receipt_history
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvReceiptHist = $db->sql_query($SQLInvReceiptHist);

        return $cpUtil->redirect("index.php?_topRm=finance&module=aceIms_order&_action=edit");
    }

    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id     = $fn->getReqParam('order_id');

        $irh = $fn->getRecordByCondition('receipt', "receipt_code = '{$receipt_code}'");

        $sqlIrh = "
        SELECT invoice_id FROM invoice_receipt_history
        WHERE receipt_id = {$irh['receipt_id']}
        ";
        $resultIrh = $db->sql_query($sqlIrh);
        while ($rowIrh = $db->sql_fetchrow($resultIrh)) {
            /* Updating of status in invoice table */
            $SQLInvHist = "
            SELECT *
            FROM invoice_receipt_history
            WHERE invoice_id = {$rowIrh['invoice_id']}
              AND amount > 0
            ";

            $resultInvHist  = $db->sql_query($SQLInvHist);
            $numRowsInvHist = $db->sql_numrows($resultInvHist);
            $rowInvHist     = $db->sql_fetchrow($resultInvHist);

            /* Updating status to due if one record in hist table for the invoice */
            if ($numRowsInvHist > 1) {
                $sqlIn = "
                UPDATE invoice
                SET status = 'Partial Payment'
                WHERE invoice_id = {$rowIrh['invoice_id']}
                ";
                $resultIn = $db->sql_query($sqlIn);
            } else {
                $sqlIn = "
                UPDATE invoice
                SET status = 'Due'
                WHERE invoice_id = {$rowIrh['invoice_id']}
                ";
                $resultIn = $db->sql_query($sqlIn);
            }

            /* Setting of amount to 0 in history table */
            $SqlInvrec = "
            UPDATE invoice_receipt_history
            SET amount = 0
            WHERE receipt_id = {$irh['receipt_id']}
            ";
            $resultInvrec = $db->sql_query($SqlInvrec);
        }

        /* Updating the status of the receipt in receipt table */
        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
        WHERE receipt_code = '{$receipt_code}'
        ";
        $resultRec = $db->sql_query($sqlRec);

        $SQLUpdate = "
        UPDATE `order` SET order_status ='Due'
        WHERE order_id = {$order_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return;
    }

    /**
     *
     */
    function getTotalAmountFromOrderItem($order_id, $course_id, $contact_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $courseRec = $fn->getRecordRowById('course', 'course_id', $course_id);

        $whereCondition = '';
        if ($courseRec['course_type'] == 'Long Term') {
            $whereCondition = " AND oi.module != 'aceIms_course'";
        }

        $sqlOiSum = "
        SELECT SUM(oi.unit_price) AS total_amount_payable
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
          AND oi.contact_id = {$contact_id}
        {$whereCondition}
        ";
        $resultOiSum = $db->sql_query($sqlOiSum);
        $rowOiSum    = $db->sql_fetchrow($resultOiSum);

        return $rowOiSum['total_amount_payable'];
    }
}
