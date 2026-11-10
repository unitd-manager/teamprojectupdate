<?
class CP_Admin_Modules_Subscription_Order_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDateCell($row['from_year'])}
            {$listObj->getListDateCell($row['to_year'])}
            {$listObj->getListDateCell($row['order_date'])}
            {$ordStatus}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('From Year', 'from_year')}
        {$listObj->getListHeaderCell('To Year', 'to_year')}
        {$listObj->getListHeaderCell('Date', 'o.order_date')}
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
        

		$spArrayOrderStatus = array (
				 'New'
				,'Due'
				,'Paid'
				,'Cancelled'
				
		);

		$spArrayYear = array ('2001' ,'2002' ,'2003','2004','2005','2006','2007','2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015'
							  ,'2016' ,'2017' ,'2018' ,'2019' ,'2020' ,'2021' ,'2022' ,'2023' ,'2024' ,'2025');

        $order_date = $dateUtil->formatDate($row['order_date'], 'DD MMM YYYY');

        $fielset1 = "
        {$formObj->getDateRow('Order Date', 'order_date', $order_date, $expNoEdit)}
        {$formObj->getDDRowByArr('From Year', 'from_year', $spArrayYear, $row['from_year'])}
        {$formObj->getDDRowByArr('To Year', 'to_year', $spArrayYear, $row['to_year'])}
        {$formObj->getTBRow('Amount', 'amount', $cpCfg['cp.amount'], $expNoEdit)}      
        {$formObj->getDDRowByArr('Status', 'order_status', $spArrayOrderStatus, $row['order_status'], $expNoEdit)}
        {$formObj->getTARow('Remarks', 'memo', $row['memo'])}
        <!--{$formObj->getDDRowByVL('Mode of Payment', 'payment_method',  'paymentType', $row['payment_method'])}-->
        ";

        $fielset2 = " 
            {$formObj->getTBRow('First Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            {$formObj->getTBRow('Last Name', 'cust_last_name', $row['cust_last_name'], $expNoEdit)}
	        {$formObj->getTBRow('Email', 'cust_email', $row['cust_email'], $expNoEdit)}
	        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'], $expNoEdit)}
	        {$formObj->getTBRow('Mobile', 'cust_mobile', $row['cust_mobile'], $expNoEdit)}
	        {$formObj->getTBRow('Fax', 'cust_fax', $row['cust_fax'], $expNoEdit)}
	        {$formObj->getTBRow('Address', 'cust_address', $row['cust_address'], $expNoEdit)}
	        {$formObj->getTBRow('City', 'cust_address_city', $row['cust_address_city'], $expNoEdit)}
	        {$formObj->getTBRow('State', 'cust_address_state', $row['cust_address_state'], $expNoEdit)}
	        {$formObj->getTBRow('Country', 'cust_address_country_code', $row['country_name'], $expNoEdit)}
	        {$formObj->getTBRow('Zip Code', 'cust_address_po_code', $row['cust_address_po_code'], $expNoEdit)}
		";
        
        /*$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['cust_country_name']);*/
        
        if($row['company_id']){
            $companyFld =" 
            {$formObj->getTBRow('Company Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            ";
        }
        else{
            $companyFld =" 
            {$formObj->getTBRow('First Name', 'cust_first_name', $row['cust_first_name'], $expNoEdit)}
            {$formObj->getTBRow('Last Name', 'cust_last_name', $row['cust_last_name'],$expNoEdit)}
            ";
        }
        

        /*($fielset2 = " 
        {$companyFld}
        {$formObj->getTBRow('Email', 'cust_email', $row['cust_email'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'], $expNoEdit)}
        {$formObj->getTBRow('Address 1', 'cust_address', $row['cust_address1'], $expNoEdit)}
        {$formObj->getTBRow('Area', 'cust_address_area', $row['cust_address_area'], $expNoEdit)}
        {$formObj->getTBRow('City', 'cust_address_city', $row['cust_address_city'], $expNoEdit)}
        {$formObj->getTBRow('State', 'cust_address_state', $row['cust_address_state'], $expNoEdit)}
        {$formObj->getTBRow('Country', 'cust_address_country_code', $row['cust_address_country_code'], $expNoEdit)}
        {$formObj->getTBRow('Zip Code', 'cust_address_po_code', $row['cust_address_po_code'], $expNoEdit)}
        <!--{$formObj->getFieldSetWrapped('Contact Details', $fielset2)}-->

        ";*/
        
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

        /*$order_date1 = $fn->getReqParam('order_date_1');
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


        return $text;*/
    }

    /**
     */
    function getRightPanel($row){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        
        $text = "
        {$this->getInvoiceRecords($row)}
        ";

        return $text;
    }
    /**
     */
    /*function getOrderItemPortalDisplay($row){
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
        if($cpCfg['m.subscription.ecommerce.order.orderItemDisplayForPvt'] == 1){
        
            if($registration_type  == 'Only Registration'){
                $total =  $fn->getSettingsValueByKey("registrationFee");
                $regnText = '(Only Registration)';
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
            }
            else if($registration_type  == 'Registration & Enrollment'){
                $total    = $this->getTotalForPvtInst($resultForPvt);
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
           }
            
            $expCourse   = array('condn' => " AND module='subscription_course'");
            $expDiscount = array('condn' => " AND module='subscription_discount'");
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
        
        /*$header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Trainee</th>
        <th>Type</th>
        <th>Title</th>
        <th>Price/Cost</th>
        <th>Invoice Code</th>
        </tr>
        ";
        
        $formActionPvt = "index.php?_topRm=finance&module=subscription_order&_spAction=generateInvoiceFormPvt&showHTML=0&order_id={$row['order_id']}";

        $recordCount = $fn->getRecordCount('invoice', "order_id = {$row['order_id']}");

        $formAction = "index.php?_topRm=finance&module=subscription_order&_spAction=generateMonthlyInvoiceForEntForm&showHTML=0&order_id={$row['order_id']}";
        $invoiceBtn = "
        <button href='{$formAction}' id='generateMonthlyInvoice' class='button mt5 ml5 mb20'>Generate Monthly Invoice</button>
        ";

        $text = "
        <tr class=''>
        <td>
            <div id='subscription_company#subscription_orderLink' class=''>
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
    }*/

    /**
     */
    /*function getInvoicePortalDisplay($row){
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
                $urlPrint = "index.php?_topRm=finance&module=subscription_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=subscription_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=subscription_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

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
        $formAction = "index.php?_topRm=finance&module=subscription_order&_spAction=generateReceiptForEntForm&showHTML=0&order_id={$row['order_id']}";
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
    }*/
 

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
        
            $SQL = "
            SELECT i.*
            FROM invoice i 
            WHERE i.order_id = {$row['order_id']}
            ORDER BY i.invoice_id
            ";

        $result   = $db->sql_query($SQL);  
        $total = '';
        $count = 1;
        $invoice_code = ''; 
        $add_registration_fee = '';
        $invoice_hist_amount  = '';
        
        while ($rowInvoice = $db->sql_fetchrow($result)) {         

            if($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']){
                
                $invoice_date = $dateUtil->formatDate($rowInvoice['invoice_date'], 'DD MMM YYYY');

                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    <td>{$invoice_date}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$rowInvoice['from_year']}</td>
                    <td>{$rowInvoice['to_year']}</td>
                    <td align='right'>{$cpCfg['cp.amount']}</td>
                    <td>{$rowInvoice['status']}</td>
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
        <th>Invoice Date</th>
        <th>Contact Name</th>
        <th>From Year</th>
        <th>To Year</th>
        <th align='right'>Invoice Amount</th>
        <th>Status</th>
        </tr>
        ";       


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

}