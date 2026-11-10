<?
class CP_Admin_Modules_Edukloud_Order_View extends CP_Common_Lib_ModuleViewAbstract
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
            // This is used to display Order Amount for Pvt : Used for Mass IMS
            /*if($cpCfg['m.edukloud.ecommerce.order.orderAmountForPvt'] == 1){
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
            }
            //this is used for Ent to display the status of invoice which are paid for all teh previous month.
            if($cpCfg['m.edukloud.ecommerce.order.orderSqlForEnt']){
                if($row['invoice_paid_month'] > 0){
                    $invPaidMonth = $listObj->getListDataCell('Due');
                    //to make the order status same according to the invoice paid status till the current month
                    $updateSQL = "
                    UPDATE `order`
                    SET order_status = 'Due'
                    WHERE order_id = {$row['order_id']}
                    ";
                    $result = $db->sql_query($updateSQL);
                }
                else{
                    $invPaidMonth = $listObj->getListDataCell('Paid');
                    $updateSQL = "
                    UPDATE `order`
                    SET order_status = 'Paid'
                    WHERE order_id = {$row['order_id']}
                    ";
                    $result = $db->sql_query($updateSQL);
                }
                $invAmountPaid = $listObj->getListDataCell($row['order_amount_paid']);
            }
            else{
                $ordStatus = $listObj->getListDataCell($row['order_status']);
                $orderIdFld = $listObj->getGoToDetailText($rowCounter, $row['order_id']);
            }*/
            
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

        if($cpCfg['m.edukloud.ecommerce.order.orderSqlForEnt']){
            $invPaidMonthLabel = $listObj->getListHeaderCell('Status', '');
            $invAmtPaidLabel   = $listObj->getListHeaderCell('Amount Paid', '');
        }
        else{
            $status = $listObj->getListHeaderCell('Status', 'o.order_status');
            $orderIdFldLabel = $listObj->getListHeaderCell('Order Id', 'o.order_id');
        }
        $text = "
        {$listObj->getListHeader()}
        {$orderIdFldLabel}
        {$listObj->getListHeaderCell('Parent Code', 'parent_code')}
        {$listObj->getListHeaderCell('Name', 'cust_first_name')}
        {$listObj->getListHeaderCell('DDA', 'dda')}
        {$listObj->getListHeaderCell('Date', 'o.order_date')}
        {$listObj->getListHeaderCell('Mode of Payment', 'o.payment_method')}
        {$status}
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
        
        /*if($cpCfg['m.edukloud.ecommerce.order.orderAmountForPvt'] == 1){
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
        
        $SQLCourse = "
        SELECT c.course_type FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$row['order_id']}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse); 
        
        $links = '';
        $paymentLink = '';
        //{$displayLinkData->getLinkPortalMain('edukloud_order', 'ecommerce_orderItemLink', 'Order Items', $row)}
        //$paymentLink = $displayLinkData->getLinkPortalMain('edukloud_order', 'edukloud_paymentLink', 'Payments Linked', $row);

        // This is used to display Credit Note Portal Display       
        $creditNote = '';
        if ($cpCfg['m.edukloud.order.hasCreditNoteLink']){
            $creditNote = $this->getCreditNotePortalDisplay($row);
        }

        // This is used to display Misc Receipt Portal Display       
        $miscReceipt = '';
        if ($cpCfg['m.edukloud.order.hasMiscReceiptForPvt']){
            $miscReceipt = $this->getMiscReceiptPortalDisplay($row);
        }

        // This is used to display BOOK Receipt Portal Display(used in ent ims - simply islam) - not in use now
        $bookReceipt = '';
        if ($cpCfg['m.edukloud.order.hasBookReceipt']){
            $bookReceipt = $this->getBookReceiptPortalDisplay($row);
        }
        $bookReceipt = '';

        // This is used to display Insurance Link Portal
        if($cpCfg['m.edukloud.order.hasInsuranceLink'] && $rowCourse['course_type'] != 'Short Term'){
            $links .= $displayLinkData->getLinkPortalMain("edukloud_order", "edukloud_insuranceLink", "Insurance Linked", $row);
        }
        //getOrderItemPortalDisplayForPvt
        $text = "
        {$this->getOrderItemPortalDisplay($row)}
        {$this->getInvoicePortalDisplay($row)}
        {$creditNote}
        {$this->getReceiptPortalDisplay($row)}
        {$miscReceipt}
        {$bookReceipt}
        {$this->getRefundPortalDisplay($row)}
        {$paymentLink}
        {$links}
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
        
        // This is used to display Order Item Display for Pvt : used in Mass IMS     
        if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = '
            ,o.registration_type 
            ,o.medical_insurance
            ,o.add_registration_fee
            ,o.full_time
            ,cc.no_of_months
            ';
            $leftJoinAppend = '
                LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
            ';
        }
        
        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        {$leftJoinAppend}
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
            //hide check box for private schools
            if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
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
            
            if ($cpCfg['m.edukloud.order.hasCheckBoxForInvoiceItem']) {
                $check_box_header = "<th>Check</th>";
                $tdCheckBox = $tdCheckBox;
            } else {
                $tdCheckBox = '';
            }

            if ($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == false){
                $rows .= "
                <tr>
                    {$tdCheckBox}
                    <td>{$contactName}</td>
                    <td>{$module}</td>
                    <td>{$rowOrderItem['item_title']}</td>
                    <td align='right'>{$rowOrderItem['unit_price']}</td>
                    <td>{$invoice_code}</td>
                </tr>
                ";
            }
            $total += $rowOrderItem['unit_price'];
            
            //to capture registration type and medical insurance if applied
            if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
                $registration_type = $rowOrderItem['registration_type'];
            }
            $count++;
        }
        
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=6>Total : $total</td>
            </tr>
        ";
        
        $netTotal = '';
        
        //calculate total for PVT schools
        if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
            if($registration_type  == 'Only Registration'){
                $total =  $fn->getSettingsValueByKey("registrationFee");
                $regnText = '(Only Registration)';
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
            }
            else if($registration_type  == 'Registration & Enrollment'){
                $total    = $this->getTotalForPvtInst($resultForPvt);
                $netTotal = $this->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');
           }
            
            $expCourse   = array('condn' => " AND module='edukloud_course'");
            $expDiscount = array('condn' => " AND module='edukloud_discount'");
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

        $header ="
        <tr style='background-color:#EAEAE8;'>
        {$check_box_header}
        <th>Trainee</th>
        <th>Type</th>
        <th>Title</th>
        <th>Price/Cost</th>
        <th>Invoice Code</th>
        </tr>
        ";
        
        $formAction = "index.php?module=edukloud_order&_spAction=generateInvoice&showHTML=0";
        $formActionPvt = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateInvoiceFormPvt&showHTML=0&order_id={$row['order_id']}";

        $recordCount = $fn->getRecordCount('invoice', "order_id = {$row['order_id']}");
        // This is used to display Invoice for Pvt : Used for Mass IMS
        if($cpCfg['m.edukloud.ecommerce.order.invoiceForPvt']) {
            if ($recordCount > 0){
                $formAction = '';
                $invoiceBtn = "<button  disabled='disabled' class='button mt5 ml5 mb20'>Generate Invoice</button>";
                $invoiceBtn = "<br>";
            } else {
                $invoiceBtn = "
                <button href='{$formActionPvt}' id='generateInvoicePvt' class='button mt5 ml5 mb20'>Generate Invoice</button>
                ";
            }
        } else if ($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForEnt'] == true) {
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateMonthlyInvoiceForEntForm&showHTML=0&order_id={$row['order_id']}";
            $invoiceBtn = "
            <button href='{$formAction}' id='generateMonthlyInvoice' class='button mt5 ml5 mb20'>Generate Monthly Invoice</button>
            ";
        } else {
            $invoiceBtn = "
            <button id='generateInvoice' class='button mt5 ml5 mb20'>Generate Invoice</button>
            ";
        }

        $text = "
        <tr class=''>
        <td>
            <div id='edukloud_company#edukloud_orderLink' class=''>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$discount}
                    {$trMedicalInsurance}
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
        
        if ($cpCfg['m.edukloud.ecommerce.order.invoiceForPvt'] == true){
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
        } else if ($cpCfg['m.edukloud.order.invoiceForEnt']){
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
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

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
                if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
                    if ($rowInvoice['status'] == 'Due' 
                     || $rowInvoice['status'] == '' 
                     || $rowInvoice['status'] == 'Partial Payment'
                    ) {
                        $editURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=editInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$row['order_id']}";
                        $editRow = "<td><a href='{$editURL}' id='editInvoicePvt'>Edit</a></td>";
                    }
                }
                //$editRow = '';

                $deleteRow = '';
                if ($cpCfg['m.edukloud.order.hasDeleteInvoiceForPvt']){
                    $deleteURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=deleteInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}";
                    $deleteRow = "<td><a href='{$deleteURL}' id='deleteInvoicePvt' invoice_id='{$rowInvoice['invoice_id']}'>Delete</a></td>";
                }

                $month = '';
                $tdRow = '';
                if ($cpCfg['m.edukloud.order.invoiceForEnt']) {
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
                }
                
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
                    {$editRow}
                    {$deleteRow}
                </tr>
                ";
            }

            if ($cpCfg['m.edukloud.ecommerce.order.invoiceForPvt'] == 1){
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
        if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
            $editHeader = "<th>Edit</th>";
        }
                
        $deleteHeader = '';
        if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
            $deleteHeader = "<th>Delete</th>";
        }
                
        if ($cpCfg['m.edukloud.order.invoiceForEnt']) {
            $thHeader = "<th>Contact Name</th>";
        }
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        {$thHeader}
        <th>Date</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Receipt Code</th>
        <th>Print</th>
        <th>Cancel</th>
        {$editHeader}
        {$deleteHeader}
        </tr>
        ";
        
        $formActionCreditNote = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateCreditNoteForm&showHTML=0&order_id={$row['order_id']}";

        $creditNote = '';
        if ($cpCfg['m.edukloud.order.hasCreditNoteLink']){
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
            $creditNote = "
            <button href='{$formActionCreditNote}' id='generateCreditNote' 
            class='button mt5 ml5 mb20'>Generate Credit Note</button>
            </form>
            ";
        }

        // This is used to display Receipt for Pvt : Used for Mass IMS
        $miscReceiptBtn = '';
        if ($cpCfg['m.edukloud.order.hasMiscReceipt']){
            $miscFormAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateMiscReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";

            $miscReceiptBtn = "
            <button href='{$miscFormAction}' id='generateMiscReceiptPvt' 
            class='button mt5 ml5 mb20'>Generate Misc Receipt</button>
            ";  
        }
        
        // This is used to display Receipt for Enterprise IMS : Used for Simply Islam
        $bookReceiptBtn = '';
        if ($cpCfg['m.edukloud.order.hasBookReceipt']){
            $bookFormAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateBookReceiptForm&showHTML=0&order_id={$row['order_id']}";

            $bookReceiptBtn = "
            <button href='{$bookFormAction}' id='generateBookReceipt' 
            class='button mt5 ml5 mb20'>Generate Book Receipt</button>
            ";  
        }
        $bookReceiptBtn = '';
        
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
        if($cpCfg['m.edukloud.ecommerce.order.receiptForPvt']) {
            if ($invoiceRec['status'] == 'Paid'){
                $formAction = '';
                $receiptBtn = '';
            } else {            
                $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
                $receiptBtn = "
                <button href='{$formAction}' id='generateReceiptPvt' 
                class='button mt5 ml5 mb20'>Generate Receipt</button>
                "; 
            }
        } else if ($cpCfg['m.edukloud.order.receiptForEnt']) {
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptForEntForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceiptEnt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
        } else {
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceipt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
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
            
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getInvoiceRecords($row)}
                    </div>
                </form>                 
                {$receiptBtn}
                {$miscReceiptBtn}
                {$bookReceiptBtn}
                {$creditNote}
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
        
        if ($cpCfg['m.edukloud.ecommerce.order.invoiceForPvt'] == true){
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
        } else if ($cpCfg['m.edukloud.order.invoiceForEnt']){
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
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printInvoiceIndividual&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printInvoice&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";
            }
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

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
                if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
                    if ($rowInvoice['status'] == 'Due' 
                     || $rowInvoice['status'] == '' 
                     || $rowInvoice['status'] == 'Partial Payment'
                    ) {
                        $editURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=editInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$row['order_id']}";
                        $editRow = "<td><a href='{$editURL}' id='editInvoicePvt'>Edit</a></td>";
                    }
                }
                //$editRow = '';

                $deleteRow = '';
                if ($cpCfg['m.edukloud.order.hasDeleteInvoiceForPvt']){
                    $deleteURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=deleteInvoiceFormPvt&showHTML=0&invoice_id={$rowInvoice['invoice_id']}";
                    $deleteRow = "<td><a href='{$deleteURL}' id='deleteInvoicePvt' invoice_id='{$rowInvoice['invoice_id']}'>Delete</a></td>";
                }

                $month = '';
                $tdRow = '';
                if ($cpCfg['m.edukloud.order.invoiceForEnt']) {
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

                $reportLink = "index.php?module=edukloud_order&_spAction=printInvoiceInFpdf&record_id={$rowInvoice['invoice_id']}&showHTML=0";
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

            if ($cpCfg['m.edukloud.ecommerce.order.invoiceForPvt'] == 1){
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
        if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
            $editHeader = "<th>Edit</th>";
        }
                
        $deleteHeader = '';
        if ($cpCfg['m.edukloud.order.hasEditInvoiceForPvt']){
            $deleteHeader = "<th>Delete</th>";
        }
                
        if ($cpCfg['m.edukloud.order.invoiceForEnt']) {
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
        
        $formActionCreditNote = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateCreditNoteForm&showHTML=0&order_id={$row['order_id']}";

        $creditNote = '';
        if ($cpCfg['m.edukloud.order.hasCreditNoteLink']){
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
            $creditNote = "
            <button href='{$formActionCreditNote}' id='generateCreditNote' 
            class='button mt5 ml5 mb20'>Generate Credit Note</button>
            </form>
            ";
        }

        // This is used to display Receipt for Pvt : Used for Mass IMS
        $miscReceiptBtn = '';
        if ($cpCfg['m.edukloud.order.hasMiscReceipt']){
            $miscFormAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateMiscReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";

            $miscReceiptBtn = "
            <button href='{$miscFormAction}' id='generateMiscReceiptPvt' 
            class='button mt5 ml5 mb20'>Generate Misc Receipt</button>
            ";  
        }
        
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
        if($cpCfg['m.edukloud.ecommerce.order.receiptForPvt']) {
            if ($invoiceRec['status'] == 'Paid'){
                $formAction = '';
                $receiptBtn = '';
            } else {            
                $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormPvt&showHTML=0&order_id={$row['order_id']}";
                $receiptBtn = "
                <button href='{$formAction}' id='generateReceiptPvt' 
                class='button mt5 ml5 mb20'>Generate Receipt</button>
                "; 
            }
        } else if ($cpCfg['m.edukloud.order.receiptForEnt']) {
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptForEntForm&showHTML=0&order_id={$row['order_id']}";
            $receiptBtn = "
            <button href='{$formAction}' id='generateReceiptEnt' 
            class='button mt5 ml5 mb20'>Generate Receipt</button>
            ";
        } else {
            $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptForm&showHTML=0&order_id={$row['order_id']}";
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
    function getCreditNotePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);
        
        $SQL = "
        SELECT cn.*
            ,i.invoice_code
        FROM credit_note cn 
        LEFT JOIN `invoice` i ON (cn.invoice_id = i.invoice_id)
        WHERE cn.order_id = {$row['order_id']}
        ORDER BY cn.credit_note_id
        ";
        $result   = $db->sql_query($SQL);  

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        
        while ($rowCredit = $db->sql_fetchrow($result)) {
            
            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowCredit['credit_note_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowCredit['credit_note_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_creditNote&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";
            
            $rows .= "
            <tr>
                <td>{$rowCredit['credit_note_code']}</td>
                <td>{$rowCredit['date']}</td>
                <td align='right'>{$rowCredit['amount']}</td>
                <td align='right'>{$rowCredit['invoice_code']}</td>
                <td>{$rowCredit['created_by']}</td>
                <td><a href='{$mediaLink}'>Print Credit Note</a></td>
            </tr>
            ";
            $total += $rowCredit['amount'];
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=6>Total : $total</td>
            </tr>
        ";
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Code</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Invoice Code</th>
        <th>Created By</th>
        <th>Print</th>
        </tr>
        ";
        
        $text = "
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper edukloud_company__edukloud_orderLink'>
            <h2>Credit Note(s)</h2>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
            </div>
        </td>
        </tr>
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
        
        if ($cpCfg['m.edukloud.order.hasMiscReceiptForPvt']) {
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
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceiptIndividual&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $editRow = '';
            if ($cpCfg['m.edukloud.order.hasEditReceiptForPvt'] && $count == $numRows){
                $editURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=editReceiptFormPvt&receipt_id={$rowReceipt['receipt_id']}&order_id={$row['order_id']}&showHTML=0";
                $editRow = "<td><a href='{$editURL}' class='editReceiptPvt'>Edit</a></td>";
            }

            $receipt_date = $dateUtil->formatDate($rowReceipt['date'], 'DD MMM YYYY');
            
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code={$rowReceipt['receipt_code']}>Cancel Receipt</a>";
            }
            
            $reportLink = "index.php?module=edukloud_order&_spAction=printReceiptInFpdf&record_id={$rowReceipt['receipt_id']}&showHTML=0";
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
        if ($cpCfg['m.edukloud.order.hasEditReceiptForPvt']){
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
        
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $refundButton = '';
        if ($cpCfg['m.edukloud.order.hasRefund']){
            $refundButton = "
            <button href='{$formAction}' id='generateRefund' 
            class='button mt5 ml5 mb20'>Generate Refund</button>
            ";
        }

        $text = "
        <tr class=''>
        <td>
            <div id='receiptPortalOuter' class='linkPortalWrapper edukloud_company__edukloud_orderLink'>
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
    function getMiscReceiptPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);
        
        $SQL = "
        SELECT r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$row['order_id']}
          AND irh.receipt_type = 'misc receipt'
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);  

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        
        while ($rowReceipt = $db->sql_fetchrow($result)) {
            
            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceiptIndividual&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd/m/Y');
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$rowReceipt['created_by']}</td>
                <td>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$mediaLink}'>Print Misc Receipt</a></td>
            </tr>
            ";
            $total += $rowReceipt['amount'];
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Code</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Created By</th>
        <th>Status</th>
        <th>Print</th>
        </tr>
        ";
        
        $text = "
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper edukloud_company__edukloud_orderLink'>
            <h2>Misc Receipt(s)</h2>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
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
    function getRefundPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);
        
        if ($receiptRec['receipt_id'] == '') {
            return;
        }
        
        $SQL = "
        SELECT r.*
        FROM refund r 
        WHERE r.receipt_id = {$receiptRec['receipt_id']}
        ORDER BY r.refund_id
        ";
        $result   = $db->sql_query($SQL);  

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        
        while ($rowRefund = $db->sql_fetchrow($result)) {
            
            if ($row['contact_type'] == 'Indvidual') {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printRefundIndividual&refund_code={$rowRefund['refund_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printRefund&refund_code={$rowRefund['refund_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND room_name = 'edukloud_refund' AND actual_file_name LIKE '%{$rowRefund['refund_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowRefund['refund_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_refund&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$rowRefund['refund_code']}</td>
                <td align='right'>{$rowRefund['amount']}</td>
                <td>{$rowRefund['created_by']}</td>
                <!--<td><a href='{$urlPrint}' target='_blank'>Print Refund</a></td>-->
                <td><a href='{$mediaLink}'>Print Refund</a></td>
            </tr>
            ";
            $total += $rowRefund['amount'];
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=6>Total : $total</td>
            </tr>
        ";
        
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Code</th>
        <th>Amount</th>
        <th>Created By</th>
        <th>Print</th>
        </tr>
        ";
        
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}";

        $text = "
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper edukloud_company__edukloud_orderLink'>
            <h2>Refund(s)</h2>
                <form id='orderItemPrint' class='' method='post' 
                action='{$formAction}'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
                <input type='hidden' name='order_id' value='{$row['order_id']}' />
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
            if($course_type == 'Long Term' && $row['module'] == 'edukloud_subject'){
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
            else if($course_type == 'Short Term' && $row['module'] == 'edukloud_course'){
                $total = $row['unit_price'];
            }
           
            //to get discount
           if($row['module'] == 'edukloud_discount'){
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

        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormSubmit&showHTML=0";

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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
               
        $order_id = $fn->getReqParam('order_id');

        unset($_SESSION['selectedInvoiceIds']);

        $rows = '';
         
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
            ORDER BY i.contact_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL);
        $repeat_name = '';
        
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
        
        $rows = "
        {$rows}
        <div class='clearfix'></div>
        ";
                
        $current_date = date('Y-m-d');
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        $reg_fees = $this->getRegFeeRowForStudentInReceiptForEntForm($result1);
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
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

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
     function getGenerateReceiptForEntForm12() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        
        $rows = '';
        
        $order_id = $fn->getReqParam('order_id');

        $sqlOI = "
        SELECT DISTINCT oi.order_item_id
              ,oi.order_id
              ,oi.contact_id
              ,c.first_name
              ,c.last_name
              ,(
              SELECT SUM(amount) AS prev_sum
              FROM invoice_receipt_history invHist
              WHERE invHist.invoice_id = i.invoice_id 
              ) as prev_inv_amount
              ,i.status
              ,i.invoice_amount
        FROM order_item oi
        LEFT JOIN (invoice i) ON (oi.order_id = i.order_id AND oi.contact_id = i.contact_id)
        LEFT JOIN (contact c) ON (oi.contact_id = c.contact_id)
        WHERE oi.order_id = {$order_id}
          AND oi.module = 'edukloud_course'
        ORDER BY c.first_name ASC
        ";
        
        $sqlOI = "
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
        ORDER BY i.contact_id, i.invoice_month
        ";
        
        $resultOI  = $db->sql_query($sqlOI);
        $numRows  = $db->sql_numrows($resultOI);
        
        $contact_id = '';
        $count = '';
        
        while ($rowOI = $db->sql_fetchrow($resultOI)) {
            $rowsHTML = '';
            $contact_name = $rowOI['first_name'] . ' ' . $rowOI['last_name'];

            if ($rowOI['status'] == 'Paid') {
                $status = $order_status;
            } else {
                if ($rowOI['prev_inv_amount']) {
                    $balance = $rowOI['invoice_amount'] - $rowOI['prev_inv_amount'];
                } else {
                    $balance = $rowOI['invoice_amount'];
                }
                $status = "
                    <input type='checkbox' name='invoiceCode[]' value='{$balance}' class='invoiceCode'>
                ";
            }
            
            $rowsHTML .="
            <td>{$status}</td>
            ";
            
            if($contact_id != $rowOI['contact_id'] || $contact_id == ''){
                $rows .= "
                <tr>
                    <td>{$contact_name}</td>
                    ";
            }
            $rows .= "
            {$rowsHTML}
            ";
            
            if($contact_id != $rowOI['contact_id'] && $contact_id != '' || $numRows == $count + 1){
                $rows .= "
                </tr>
                ";
            }
            $contact_id = $rowOI['contact_id'];
            $count++;
        }
        /*
        while ($rowOI = $db->sql_fetchrow($resultOI)) {
            $rowsHTML = '';
            $contact_name = $rowOI['first_name'] . ' ' . $rowOI['last_name'];
            
            for($i=1;$i<=12;$i++){
                $status = '';
                $wIncomeByStudentEnt = getCPWidgetObj('edukloud_incomeByStudentEnt');
                $order_status = $wIncomeByStudentEnt->view->getStudentPaymentStatus($rowOI['order_id'], $rowOI['contact_id'], $i);
                $contact_id = $rowOI['contact_id'];
                
                if ($rowOI['status'] == 'Paid') {
                    $status = $order_status;
                } else {
                    
                    if ($rowOI['prev_inv_amount']) {
                        $balance = $rowOI['invoice_amount'] - $rowOI['prev_inv_amount'];
                    } else {
                        $balance = $rowOI['invoice_amount'];
                    }

                    $fldName = "inv__{$order_id}__{$contact_id}__{$i}";
                    $status = "<input type='checkbox' name={$fldName} value='{$balance}' class='orderItem'>";
                }
            
                $rowsHTML .="
                <td>{$status}</td>
                ";
            }
            
            $rows .= "
            <tr>
                <td>{$contact_name}</td>
                {$rowsHTML}
                <td><a href='#'>Check All</a></td>
            </tr>
            ";
        }
        */
        
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormSubmit&showHTML=0";

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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>

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
     function getGenerateMonthlyInvoiceForEntFormOld() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        
        $order_id = $fn->getReqParam('order_id');

        $row = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
        $cRec = $fn->getRecordRowByID('course', 'course_id', $row['course_id']);

        if($row['course_subsidy_history_id'] > 0){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE csh.course_subsidy_history_id = {$row['course_subsidy_history_id']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
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
    
        if($row['discount'] > 0){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE csh.course_subsidy_history_id = {$row['discount']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
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
                $total = $cRec['price'] - $discTotal;
            }
        }

        $SQL = "
        SELECT c.price FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id  = {$order_id}
        ";
        $result = $db->sql_query($SQL);
        $rowCoursePrice = $db->sql_fetchrow($result);
               
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateMonthlyInvoiceForEntFormSubmit&showHTML=0";
        
        $day = array(
                       '01' => '01'
                      ,'02' => '02'
                      ,'03' => '03'
                      ,'04' => '04'
                      ,'05' => '05'
                      ,'06' => '06'
                      ,'07' => '07'
                      ,'08' => '08'
                      ,'09' => '09'
                      ,'10' => '10'
                      ,'11' => '11'
                      ,'12' => '12'
                      ,'13' => '13'
                      ,'14' => '14'
                      ,'15' => '15'
                      ,'16' => '16'
                      ,'17' => '17'
                      ,'18' => '18'
                      ,'19' => '19'
                      ,'20' => '20'
                      ,'21' => '21'
                      ,'22' => '22'
                      ,'23' => '23'
                      ,'24' => '24'
                      ,'25' => '25'
                      ,'26' => '26'
                      ,'27' => '27'
                      ,'28' => '28'
                      ,'29' => '29'
                      ,'30' => '30'
                      ,'31' => '31'
                      );

        $month = array(
                       '1'  => 'January'
                      ,'2'  => 'February'
                      ,'3'  => 'March'
                      ,'4'  => 'April'
                      ,'5'  => 'May'
                      ,'6'  => 'June'
                      ,'7'  => 'July'
                      ,'8'  => 'August'
                      ,'9'  => 'September'
                      ,'10' => 'October'
                      ,'11' => 'November'
                      ,'12' => 'December'
                      );
        $expArr = array('useKey' => 1);
        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar monthlyInvoiceForEntForm' method='post' action='{$formAction}'>
            <div class='edit_invoices'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$formObj->getDropDownRowByArray('Day', 'date_of_invoice', $day, '')}
            {$formObj->getTBRow('Amount', 'invoice_amount', $total, $expEdit)}
            {$formObj->getDropDownRowByArray('Start Month', 'start_month', $month, '', $expArr)}
            {$formObj->getDropDownRowByArray('End Month', 'end_month', $month, '', $expArr)}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
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
          AND oi.module = 'edukloud_course'
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
                $wIncomeByStudentEnt = getCPWidgetObj('edukloud_incomeByStudentEnt');
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
        
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateMonthlyInvoiceForEntFormSubmit&showHTML=0";

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
    
    /**
     *
     */
     function getGenerateCreditNoteForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
               
        $order_id = $fn->getReqParam('order_id');

        $rows = '';
         
        $sqlInvoice = "
        SELECT i.invoice_id
              ,i.invoice_code
        FROM invoice i
        WHERE i.order_id = {$order_id}
            AND status = 'Cancelled'
        ";

        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateCreditNoteFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar creditNoteForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Invoice Code', 'invoice_id', $sqlInvoice)}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
     function getGenerateRefundForm() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $order_id = $fn->getReqParam('order_id');
        $receipt_id = $fn->getReqParam('receipt_id');

        $rows = '';
         
        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <div class='floatbox'>
                <div class='float_left'>
                <input type='checkbox' name='receiptCode[]' value='{$row['receipt_code']}' class='receiptCode'>
                </div>
                <div>{$row['receipt_code']}</div>
            </div>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateRefundFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <h3>Please select Receipt</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
        </form>
        ";

        return $text;
    }
 
    /**
     *
     */
     function getGenerateReceiptFormPvt() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $order_id= $fn->getReqParam('order_id');

        $rows = '';
        $today = date('Y-m-d');
        $invoice_hist_amount ='';
         
        // TO Change for Installment
        $SQL = "
        SELECT i.*
            ,ir.invoice_receipt_history_id 	
            ,inst.amount as invoice_hist_amount
            ,inst.invoice_date as invoice_due_date
            ,inst.invoice_paid_status as invoice_hist_status
            ,ir.receipt_id as receipt_paid_id
            ,inst.title as invoice_hist_title
            ,inst.installment_id
            ,(SELECT SUM(amount) 
              FROM invoice_receipt_history ir1
              WHERE ir1.installment_id = inst.installment_id)
              as amount_already_paid
        FROM invoice i
        LEFT JOIN `installment` inst ON (inst.invoice_id = i.invoice_id)
        LEFT JOIN `invoice_receipt_history` ir ON (ir.installment_id = inst.installment_id)
        WHERE i.order_id = {$order_id}
        ORDER BY installment_id
        ";
        $SQL = "
        SELECT i.*
            ,inst.amount as invoice_hist_amount
            ,inst.invoice_date as invoice_due_date
            ,inst.invoice_paid_status as invoice_hist_status
            ,inst.title as invoice_hist_title
            ,inst.installment_id
            ,(SELECT SUM(amount) 
              FROM invoice_receipt_history ir1
              WHERE ir1.installment_id = inst.installment_id)
              as amount_already_paid
        FROM invoice i
        LEFT JOIN `installment` inst ON (inst.invoice_id = i.invoice_id)
        WHERE i.order_id = {$order_id}
          AND inst.installment_id IS NOT NULL
        ORDER BY installment_id
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $status = '';
            $amount_already_paid= '0';
            if($row['invoice_hist_status'] == 'Paid'){
                $status = 'DISABLED';
            }
            $invoice_id = $row['invoice_id'];
            $invoice_hist_amount = round($row['invoice_hist_amount'], 3);
            if($row['amount_already_paid'] != ''){
                $amount_already_paid = number_format($row['amount_already_paid'],2);
            }
            $rows .= "
            <div class='mb10'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceHistId[]' value='{$row['installment_id']}' class='invoiceCodePvt' $status>
                    </div>
                    <div class='float_left'>{$row['invoice_hist_title']} ({$invoice_hist_amount} SGD)</div>
                    <div class='float_left'> : {$row['invoice_hist_status']}</div>
                    <div class=''>Paid:{$amount_already_paid}SGD</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormSubmitPvt&showHTML=0";

        /* Hiding Receipt Code and COI no for Short term courses */
        $receipt_code = $formObj->getTBRow('Receipt Code', 'receipt_code');
        $coi_no       = $formObj->getTBRow('COI NO', 'coi_no');
        
        $SQLCourse = "
        SELECT c.course_type
        FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$order_id}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse);
        
        if ($rowCourse['course_type'] == 'Short Term') {
            $receipt_code = '';
            $coi_no = '';
        }
        
        $text = "
        <form id='portalForm' class='yform columnar receiptFormForPvt' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            <div class='form-row-wrapper receipt_invoices'>
                {$rows}
            </div>
            {$formObj->getTBRow('Amount', 'amount')}
            {$receipt_code}
            {$formObj->getDateRow('Receipt Date', 'date', $today)}
            {$coi_no}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'])}
            {$formObj->getTBRow('Approval Code', 'approval_code')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='course_type' value='{$rowCourse['course_type']}' />
        </form>
        ";

        return $text;
    }

    /**
     * Misc Receipt generation Form for Private Institution
     */
     function getGenerateMiscReceiptFormPvt() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $order_id= $fn->getReqParam('order_id');
        $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $order_id);

        $today = date('Y-m-d');
         
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateReceiptFormSubmitPvt&showHTML=0";

        /* Hiding Receipt Code and COI no for Short term courses */
        $receipt_code = $formObj->getTBRow('Receipt Code', 'receipt_code');
        $coi_no       = $formObj->getTBRow('COI NO', 'coi_no');
        
        $SQLCourse = "
        SELECT c.course_type
        FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$order_id}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse);
        
        if ($rowCourse['course_type'] == 'Short Term') {
            $receipt_code = '';
            $coi_no = '';
        }
        
        $late_fee                   = $fn->getSettingsValueByKey('miscReceiptLateFeeCharge');
        $module_subject_change_fee  = $fn->getSettingsValueByKey('miscReceiptModuleSubjectChangeFee');
        $exam_result_review_fee     = $fn->getSettingsValueByKey('miscReceiptExamResultReviewFee');
        $ns_deferment_fees            = $fn->getSettingsValueByKey('miscReceiptNSDefermentFee');
        $credit_card_service_fees   = $fn->getSettingsValueByKey('miscReceiptCreditCardServiceCharge');
        $other_charges              = $fn->getSettingsValueByKey('miscReceiptOtherCharge');
        
        $misc_total = $late_fee + $module_subject_change_fee + $exam_result_review_fee + $ns_deferment_fees + $credit_card_service_fees + $other_charges;
        
        $text = "
        <form id='portalForm' class='yform columnar miscReceiptFormForPvt' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Late Fees Charge', 'late_fees', $late_fee)}
            {$formObj->getTBRow('Change of modules/subjects Fees', 'module_subject_change_fee', $module_subject_change_fee)}
            {$formObj->getTBRow('Review of exam results Fees', 'exam_result_review_fee', $exam_result_review_fee)}
            {$formObj->getTBRow('Administration Fees for NS Deferment', 'ns_deferment_fees', $ns_deferment_fees)}
            {$formObj->getTBRow('Credit Card Service charge', 'credit_card_service_fees', $credit_card_service_fees)}
            {$formObj->getTBRow('Other Charge', 'other_charges', $other_charges)}
            {$formObj->getTBRow('Amount', 'amount', $misc_total)}
            {$receipt_code}
            {$formObj->getDateRow('Receipt Date', 'date', $today)}
            {$coi_no}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'])}
            {$formObj->getTBRow('Approval Code', 'approval_code')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoiceRec['invoice_id']}' />
            <input type='hidden' name='course_type' value='{$rowCourse['course_type']}' />
            <input type='hidden' name='receipt_type' value='misc receipt' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditInvoiceFormPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = '';
        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        
        if ($invoice_id == '') {
            return "Please select the Invoice";
        }

        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        
        $SQLOrder = "
        SELECT o.no_of_installment
        FROM `order` o
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        WHERE i.invoice_id = {$invoice_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        $SQLInstallment = "
        SELECT i.* FROM installment i
        WHERE i.invoice_id = {$invoiceRec['invoice_id']}
        ORDER BY i.installment_id ASC;
        ";
        $resultInstallment  = $db->sql_query($SQLInstallment);
        $numRowsInstallment = $db->sql_numrows($resultInstallment);
        
        $SQLInstallmentReg = "
        SELECT i.* FROM installment i
        WHERE i.invoice_id = {$invoiceRec['invoice_id']}
          AND i.title = 'Registration'
        ORDER BY i.installment_id ASC;
        ";
        $resultInstallmentReg  = $db->sql_query($SQLInstallmentReg);
        $numRowsInstallmentReg = $db->sql_numrows($resultInstallmentReg);
        
        if($numRowsInstallmentReg == 1){
            $numRowsInstallment = $numRowsInstallment -1;            
        }

        // To check if the no of installments entered new is greater
        if ($rowOrder['no_of_installment'] == $numRowsInstallment) {
        } else if ($rowOrder['no_of_installment'] > $numRowsInstallment) {
            $remaining_installments = $rowOrder['no_of_installment'] - $numRowsInstallment;
            
            $count = $numRowsInstallment + 1;
            for ($i=1; $i <= $remaining_installments; $i++) {
                $fa = array();
                $fa['invoice_id']    = $invoice_id;
                $fa['title']         = 'Installment' . $count;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']    = $fn->getSessionParam('userName');
                $histId              = $fn->addRecord($fa, 'installment');
                $count++;
            }
        } else if ($rowOrder['no_of_installment'] < $numRowsInstallment) {
            $remaining_installments = $numRowsInstallment - $rowOrder['no_of_installment'];
            
            $count = $rowOrder['no_of_installment'] + 1;
            for ($i=1; $i <= $remaining_installments; $i++) {
                $title = 'Installment' . $count;
                $SQLDelete = "
                DELETE FROM installment WHERE title = '{$title}' AND invoice_id = {$invoice_id}
                ";
                $resultDelete = $db->sql_query($SQLDelete);
                $count++;
            }
        }
        
        $SQLInst = $SQLInstallment;
        $resultInst = $db->sql_query($SQLInst);
        
        while ($rowInst = $db->sql_fetchrow($resultInst)) {
            $instId = $rowInst['installment_id'];
            
            $expEdit = array('isEditable' => 0);
            $amount = $rowInst['amount'];
            $rows .= "
            <div class='floatbox'>
                <div class='float_right'>{$formObj->getTBRow('Invoice Amount', "invoice_amount__{$instId}", $amount)}</div>
                <div class='invoiceDate float_right'>{$formObj->getDateRow('Date', "invoice_date__{$instId}", $rowInst['invoice_date'])}</div>
                <div class='installmentTitle'>{$formObj->getTBRow('', "", $rowInst['title'], $expEdit)}</div>
            </div>
            ";
        }
        
        $expEdit = array('isEditable' => 0);
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=editInvoiceFormSubmitPvt&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar editInvoiceFormPvt' method='post' action='{$formAction}'>
            <div class='edit_invoices'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$rows}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditReceiptFormPvt() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $receipt_id = $fn->getReqParam('receipt_id');
        $order_id   = $fn->getReqParam('order_id');

        $rowInvRecHist = $fn->getRecordRowByID('invoice_receipt_history', 'receipt_id', $receipt_id);

        $rowReceipt = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=editReceiptFormSubmitPvt&showHTML=0";
        
        $expEdit = array('isEditable' => 0);
        /* Hiding Receipt Code and COI no for Short term courses */
        $receipt_code = $formObj->getTBRow('Receipt Code', 'receipt_code', $rowReceipt['receipt_code'], $expEdit);
        $coi_no       = $formObj->getTBRow('COI NO', 'coi_no', $rowReceipt['coi_no'], $expEdit);
        
        $SQLCourse = "
        SELECT c.course_type
        FROM course c
        LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$order_id}
        ";
        $resultCourse = $db->sql_query($SQLCourse);
        $rowCourse = $db->sql_fetchrow($resultCourse);
        
        if ($rowCourse['course_type'] == 'Short Term') {
            $receipt_code = '';
            $coi_no = '';
        }
        
        $receipt_date = $dateUtil->formatDate($rowReceipt['date'], 'YYYY-MM-DD');
        $cheque_date  = $dateUtil->formatDate($rowReceipt['cheque_date'], 'YYYY-MM-DD');
        
        if ($rowReceipt['mode_of_payment'] == 'Cheque') {
            $paymentMode = array('rowCls' => 'showme');
        } else {
            $paymentMode = array('rowCls' => 'hideme');
        }
        
        $rows = '';
        $SQLIrh = "
        SELECT i.title
              ,irh.amount
        FROM installment i
        LEFT JOIN (invoice_receipt_history irh) ON (i.installment_id = irh.installment_id)
        WHERE irh.receipt_id = {$receipt_id}
          AND irh.amount > 0
        ORDER BY i.installment_id ASC
        ";
        $resultIrh = $db->sql_query($SQLIrh);
        while ($rowIrh = $db->sql_fetchrow($resultIrh)) {
            $amount = number_format($rowIrh['amount'], 2);
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>{$rowIrh['title']}</div>
                    <div class=''>{$amount} SGD</div>
                </div>
            </div>
            ";
        }

        $text = "
        <form id='portalForm' class='yform columnar editReceiptFormForPvt receiptForm' method='post' action='{$formAction}'>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount', $rowReceipt['amount'])}
            {$receipt_code}
            {$formObj->getDateRow('Receipt Date', 'date', $receipt_date)}
            {$coi_no}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment', 'paymentType', $rowReceipt['mode_of_payment'])}
            {$formObj->getTBRow('Cheque No', 'cheque_no', $rowReceipt['cheque_no'], $paymentMode)}
            {$formObj->getDateRow('Cheque date', 'cheque_date', $cheque_date, $paymentMode)}
            {$formObj->getTBRow('Bank', 'bank_name', $rowReceipt['bank_name'], $paymentMode)}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'])}
            {$formObj->getTBRow('Approval Code', 'approval_code', $rowReceipt['approval_code'])}
            {$formObj->getTextAreaRow('Note', 'remarks', $rowReceipt['remarks'])}
            <input type='hidden' name='receipt_id' value='{$receipt_id}' />
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$rowInvRecHist['invoice_id']}' />
        </form>
        ";

        return $text;
    }
    /**
     * Misc Receipt generation Form for Private Institution
     */
     function getGenerateBookReceiptForm() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $order_id= $fn->getReqParam('order_id');

        $today = date('Y-m-d');
         
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateBookReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar bookReceiptForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Book Fees', 'amount')}
            {$formObj->getDateRow('Receipt Date', 'date', $today)}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Issued By', 'issued_by', $_SESSION['userFullName'])}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getBookReceiptPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);
        
        if ($cpCfg['m.edukloud.order.hasMiscReceiptForPvt']) {
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
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceiptIndividual&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            } else {
                $urlPrint = "index.php?_topRm=finance&module=edukloud_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";
            }

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=edukloud_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $editRow = '';
            if ($cpCfg['m.edukloud.order.hasEditReceiptForPvt'] && $count == $numRows){
                $editURL = "index.php?_topRm=finance&module=edukloud_order&_spAction=editReceiptFormPvt&receipt_id={$rowReceipt['receipt_id']}&order_id={$row['order_id']}&showHTML=0";
                $editRow = "<td><a href='{$editURL}' class='editReceiptPvt'>Edit</a></td>";
            }

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');
            
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code={$rowReceipt['receipt_code']}>Cancel Receipt</a>";
            }
            
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$rowReceipt['created_by']}</td>
                <td>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$mediaLink}'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
                {$editRow}
            </tr>
            ";
            $total += $rowReceipt['amount'];
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";
        
        $editHeader = '';
        if ($cpCfg['m.edukloud.order.hasEditReceiptForPvt']){
            $editHeader = "<th>Edit</th>";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Code</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Created By</th>
        <th>Status</th>
        <th>Print</th>
        <th>Cancel</th>
        {$editHeader}
        </tr>
        ";
        
        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $refundButton = '';
        if ($cpCfg['m.edukloud.order.hasRefund']){
            $refundButton = "
            <button href='{$formAction}' id='generateRefund' 
            class='button mt5 ml5 mb20'>Generate Refund</button>
            ";
        }

        $text = "
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper edukloud_company__edukloud_orderLink'>
            <h2>Book Receipt(s)</h2>
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

}