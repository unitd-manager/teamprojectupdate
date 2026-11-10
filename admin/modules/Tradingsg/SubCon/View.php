<?
class CPL_Admin_Modules_Tradingsg_SubCon_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListRowEnd($row['sub_con_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Website', 'a.website')}
        {$listObj->getListHeaderCell('Telephone', 'a.phone' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getTBRow('Sub Con Name', 'company_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $createLogin = '';
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');
        //<td>{$formObj->getTBRow('Discount Percent', 'discount_percent', $row['discount_percent'])}</td>


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Sub Con Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    {$createLogin}
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Name', 'company_name', $row['company_name'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Fax', 'fax', $row['fax'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                                <td width='20%'>{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Address</th>
                            </tr>

                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Postal Code', 'address_state', $row['address_state'])}</td>
                                <td width='20%'>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit1($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $createLogin = '';
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Supplier Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    {$createLogin}
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'company_name', $row['company_name'])}</td>
                                <td>{$formObj->getTBRow('Website', 'website', $row['website'])}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td>{$formObj->getTBRow('Alternate Email', 'notification_email', $row['notification_email'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('TIN No.', 'tin_no', $row['tin_no'])}</td>
                                <td>{$formObj->getTBRow('CST No.', 'cst_no', $row['cst_no'])}</td>
                            </tr>

                            <tr>
                                <th colspan='6'>Supplier Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <th colspan='6'>Return Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'return_address_flat', $row['return_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'return_address_street', $row['return_address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'return_address_town', $row['return_address_town'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'return_address_state', $row['return_address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'return_address_country', $sqlCountry, $row['return_address_country'], $expCountry)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    function getEdit2($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';
        
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');
        $sqlGroupName = $fn->getValueListSQL('companyGroupName');
        $sqlCustomerType = $fn->getValueListSQL('customerType');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        if ($cpCfg['m.tradingsg.company.hasDiscountPercent']) {
            $discountPercent = $formObj->getTBRow('Discount Percent', 'discount_percent', $row['discount_percent']);
        }

        if ($cpCfg['m.tradingsg.company.hasCstNo']) {
            $cstNo = $formObj->getTBRow('Cst No', 'cst_no', $row['cst_no']);
        }

        if ($cpCfg['m.tradingsg.company.hasTinNo']) {
            $tinNo = $formObj->getTBRow('Tin No', 'tin_no', $row['tin_no']);
        }

        if ($cpCfg['m.tradingsg.company.hasGstNo']) {
            $gstNo = $formObj->getTBRow('Gst No', 'gst_no', $row['gst_no']);
        }

        //{$formObj->getDDRowBySQL('Customer Type', 'customer_type', $sqlCustomerType, $row['customer_type'], $expVl)}
        //{$formObj->getYesNoRRow('Add FREIGHT COST', 'add_freight_cost', $row['add_freight_cost'])}

        $fieldset1 = "
        {$formObj->getTBRow('Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        ";


        $fieldset2 = "
        {$formObj->getTBRow('Office Address', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('Postal Code', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";

        $fieldset3 = "
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Supplier Type', 'supplier_type', $sqlSupplier, $row['supplier_type'], $expVl)}
        {$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expVl)}
        {$formObj->getDDRowBySQL('Company Size', 'company_size', $sqlSize, $row['company_size'], $expVl)}
        {$formObj->getDDRowBySQL('Company Source', 'source', $sqlSource, $row['source'], $expVl)}
        {$discountPercent}
        {$cstNo}
        {$tinNo}
        {$gstNo}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Address', $fieldset2)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getCreateLoginForm() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $sub_con_id = $fn->getReqParam('sub_con_id');
        $email = $fn->getReqParam('email');

        $formAction = "index.php?_topRm=utils&module=tradingsg_subCon&_spAction=createLoginFormSubmit&showHTML=0";

        $text = "
        <form id='createLoginForm' class='createLoginForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('First Name', 'first_name', '')}
            {$formObj->getTBRow('Last Name', 'last_name', '')}
            {$formObj->getTBRow('Email', 'email', $email)}
            {$formObj->getTBRow('Password', 'pass_word', '')}
            <input type='hidden' name='sub_con_id' value='{$sub_con_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_subCon', 'attachment', $row)}
        ";

        $sqlSupplier = "
        SELECT s.*
        FROM sub_con s
        WHERE s.sub_con_id = {$row['sub_con_id']}
        ";

        $resultSupplier = $db->sql_query($sqlSupplier);
        $rowSupplier = $db->sql_fetchrow($resultSupplier);

        $printText ="";
        if ($rowSupplier['sub_con_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddPurchaseOrder($row['sub_con_id'])}</div>
            ";
        }
        $text=$printText;

        return $text;
    }
    /**
     *
     */
    function getAddPurchaseOrder($sub_con_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($sub_con_id == ''){
            $sub_con_id = $fn->getReqParam('sub_con_id');
        }

        $PurchaseOrder = $this->getAddPurchaseOrderDetail($sub_con_id);

        $recCount = $fn->getRecordCount('sub_con_work_order', "sub_con_id = '{$sub_con_id}'");

        $header ="
        <thead>
            <tr>
                <th width='8%' >Date</th>
                <th width='8%' >Project</th>
                <th width='8%' >WO Code</th>
                <th width='14%' class='txtRight'>Amount</th>
                <th width='15%'>Status</th>
                <th width='15%' class='txtRight'>Balance</th>
                <th width='15%'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $actionButtons = '';

        $SQLPO = "
        SELECT p.sub_con_work_order_id
        FROM sub_con_work_order p
        WHERE p.sub_con_id = {$sub_con_id}
        AND (p.status != 'Cancelled'
        OR p.status IS NULL)
        ";
        $resultPO = $db->sql_query($SQLPO);
        $numRowsPO = $db->sql_numrows($resultPO);

        if($numRowsPO > 0){
            $formActionPurchaseOrder = "index.php?module=tradingsg_subCon&_spAction=generateWorkOrderForm&sub_con_id={$sub_con_id}&showHTML=0";

            $actionButtons .="
            <div class='header'>
                <div class='floatbox'>
                    <div class='btn btn-info'>
                        <a href='{$formActionPurchaseOrder}' id='generatePO'>Make Sub Con Payment</a>
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        <div class='linkPortalWrapper tradingsg_subCon__tradingsg_sub_con_work_orderLink'>
            {$actionButtons}
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Work Order Linked</div>
                    <div class='txtRight float_right'>
                        <span class='count'>({$recCount})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody>
                            {$PurchaseOrder}
                        </tbody>
                    </table>
                    <input type='hidden' name='sub_con_id' value='{$sub_con_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddPurchaseOrderDetail($sub_con_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($sub_con_id == ''){
            $sub_con_id = $fn->getReqParam('sub_con_id');
        }

        //$sub_con_id = $fn->getReqParam('sub_con_id');

        $rows  = "";

        $SQL="
        SELECT pc.*
        FROM sub_con_work_order pc
        LEFT JOIN sub_con su ON pc.sub_con_id = su.sub_con_id
        WHERE pc.sub_con_id = {$sub_con_id}
        order by sub_con_work_order_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $sub_con_work_order_date = $fn->getCPDate($row['work_order_date'], 'd-m-Y');

            $SQLTotal = "
                SELECT SUM(round((pop.amount),2)) AS total_cost
                FROM work_order_line_items pop WHERE pop.sub_con_work_order_id = {$row['sub_con_work_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $totalCost = number_format($rowTotal['total_cost'], 2);

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM sub_con_payments_history srh
            LEFT JOIN sub_con_payments sr ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
            WHERE srh.sub_con_work_order_id = {$row['sub_con_work_order_id']}
              AND sr.status    != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

            $Balance = $rowTotal['total_cost'] - $rowPartialPayment['Po_partial_payment'];
            $Balance = number_format($Balance, 2);
            $viewHistoryUrl = "index.php?module=tradingsg_subCon&_spAction=receiptHistoryForSupplier&sub_con_work_order_id={$row['sub_con_work_order_id']}&showHTML=0";
            $viewHistory = "
            <a href='{$viewHistoryUrl}' sub_con_work_order_id='{$row['sub_con_work_order_id']}' class='receiptViewHistory'><u>Receipts</u></a>";

            $projRec = $fn->getRecordRowByID('project', 'project_id', $row['project_id']);
            $proTitle = "<a href='index.php?module=enggCrm_project&record_id={$row['project_id']}&_action=edit' target='_blank'><u>{$projRec['title']}</u></a>";

            $rows .= "
                <tr>
                    <td width='8%' >{$sub_con_work_order_date}</td>
                    <td width='8%' >{$proTitle}</td>
                    <td width='8%' >{$row['sub_con_worker_code']}</td>
                    <td width='14%' class='txtRight'>{$totalCost}</td>
                    <td width='15%'>{$row['status']}</td>
                    <td width='15%' class='txtRight'>{$Balance}</td>
                    <td width='15%'>{$viewHistory}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
    /**
     *
     */
    function getGenerateWorkOrderForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $sub_con_id = $fn->getReqParam('sub_con_id');

        unset($_SESSION['selectedWOIds']);

        $rows = '';
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(supHist.amount) AS prev_sum
            FROM sub_con_payments_history supHist
            LEFT JOIN sub_con_payments r ON (r.sub_con_payments_id = supHist.sub_con_payments_id)
            WHERE supHist.sub_con_work_order_id =  i.sub_con_work_order_id
            AND r.status != 'Cancelled'
            ) as prev_inv_amount
        FROM sub_con_work_order i
        LEFT JOIN `sub_con` o ON (i.sub_con_id = o.sub_con_id)
        WHERE i.sub_con_id = {$sub_con_id}
        AND (i.status = 'Due' || i.status = 'Partially Paid' || i.status = 'New' || i.status IS NULL || i.status = 'Hold' || i.status = 'Confirmed')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no work order is available or all the work orders are closed" ;
        }

        $count = 1;
        $po_amount = 0;
        $prev_inv_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $sqlQty = "
            SELECT SUM(pop.amount) AS po_amount
            FROM work_order_line_items pop
            WHERE pop.sub_con_work_order_id = {$row['sub_con_work_order_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);
            $po_amount = $rowQty['po_amount'];

            $paidAmountPrev = "";
            $prev_inv_amount = number_format($row['prev_inv_amount'], 2);
            if($row['prev_inv_amount'] > 0){
                $paidAmountPrev = "Paid: {$prev_inv_amount}";
            }

            $po_amount = number_format($po_amount, 2);
            $project_id = $row['project_id'];

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='poCode[]' value='{$row['sub_con_worker_code']}' sub_con_work_order_id='{$row['sub_con_work_order_id']}' class='poCode'>
                    </div>
                    <div class='float_left'>{$row['sub_con_worker_code']}({$po_amount})</div>
                    <div class=''>{$paidAmountPrev}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?module=tradingsg_subCon&_spAction=generateWorkOrderFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Work Order</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='sub_con_id' value='{$sub_con_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getNewSupplier(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addSupplier&lnkRoom=tradingsg_subCon&showHTML=0";
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name', 'company_name')}
                {$formObj->getTBRow('Website', 'website')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getTBRow('Gst No', 'gst_no')}
                {$formObj->getTBRow('Office Address', 'address_flat')}
                {$formObj->getTBRow('Street Address', 'address_street')}
                {$formObj->getTBRow('District/ Town', 'address_town')}
                {$formObj->getTBRow('Postal Code', 'address_state')}
                {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry)}
            </fieldset>
            
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptHistoryForSupplier() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $sub_con_work_order_id    = $fn->getReqParam('sub_con_work_order_id');

        $rows = '';
        $errorText = '';

        $sqlClient = "
        SELECT sr.amount
              ,sr.creation_date AS date
              ,sr.mode_of_payment
              ,sr.status
              ,sr.sub_con_payments_id
              ,sr.sub_con_id
              ,srh.sub_con_work_order_id
        FROM sub_con_payments_history srh
        LEFT JOIN (sub_con_payments sr) ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
        WHERE srh.sub_con_work_order_id = {$sub_con_work_order_id}
        ORDER BY srh.sub_con_payments_history_id
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        if ($numRows == 0) {
            $clientRows =  "
            <table class='thinlist'>
                <td>Sorry, no previous payment records for this Sub Con</td>
            </table>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $date = $fn->getCPDate($row['date'], 'd-m-Y');
                if ($row['status'] != 'Cancelled'){
                    $cancelSupplierReceipt = "<a href='#' class='cancelSupplierReceipt' sub_con_payments_id='{$row['sub_con_payments_id']}' sub_con_work_order_id='{$row['sub_con_work_order_id']}' sub_con_id='{$row['sub_con_id']}'><u>Cancel</u></a>";
                } else{
                    $cancelSupplierReceipt = 'Cancelled';                    
                }

                $rows .= "
                <tr>
                    <td>{$date}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['mode_of_payment']}</td>
                    <td>{$cancelSupplierReceipt}</td>
                </tr>
                ";
            }

            $clientRows = "
            <table class='thinlist'>
                <thead>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Mode of Payment</th>
                    <th></th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }

        $text ="
        {$clientRows}
        ";

        return $text;
    }
}