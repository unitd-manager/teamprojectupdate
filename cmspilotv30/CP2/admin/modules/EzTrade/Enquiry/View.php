<?
class CP_Admin_Modules_EzTrade_Enquiry_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['enquiry_code'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['subject'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['followup_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListRowEnd($row['enquiry_id'])}
            ";
            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Enquiry Number', 'e.enquiry_code')}
        {$listObj->getListHeaderCell('Client', 'company_name')}
        {$listObj->getListHeaderCell('Contact Person', 'e.last_name')}
        {$listObj->getListHeaderCell('Enquiry Title', 'e.subject')}
        {$listObj->getListHeaderCell('Enquiry Date', 'e.creation_date')}
        {$listObj->getListHeaderCell('Status', 'e.status')}
        {$listObj->getListHeaderCell('Required Response Date', 'e.followup_date')}
        {$listObj->getListHeaderCell('Staff', 'e.staff_name')}
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
        $fn = Zend_Registry::get('fn');

        $sqlCompanyName = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Customer'"));

        $fieldset = "
        {$formObj->getTBRow('Enquiry Title', 'subject')}
        {$formObj->getDDRowBySQL('Client Name', 'company_id_customer', $sqlCompanyName)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expNoEdit = array('isEditable' => 0);
        $expComp = array('displayText' => $row['company_name']);
        $companyText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', $row['company_id_customer'], $expComp);

        $expSalesAgent = array('displayText' => $row['sales_agent']);
        $salesAgentText = $fn->getRecordDetailLink('ezTrade_company', 'record_id',
                               $row['company_id_sales_agent'], $expSalesAgent);
        $expSalesAgentFrm = array('detailValue' => $salesAgentText);
        $sqlSalesAgent = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Sales Agent'"));

        $expContact = array('detailValue' => $row['contact_name']);
        $modContact = getCPModuleObj('ezTrade_contact');
        $sqlCustomerContact = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('ezTrade_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id_customer']);

        $sqlCurrency = $fn->getValueListSQL('currency');
        $expVl      = array('sqlType' => 'OneField');

        $expStaff   = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $modDeliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $sqlDeliveryTermsCustomer = $modDeliveryTerms->model->getDeliveryTermsSupplierSQL($row['company_id_customer']);

        $fieldset1 = "
        {$formObj->getTBRow('Enquiry Number', 'enquiry_id', $row['enquiry_code'], $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'company_id_customer', $companyText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerContact, $row['contact_id_customer'], $expContact)}
        {$formObj->getTBRow('Enquiry Title', 'subject', $row['subject'])}
        {$formObj->getDateRow('Enquiry Date', 'enquiry_date', $row['enquiry_date'])}
        {$formObj->getDDRowByArr('Enquiry Status', 'status', $cpCfg['m.trading.enquiry.statusArr'], $row['status'])}
        {$formObj->getDateRow('Required Response Date', 'followup_date', $row['followup_date'])}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager, $row['staff_id'], $expStaff)}
        {$formObj->getDDRowBySQL('Delivery Terms', 'delivery_terms', $sqlDeliveryTermsCustomer, $row['delivery_terms'], $expVl)}
        {$formObj->getTBRow('Client Reference Number', 'customer_rfq_code', $row['customer_rfq_code'])}
        {$formObj->getDDRowByArr('Shipping Method', 'shipping_method', $cpCfg['m.trading.enquiry.shippingMethodArr'], $row['shipping_method'])}
        {$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc)}
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getDDRowBySQL('Sales Agent', 'company_id_sales_agent', $sqlSalesAgent, $row['company_id_sales_agent'], $expSalesAgentFrm)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Enquiry Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $fnLink = Zend_Registry::get('fnLink');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        //return;

        $links = "";
        $record_id = $fn->getIssetParam($row, 'enquiry_id');

        $links .= "
        {$displayLinkData->getLinkPortalMain("ezTrade_enquiry", "ezTrade_productLink", "Enquiry Line", $row)}
        {$displayLinkData->getLinkPortalMain("ezTrade_enquiry", "ezTrade_productLink", "Enquiry Line From Inventory", $row)}
        ";

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay("Attachments", "ezTrade_enquiry", "attachment", $row)}
        {$comment->getView(array(
             'roomName' => 'ezTrade_enquiry'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.enquiry.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getChooseLinkValidation() {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $enquiry_id = $fn->getReqParam('enquiry_id');
        $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);

        $status = 'success';
        $errMsg = '';
        if ($rowEnquiry['status'] == 'cancelled') {
            $status = 'error';
            $errMsg = 'Enquiry is cancelled';
        }

        return $cpUtil->getJsonText($status, '', $errMsg);
    }

    /**
     *
     */
    function getRaiseRfqList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');

        $enquiry_id = $fn->getReqParam('enquiry_id');

        $SQL = "
        SELECT DISTINCT
               ep.enquiry_product_id
              ,CONCAT_WS('-', e.enquiry_code, ep.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,ep.quantity
              ,ep.delivery_date
              ,ep.packing_requirement
              ,ep.remark
              ,ep.status
        FROM product p
        JOIN enquiry_product ep ON (ep.product_id = p.product_id)
        JOIN enquiry e          ON (e.enquiry_id = ep.enquiry_id)
        LEFT JOIN company c     ON (c.company_id = ep.company_id_supplier)
        WHERE ep.enquiry_id = {$enquiry_id}
        ORDER BY ep.line_no
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity' type='hidden' value='{$row['quantity']}' name='quantity__{$row['product_id']}' />
            {$row['quantity']}
            ";
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['enquiry_product_id']}' name='enquiry_product_ids[]' />
            ";

            $exp = array(
                 'hasFlagInList' => false
                ,'keyFieldValue' => $row['enquiry_product_id']
                ,'hasEditInList' => false
                ,'hasRowNumber' => false
            );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['delivery_date'])}
            {$listObj->getListDataCell($row['packing_requirement'])}
            {$listObj->getListDataCell($row['remark'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($quantityText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['enquiry_product_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseRfqCancel' />
            <input type='button' value='Raise RFQ' id='btnRaiseRfq' />
            </div>
        </form>
        ";

        $modCompany = getCPModuleObj('ezTrade_company');
        $sqlSupplier = $modCompany->model->getSupplierSQL();

        $supplierText = "
        <div class='floatbox'>
            <div class='float_right raiseFlds'>
            {$formObj->getDDRowBySQL('Supplier Name', 'company_id_supplier', $sqlSupplier)}
            </div>
        </div>
        ";

        $exp = array(
             'hasEditInList' => false
            ,'hasRowNumber' => false
            ,'hasFlagInList' => false
        );
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$supplierText}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Line #')}
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Request Delivery Date')}
            {$listObj->getListHeaderCell('Packing Requirements')}
            {$listObj->getListHeaderCell('Remarks')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRaiseQuoteList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');

        $enquiry_id = $fn->getReqParam('enquiry_id');

        $SQL = "
        SELECT DISTINCT
               ep.enquiry_product_id
              ,CONCAT_WS('-', e.enquiry_code, ep.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,ep.quantity
              ,ep.delivery_date
              ,ep.packing_requirement
              ,ep.remark
              ,ep.status

              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,qri.status AS quote_request_line_status

        FROM product p
        JOIN enquiry_product ep ON (ep.product_id = p.product_id)
        JOIN enquiry e          ON (e.enquiry_id = ep.enquiry_id)
        LEFT JOIN company c     ON (c.company_id = ep.company_id_supplier)
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = ep.quote_request_items_id)
        LEFT JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        WHERE ep.enquiry_id = {$enquiry_id}
        ORDER BY ep.line_no
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            {$row['quantity']}
            <input class='quantity' type='hidden' value='{$row['quantity']}' name='quantity__{$row['product_id']}' />
            ";
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['product_id']}-{$row['enquiry_product_id']}'
                   name='ids[]' checked='checked'/>
            ";

            $exp = array(
                'hasFlagInList' => false
               ,'keyFieldValue' => $row['enquiry_product_id']
               ,'hasEditInList' => false
               ,'hasRowNumber' => false
            );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['delivery_date'])}
            {$listObj->getListDataCell($row['packing_requirement'])}
            {$listObj->getListDataCell($row['remark'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['quote_request_line_no'])}
            {$listObj->getListDataCell($row['quote_request_line_status'])}
            {$listObj->getListDataCell($quantityText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['enquiry_product_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseQuoteCancel' />
            <input type='button' value='Raise Quote' id='btnRaiseQuote' />
            </div>
        </form>
        ";

        $exp = array(
             'hasEditInList' => false
            ,'hasRowNumber' => false
            ,'hasFlagInList' => false
        );
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Line #')}
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Request Delivery Date')}
            {$listObj->getListHeaderCell('Packing Requirements')}
            {$listObj->getListHeaderCell('Remarks')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('RFQ Line Number')}
            {$listObj->getListHeaderCell('RFQ Line Status')}
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

}
