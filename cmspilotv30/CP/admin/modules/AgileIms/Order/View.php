<?
class CP_Admin_Modules_AgileIms_Order_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $currency = strtoupper($row['currency']);

            $sqlOi = "
            SELECT SUM(oi.unit_price) AS total_order_amount
            FROM order_item oi
            WHERE oi.order_id = {$row['order_id']}
            ";                
            $resultOi = $db->sql_query($sqlOi);  
            $rowOi = $db->sql_fetchrow($resultOi);
            
            $orderAmount = $currency.'&nbsp;'. number_format($rowOi['total_order_amount'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['order_id'])}
            {$listObj->getListDataCell($row['contact_type'])}
            {$listObj->getListDataCell($row['cust_first_name'] . ' ' . $row['cust_last_name'])}
            {$listObj->getListDateCell($row['order_date'])}
            {$listObj->getListDataCell($orderAmount, 'txtRight')}
            {$listObj->getListDataCell($row['order_status'])}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Id', 'o.order_id')}
        {$listObj->getListHeaderCell('Order Type', 'contact_type')}
        {$listObj->getListHeaderCell('Contact Name', 'cust_first_name')}
        {$listObj->getListHeaderCell('Order Date', 'o.order_date')}
        {$listObj->getListHeaderCell('Amount', '')}
        {$listObj->getListHeaderCell('Status', 'o.order_status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getDateRow('Order Date', 'order_date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     */
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);
        
        $creation_date = $dateUtil->formatDate($row['creation_date'], 'YYYY-MM-DD');

        $currency = strtoupper($row['currency']);
        
        $order_date = $dateUtil->formatDate($row['order_date'], 'DD MMM YYYY');
        
        $mode_of_payment = '';
        if ($cpCfg['cp.forAgileIms'] == 0) {
            $mode_of_payment = $formObj->getDDRowByVL('Mode of Payment', 'payment_method',  'paymentType', $row['payment_method']);
        }
        
        $fielset1 = "
        {$formObj->getTBRow('Order Type', 'contact_type', $row['contact_type'], $expNoEdit)}
        {$formObj->getTBRow('Order Date', 'order_date', $order_date, $expNoEdit)}
        {$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        {$formObj->getTARow('Remarks', 'memo', $row['memo'])}
        {$mode_of_payment}
        ";
        
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry1 = array('detailValue' => $row['cust_country_name']);
        $expCountry2 = array('detailValue' => $row['shipping_country_name']);
        
        if($row['company_id']){
            $companyFld =" 
            {$formObj->getTBRow('Company Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            ";
        }
        else{
            $companyFld =" 
            {$formObj->getTBRow('Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            ";
        }
        
        $fielset2 = " 
        {$companyFld}
        {$formObj->getTBRow('Email', 'cust_email', $row['cust_email'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'], $expNoEdit)}
        {$formObj->getTBRow('Address 1', 'cust_address1', $row['cust_address1'], $expNoEdit)}
        {$formObj->getTBRow('Address 2', 'cust_address2', $row['cust_address2'], $expNoEdit)}
        {$formObj->getTBRow('Country', 'cust_country_name', $row['cust_country_name'], $expNoEdit)}
        {$formObj->getTBRow('Zip Code', 'cust_address_po_code', $row['cust_address_po_code'], $expNoEdit)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Contact Details', $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $order_date1 = $fn->getReqParam('order_date_1');
        $order_date2 = $fn->getReqParam('order_date_2');
        $order_status   = $fn->getReqParam('order_status');
        $payment_method = $fn->getReqParam('payment_method');

        $dirText = "";

		if ($cpCfg['cp.hasDirectoryMg'] == 1){
	        $business_id = $fn->getReqParam('business_id');
	        $business_contact_id = $fn->getReqParam('business_contact_id');

	        $SQLBusiness = "
	        SELECT b.business_id
	              ,b.business_name
	        FROM business b
	        ORDER BY b.business_name
	        ";

	        $SQLBusinessContact = "
	        SELECT bc.business_contact_id
	              ,CONCAT_WS(' ', bc.first_name, bc.last_name) AS contact_name
	        FROM business_contact bc
	        ORDER BY contact_name
	        ";

			$dirText = "
	        <td class='fieldValue'>
	            <select name='business_id'>
	                <option value=''>Business</option>
	        		{$dbUtil->getDropDownFromSQLCols2($db, $SQLBusiness, $business_id)}
	            </select>
	        </td>

	        <td class='fieldValue'>
	            <select name='business_contact_id'>
	                <option value=''>Contact</option>
	        		{$dbUtil->getDropDownFromSQLCols2($db, $SQLBusinessContact, $business_contact_id)}
	            </select>
	        </td>
	        ";
	    }

        $orgText = "";
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
	        $organization_id = $fn->getReqParam('organization_id');

	        $SQLOrg = "
	        SELECT o.organization_id
	              ,o.name
	        FROM organization o
	        ORDER BY o.name
	        ";

			$orgText = "
	        <td class='fieldValue'>
	            <select name='organization_id'>
	                <option value=''>Organization</option>
	        		{$dbUtil->getDropDownFromSQLCols2($db, $SQLOrg, $organization_id)}
	            </select>
	        </td>
	        ";
        }

        $sqlModeOfPayment = $fn->getValueListSQL('paymentType');

        $mode_of_payment = '';
        if ($cpCfg['cp.forAgileIms'] == 0) {
            $mode_of_payment = "
            <td>
                <select name='payment_method' >
                    <option value=''>Mode of Payment</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $payment_method)}
                </select>
            </td>
            ";
        }
        
        $text = "
        {$dirText}
        {$orgText}
        <td>
        	{$formObj->getDateRangeRow('Creation Date:', 'order_date', $order_date1, $order_date2)}
        </td>

        <td class='fieldValue'>
            <select name='order_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $order_status)}
            </select>
        </td>
        
        {$mode_of_payment}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        
        $SQLCourse = "
        SELECT c.course_type FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$row['order_id']}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse); 
        
        $text = "
        <div><b><u>ENROLLMENT SUMMARY</u></b></div>
        {$this->getOrderItemPortalDisplay($row)}
        {$this->getInvoicePortalDisplay($row)}
        <div class='mt20'></div>
        {$this->getReceiptPortalDisplay($row)}
        ";

        return $text;
    }

    /**
     */
    function getOrderItemPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        
        $SQL = "
        SELECT DISTINCT oi.order_item_id
              ,oi.record_id
              ,oi.qty
              ,oi.unit_price
              ,oi.item_title
              ,oi.invoice_clear_status
              ,oi.module
              ,oi.contact_id
              ,oi.subsidy_paid
              ,oi.invoice_id
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,cont.first_name AS contact_name
              ,o.registration_type 
              ,o.add_registration_fee
              ,o.full_time
              ,o.order_status
              ,i.invoice_code
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        LEFT JOIN invoice i ON (oi.invoice_id = i.invoice_id)
        WHERE oi.order_id = {$row['order_id']}
        ORDER BY oi.order_item_id
        ";
        $result = $db->sql_query($SQL);  

        $rows = "";
        $total_amount = 0;
        while ($rowOrderItem = $db->sql_fetchrow($result)) {            
            $checkBox = '';

            if ($rowOrderItem['module'] == 'agileIms_course') {
                $contactRec  = $fn->getRecordRowById('contact', 'contact_id', $rowOrderItem['contact_id']);
                $contactName = $contactRec['first_name'];
                $type = 'Course';
            } else if ($rowOrderItem['module'] == 'agileIms_discount' || $rowOrderItem['module'] == 'agileIms_subsidy'){
                $type = 'Less Subsidy/Discount';
            } else {
                $type = 'Registration Fees';
            }
            
            if ($rowOrderItem['module'] == 'agileIms_course' || $rowOrderItem['order_status'] == 'Cancelled') {
                if (($rowOrderItem['invoice_id'] != '' && $rowOrderItem['invoice_id'] > 0) || $rowOrderItem['order_status'] == 'Cancelled') {
                    $status = 'DISABLED';
                } else {
                    $status = '';
                }
                
                $tdCheckBox = "
                <td>
                    <input type='checkbox' name='traineeId[]' value='{$rowOrderItem['contact_id']}' $status>
                </td>
                ";
            } else {
                $tdCheckBox = "<td></td>";
            }

            $unit_price_formatted = number_format($rowOrderItem['unit_price'], 2);
            
            if ($row['contact_type'] == 'Indvidual') {
                $urlEnrollment = "index.php?_topRm=main&module=agileIms_contact&_action=edit&contact_id={$rowOrderItem['contact_id']}";
            } else {
                $orderRec  = $fn->getRecordRowById('order', 'order_id', $rowOrderItem['order_id']);
                $urlEnrollment = "index.php?_topRm=main&module=agileIms_company&_action=edit&company_id={$orderRec['company_id']}";
            }

            $rows .= "
            <tr>
                {$tdCheckBox}
                <td><a href='{$urlEnrollment}'><u>{$contactName}</u></a></td>
                <td>{$type}</td>
                <td>{$rowOrderItem['item_title']}</td>
                <td align='right'>{$unit_price_formatted}</td>
                <td>{$rowOrderItem['invoice_code']}</td>
            </tr>
            ";
            $total_amount += $rowOrderItem['unit_price'];
        }
        $total = number_format($total_amount, 2);
        
        $total = "
        <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
            <td class='txtRight' colspan=5>Total : $total</td>
            <td></td>
        </tr>
        ";

        $formAction = "index.php?module=agileIms_order&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $recordCountOverall = $fn->getRecordCount('order_item', "order_id = {$row['order_id']}");
        $recordCountInvGenerated = $fn->getRecordCount('order_item', "order_id = {$row['order_id']} AND invoice_id != ''");
        if ($recordCountOverall == $recordCountInvGenerated){
            $formAction = '';
            $invoiceBtn = "<button  disabled='disabled' class='button mt5 ml5 mb20'>Generate Invoice</button>";
            $invoiceBtn = "<br>";
        } else {
            $invoiceBtn = "<button href='' id='generateInvoice' class='button mt5 ml5 mb20'>Generate Invoice</button>";
        }

        $text = "
        <tr class=''>
        <td>
            <div id='agileIms_company#agileIms_orderLink' class=''>
                <form id='orderItemList' class='cpJqForm' method='post' action='{$formAction}'>
                <table class='thinlist'>
                    <tr style='background-color:#EAEAE8;'>
                        <th>Check/Uncheck</th>
                        <th>Trainee</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th class='txtRight'>Price/Cost</th>
                        <th>Invoice Code</th>
                    </tr>
                    {$rows}
                    {$total}
                </table>
                <input type='hidden' name='order_id' value='{$row['order_id']}' />
                <input type='hidden' name='callbackAfterSuccess' value='cpm.agileIms.order.cbAfterGenerateInvoice' />
                {$invoiceBtn}
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     */
    function getInvoicePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status = $fn->getReqParam('status');
        
        $recordCountInv = $fn->getRecordCount('invoice', "(status = 'Due' OR status = 'Partial Payment') AND order_id = {$row['order_id']}");
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
        if ($recordCountInv){
            $formAction = "index.php?module=agileIms_receipt&_spAction=generateReceiptForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceipt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
        } else {
            $formAction = '';
            $receiptBtn = '';
        }

        $sqlInvoiceStatus = 'SELECT DISTINCT status FROM invoice';
        
        $text = "
        <tr class=''>
        <td>
            <div id='' class='invoiceDisplay'>
                <h2>Invoice(s)</h2>
                <div>
                    {$formObj->getDropDownBySQL('Status', 'status', $sqlInvoiceStatus, '', array('sqlType' => 'OneField'))}
                </div>
            
                <form id='generateReceiptForm' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getInvoiceRecords($row)}
                    </div>
                </form>                 
                {$receiptBtn}
            </div>
        </td>
        </tr>
        ";

        return $text;
    }
    
    /**
     */
    function getInvoiceRecords($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows  = "";
        
        $status   = $fn->getReqParam('status');
        
        $sqlAppend = "";
        if ($status) {
            $sqlAppend .= " AND i.status = '{$status}'";
        }
        
        $SQL = "
        SELECT i.*
               ,(
               SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
               FROM receipt r, invoice_receipt_history invrecpt
               WHERE r.receipt_id = invrecpt.receipt_id
               AND i.invoice_id = invrecpt.invoice_id
               ) AS receipt_codes_history
              ,i.invoice_date
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)
        WHERE o.order_id = {$row['order_id']}
              {$sqlAppend}
          AND i.add_registration_fee IS NULL
        ORDER BY i.invoice_id ASC
        ";
        $result = $db->sql_query($SQL);  
        $total = '';
        $invoice_code = ''; 
        $add_registration_fee = '';
        
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=agileIms_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=agileIms_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=agileIms_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if ($rowInvoice['status'] != 'Cancelled' && $invoice_code != $rowInvoice['invoice_code']) {
                $total += $rowInvoice['invoice_amount'];
            }
            
            if ($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']) {
                $cancelInvoiceLink = '';
                if ($rowInvoice['status'] != 'Cancelled') {
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code={$rowInvoice['invoice_code']}><u>Cancel Invoice</u></a>";
                }

            if ($row['contact_type'] == 'Indvidual') {                
                $reportLink = "index.php?module=agileIms_order&_spAction=printInvoiceForStudent&invoice_id={$rowInvoice['invoice_id']}&showHTML=0";
            }else{
                $reportLink = "index.php?module=agileIms_order&_spAction=printInvoiceForCompany&invoice_id={$rowInvoice['invoice_id']}&showHTML=0";               
            }   
                $invoice_date = $dateUtil->formatDate($rowInvoice['invoice_date'], 'DD MMM YYYY');                
                $invoice_amount = number_format($rowInvoice['invoice_amount'], 2);
                
                if ($rowInvoice['status'] == 'Due' 
                 || $rowInvoice['status'] == '' 
                 || $rowInvoice['status'] == 'Partial Payment'
                ) {
                    $editURL = "index.php?_topRm=finance&module=agileIms_invoice&_spAction=editInvoiceForm&showHTML=0&invoice_id={$rowInvoice['invoice_id']}";
                    $editRow = "<td><a href='{$editURL}' id='editInvoice'><u>Edit</u></a></td>";
                } else {
                    $editRow = "<td></td>";
                }

                $print_invoice = "<a href='{$reportLink}' target='_blank'><u>Print Invoice</u></a>";
                
                $cancelledClass = '';
                if ($rowInvoice['status'] == 'Cancelled') {
                    $cancelledClass = 'highlightClass';
                }

                if ($rowInvoice['modification_date']) {
                    $date = $dateUtil->formatDate($rowInvoice['modification_date'], 'DD-MM-YYYY');
                } else {
                    $date = $dateUtil->formatDate($rowInvoice['creation_date'], 'DD-MM-YYYY');
                }

                if ($rowInvoice['modified_by']) {
                    $name = $rowInvoice['modified_by'];
                } else {
                    $name = $rowInvoice['created_by'];
                }

                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$invoice_amount}</td>
                    <td class='{$cancelledClass}'>{$rowInvoice['status']}</td>
                    <td>{$rowInvoice['receipt_codes_history']}</td>
                    <td><a href='{$reportLink}' target='_blank'><u>Print Invoice</u></a></td>
                    <td>{$cancelInvoiceLink}</td>
                    {$editRow}
                    <td>
                        <a class='viewInvoiceDetails jqui-dialog' href='index.php?module=agileIms_invoice&_spAction=viewInvoiceDetails&showHTML=0&invoice_id={$rowInvoice['invoice_id']}'><u>Detail</u></a>
                    </td>
                    <td>{$date}</td>
                    <td>{$name}</td>
                </tr>
                ";
            }
            $invoice_code = $rowInvoice['invoice_code'];
        }
        
        $total += $add_registration_fee;
        $total = number_format($total, 2);
        $total = "
        <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
            <td colspan=3 class='txtRight'>Total : $total</td>
            <td colspan=8></td>
        </tr>
        ";
        
        $editHeader = '';
        if ($cpCfg['m.agileIms.order.hasEditInvoiceForPvt']){
            $editHeader = "<th>Edit</th>";
        }
                
        $header = "
        <tr style='background-color:#EAEAE8;'>
            <th>Invoice Code</th>
            <th>Invoice Date</th>
            <th>Invoice Amount</th>
            <th>Status</th>
            <th>Receipt Code</th>
            <th>Print</th>
            <th>Cancel</th>
            {$editHeader}
            <th>Detail</th>
            <th>Creation/<br/>Updation Date</th>
            <th>Created/Updated by</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
            {$total}
        </table>
        ";

        return $text;
    }

    /**
     *This Function is used to print the invoice for student which has a type as referrral.
     *
     */
    function getPrintInvoiceForStudentOld() {
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

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Invoice');
        $pdf->SetTitle('Print Invoice');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/

        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();

        $invoice_id = $fn->getReqParam('invoice_id');

            $SQL = "
            SELECT i.*
                  ,c.registration_no
                  ,c.first_name AS student_name
                  ,cc.course_code
                  ,cc.title AS course_name
                  ,cc.award_course
                  ,cc.valid_date_from
                  ,cc.valid_date_to
                  ,o.order_id
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_course') AS course_amount
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_registration') AS registration_amount
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_discount') AS discount_amount
            FROM invoice i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN `invoice_item` invt ON (invt.invoice_id = i.invoice_id)
            LEFT JOIN `contact` c ON (c.contact_id = invt.contact_id)
            LEFT JOIN `course` cc ON (cc.course_id = invt.record_id)
            WHERE i.invoice_id = '{$invoice_id}'
            ORDER BY i.invoice_id
            ";
            $result = $db->sql_query($SQL);
            $invoiceRow = $db->sql_fetchrow($result);
            $numRows  = $db->sql_numrows($result);
            $today = date("d-m-Y");

            $rows = "";
            $notes = '';
            $subtotalvalue = '';
            //============================================================================= //
            $num = '';

            $invoice_date   = $fn->getCPDate($invoiceRow['invoice_date'], 'd-m-Y');
            $commence_date  = $fn->getCPDate($invoiceRow['valid_date_from'], 'd F Y');
            $end_date       = $fn->getCPDate($invoiceRow['valid_date_to'], 'd F Y');

            $pdf->SetFont('Courier','',10);

            $pdf->ln(5);
            $tbl2 = '<table border="0" width="100%">
                        <tr>
                            <td border="0" align="center" width="100%"><font style="font-size:25px; font-weight:bold">INVOICE</font>
                            </td>
                        </tr>
                        <tr>
                            <td width="30%"></td>
                            <td width="40%" align="center">INVOICE NO: INV - '.$invoiceRow['invoice_code'].'</td>
                            <td width="30%" align="right"><b>Date:</b> '.$invoice_date.'</td>
                        </tr>
                    </table>';

            $tbl1 = '<table border="0" width="100%" cellpadding="4">
                        <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                            <td height="25" width="25%" style="border-right:1px white;">&nbsp;Registration No</td>
                            <td width="75%">&nbsp;Student Name</td>
                        </tr>
                        <tr>
                            <td height="25">'.$invoiceRow['registration_no'].'<br/></td>
                            <td>'.$invoiceRow['student_name'].'<br/></td>
                        </tr>
                        <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                            <td height="25" width="25%" style="border-right:1px white;">&nbsp;Course Code</td>
                            <td width="75%" style="border-right:1px white;">&nbsp;Course Enrolled</td>
                        </tr>
                        <tr>
                            <td height="25" width="25%">'.$invoiceRow['course_code'].'<br/></td>
                            <td width="75%">'.$invoiceRow['course_name'].'<br/></td>
                        </tr>
                        <tr>
                            <td height="25" width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;Commencement Date</td>
                            <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">'.$commence_date.'</td>
                            <td width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;End Date</td>
                            <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;border-right:2px solid #DDE4FF;">'.$end_date.'</td>
                        </tr>
                    </table>';

            $subtotalvalue = $invoiceRow['registration_amount'] + $invoiceRow['course_amount'] + $invoiceRow['discount_amount'];

            $discountInvoiceLabel = '';
            $discountInvoiceValue = '';
            if ($invoiceRow['discount_amount'] != ''){
                $discountInvoiceLabel = '<br/><br/> Discount/School Grant(less)';
                $discountInvoiceValue = '<br/><br/>'.$invoiceRow['discount_amount'];
            }

            $tblPayments =  '<table border="1" width="100%" cellpadding="4">
                                <tr bgcolor="#DDE4FF">
                                    <td height="30" style="font-weight:bold;font-size:14pt;">Details of Payment</td>
                                </tr>
                                <tr bgcolor="#DDE4FF">
                                    <td width="78%" style="font-weight:bold;">Description of Items</td>
                                    <td width="22%" align="right" style="font-weight:bold;">S$ Amount</td>
                                </tr>
                                <tr>
                                    <td><br/>Registration fees<br/><br/>Course fees'.$discountInvoiceLabel.'</td>
                                    <td align="right"><br/>'.$invoiceRow['registration_amount'].'<br/><br/>'.$invoiceRow['course_amount'].''.$discountInvoiceValue.'</td>
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td height="25">Total course fees payable</td>
                                    <td align="right">'.number_format($subtotalvalue,2).'</td>
                                </tr>
                            </table>';

            $additionalNotes  = 'Kindly refer the attachment for participant’s detail.';
            $additionalNotes1  =  'Please check your particulars as the certificate will be printed based on above.';
            $additionalNotes2 = 'For any other enquiries regarding this Invoice, please contact us.';

            $paymentSlipText = 'Please detach this portion of the bill to accompany payment.';
            $paymentSlipText1 =  'All cheques should be crossed A/C Payee only and made payable to:'; 
            $paymentSlipText2  = 'HALLMARK SAFETY TRAINING PTE LTD.';

            $dueDateInvoice = $fn->getCPDate($invoiceRow['invoice_due_date'], 'd-m-Y');

            $tblFooter =  '
            <table border="0" width="100%" cellpadding="4">
                <tr style="font-weight:bold;">
                    <td>Notes:</td>
                </tr>
                <tr>
                    <td>'.$invoiceRow['notes'].'</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td>Terms:</td>
                </tr>
                <tr>
                    <td>'.$invoiceRow['invoice_terms'].'</td>
                </tr>
            </table>
            <table border="0" width="100%" cellpadding="2">
                <tr>
                    <td>'.$additionalNotes.'<br/>
                        '.$additionalNotes1.'<br/>
                        '.$additionalNotes2.'<br/><br/>
                        <b>PAYMENT SLIP</b><br/>
                        '.$paymentSlipText.'<br/>
                        '.$paymentSlipText1.'<br/>
                        '.$paymentSlipText2.'
                    </td>
                </tr>
            </table>
            ';

            $tblSummary =  '
            <table border="1" width="100%" cellpadding="4">
                <tr style="font-weight:bold;">
                    <td width="30%">Invoice No.</td>
                    <td width="30%">Due date</td>
                    <td width="40%" align="right">Total amount(No GST)</td>
                </tr>    
                <tr style="font-weight:bold;">
                    <td width="30%">'.$invoiceRow['invoice_code'].'</td>
                    <td width="30%">'.$dueDateInvoice.'</td>
                    <td width="40%" align="right">$'.number_format($subtotalvalue, 2).'</td>
                </tr>
            </table>
            ';

        //$pdf->ln(5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(2);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tblPayments, true, false, false, false, '');
        $pdf->writeHTML($tblFooter, true, false, false, false, '');
        $pdf->ln(3);
        $pdf->writeHTML($tblSummary, true, false, false, false, '');
        $pdf->Output('print_invoice.pdf', 'I');
    }

    /**
     *This Function is used to print the invoice for Company which has a type as referrral.
     *
     */
    function getPrintInvoiceForCompanyOld() {
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

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Invoice');
        $pdf->SetTitle('Print Invoice');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/

        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();

        $invoice_id = $fn->getReqParam('invoice_id');

            $SQL = "
            SELECT i.*
                  ,com.company_id
                  ,com.title As company_name
                  ,com.address1
                  ,com.address2
                  ,com.email
                  ,com.address_po_code
                  ,com.salutation As company_contact_salutation
                  ,com.contact_name As company_contact_name
                  ,(SELECT gc.name FROM geo_country gc
                    WHERE gc.country_code = com.address_country_code)
                    As address_country
                  ,com.address_city
                  ,c.registration_no
                  ,c.first_name AS contact_name
                  ,c.id_card_no AS contact_id_card_no
                  ,cc.course_code
                  ,cc.title AS course_name
                  ,cc.award_course
                  ,cc.valid_date_from
                  ,cc.valid_date_to
                  ,o.order_id
                  ,cc.course_id
                  ,cc.price
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_course') AS course_amount
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_registration') AS registration_amount
                  ,(SELECT SUM(unit_price)
                    FROM invoice_item
                    WHERE invoice_id = i.invoice_id
                    AND module ='agileIms_discount') AS discount_amount
            FROM invoice i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN `company` com ON (com.company_id = o.company_id)
            LEFT JOIN `invoice_item` invt ON (invt.invoice_id = i.invoice_id)
            LEFT JOIN `contact` c ON (c.contact_id = invt.contact_id)
            LEFT JOIN `course` cc ON (cc.course_id = invt.record_id)
            WHERE i.invoice_id = '{$invoice_id}'
            ORDER BY i.invoice_id
            ";
            $result = $db->sql_query($SQL);
            $invoiceRow = $db->sql_fetchrow($result);
            $today = date("d-m-Y");

            $rows = "";
            $notes = '';
            $subtotalvalue = '';
            //============================================================================= //
            $num = '';

            $invoice_date   = $fn->getCPDate($invoiceRow['invoice_date'], 'd-m-Y');
            $commence_date  = $fn->getCPDate($invoiceRow['valid_date_from'], 'd F Y');
            $end_date       = $fn->getCPDate($invoiceRow['valid_date_to'], 'd F Y');

            $pdf->SetFont('Courier','',10);

            $companyContactPerson = '';
            $companyName = '';
            $companyAddress1 = '';
            $companyAddress2 = '';
            $companyAddressPoCode = '';
            $companyAddressCity = '';
            $companyAddressCountry = '';
            $companyEmail = '';
            $tblterms = '';             

            $contact_list = '';
            $contact_registration_list = '';

            if ($invoiceRow['company_contact_name'] != '') {
                $companyContactPerson = $invoiceRow['company_contact_salutation'] . ' ' . $invoiceRow['company_contact_name'].'<br/>';
            }    

            if ($invoiceRow['company_name'] != '') {
                $companyName = $invoiceRow['company_name'].'<br/>';
            }    
            
            if ($invoiceRow['address1'] != '') {
                $companyAddress1 = $invoiceRow['address1'].'<br/>';
            } 

            if ($invoiceRow['address2'] != '') {
                $companyAddress2 = $invoiceRow['address2'].'<br/>'; 
            }

            if ($invoiceRow['address_city'] != '') {
                $companyAddressCity = $invoiceRow['address_city'].'<br/>'; 

            }

            if ($invoiceRow['address_po_code'] != '') {
                $companyAddressPoCode = ' - '.$invoiceRow['address_po_code'].'<br/>'; 

            }

            if ($invoiceRow['address_country'] != '') {
                $companyAddressCountry = $invoiceRow['address_country'];                              
            }

            if ($invoiceRow['email'] != '') {
                $companyEmail =  'Email: '.$invoiceRow['email'];                              
            }

            //$pdf->ln(5);
            $tbl1 = '<table border="0" width="100%">
                        <tr>
                            <td border="0" align="center" width="100%"><font style="font-size:25px; font-weight:bold">INVOICE</font>
                            </td>
                        </tr>
                        <tr>
                            <td width="30%"></td>
                            <td width="40%" align="center">INVOICE NO: INV - '.$invoiceRow['invoice_code'].'</td>
                            <td width="30%" align="right"><b>Date:</b> '.$invoice_date.'</td>
                        </tr>
                    </table>';

            $tbl2 = '<table border="0" width="100%" cellpadding="4">
                        <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                            <td height="25" width="50%" style="border-right:1px white;">&nbsp;Attention to</td>
                        </tr>
                        <tr>
                            <td>'.$companyContactPerson.''.$companyName.''.$companyAddress1.''.$companyAddress2.''.$companyAddressCity.''.$companyAddressCountry.''.$companyAddressPoCode.''.$companyEmail.'</td>
                        </tr>
                    </table>';

            $tbl3 = '<table border="0" width="100%" cellpadding="4">
                        <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                            <td height="25" width="25%" style="border-right:1px white;">&nbsp;Course Code</td>
                            <td width="75%" style="border-right:1px white;">&nbsp;Course Enrolled</td>
                        </tr>
                        <tr>
                            <td height="25" width="25%">'.$invoiceRow['course_code'].'</td>
                            <td width="75%">'.$invoiceRow['course_name'].'</td>
                        </tr>
                    </table>';

            $tbl4 = '<table border="0" width="100%" cellpadding="4">
                        <tr>
                            <td height="25" width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;Commencement Date</td>
                            <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">'.$commence_date.'</td>
                            <td width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;End Date</td>
                            <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;border-right:2px solid #DDE4FF;">'.$end_date.'</td>
                        </tr>
                    </table>';

            $discountInvoiceLabel = '';
            $discountInvoiceValue = '';
            if ($invoiceRow['discount_amount'] != ''){
                $discountInvoiceLabel = '<br/><br/> Discount/School Grant(less)';
                $discountInvoiceValue = '<br/><br/>'.$invoiceRow['discount_amount'];
            }

            $sqlContact = "
            SELECT DISTINCT c.contact_id
                  ,c.first_name AS contact_name
                  ,c.id_card_no AS contact_id_card_no
            FROM contact c
            LEFT JOIN (invoice_item it) ON (c.contact_id = it.contact_id)
            WHERE it.module = 'agileIms_course'
              AND it.invoice_id = {$invoiceRow['invoice_id']}
            ";
            $resultContact = $db->sql_query($sqlContact);
            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $contactName   = $rowContact['contact_name'] .'&nbsp; - &nbsp;'. $rowContact['contact_id_card_no'];
                $contact_list  .= $contactName.'<br/>';
            }    

            $student_rec_for_invoice = $fn->getRecordCount('invoice_item', "invoice_id = {$invoiceRow['invoice_id']} AND module = 'agileIms_course'");

            $price = $invoiceRow['price'] * $student_rec_for_invoice;
            $subtotalvalue = $price + $invoiceRow['discount_amount'];
            $tblPayments =  '
            <table border="1" width="100%" cellpadding="4">
                <tr bgcolor="#DDE4FF">
                    <td height="30" style="font-weight:bold;font-size:14pt;">Details of Payment</td>
                </tr>
                <tr bgcolor="#DDE4FF">
                    <td width="78%" style="font-weight:bold;">Description of Items</td>
                    <td width="22%" align="right" style="font-weight:bold;">S$ Amount</td>
                </tr>
                <tr>
                    <td><br/> Total Course Fees for('.$student_rec_for_invoice.') Trainee(s) ($'.$invoiceRow['price'].' * '.$student_rec_for_invoice.'Pax)'.$discountInvoiceLabel.'<br/><br/><b>Name of the Participant :</b><br/>'.$contact_list.''.$contact_registration_list.'
                    </td>
                    <td align="right">'.number_format($price,2).''.$discountInvoiceValue.'</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td height="">Total Amount (NO GST)</td>
                    <td align="right">$'.number_format($subtotalvalue,2).'</td>
                </tr>
            </table>';

            $additionalNotes  = 'Kindly refer the attachment for participant’s detail.';
            $additionalNotes1  =  'Please check your particulars as the certificate will be printed based on above.';
            $additionalNotes2 = 'For any other enquiries regarding this Invoice, please contact us.';

            $paymentSlipText = 'Please detach this portion of the bill to accompany payment.';
            $paymentSlipText1 =  'All cheques should be crossed A/C Payee only and made payable to:'; 
            $paymentSlipText2  = 'HALLMARK SAFETY TRAINING PTE LTD.';

            $tblnotes = '
            <table border="0" width="100%" cellpadding="2">
                <tr style="font-weight:bold;">
                    <td>Terms :</td>      
                </tr>
                <tr>
                    <td>'.$invoiceRow['invoice_terms'].'</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td>Notes :</td>      
                </tr>
                <tr>
                    <td>'.$invoiceRow['notes'].'<br/><br/></td>
                </tr>
            </table>
            <table border="0" width="100%" cellpadding="2">
                <tr>
                    <td>'.$additionalNotes.'<br/>
                        '.$additionalNotes1.'<br/>
                        '.$additionalNotes2.'<br/><br/>
                        <b>PAYMENT SLIP</b><br/>
                        '.$paymentSlipText.'<br/>
                        '.$paymentSlipText1.'<br/>
                        '.$paymentSlipText2.'
                    </td>
                </tr>
            </table>
            ';  

            $dueDateInvoice = $fn->getCPDate($invoiceRow['invoice_due_date'], 'd-m-Y');

            $tbl5 =   '<table border="1" width="100%" cellpadding="4">
                            <tr style="font-weight:bold;">
                                <td width="30%">Invoice No.</td>
                                <td width="30%">Due date</td>
                                <td width="40%" align="right">Total amount(No GST)</td>
                            </tr>    
                            <tr style="font-weight:bold;">
                                <td width="30%">'.$invoiceRow['invoice_code'].'</td>
                                <td width="30%">'.$dueDateInvoice.'</td>
                                <td width="40%" align="right">$'.number_format($subtotalvalue, 2).'</td>
                            </tr>    
                       </table>';                                             

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tblPayments, true, false, false, false, '');
        $pdf->writeHTML($tblnotes, true, false, false, false, '');
        $pdf->ln(3);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->Output('print_invoice.pdf', 'I');
    }

    /**
     * This Function is used to print Invoice for Student
     */
    function getPrintInvoiceForStudent() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Invoice');
        $pdf->SetTitle('Print Invoice');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();

        $invoice_id = $fn->getReqParam('invoice_id');

        $SQL = "
        SELECT i.*
              ,(SELECT gc.name FROM geo_country gc
                WHERE gc.country_code = i.cust_address_country_code)
              AS address_country
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result     = $db->sql_query($SQL);
        $invoiceRow = $db->sql_fetchrow($result);

        $sqlCourse = "
        SELECT item_title AS course_name
              ,course_start_date
              ,course_end_date
              ,course_code
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
          AND module = 'agileIms_course'
        ";
        $resultCourse = $db->sql_query($sqlCourse);
        $courseRow    = $db->sql_fetchrow($resultCourse);
        //============================================================================= //
        $invoice_date   = $fn->getCPDate($invoiceRow['invoice_date'], 'd-m-Y');
        $commence_date  = $fn->getCPDate($courseRow['course_start_date'], 'd F Y');
        $end_date       = $fn->getCPDate($courseRow['course_end_date'], 'd F Y');

        $pdf->SetFont('Courier','',10);
        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td border="0" align="center" width="100%"><font style="font-size:25px; font-weight:bold">INVOICE</font>
                </td>
            </tr>
            <tr>
                <td width="30%"></td>
                <td width="40%" align="center">INVOICE NO: INV - '.$invoiceRow['invoice_code'].'</td>
                <td width="30%" align="right"><b>Date:</b> '.$invoice_date.'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4">
            <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                <td height="25" width="25%" style="border-right:1px white;">&nbsp;Registration No</td>
                <td width="75%">&nbsp;Student Name</td>
            </tr>
            <tr>
                <td height="25">'.$invoiceRow['contact_reg_no'].'<br/></td>
                <td>'.$invoiceRow['cust_first_name'].'<br/></td>
            </tr>
            <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                <td height="25" width="25%" style="border-right:1px white;">&nbsp;Course Code</td>
                <td width="75%" style="border-right:1px white;">&nbsp;Course Enrolled</td>
            </tr>
            <tr>
                <td height="25" width="25%">'.$courseRow['course_code'].'<br/></td>
                <td width="75%">'.$courseRow['course_name'].'<br/></td>
            </tr>
            <tr>
                <td height="25" width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;Commencement Date</td>
                <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">'.$commence_date.'</td>
                <td width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;End Date</td>
                <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;border-right:2px solid #DDE4FF;">'.$end_date.'</td>
            </tr>
        </table>
        ';

        /* Finding total course amount for the invoice */
        $sqlIiCourseAmt = "
        SELECT SUM(unit_price) AS total_course_amt
        FROM invoice_item
        WHERE module = 'agileIms_course'
          AND invoice_id = {$invoice_id}
        ";
        $resultIiCourseAmt = $db->sql_query($sqlIiCourseAmt);
        $rowIiCourseAmt    = $db->sql_fetchrow($resultIiCourseAmt);        
        $price = $rowIiCourseAmt['total_course_amt'];
        
        /* Finding total discount/subsidy amount for the invoice */
        $sqlIiDiscAmt = "
        SELECT SUM(unit_price) AS total_discount_amt
        FROM invoice_item
        WHERE (module = 'agileIms_discount' OR module = 'agileIms_subsidy')
          AND invoice_id = {$invoice_id}
        ";
        $resultIiDiscAmt = $db->sql_query($sqlIiDiscAmt);
        $rowIiDiscAmt    = $db->sql_fetchrow($resultIiDiscAmt);

        /* Displaying Discount row if any discount is given */
        $discountInvoiceLabel = '';
        $discountInvoiceValue = '';
        $total_disc_amt = 0;
        if ($rowIiDiscAmt['total_discount_amt'] < 0){
            $total_disc_amt = $rowIiDiscAmt['total_discount_amt'];
            $discountInvoiceLabel = '<br/><br/> Discount/School Grant(less)';
            $discountInvoiceValue = '<br/><br/>'.number_format($total_disc_amt, 2);
        }

        /* Finding total registration amount for the invoice */
        $sqlIiRegAmt = "
        SELECT SUM(unit_price) AS total_registration_amt
        FROM invoice_item
        WHERE module = 'agileIms_registration'
          AND invoice_id = {$invoice_id}
        ";
        $resultIiRegAmt = $db->sql_query($sqlIiRegAmt);
        $rowIiRegAmt    = $db->sql_fetchrow($resultIiRegAmt);

        /* Displaying Discount row if any discount is given */
        $registrationInvoiceLabel = '';
        $registrationInvoiceValue = '';
        $total_reg_amt = 0;
        if ($rowIiRegAmt['total_registration_amt'] > 0){
            $total_reg_amt = $rowIiRegAmt['total_registration_amt'];
            $registrationInvoiceLabel = '<br/><br/> Registration Fees';
            $registrationInvoiceValue = '<br/><br/>'.number_format($total_reg_amt, 2) .'<br/><br/>';
        }

        /* Total course price - Total discounted price */
        $subtotalvalue = $price + $total_disc_amt + $total_reg_amt;

        $tblPayments =  '
        <table border="1" width="100%" cellpadding="4">
            <tr bgcolor="#DDE4FF">
                <td height="30" style="font-weight:bold;font-size:14pt;">Details of Payment</td>
            </tr>
            <tr bgcolor="#DDE4FF">
                <td width="78%" style="font-weight:bold;">Description of Items</td>
                <td width="22%" align="right" style="font-weight:bold;">S$ Amount</td>
            </tr>
            <tr>
                <td>'. $registrationInvoiceLabel .'<br/><br/>Course Fees'.$discountInvoiceLabel.'</td>
                <td align="right">'.$registrationInvoiceValue. number_format($price,2).''.$discountInvoiceValue.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td height="25">Total course fees payable</td>
                <td align="right">$'.number_format($subtotalvalue, 2).'</td>
            </tr>
        </table>
        ';

        $additionalNotes  = 'Kindly refer the attachment for participant’s detail.';
        $additionalNotes1  =  'Please check your particulars as the certificate will be printed based on above.';
        $additionalNotes2 = 'For any other enquiries regarding this Invoice, please contact us.';

        $paymentSlipText = 'Please detach this portion of the bill to accompany payment.';
        //$paymentSlipText1 =  'All cheques should be crossed A/C Payee only and made payable to:'; 
        //$paymentSlipText2  = 'HALLMARK SAFETY TRAINING PTE LTD.';
        $paymentSlipText1 = '';
        $paymentSlipText2 = '';

        $dueDateInvoice = $fn->getCPDate($invoiceRow['invoice_due_date'], 'd-m-Y');

        $notes = '';
        if ($invoiceRow['notes']) {
            $notes = "
            <tr>
                <td><b>Notes:</b></td>
            </tr>
            <tr>
                <td>{$invoiceRow['notes']}</td>
            </tr>
            ";
        }

        $invoice_terms = '';
        if ($invoiceRow['invoice_terms']) {
            $invoice_terms = "
            <tr>
                <td><b>Terms:</b></td>
            </tr>
            <tr>
                <td>{$invoiceRow['invoice_terms']}</td>
            </tr>
            ";
        }

        $tblFooter =  '
        <table border="0" width="100%" cellpadding="4">'.
            $notes . ' ' . $invoice_terms
        .'</table>
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td>'.$additionalNotes.'<br/>
                    '.$additionalNotes1.'<br/>
                    '.$additionalNotes2.'<br/><br/>
                    <b>PAYMENT SLIP</b><br/>
                    '.$paymentSlipText.'<br/>
                    '.$paymentSlipText1.'<br/>
                    '.$paymentSlipText2.'
                </td>
            </tr>
        </table>
        ';

        $tblSummary =  '
        <table border="1" width="100%" cellpadding="4">
            <tr style="font-weight:bold;">
                <td width="30%">Invoice No.</td>
                <td width="30%">Due date</td>
                <td width="40%" align="right">Total amount(No GST)</td>
            </tr>    
            <tr style="font-weight:bold;">
                <td width="30%">INV - '.$invoiceRow['invoice_code'].'</td>
                <td width="30%">'.$dueDateInvoice.'</td>
                <td width="40%" align="right">$'.number_format($subtotalvalue, 2).'</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tblPayments, true, false, false, false, '');
        $pdf->writeHTML($tblFooter, true, false, false, false, '');
        $pdf->writeHTML($tblSummary, true, false, false, false, '');
        $pdf->Output('print_invoice.pdf', 'I');
    }

    /**
     * This Function is used to print Invoice for Company
     */
    function getPrintInvoiceForCompany() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Invoice');
        $pdf->SetTitle('Print Invoice');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*
        HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) 
        PATH INCLUDE: (admin/lib/headfoot.php)
        */

        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();

        $invoice_id = $fn->getReqParam('invoice_id');

        $SQL = "
        SELECT i.*
              ,(SELECT gc.name FROM geo_country gc
                WHERE gc.country_code = i.cust_address_country_code)
              AS address_country
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result     = $db->sql_query($SQL);
        $invoiceRow = $db->sql_fetchrow($result);

        $sqlCourse = "
        SELECT item_title AS course_name
              ,course_start_date
              ,course_end_date
              ,course_code
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
          AND module = 'agileIms_course'
        ";
        $resultCourse = $db->sql_query($sqlCourse);
        $courseRow    = $db->sql_fetchrow($resultCourse);
        //============================================================================= //
        $invoice_date   = $fn->getCPDate($invoiceRow['invoice_date'], 'd-m-Y');
        $commence_date  = $fn->getCPDate($courseRow['course_start_date'], 'd F Y');
        $end_date       = $fn->getCPDate($courseRow['course_end_date'], 'd F Y');

        $pdf->SetFont('Courier','',10);

        if ($invoiceRow['company_contact_name'] != '') {
            $companyContactPerson = $invoiceRow['company_contact_salutation'] . ' ' . $invoiceRow['company_contact_name'].'<br/>';
        }    

        if ($invoiceRow['cust_first_name'] != '') {
            $companyName = $invoiceRow['cust_first_name'].'<br/>';
        }    
            
        if ($invoiceRow['cust_address1'] != '') {
            $companyAddress1 = $invoiceRow['cust_address1'].'<br/>';
        } 

        if ($invoiceRow['cust_address2'] != '') {
            $companyAddress2 = $invoiceRow['cust_address2'].'<br/>'; 
        }

        if ($invoiceRow['address_country'] != '') {
            $companyAddressCountry = $invoiceRow['address_country'] . ' - ' .$invoiceRow['cust_address_po_code'].'<br/>';                              
        }

        if ($invoiceRow['cust_email'] != '') {
            $companyEmail =  'Email: '.$invoiceRow['cust_email'];                              
        }

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td height="28" align="center" width="100%">
                    <font style="font-size:25px; font-weight:bold">INVOICE</font>
                </td>
            </tr>
            <tr>
                <td width="30%"></td>
                <td width="40%">INVOICE NO: INV - '.$invoiceRow['invoice_code'].'</td>
                <td width="30%"><b>Date:</b> '.$invoice_date.'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4">
            <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                <td height="25" width="50%" style="border-right:1px white;">&nbsp;Attention to</td>
            </tr>
            <tr>
                <td>'.$companyContactPerson.''.$companyName.''.$companyAddress1.''.$companyAddress2.''.$companyAddressCountry.''.$companyEmail.'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4">
            <tr bgcolor="#DDE4FF" style="font-weight:bold;">
                <td height="25" width="25%" style="border-right:1px white;">&nbsp;Course Code</td>
                <td width="75%" style="border-right:1px white;">&nbsp;Course Enrolled</td>
            </tr>
            <tr>
                <td height="25" width="25%">'.$courseRow['course_code'].'</td>
                <td width="75%">'.$courseRow['course_name'].'</td>
            </tr>

            <tr>
                <td height="25" width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;Commencement Date</td>
                <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">'.$commence_date.'</td>
                <td width="25%" bgcolor="#DDE4FF" style="font-weight:bold;border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;">&nbsp;End Date</td>
                <td width="25%" style="border-top:2px solid #DDE4FF;border-bottom:2px solid #DDE4FF;border-right:2px solid #DDE4FF;">'.$end_date.'</td>
            </tr>
        </table>
        ';

        /* Finding students for the selected invoice */
        $sqlContact = "
        SELECT DISTINCT c.contact_id
                ,c.first_name AS contact_name
                ,c.id_card_no AS contact_id_card_no
        FROM contact c
        LEFT JOIN (invoice_item it) ON (c.contact_id = it.contact_id)
        WHERE it.module = 'agileIms_course'
            AND it.invoice_id = {$invoiceRow['invoice_id']}
        ";
        $resultContact  = $db->sql_query($sqlContact);
        $numRowsContact = $db->sql_numrows($resultContact);

        $contact_list = '';
        while ($rowContact = $db->sql_fetchrow($resultContact)) {
            $contactName   = $rowContact['contact_name'] .'&nbsp; - &nbsp;'. $rowContact['contact_id_card_no'];
            $contact_list  .= $contactName.'<br/>';
        }

        /* Finding total course amount for the invoice */
        $sqlIiCourseAmt = "
        SELECT SUM(unit_price) AS total_course_amt
        FROM invoice_item
        WHERE module = 'agileIms_course'
          AND invoice_id = {$invoice_id}
        ";
        $resultIiCourseAmt = $db->sql_query($sqlIiCourseAmt);
        $rowIiCourseAmt    = $db->sql_fetchrow($resultIiCourseAmt);        
        $price = $rowIiCourseAmt['total_course_amt'];
        
        /* Finding total discount/subsidy amount for the invoice */
        $sqlIiDiscAmt = "
        SELECT SUM(unit_price) AS total_discount_amt
        FROM invoice_item
        WHERE (module = 'agileIms_discount' OR module = 'agileIms_subsidy')
          AND invoice_id = {$invoice_id}
        ";
        $resultIiDiscAmt = $db->sql_query($sqlIiDiscAmt);
        $rowIiDiscAmt    = $db->sql_fetchrow($resultIiDiscAmt);

        /* Displaying Discount row if any discount is given */
        $discountInvoiceLabel = '';
        $discountInvoiceValue = '';
        $total_disc_amt = 0;
        if ($rowIiDiscAmt['total_discount_amt'] < 0){
            $total_disc_amt = $rowIiDiscAmt['total_discount_amt'];
            $discountInvoiceLabel = '<br/><br/> Discount/School Grant(less)';
            $discountInvoiceValue = '<br/><br/>'.number_format($total_disc_amt, 2);
        }

        /* Finding total registration amount for the invoice */
        $sqlIiRegAmt = "
        SELECT SUM(unit_price) AS total_registration_amt
        FROM invoice_item
        WHERE module = 'agileIms_registration'
          AND invoice_id = {$invoice_id}
        ";
        $resultIiRegAmt = $db->sql_query($sqlIiRegAmt);
        $rowIiRegAmt    = $db->sql_fetchrow($resultIiRegAmt);

        /* Displaying Discount row if any discount is given */
        $registrationInvoiceLabel = '';
        $registrationInvoiceValue = '';
        $total_reg_amt = 0;
        if ($rowIiRegAmt['total_registration_amt'] > 0){
            $total_reg_amt = $rowIiRegAmt['total_registration_amt'];
            $registrationInvoiceLabel = '<br/><br/> Total Registration Amount';
            $registrationInvoiceValue = '<br/><br/>'.number_format($total_reg_amt, 2) .'<br/><br/>';
        }

        /* Total course price - Total discounted price */
        $subtotalvalue = $price + $total_disc_amt + $total_reg_amt;

        $tblPayments =  '
        <table border="1" width="100%" cellpadding="4">
            <tr bgcolor="#DDE4FF">
                <td height="30" style="font-weight:bold;font-size:14pt;">Details of Payment</td>
            </tr>
            <tr bgcolor="#DDE4FF">
                <td width="78%" style="font-weight:bold;">Description of Items</td>
                <td width="22%" align="right" style="font-weight:bold;">S$ Amount</td>
            </tr>
            <tr>
                <td>'. $registrationInvoiceLabel .'<br/><br/>Total Course Fees for ('.$numRowsContact.') Trainee(s)'.$discountInvoiceLabel.'<br/><br/><b>Name of the Participant :</b><br/>'.$contact_list.'
                </td>
                <td align="right">'.$registrationInvoiceValue. number_format($price,2).''.$discountInvoiceValue.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td>Total Amount (NO GST)</td>
                <td align="right">$'.number_format($subtotalvalue, 2).'</td>
            </tr>
        </table>
        ';

        $notes = '';
        if ($invoiceRow['notes']) {
            $notes = "
            <tr>
                <td><b>Notes:</b></td>
            </tr>
            <tr>
                <td>{$invoiceRow['notes']}</td>
            </tr>
            ";
        }

        $invoice_terms = '';
        if ($invoiceRow['invoice_terms']) {
            $invoice_terms = "
            <tr>
                <td><b>Terms:</b></td>
            </tr>
            <tr>
                <td>{$invoiceRow['invoice_terms']}</td>
            </tr>
            ";
        }

        $tblTermsNotes = '
        <table border="0" width="100%" cellpadding="4">'.
            $notes . ' ' . $invoice_terms
        .'</table>
        ';  

        $additionalNotes  = 'Kindly refer the attachment for participant’s detail.';
        $additionalNotes1 = 'Please check your particulars as the certificate will be printed based on above.';
        $additionalNotes2 = 'For any other enquiries regarding this Invoice, please contact us.';

        $paymentSlipText  = 'Please detach this portion of the bill to accompany payment.';
        //$paymentSlipText1 = 'All cheques should be crossed A/C Payee only and made payable to:'; 
        //$paymentSlipText2 = 'HALLMARK SAFETY TRAINING PTE LTD.';
        $paymentSlipText1 = ''; 
        $paymentSlipText2 = '';

        $tblInvoiceDetails = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td>'.$additionalNotes.'<br/>
                    '.$additionalNotes1.'<br/>
                    '.$additionalNotes2.'<br/><br/>
                    <b>PAYMENT SLIP</b><br/>
                    '.$paymentSlipText.'<br/>
                    '.$paymentSlipText1.'<br/>
                    '.$paymentSlipText2.'
                </td>
            </tr>
        </table>
        ';

        $dueDateInvoice = $fn->getCPDate($invoiceRow['invoice_due_date'], 'd-m-Y');
        
        $tbl5 = '
        <table border="1" width="100%" cellpadding="4">
            <tr style="font-weight:bold;">
                <td width="30%">Invoice No.</td>
                <td width="30%">Due date</td>
                <td width="40%" align="right">Total amount(No GST)</td>
            </tr>    
            <tr style="font-weight:bold;">
                <td width="30%">'.$invoiceRow['invoice_code'].'</td>
                <td width="30%">'.$dueDateInvoice.'</td>
                <td width="40%" align="right">$'.number_format($subtotalvalue, 2).'</td>
            </tr>    
        </table>';                                             

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tblPayments, true, false, false, false, '');
        $pdf->writeHTML($tblTermsNotes, true, false, false, false, '');
        $pdf->writeHTML($tblInvoiceDetails, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->Output('print_invoice.pdf', 'I');
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
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Receipt');
        $pdf->SetTitle('Print Receipt');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START

        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();

        /*
        This fucntions requires
        1.total invoice amount for thie receipt
        2.Amount already paid for this invoice
        3. Amount Paid now
        4. Balance to be calculated.
        */

        $receipt_id = $fn->getReqParam('receipt_id');
        $order_id = $fn->getReqParam('order_id');

        $SQL = "
        SELECT i.creation_date
              ,i.invoice_id AS invoice_id_main
              ,i.invoice_code
              ,i.invoice_amount
              ,o.currency
              ,r.receipt_id
              ,r.amount AS receipt_amount
              ,r.receipt_code
              ,r.mode_of_payment
              ,r.remarks
              ,r.date AS receipt_date
              ,o.company_id
              ,o.contact_id
        FROM receipt r
        LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        WHERE r.receipt_id = '{$receipt_id}'
        AND i.order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $receiptRow = $db->sql_fetchrow($result2);
        $numRows  = $db->sql_numrows($result);
        $today = date("d-m-Y");

        $previous_paid_amount = '';
        $total_amount = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        $receipt_type ='';
        //============================================================================= //
        $num = '';

        $pdf->SetFont('Courier','',10);

        $receipt_date = $receiptRow['receipt_date'];
        $receipt_date = date("d-m-Y", strtotime($receipt_date));

        $tbl2 = '<table border="0" width="100%" cellpadding="5">
                    <tr>
                        <td border="0" align="center" height="30"><font style="font-size:25px; font-weight:bold">RECEIPT</font>
                            <br/>RECEIPT NO : REC - '.$receiptRow['receipt_code'].'
                        </td>
                    </tr>
                </table>';
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $first_name      = '';
        $registration_no = '';
        $id_card_no      = '';

        /*if($receiptRow['company_id'] != ''){
            $sqlCompany = "
            SELECT * FROM company WHERE company_id = {$receiptRow['company_id']}
            ";
            $resultComp = $db->sql_query($sqlCompany);
            $companyRow = $db->sql_fetchrow($resultComp);

            $company_name    = $companyRow['title'];
            $address_flat    = $companyRow['address_town'];
            $address_street  = $companyRow['address_state'];
            //$address_country = $companyRow['address_country_code'];
        }
        else*/
        if ($receiptRow['contact_id'] != '') {
            $sqlContact = "
            SELECT * FROM contact WHERE contact_id = {$receiptRow['contact_id']}
            ";
            $resultCont = $db->sql_query($sqlContact);
            $contactRow = $db->sql_fetchrow($resultCont);

            $first_name      = '<b>Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> '.$contactRow['first_name'];
            $registration_no = '<b>Reg no &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> '.$contactRow['registration_no'];
            $id_card_no      = '<b>NRIC/FIN/PP no :</b> '.$contactRow['id_card_no'];

        }

        $tbl1 = '<table border="0" width="100%" cellpadding="0">
                    <tr>
                        <td border="0"width="55%"align="left" style="text-decoration:underline;font-weight:bold;">Received From</td>
                        <td width="45%"align="right"><b>Receipt Date&nbsp;:&nbsp;</b>'.$receipt_date.'</td>
                    </tr>
                    <tr>
                        <td border="0" align="left" width="50%">'.$first_name.'<br/>
                        '.$registration_no.'<br/>
                        '.$id_card_no.'<br/>
                        </td>
                    </tr>
                </table>';
        $pdf->writeHTML($tbl1, true, false, false, false, '');

        $tbl3 ='<table cellpadding="2" width="100%">';

        $tbl3 = $tbl3.'
                    <thead>
                    <tr>
                        <th style="border-bottom:2px solid #1242AB;" align="center" width="50%"><b>DESCRIPTION</b></th>
                        <th style="border-bottom:2px solid #1242AB;" align="right" width="50%"><b>AMOUNT S$</b></th>
                    </tr>
                    </thead>';

        while ($row = $db->sql_fetchrow($result)) {

            $count++;
            $lineItemNumber++;

           /*This sql used to find the previous amount paid for the invoice */
            $sqlPreviousPayment = "
            SELECT SUM(irhist.amount) AS total_amount_paid
            FROM invoice_receipt_history irhist
            LEFT JOIN receipt r ON (irhist.receipt_id = r.receipt_id)
            WHERE irhist.invoice_id = {$row['invoice_id_main']}
              AND irhist.receipt_id != {$row['receipt_id']}
              AND r.receipt_status != 'Cancelled'
            ";
            $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
            $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
            $previous_paid_amount += $rowPreviousPayment['total_amount_paid'];

            $sqlInvoiceAmount = "
            SELECT i.invoice_amount
            FROM invoice i
            WHERE i.invoice_id = {$row['invoice_id_main']}
            ";
            $resultInvAmount = $db->sql_query($sqlInvoiceAmount);
            $rowInvoiceAmount= $db->sql_fetchrow($resultInvAmount);

            $total_amount += $rowInvoiceAmount['invoice_amount'];

            $invoice_code        = $row['invoice_code'];
            $receipt_amount      = $row['receipt_amount'];
            $mode_of_payment = $row['mode_of_payment'];
            $remarks = $row['remarks'];


            $label = 'Invoice Amount (Invoice Code : ' . $invoice_code . ')';

            $tbl3 = $tbl3.'
                <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
                    <td style= "border-right:1px white;" align="left" width="50%">'.$label.'</td>
                    <td style= "border-right:1px white;" width="50%"  align="right">'.number_format($rowInvoiceAmount['invoice_amount'],2).'</td>
                </tr>
            ';
        }

        $balance_due = $total_amount - $previous_paid_amount - $receipt_amount;


        $tbl3 = $tbl3.'
        <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
            <td align="left" style= "border-right:1px white;" width="50%">Amount already Paid </td>
            <td align="right"style= "border-right:1px white;" width="50%">'.number_format(round($previous_paid_amount), 2).'</td>
        </tr>
        <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
            <td align="left" style= "border-right:1px white;" width="50%"><b>Amount Received Now </b></td>
            <td align="right"style= "border-right:1px white;" width="50%"><b>'.number_format($receipt_amount, 2).'</b></td>
        </tr>
        <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
            <td align="left" style= "border-right:1px white;" width="50%">Balance Amount to be Paid </td>
            <td align="right"style= "border-right:1px white;" width="50%">'.number_format($balance_due, 2).'</td>
        </tr>
        ';

        $tbl3 = $tbl3.'</table>';

        $paymentMode ='
        <div><b>Payment Mode: </b><br/>
            '.$mode_of_payment.'
        </div>
        ';

        $notesText ='
        <div><b>Notes: </b><br/>
            '.$remarks.'
        </div>
        ';

        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(10);
        $pdf->writeHTML($paymentMode, true, false, false, false, '');
        $pdf->ln(10);
        $pdf->writeHTML($notesText, true, false, false, false, '');
        $pdf->Output('Print_Receipt.pdf', 'I');

    }

    /**
     *
     */
    function getReceiptPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);
        
        if ($cpCfg['m.agileIms.order.hasMiscReceiptForPvt']) {
            $sqlAppend = 'AND irh.receipt_type IS NULL';
        }
        
        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$row['order_id']} 
              {$sqlAppend}
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);  
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;
        
        while ($rowReceipt = $db->sql_fetchrow($result)) {
            
            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=agileIms_order&_spAction=printReceiptIndividual&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=agileIms_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=agileIms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $dateUtil->formatDate($rowReceipt['date'], 'DD MMM YYYY');

            $reportLink = "index.php?module=agileIms_order&_spAction=printReceipt&receipt_id={$rowReceipt['receipt_id']}&order_id={$row['order_id']}&showHTML=0";
            $editRow = '';
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code={$rowReceipt['receipt_code']}><u>Cancel Receipt</u></a>";

                if ($cpCfg['m.agileIms.order.hasEditReceiptForPvt'] && $count == $numRows){
                    $editURL = "index.php?_topRm=finance&module=agileIms_receipt&_spAction=editReceiptForm&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";
                    $editRow = "<td><a href='{$editURL}' id='editReceipt'><u>Edit</u></a></td>";
                }

            }

            $cancelledClass = '';
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelledClass = 'highlightClass';
            }

            $receipt_amount  = number_format($rowReceipt['amount'], 2);

            if ($rowReceipt['modification_date']) {
                $date = $dateUtil->formatDate($rowReceipt['modification_date'], 'DD-MM-YYYY');
            } else {
                $date = $dateUtil->formatDate($rowReceipt['creation_date'], 'DD-MM-YYYY');
            }

            if ($rowReceipt['modified_by']) {
                $name = $rowReceipt['modified_by'];
            } else {
                $name = $rowReceipt['created_by'];
            }

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td align='right'>{$receipt_amount}</td>
                <td class='{$cancelledClass}'>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$reportLink}' target='_blank'><u>Print Receipt</u></a></td>
                <td>{$cancelReceiptLink}</td>
                {$editRow}
                <td>{$date}</td>
                <td>{$name}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];    
            }
            $count++;
        }
        
        if ($total) {
            $total = number_format($total, 2);
        }
        
        $total = "
        <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
            <td colspan=3 class='txtRight'>Total : $total</td>
            <td colspan=6></td>
        </tr>
        ";
        
        $editHeader = '';
        if ($cpCfg['m.agileIms.order.hasEditReceiptForPvt']){
            $editHeader = "<th>Edit</th>";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Code</th>
            <th>Payment Date</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Status</th>
            <th>Print</th>
            <th>Cancel</th>
            {$editHeader}
            <th>Creation/<br/>Updation Date</th>
            <th>Created/Updated by</th>
        </tr>
        ";
        
        $formAction = "index.php?_topRm=finance&module=agileIms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <tr class=''>
            <td>
                <div id='receiptPortalOuter' class='receiptDisplay linkPortalWrapper agileIms_company__agileIms_orderLink'>
                    <h2>Receipt(s)</h2>
                    <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                            {$total}
                        </table>
                        <input type='hidden' name='order_id' value='{$row['order_id']}' />
                        <input type='hidden' name='receipt_id' value='{$receiptRec['receipt_id']}' />
                    </form>
                </div>
            </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getTotalForPvtInst($result, $getTotalOnly = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $total = '';
        $discountTotal ='';
        $discount = '';
        $medical_insurance_value = '';

        while ($row = $db->sql_fetchrow($result)) {
            $course_id = $row['record_id'];
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
            $course_type = $courseRec['course_type'];
            
            //to get subject total for long term course
            if($course_type == 'Long Term' && $row['module'] == 'agileIms_subject'){
                //$total += $row['unit_price'];
                 
                if($row['full_time'] == 1){
                    $total += $row['unit_price'];
                }
                else{
                //for part time if the no of months is less than 9 , then use the below calc
                    if($row['no_of_months'] != 9 && $row['no_of_months'] != ''){
                        $total += ($row['unit_price']/9)* $row['no_of_months'];
                    }
                    else{
                        $total += $row['unit_price'];
                    }
                 }
                 
            }
            //to get course total for short term course
            else if($course_type == 'Short Term' && $row['module'] == 'agileIms_course'){
                $total = $row['unit_price'];
            }
           
            //to get discount
           if($row['module'] == 'agileIms_discount'){
                $discount = $row['unit_price'];
           }

            //to get medical insurance
           if($row['medical_insurance'] == 1){
                $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");                
           }
        }
        
        if($getTotalOnly == 'getTotalOnly'){
            return $total;          
        }
        
        if($discount > 0){
            $discountTotal = (($total + $medical_insurance_value) * $discount)/100;
            $discountTotal = round($discountTotal, 3);
            $total = $total - $discountTotal;
        }
        $total += $medical_insurance_value;
        
        return $total;
    }

}