<?
class CP_Admin_Modules_EnterpriseIms_Order_View extends CP_Common_Lib_ModuleViewAbstract
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
        $invPaidMonthLabel = '';
        $invAmtPaidLabel = '';
        $status = '';
        $ordStatus = '';
        $orderIdFld = '';
        $orderIdFldLabel = '';
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $invAmountPaid = '';
            $invPaidMonth = ''; 
            $currency = strtoupper($row['currency']);
  
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$orderIdFld}
            {$listObj->getListDataCell($row['parent_code'])}
            {$listObj->getListDataCell($row['cust_first_name'] . ' ' . $row['cust_last_name'])}
            {$listObj->getListDataCell($row['dda'])}
            {$listObj->getListDateCell($row['order_date'])}
            {$listObj->getListDataCell($row['payment_method'])}
            {$ordStatus}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Parent Code', 'parent_code')}
        {$listObj->getListHeaderCell('Name', 'cust_first_name')}
        {$listObj->getListHeaderCell('DDA', 'dda')}
        {$listObj->getListHeaderCell('Date', 'o.order_date')}
        {$listObj->getListHeaderCell('Mode of Payment', 'o.payment_method')}
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
        
        /*if($cpCfg['m.enterpriseIms.ecommerce.order.orderAmountForPvt'] == 1){
            $SQLPvt = "
            SELECT oi.*
                  ,o.order_id
                  ,o.contact_module
                  ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
                  ,o.registration_type ,o.medical_insurance
            FROM order_item oi 
            LEFT JOIN `order` o ON (o.order_id = oi.order_id)
            LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
            WHERE oi.order_id = {$row['order_id']}
            ORDER BY oi.order_item_id
            ";
            $SQLPvt = "
            SELECT oi.*
                  ,o.order_id
                  ,o.contact_module
                  ,o.registration_type 
                  ,o.medical_insurance
                  ,o.add_registration_fee
                  ,o.full_time
                  ,cc.no_of_months
            FROM order_item oi 
            LEFT JOIN `order` o ON (o.order_id = oi.order_id)
            LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
            WHERE oi.order_id = {$row['order_id']}
            ORDER BY oi.order_item_id
            ";            
            $resultForPvt = $db->sql_query($SQLPvt);  
            $orderAmount  = $this->getTotalForPvtInst($resultForPvt);
           if($row['registration_type']  == 'Only Registration'){
                $orderAmount =  $fn->getSettingsValueByKey("registrationFee");
            }
        }
        else{
            $orderAmount = $currency.'&nbsp;'.$row['order_amount'];
        }*/

        $order_date = $dateUtil->formatDate($row['order_date'], 'DD MMM YYYY');

        $fielset1 = "
        {$formObj->getTBRow('Order Type', 'contact_type', $row['contact_type'], $expNoEdit)}
        {$formObj->getTBRow('Order Date', 'order_date', $order_date, $expNoEdit)}
        {$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        {$formObj->getTARow('Remarks', 'memo', $row['memo'])}
        {$formObj->getDDRowByVL('Mode of Payment', 'payment_method',  'paymentType', $row['payment_method'])}
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
            {$formObj->getTBRow('First Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            {$formObj->getTBRow('Last Name', 'cust_last_name', $row['cust_last_name'], $expNoEdit)}
            ";
        }
        

        $fielset2 = " 
        {$companyFld}
        {$formObj->getTBRow('Email', 'cust_email', $row['cust_email'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'], $expNoEdit)}
        {$formObj->getTBRow('Address 1', 'cust_address1', $row['cust_address1'], $expNoEdit)}
        {$formObj->getTBRow('Address 2', 'cust_address2', $row['cust_address2'], $expNoEdit)}
        {$formObj->getTBRow('Area', 'cust_address_area', $row['cust_address_area'], $expNoEdit)}
        {$formObj->getTBRow('City', 'cust_address_city', $row['cust_address_city'], $expNoEdit)}
        {$formObj->getTBRow('State', 'cust_address_state', $row['cust_address_state'], $expNoEdit)}
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

        <td>
            <select name='payment_method' >
                <option value=''>Mode of Payment</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $payment_method)}
            </select>
        </td>
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
        
        $text = "
        {$this->getOrderItemPortalDisplay($row)}
        {$this->getInvoicePortalDisplay($row)}
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
        
        $rows = "";
        $links= "";
        $exp = array('isEditable' => 1);
        $sqlAppend = '';
        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $clearInvoiceStatus = '';
        $subsidy = "";
        $registration_type = "";
        $regnText = '';
        $trMedicalInsurance = "";        
        $medical_insurance_value = '';
        $contactNamePvt = '';
        $leftJoinAppend = '';
        
        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        WHERE oi.order_id = {$row['order_id']}
        ORDER BY oi.order_item_id
        ";
        
        $result      = $db->sql_query($SQL);  
        // Below is used to pass the result set to get the total for pvt inst.
        $resultForPvt  = $db->sql_query($SQL);  
        $resultForPvt1 = $db->sql_query($SQL);  
        $count = '';
        $contactName = '';
        $check_box_header = '';
                
        while ($rowOrderItem = $db->sql_fetchrow($result)) {
            $module = $rowOrderItem['module'];
            $module = substr($rowOrderItem['module'] , 4);
            $module = ucfirst($module);

            if($module != 'Subsidy' && $module != 'Discount'){
                if($rowOrderItem['invoice_id'] != '' &&
                $rowOrderItem['invoice_id'] > 0){
                    $clearUrl = '';
                    $status = 'DISABLED';
                    
                    if($rowOrderItem['invoice_clear_status'] == 'Cancelled'){
                        $status = '';
                        $clearInvoiceStatus = '';
                    }
                    else{
                        $clearInvoiceStatus = "
                        <a href='javascript:void(0);' order_item_id = 
                        '{$rowOrderItem['order_item_id']}' class='clearInvoiceCode'>Void</a>
                        ";
                    }
                    
                }                    
                else{
                    $status = '';
                    $clearInvoiceStatus= '';
                }
                
                $tdCheckBox = "
                <td>
                    <input type='checkbox' name='orderItemId[]' value='{$rowOrderItem['order_item_id']}' $status>
                    {$clearInvoiceStatus}
                </td>
                ";
            }
            else{
                $tdCheckBox = "
                <td>
                </td>
                ";
            }

            if($rowOrderItem['invoice_id'] ){
                $invoice_code =  $rowOrderItem['invoice_id'];
            }
            else{
                $invoice_code = '';
            }

            //to show contact name only once in case of order raised for individual
            if(($count < 1 && $rowOrderItem['contact_type'] == 'Indvidual') || 
                $rowOrderItem['contact_type'] == 'Company'){
                $contactName = $rowOrderItem['contact_name'];
                $contactNamePvt = $rowOrderItem['contact_name'];
            }
            else{
                $contactName = '';
            }
            

            $rows .= "
            <tr>
                <td>{$contactName}</td>
                <td>{$module}</td>
                <td>{$rowOrderItem['item_title']}</td>
                <td align='right'>{$rowOrderItem['unit_price']}</td>
                <td>{$invoice_code}</td>
            </tr>
            ";
            
            $total += $rowOrderItem['unit_price'];
            
            $count++;
        }
        
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=6>Total : $total</td>
            </tr>
        ";
        
        $netTotal = '';
        
        //calculate total for PVT schools
        /*
        if($cpCfg['m.enterpriseIms.ecommerce.order.orderItemDisplayForPvt'] == 1){
        
            if($registration_type  == 'Only Registration'){
                $total =  $fn->getSettingsValueByKey("registrationFee");
                $regnText = '(Only Registration)';
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
            }
            else if($registration_type  == 'Registration & Enrollment'){
                $total    = $this->getTotalForPvtInst($resultForPvt);
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
           }
            
            $expCourse   = array('condn' => " AND module='enterpriseIms_course'");
            $expDiscount = array('condn' => " AND module='enterpriseIms_discount'");
            $orderItemRecCourse = $fn->getRecordRowByID('order_item', 'order_id', 
            $row['order_id'], $expCourse);
            
            $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', 
            $row['order_id'], $expDiscount);
            $discountTotal = '';
            $discountPer   = '';

            if($row['medical_insurance'] == 1){
                $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");                
                $netTotal = $netTotal + $medical_insurance_value;
            }

            if($orderItemRecDiscount['unit_price'] > 0){
                $discountPer   = $orderItemRecDiscount['unit_price'];
                $discountTotal = ($netTotal *  $discountPer)/100;
                $discountTotal = round($discountTotal, 3);
            }            
            if($row['add_registration_fee'] == 1){
                $add_registration_fee =  $fn->getSettingsValueByKey("registrationFee");
            }
            else{
                $add_registration_fee = '';
            }
            $netTotal = number_format($netTotal, 2);
            
            $rows .= "
            <tr>
                <td></td>
                <td>{$contactName}</td>
                <td>Course</td>
                <td>{$orderItemRecCourse['item_title']}</td>
                <td align='right'>{$netTotal}</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Less Discount/School Grant</td>
                <td align='right'>{$discountTotal}</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Application Fee</td>
                <td align='right'>{$add_registration_fee}</td>
                <td></td>
            </tr>
            ";
            if($registration_type  != 'Only Registration'){
                $total = $total + $add_registration_fee;
            }
			$total = round($total, 2);
            $total = "
                <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                    <td colspan=6>Total $regnText : $total</td>
                </tr>
            ";
        }        
        */
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Trainee</th>
        <th>Type</th>
        <th>Title</th>
        <th>Price/Cost</th>
        <th>Invoice Code</th>
        </tr>
        ";
        
        $formActionPvt = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateInvoiceFormPvt&showHTML=0&order_id={$row['order_id']}";

        $recordCount = $fn->getRecordCount('invoice', "order_id = {$row['order_id']}");

        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateMonthlyInvoiceForEntForm&showHTML=0&order_id={$row['order_id']}";
        $invoiceBtn = "
        <button href='{$formAction}' id='generateMonthlyInvoice' class='button mt5 ml5 mb20'>Generate Monthly Invoice</button>
        ";

        $text = "
        <tr class=''>
        <td>
            <div id='enterpriseIms_company#enterpriseIms_orderLink' class=''>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
                <input type='hidden' name='order_id' value='{$row['order_id']}' />
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

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        
        $status = $fn->getReqParam('status');
        
        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);
        
        $SQL = "
        SELECT i.*
               ,(
               SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
               FROM receipt r, invoice_receipt_history invrecpt
               WHERE r.receipt_id = invrecpt.receipt_id
               AND i.invoice_id = invrecpt.invoice_id
               ) AS receipt_codes_history
              ,c.first_name
              ,c.last_name
              ,DATE_FORMAT(i.invoice_date, '%d-%m-%Y') AS invoice_date
               {$sqlAppend}
        FROM invoice i
        LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
        {$leftJoin}
        WHERE i.order_id = {$row['order_id']}
        ORDER BY i.contact_id ASC
        ";

        $result   = $db->sql_query($SQL);  
        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = ''; 
        $add_registration_fee = '';
        $invoice_hist_amount  = '';
        
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';

            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=enterpriseIms_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] == 'Paid'){
                $selectedValuePaid =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Due'){
                $selectedValueDue =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Cancelled'){
                $selectedValueCancel =  "selected='selected'";
            }
            
            if($rowInvoice['status'] != 'Cancelled' && $invoice_code != $rowInvoice['invoice_code']){
                $total += $rowInvoice['invoice_amount'];
            }
            if($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']){

                $editRow = '<td></td>';

                $month = '';
                $tdRow = '';
                switch ($rowInvoice['invoice_month']) {
                    case 1: $month = 'Jan';
                    break;
                    case 2: $month = 'Feb';
                    break;
                    case 3: $month = 'Mar';
                    break;
                    case 4: $month = 'Apr';
                    break;
                    case 5: $month = 'May';
                    break;
                    case 6: $month = 'Jun';
                    break;
                    case 7: $month = 'Jul';
                    break;
                    case 8: $month = 'Aug';
                    break;
                    case 9: $month = 'Sep';
                    break;
                    case 10: $month = 'Oct';
                    break;
                    case 11: $month = 'Nov';
                    break;
                    case 12: $month = 'Dec';
                    break;
                }
                $contact_name = $rowInvoice['first_name'] . ' ' . $rowInvoice['last_name'] . ' - ' . $month;
                $tdRow = "<td>{$contact_name}</td>";
                
                $cancelInvoiceLink = '';
                if ($rowInvoice['status'] != 'Cancelled') {
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code={$rowInvoice['invoice_code']}>Cancel Invoice</a>";
                }

                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    {$tdRow}
                    <td>{$rowInvoice['invoice_date']}</td>
                    <td align='right'>{$rowInvoice['invoice_amount']}</td>
                    <td>{$rowInvoice['status']}</td>
                    <td>{$rowInvoice['receipt_codes_history']}</td>
                    <!--<td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>-->
                    <td><a href='{$mediaLink}'>Print Invoice</a></td>
                    <td>{$cancelInvoiceLink}</td>
                </tr>
                ";
            }

            $invoice_code = $rowInvoice['invoice_code'];
        }
        $total += $add_registration_fee;
        $total = number_format($total, 2);
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=8>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Contact Name</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Receipt Code</th>
        <th>Print</th>
        <th>Cancel</th>
        </tr>
        ";

        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptForEntForm&showHTML=0&order_id={$row['order_id']}";
        $receiptBtn = "
        <button href='{$formAction}' id='generateReceiptEnt' 
        class='button mt5 ml5 mb20'>Generate Receipt</button>
        ";

        $sqlInvoiceStatus = 'SELECT DISTINCT status FROM invoice';
        
        $text = "
        <tr class=''>
        <td>
            <div id='' class='invoiceDisplay'>
                <h2>Invoice(s)</h2>
                <div>
                    {$formObj->getDropDownBySQL('Status', 'status', $sqlInvoiceStatus, '', array('sqlType' => 'OneField'))}
                </div>
            
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
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
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        
        $status = $fn->getReqParam('status');
        
        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }
        
        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);
        
        if ($cpCfg['m.enterpriseIms.ecommerce.order.invoiceForPvt'] == true){
            $SQL = "
            SELECT inst.amount as invoice_hist_amount
                   ,inst.invoice_date as invoice_due_date
                   ,inst.invoice_paid_status as invoice_hist_status
                   ,inst.title as invoice_hist_title
                   ,inst.installment_id
                   ,i.invoice_code
                   ,i.status
                   ,i.invoice_amount
                   ,i.invoice_id
                   ,i.invoice_date
                   ,(
                   SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
                   FROM receipt r, invoice_receipt_history invrecpt
                   WHERE r.receipt_id = invrecpt.receipt_id
                   AND i.invoice_id = invrecpt.invoice_id
                   AND invrecpt.receipt_type IS NULL
                   AND invrecpt.amount > 0
                   ) AS receipt_codes_history
                   ,(
                   SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
                   FROM receipt r, invoice_receipt_history invrecpt
                   WHERE r.receipt_id = invrecpt.receipt_id
                   AND inst.installment_id = invrecpt.installment_id
                   AND invrecpt.amount > 0
                   ) AS receipt_inst_history
            FROM installment inst
            LEFT JOIN `invoice` i ON (inst.invoice_id = i.invoice_id)
            WHERE i.order_id = {$row['order_id']}
            ORDER BY inst.installment_id
            ";
        } else if ($cpCfg['m.enterpriseIms.order.invoiceForEnt']){
            $SQL = "
            SELECT i.*
                   ,(
                   SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
                   FROM receipt r, invoice_receipt_history invrecpt
                   WHERE r.receipt_id = invrecpt.receipt_id
                   AND i.invoice_id = invrecpt.invoice_id
                   ) AS receipt_codes_history
                  ,c.first_name
                  ,c.last_name
                  ,i.invoice_date
            FROM invoice i
            LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
            {$leftJoin}
            WHERE i.order_id = {$row['order_id']}
                  {$sqlAppend}
              AND i.add_registration_fee IS NULL
            ORDER BY i.invoice_month ASC
            ";
        }
        else{
            $SQL = "
            SELECT i.*
                ,(
                SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
                FROM receipt r, invoice_receipt_history invrecpt
                WHERE r.receipt_id = invrecpt.receipt_id
                AND i.invoice_id = invrecpt.invoice_id
                ) AS receipt_codes_history
                {$sqlAppend}
            FROM invoice i 
            {$leftJoin}
            WHERE i.order_id = {$row['order_id']}
            ORDER BY i.invoice_id
            ";
        }

        $result   = $db->sql_query($SQL);  
        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = ''; 
        $add_registration_fee = '';
        $invoice_hist_amount  = '';
        
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';

            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=enterpriseIms_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] == 'Paid'){
                $selectedValuePaid =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Due'){
                $selectedValueDue =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Cancelled'){
                $selectedValueCancel =  "selected='selected'";
            }
            
            if($rowInvoice['status'] != 'Cancelled' && $invoice_code != $rowInvoice['invoice_code']){
                $total += $rowInvoice['invoice_amount'];
            }
            if($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']){

                /* Half way done. Need to do submit functioanlity. Move $editRow = ''; from below to this comment line */
                $editRow = '<td></td>';
                if ($cpCfg['m.enterpriseIms.order.hasEditInvoiceForPvt']){
                    if ($rowInvoice['status'] == 'Due' 
                     || $rowInvoice['status'] == '' 
                     || $rowInvoice['status'] == 'Partial Payment'
                    ) {
                        $editURL = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=editInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$row['order_id']}";
                        $editRow = "<td><a href='{$editURL}' id='editInvoicePvt'>Edit</a></td>";
                    }
                }
                //$editRow = '';

                $deleteRow = '';
                if ($cpCfg['m.enterpriseIms.order.hasDeleteInvoiceForPvt']){
                    $deleteURL = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=deleteInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}";
                    $deleteRow = "<td><a href='{$deleteURL}' id='deleteInvoicePvt' invoice_id='{$rowInvoice['invoice_id']}'>Delete</a></td>";
                }

                $month = '';
                $tdRow = '';
                if ($cpCfg['m.enterpriseIms.order.invoiceForEnt']) {
                    switch ($rowInvoice['invoice_month']) {
                        case 1: $month = 'Jan';
                        break;
                        case 2: $month = 'Feb';
                        break;
                        case 3: $month = 'Mar';
                        break;
                        case 4: $month = 'Apr';
                        break;
                        case 5: $month = 'May';
                        break;
                        case 6: $month = 'Jun';
                        break;
                        case 7: $month = 'Jul';
                        break;
                        case 8: $month = 'Aug';
                        break;
                        case 9: $month = 'Sep';
                        break;
                        case 10: $month = 'Oct';
                        break;
                        case 11: $month = 'Nov';
                        break;
                        case 12: $month = 'Dec';
                        break;
                    }
                    $contact_name = $rowInvoice['first_name'] . ' ' . $rowInvoice['last_name'];
                    $tdRow = "<td>{$contact_name}</td>";
                }
                
                $cancelInvoiceLink = '';
                if ($rowInvoice['status'] != 'Cancelled') {
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code={$rowInvoice['invoice_code']}>Cancel Invoice</a>";
                }

                $reportLink = "index.php?module=enterpriseIms_order&_spAction=printInvoiceInFpdf&record_id={$rowInvoice['invoice_id']}&showHTML=0";
                $invoice_date = $dateUtil->formatDate($rowInvoice['invoice_date'], 'DD MMM YYYY');
                
                $invoice_amount = number_format($rowInvoice['invoice_amount'] - $rowInvoice['discount_amount'], 2);
                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    {$tdRow}
                    <td>{$invoice_date}</td>
                    <td align='right'>{$invoice_amount}</td>
                    <td>{$month}</td>
                    <td align='right'>{$rowInvoice['discount_amount']}</td>
                    <td>{$rowInvoice['status']}</td>
                    <td>{$rowInvoice['receipt_codes_history']}</td>
                    <!--<td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>-->
                    <td><a href='{$reportLink}' target='_blank'>Print Invoice</a></td>
                    <td>{$cancelInvoiceLink}</td>
                    {$editRow}
                    {$deleteRow}
                </tr>
                ";
            }

            if ($cpCfg['m.enterpriseIms.ecommerce.order.invoiceForPvt'] == 1){
                //$receiptCode = $fn->getRecordRowByID('receipt', 'receipt_id', $rowInvoice['receipt_paid_id']);
                $invoice_due_date = $fn->getCPDate($rowInvoice['invoice_due_date'], 'd/m/Y');
                $invoice_hist_amount = round($rowInvoice['invoice_hist_amount'], 3);

                $rowsPvt .= "
                <tr> 
                    <td>{$rowInvoice['invoice_hist_title']}</td>
                    <td>{$invoice_due_date}</td> 
                    <td align='right'>{$invoice_hist_amount}</td>
                    <td>{$rowInvoice['invoice_hist_status']}</td>
                    <td>{$rowInvoice['receipt_inst_history']}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                ";
                $count++;
                
                if($row['add_registration_fee'] == 1){
                    $add_registration_fee =  $fn->getSettingsValueByKey("registrationFee");
                }
            }
            $invoice_code = $rowInvoice['invoice_code'];
        }
        $total += $add_registration_fee;
        $total = number_format($total, 2);
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=8>Total : $total</td>
            </tr>
        ";
        
        $editHeader = '';
        if ($cpCfg['m.enterpriseIms.order.hasEditInvoiceForPvt']){
            $editHeader = "<th>Edit</th>";
        }
                
        $deleteHeader = '';
        if ($cpCfg['m.enterpriseIms.order.hasEditInvoiceForPvt']){
            $deleteHeader = "<th>Delete</th>";
        }
                
        if ($cpCfg['m.enterpriseIms.order.invoiceForEnt']) {
            $thHeader = "<th>Contact Name</th>";
        }
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        {$thHeader}
        <th>Invoice Date</th>
        <th>Invoice Amount</th>
        <th>Invoice Month</th>
        <th>Discount</th>
        <th>Status</th>
        <th>Receipt Code</th>
        <th>Print</th>
        <th>Cancel</th>
        {$editHeader}
        {$deleteHeader}
        </tr>
        ";
        
        $formActionCreditNote = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateCreditNoteForm&showHTML=0&order_id={$row['order_id']}";

        $creditNote = '';
        if ($cpCfg['m.enterpriseIms.order.hasCreditNoteLink']){
            $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
            $creditNote = "
            <button href='{$formActionCreditNote}' id='generateCreditNote' 
            class='button mt5 ml5 mb20'>Generate Credit Note</button>
            </form>
            ";
        }

        // This is used to display Receipt for Pvt : Used for Mass IMS
        $miscReceiptBtn = '';
        if ($cpCfg['m.enterpriseIms.order.hasMiscReceipt']){
            $miscFormAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateMiscReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";

            $miscReceiptBtn = "
            <button href='{$miscFormAction}' id='generateMiscReceiptPvt' 
            class='button mt5 ml5 mb20'>Generate Misc Receipt</button>
            ";  
        }
        
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
        if($cpCfg['m.enterpriseIms.ecommerce.order.receiptForPvt']) {
            if ($invoiceRec['status'] == 'Paid'){
                $formAction = '';
                $receiptBtn = '';
            } else {            
                $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
                $receiptBtn = "
                <button href='{$formAction}' id='generateReceiptPvt' 
                class='button mt5 ml5 mb20'>Generate Receipt</button>
                "; 
            }
        } else if ($cpCfg['m.enterpriseIms.order.receiptForEnt']) {
            $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptForEntForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceiptEnt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
        } else {
            $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceipt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
        }

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
            {$rowsPvt}
            {$total}
        </table>
        ";

        return $text;
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
        
        if ($cpCfg['m.enterpriseIms.order.hasMiscReceiptForPvt']) {
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
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printReceiptIndividual&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=enterpriseIms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $editRow = '';
            if ($cpCfg['m.enterpriseIms.order.hasEditReceiptForPvt'] && $count == $numRows){
                $editURL = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=editReceiptFormPvt&receipt_id={$rowReceipt['receipt_id']}&order_id={$row['order_id']}&showHTML=0";
                $editRow = "<td><a href='{$editURL}' class='editReceiptPvt'>Edit</a></td>";
            }

            $receipt_date = $dateUtil->formatDate($rowReceipt['date'], 'DD MMM YYYY');
            
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code={$rowReceipt['receipt_code']}>Cancel Receipt</a>";
            }
            
            $reportLink = "index.php?module=enterpriseIms_order&_spAction=printReceiptInFpdf&record_id={$rowReceipt['receipt_id']}&showHTML=0";
            $receipt_amount  = number_format($rowReceipt['amount'], 2);
            
            $discount_amount = $rowReceipt['discount_amount'];
            if ($rowReceipt['discount_amount'] == 0 || $rowReceipt['discount_amount'] == NULL) {
                $discount_amount = 0;
            }
            
            $discount_amount = number_format($discount_amount, 2);
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td align='right'>{$receipt_amount}</td>
                <td align='right'>{$discount_amount}</td>
                <td>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$reportLink}' target='_blank'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
                {$editRow}
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];    
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";
        
        $editHeader = '';
        if ($cpCfg['m.enterpriseIms.order.hasEditReceiptForPvt']){
            $editHeader = "<th>Edit</th>";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Code</th>
        <th>Payment Date</th>
        <th>Receipt Amount</th>
        <th>Discount</th>
        <th>Status</th>
        <th>Print</th>
        <th>Cancel</th>
        {$editHeader}
        </tr>
        ";
        
        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $refundButton = '';
        if ($cpCfg['m.enterpriseIms.order.hasRefund']){
            $refundButton = "
            <button href='{$formAction}' id='generateRefund' 
            class='button mt5 ml5 mb20'>Generate Refund</button>
            ";
        }

        $text = "
        <tr class=''>
        <td>
            <div id='receiptPortalOuter' class='linkPortalWrapper enterpriseIms_company__enterpriseIms_orderLink'>
            <h2>Receipt(s)</h2>
                <form id='orderItemPrint' class='' method='post' 
                action='{$formAction}'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
                {$refundButton}
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
            if($course_type == 'Long Term' && $row['module'] == 'enterpriseIms_subject'){
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
            else if($course_type == 'Short Term' && $row['module'] == 'enterpriseIms_course'){
                $total = $row['unit_price'];
            }
           
            //to get discount
           if($row['module'] == 'enterpriseIms_discount'){
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

    /**
     *
     */
     function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
               
        $order_id = $fn->getReqParam('order_id');

        $rows = '';
         
        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history invHist
            WHERE invHist.invoice_id =  i.invoice_id 
            ) as prev_inv_amount
        FROM invoice i
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$row['invoice_amount']}SGD)</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}SGD</div>
                </div>
            </div>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
     function getGenerateReceiptForEntForm() {
        /********************************* PROCESS ************************************/
        /*
        ACTION: ORDER RIGHT PANEL: DISPLAY FOR RECEIPT WINDOW, WHEN 'GENERATE RECEIPT' BUTTON IS CLICKED 
        STEP 1: FINDING THE INVOICE DETAILS AND THE AMOUNT PAID EARLIER FOR THE SPECIFIC ORDER
        STEP 2: DISPLAY OF INVOICE CHECKBOX WITH PAYMENT DETAILS (INVOICE MONTH, AMOUNT PAID EARLIER)
        STEP 3: DISPLAY OF INVOICE CHECKBOX FOR REG FEE
        STEP 4: DISPLAY OF RECEIPT FORM
        */
        /******************************* END PROCESS **********************************/

        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
               
        $order_id = $fn->getReqParam('order_id');

        unset($_SESSION['selectedInvoiceIds']);

        $rows = '';
         
        /********************************** STEP 1 **************************************/
        $SQL = "
        SELECT i.*
              ,c.first_name
              ,c.last_name
            ,(
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history invHist
            WHERE invHist.invoice_id =  i.invoice_id 
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
            ORDER BY i.contact_id ASC, i.invoice_month ASC
        ";
        $result  = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL);
        $repeat_name = '';
        /********************************** STEP 1 ENDS HERE ****************************/
        
        /********************************** STEP 2 **************************************/
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['add_registration_fee'] == NULL) {
                $namdeDiv = '';
                $contact_name = $row['first_name'] . ' ' . $row['last_name'];
                if($contact_name != $repeat_name){
                    $namdeDiv ="<div class='mb5'><strong>{$contact_name}</strong></div>";
                    $rows .= "
                    <div class='form-row-wrapper'>
                    {$namdeDiv}
                    ";
                }
                
                $month = '';
                switch ($row['invoice_month']) {
                    case 1: $month = 'Jan';
                    break;
                    case 2: $month = 'Feb';
                    break;
                    case 3: $month = 'Mar';
                    break;
                    case 4: $month = 'Apr';
                    break;
                    case 5: $month = 'May';
                    break;
                    case 6: $month = 'Jun';
                    break;
                    case 7: $month = 'Jul';
                    break;
                    case 8: $month = 'Aug';
                    break;
                    case 9: $month = 'Sep';
                    break;
                    case 10: $month = 'Oct';
                    break;
                    case 11: $month = 'Nov';
                    break;
                    case 12: $month = 'Dec';
                    break;
                }
                
                $invoice_code = substr($row['invoice_code'], 4);
                $rows .= "
                <div class='float_left invoice_items'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left monthField'>{$month}</div>
                    <div class='float_left'>Paid : {$row['prev_inv_amount']}</div>
                </div>
                ";
                $repeat_name = $contact_name;
                
                if($contact_name != $repeat_name && $repeat_name != ''){
                    $rows .= "
                    </div>
                    ";
                }
            }
        }
        /********************************** STEP 2 ENDS HERE ****************************/

        $rows = "
        {$rows}
        <div class='clearfix'></div>
        ";
                
        $current_date = date('Y-m-d');
        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        /********************************** STEP 3 **************************************/
        $reg_fees = $this->getRegFeeRowForStudentInReceiptForEntForm($result1);
        /********************************** STEP 3 ENDS HERE ****************************/

        /********************************** STEP 4 **************************************/
        $text = "
        <form id='portalForm' class='yform columnar receiptForm' name='receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$reg_fees}
            {$rows}
            <div class='float_box clearfix'>
                <div class='float_left'>
                    <a href='#' class='populateAmountPayable'><u>Check amount Payable</u></a>
                </div>
                <div id='totalAmountPayable' class='ml10 mt10'></div>
            </div>
            {$formObj->getTBRow('Amount paying now', 'amount')}
            <div>NOTE: Discount amount populated is for one invoice</div>
            {$formObj->getTBRow('Discount Amount', 'discount_amount')}
            <div class='floatbox'>
                <div class='float_left'>Update discount for all future months</div>
                <div class='ml10'>
                    <input type='checkbox' name='discount_for_all_months' value='1' checked='checked'>
                </div>
            </div>
            {$formObj->getDateRow('Receipt date', 'date', $current_date)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Remarks', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";
        /********************************** STEP 4 ENDS HERE ****************************/

        return $text;
    }
    
    /**
     *
     */
    function getRegFeeRowForStudentInReceiptForEntForm($result1) {
        $db = Zend_Registry::get('db');

        $rows = '';
        while ($row = $db->sql_fetchrow($result1)) {
            if ($row['add_registration_fee'] == 1) {
                $contact_name = $row['first_name'] . ' ' . $row['last_name'];
                
                $invoice_code = substr($row['invoice_code'], 4);
                $rows .= "
                <div class='form-row-wrapper'>
                    <div class='mb5'><strong>{$contact_name}</strong></div>
                    <div class='float_left invoice_items'>
                        <div class='float_left'>
                            <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                        </div>
                        <div class='float_left monthField'>Reg Fee</div>
                        <div class='float_left'>Paid : {$row['prev_inv_amount']}</div>
                    </div>
                </div>
                ";
            }
        }
        
        return $rows;
    }
    /**
     *
     */
     function getGenerateMonthlyInvoiceForEntForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $rows = '';
        
        $order_id = $fn->getReqParam('order_id');

        $sqlOI = "
        SELECT DISTINCT oi.order_item_id
              ,oi.order_id
              ,oi.contact_id
              ,c.first_name
              ,c.last_name
        FROM order_item oi
        LEFT JOIN (invoice i) ON (oi.order_id = i.order_id AND oi.contact_id = i.contact_id)
        LEFT JOIN (contact c) ON (oi.contact_id = c.contact_id)
        WHERE oi.order_id = {$order_id}
          AND oi.module = 'enterpriseIms_course'
        ORDER BY c.first_name ASC
        ";
        
        $resultOI  = $db->sql_query($sqlOI);
        while ($rowOI = $db->sql_fetchrow($resultOI)) {
            $rowsHTML = '';
            $contact_name = $rowOI['first_name'] . ' ' . $rowOI['last_name'];
            
            $sqlCc = "
            SELECT * FROM course_contact
            WHERE order_id = {$order_id}
              AND contact_id = {$rowOI['contact_id']}
            ";
            $resultCc = $db->sql_query($sqlCc);
            $rowCc = $db->sql_fetchrow($resultCc);
            $cRec = $fn->getRecordRowByID('course', 'course_id', $rowCc['course_id']);

            $total = 0;
            $subsidyTotal = 0;
            $discTotal = 0;
            
            if($rowCc['course_subsidy_history_id'] > 0){
                $sql1 = "
                SELECT sd.*
                FROM subsidy_discount sd
                LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                WHERE csh.course_subsidy_history_id = {$rowCc['course_subsidy_history_id']}
                ";
                $result1  = $db->sql_query($sql1);
                $row1 = $db->sql_fetchrow($result1);
    
                if ($cRec['price'] != ''){
                    $total = $cRec['price'];
                }
                
                if ($row1['value'] != ''){
                    if($row1['mode_of_calculation'] == 'Value'){
                        $subsidyTotal = $row1['value'];
                    }
                    else{
                        $subsidyTotal = ($cRec['price']*$row1['value'])/100;
                    }
                }
            }
        
            if($rowCc['discount'] > 0){
                /*$sql1 = "
                SELECT sd.*
                FROM subsidy_discount sd
                LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                WHERE csh.course_subsidy_history_id = {$rowCc['discount']}
                ";*/

                $sql1 = "
                SELECT sd.*
                FROM subsidy_discount sd
                WHERE sd.subsidy_discount_id = {$rowCc['discount']}
                ";
                $result1  = $db->sql_query($sql1);
                $row1 = $db->sql_fetchrow($result1);
    
                if ($cRec['price'] != ''){
                    $total = $cRec['price'];
                }
                
                if ($row1['value'] != ''){
                    if($row1['mode_of_calculation'] == 'Value'){
                        $discTotal = $row1['value'];
                    }
                    else{
                        $discTotal = ($cRec['price']*$row1['value'])/100;
                    }
                }
            }
    
            $total = $cRec['price'] - $subsidyTotal - $discTotal;
    
            for($i=1;$i<=12;$i++){
                $status = '';
                $wIncomeByStudentEnt = getCPWidgetObj('enterpriseIms_incomeByStudentEnt');
                $order_status = $wIncomeByStudentEnt->view->getStudentPaymentStatus($rowOI['order_id'], $rowOI['contact_id'], $i);
                $contact_id = $rowOI['contact_id'];
                
                if ($order_status) {
                    $status = $order_status;
                } else {
                    $fldName = "inv__{$order_id}__{$contact_id}__{$total}__{$i}";
                    
                    $status = "<input type='checkbox' name={$fldName} value='{$i}' class='orderItem'>";
                }
            
                $rowsHTML .="
                <td>{$status}</td>
                ";
            }
            
            $rows .= "
            <tr>
                <td>{$contact_name}</td>
                {$rowsHTML}
                <td class='click-all-side'>
                    <a href='#' class='check-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
                    <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
                </td>
            </tr>
            ";
        }
        
        $formAction = "index.php?_topRm=finance&module=enterpriseIms_order&_spAction=generateMonthlyInvoiceForEntFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar monthlyInvoiceForEntForm' method='post' action='{$formAction}'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                        <th>Check / Uncheck</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";
        return $text;

    }

}