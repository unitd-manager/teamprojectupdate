<?
class CP_Admin_Modules_EzTrade_ProductLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['product_id'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Item Number', 'p.product_code')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Item Name', 'p.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Category', 'c.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Sub Category', 'sc.title')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
     *
     */
    function getListNotLinked($result, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');

        $numRows = $db->sql_numrows($result);
        $rows       = '';
        $rowCounter = 0;

        //------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['product_id'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Item Number', 'p.product_code')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Item Name', 'p.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Category', 'c.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Sub Category', 'sc.title')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $subCatOptions  = '';
        $collection_name   = $fn->getReqParam('collection_name');
        $status   = $fn->getReqParam('status');
        $fn = Zend_Registry::get('fn');
        $srcRoom  = $fn->getReqParam('srcRoom');

        $text = '';
        if ($srcRoom == 'ezTrade_shipment') {
            $SQLSO = "
            SELECT so.sales_order_id
                  ,so.so_code
            FROM sales_order so
            WHERE so.status = 'new' OR so.status = 'open'
            ORDER BY so.so_code
            ";
            $soOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSO);


            $text = "
            <td>
                <select name='collection_name'>
                    <option value=''>Open Orders</option>
                    {$soOptions}
                </select>
            </td>

            ";

        } else {
            $product = getCPViewObj('ezTrade_product');
            $text = $product->getQuickSearch();
        }

        return $text;
    }

    /**
     *
     */
    function getNewPortalFromEnquiryValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('quantity', 'Please enter the quantity');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getDetailPortal(){
        return $this->getEdit('detail');
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formObj->mode = 'edit';
        $text = '';

        if ($tv['srcRoom'] == 'ezTrade_enquiry') {
            $text = $this->getEditPortalFromEnquiry();
        } else if ($tv['srcRoom'] == 'ezTrade_rfq') {
            $text = $this->getEditPortalFromRfq();
        } else if ($tv['srcRoom'] == 'ezTrade_quote') {
            $text = $this->getEditPortalFromQuote();
        } else if ($tv['srcRoom'] == 'ezTrade_salesOrder') {
            $text = $this->getEditPortalFromSO();
        } else if ($tv['srcRoom'] == 'ezTrade_shipment') {
            $text = $this->getEditPortalFromShipment();
        } else if ($tv['srcRoom'] == 'ezTrade_invoice') {
            $text = $this->getEditPortalFromInvoice();
        } else if ($tv['srcRoom'] == 'ezTrade_purchaseOrder') {
            $text = $this->getEditPortalFromPO();
        }

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromEnquiry(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_spAction=savePortalFromEnquiry&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $enquiry_product_id = $fn->getReqParam('id');

        $fnsModProduct = includeCPClass('fnsMod', 'ezTrade_product', 'FunctionsMod');

        $SQL = "
        SELECT ep.enquiry_product_id
              ,ep.quantity
              ,ep.delivery_date
              ,ep.remark
              ,ep.status
              ,ep.packing_requirement
              ,p.title AS product_name
              ,p.unit
              ,c.company_name AS company_id_supplier
              ,c.buy_currency
        FROM enquiry_product ep
        JOIN product p      ON (ep.product_id = p.product_id)
        LEFT JOIN company c ON (c.company_id = ep.company_id_supplier)
        WHERE ep.enquiry_product_id = {$enquiry_product_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expNonEditable = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNonEditable)}
            {$formObj->getTBRow('Request Quantity', 'quantity', $row['quantity'])}
            {$formObj->getDateRow('Request Delivery Date', 'delivery_date', $row['delivery_date'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.enquiryProductStatusArr'], $row['status'])}
            {$formObj->getTextAreaRow('Packing Requirements', 'packing_requirement', $row['packing_requirement'])}
            {$formObj->getTextAreaRow('Remarks', 'remark', $row['remark'])}
            <input type='hidden' name='enquiry_product_id' value='{$enquiry_product_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditPortalFromRfq(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_spAction=savePortalFromRfq&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $quote_request_items_id = $fn->getReqParam('id');

        $SQL = "
        SELECT DISTINCT
               qri.quote_request_items_id
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,qri.quantity
              ,qri.buy_unit_price
              ,qri.quantity * qri.buy_unit_price AS buy_price
              ,qri.buy_unit_price_base
              ,qri.quantity * qri.buy_unit_price_base AS buy_price_base
              ,qri.status
              ,qr.buy_currency
              ,qri.request_delivery_date
              ,qri.packing_requirement
              ,qri.notes_to_supplier
              ,qri.lead_time
              ,qri.min_order_quantity
              ,qri.order_multiplier
              ,qri.notes_from_supplier
              ,qri.country_of_origin
              ,qri.packing_details
              ,qri.carton_dimensions
              ,qri.gross_weight
              ,qri.net_weight
              ,qri.total_volume
              ,qr.company_id_supplier
        FROM quote_request_items qri
        JOIN product p ON (p.product_id = qri.product_id)
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        WHERE qri.quote_request_items_id = {$quote_request_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);

        $fnsModDeliveryTerms = getCPModelObj('ezTrade_deliveryTermsLink');
        $fnsModProduct = getCPModelObj('ezTrade_product');

        $sqlDeliveryTermsSupplier = $fnsModDeliveryTerms->getDeliveryTermsSupplierSQL($row['company_id_supplier']);

        $textBuyCurrency = $this->getMultiFieldValueHTMLTemplate('Buy Currency', true);
        $textBuyCurrency = str_replace('[[col1_value]]', $row['buy_currency'], $textBuyCurrency);
        $textBuyCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textBuyCurrency);

        $fldBuyUnitPrice = $row['buy_unit_price'];
        if ($formObj->mode == 'edit') {
            $fldBuyUnitPrice = "
            <input id='buy_unit_price' class='inputBox2' type='text' name='buy_unit_price' value='{$row['buy_unit_price']}' />
            ";
        }
        $fldBuyUnitPriceBase = "<div id='t_buy_unit_price_base'>{$row['buy_unit_price_base']}</div>";
        $textBuyUnitPrice = $this->getMultiFieldValueHTMLTemplate('Unit Buy Price');
        $textBuyUnitPrice = str_replace('[[col1_value]]', $fldBuyUnitPrice, $textBuyUnitPrice);
        $textBuyUnitPrice = str_replace('[[col2_value]]', $fldBuyUnitPriceBase, $textBuyUnitPrice);

        $fldBuyPrice    = "<div id='t_buy_price'>{$row['buy_price']}</div>";
        $fldBuyPriceBase = "<div id='t_buy_price_base'>{$row['buy_price_base']}</div>";
        $textBuyPrice = $this->getMultiFieldValueHTMLTemplate('Total Buy Price');
        $textBuyPrice = str_replace('[[col1_value]]', $fldBuyPrice, $textBuyPrice);
        $textBuyPrice = str_replace('[[col2_value]]', $fldBuyPriceBase, $textBuyPrice);

        $exp = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Request Quantity', 'quantity', $row['quantity'])}
        {$formObj->getDateRow('Request delivery Date', 'request_delivery_date', $row['request_delivery_date'])}
        {$formObj->getTARow('Packing Requirement', 'packing_requirement', $row['packing_requirement'])}
        {$formObj->getTARow('Notes to Supplier', 'notes_to_supplier', $row['notes_to_supplier'])}
        ";

        $fieldset2 = "
        {$textBuyCurrency}
        {$textBuyUnitPrice}
        {$textBuyPrice}
        {$formObj->getTBRow('Lead Time', 'lead_time', $row['lead_time'])}
        {$formObj->getTBRow('Min Order Quantity', 'min_order_quantity', $row['min_order_quantity'])}
        {$formObj->getTBRow('Order Multiplier', 'order_multiplier', $row['order_multiplier'])}
        {$formObj->getTBRow('Country of Origin', 'country_of_origin', $row['country_of_origin'])}
        {$formObj->getTBRow('Packing Details', 'packing_details', $row['packing_details'])}
        {$formObj->getTBRow('Gross Weight (kg)', 'gross_weight', $row['gross_weight'])}
        {$formObj->getTBRow('Net Weight (kg)', 'net_weight', $row['net_weight'])}
        {$formObj->getTBRow('Carton Dimension (cm)', 'carton_dimensions', $row['carton_dimensions'])}
        {$formObj->getTBRow('Total Volume (cbm)', 'total_volume', $row['total_volume'])}
        {$formObj->getTARow('Notes From Supplier', 'notes_from_supplier', $row['notes_from_supplier'])}

        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $exp)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            <h3>RFQ Line to Supplier</h3>
            {$fieldset1}
            <h3>Quote Line from Supplier</h3>
            {$fieldset2}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.RFQProductStatusArr'], $row['status'])}

            <input type='hidden' name='quote_request_items_id' value='{$quote_request_items_id}' />
            <input type='hidden' id='buy_unit_price_base' name='buy_unit_price_base' value='{$row['buy_unit_price_base']}' />
            <input type='hidden' name='buy_currency' value='{$row['buy_currency']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromQuote(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = "index.php?_spAction=savePortalFromQuote&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $quote_items_id = $fn->getReqParam('id');

        $SQL = "
        SELECT DISTINCT
               qi.quote_items_id
              ,qi.quantity
              ,qi.buy_unit_price
              ,qi.buy_unit_price_base
              ,FORMAT(qi.quantity * qi.buy_unit_price, 2) AS buy_price
              ,qi.quantity * qi.buy_unit_price_base AS buy_price_base
              ,qi.markup
              ,qi.sell_unit_price
              ,qi.sell_unit_price_base
              ,qi.quantity * qi.sell_unit_price AS sell_price
              ,qi.quantity * qi.sell_unit_price_base AS sell_price_base
              ,qi.other_costs_1_label
              ,qi.other_costs_2_label
              ,qi.other_costs_3_label
              ,qi.other_costs_1_curr
              ,qi.other_costs_2_curr
              ,qi.other_costs_3_curr
              ,qi.other_costs_1
              ,qi.other_costs_2
              ,qi.other_costs_3
              ,qi.other_costs_1_base
              ,qi.other_costs_2_base
              ,qi.other_costs_3_base
              ,qi.status
              ,p.title AS product_name
              ,qri.min_order_quantity
              ,qri.order_multiplier
              ,qi.lead_time
              ,q.sell_currency
              ,qr.buy_currency
              ,CONCAT_WS('-', e.enquiry_code, ep.line_no) AS enquiry_line_no
              ,p.unit
              ,qi.margin_percent
              ,qi.valid_until
              ,qi.note_to_customer
              ,qi.packing_details
              ,qi.carton_dimensions
              ,qi.gross_weight
              ,qi.country_of_origin
              ,qi.delivery_terms
              ,qi.shipping_method
              ,qi.net_weight
              ,qi.total_volume
              ,e.enquiry_id
              ,e.company_id_customer
              ,qr.quote_request_id
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,qr.company_id_supplier
        FROM quote_items qi
        JOIN quote q                 ON (q.quote_id = qi.quote_id)
        JOIN enquiry e               ON (e.enquiry_id = q.enquiry_id)
        JOIN enquiry_product ep      ON (ep.enquiry_product_id = qi.enquiry_product_id)
        JOIN product p               ON (qi.product_id = p.product_id)
        JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        WHERE qi.quote_items_id = {$quote_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $modDeliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $sqlDeliveryTermsCustomer = $modDeliveryTerms->model->getDeliveryTermsSupplierSQL($row['company_id_customer']);

        $textBuyCurrency = $this->getMultiFieldValueHTMLTemplate('Buy Currency', true);
        $textBuyCurrency = str_replace('[[col1_value]]', $row['buy_currency'], $textBuyCurrency);
        $textBuyCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textBuyCurrency);

        $fldBuyUnitPrice = $row['buy_unit_price'];
        $fldBuyUnitPriceBase = "<div id='t_buy_unit_price_base'>{$row['buy_unit_price_base']}</div>";
        $textBuyUnitPrice = $this->getMultiFieldValueHTMLTemplate("Unit Buy Price");
        $textBuyUnitPrice = str_replace('[[col1_value]]', $fldBuyUnitPrice, $textBuyUnitPrice);
        $textBuyUnitPrice = str_replace('[[col2_value]]', $fldBuyUnitPriceBase, $textBuyUnitPrice);

        $fldBuyPrice    = "<div id='t_buy_price'>{$row['buy_price']}</div>";
        $fldBuyPriceBase = "<div id='t_buy_price_base'>{$row['buy_price_base']}</div>";
        $textBuyPrice = $this->getMultiFieldValueHTMLTemplate('Total Buy Price');
        $textBuyPrice = str_replace('[[col1_value]]', $fldBuyPrice, $textBuyPrice);
        $textBuyPrice = str_replace('[[col2_value]]', $fldBuyPriceBase, $textBuyPrice);

        //--------------------------------------------------------------------------//
        $SQLCurr = $fn->getValueListSQL('currency');

        $textOtherCostsLbl = $this->getMultiFieldValueHTMLTemplateOtherCost('', true);
        $textOtherCostsLbl = str_replace('[[col1_value]]', 'Label', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col2_value]]', 'Currency', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col3_value]]', 'Amount', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col4_value]]', $cpCfg['m.trading.companyCurrency'], $textOtherCostsLbl);


        //----------------------------------------------//
        $fldOtherCosts1Label = $row['other_costs_1_label'];
        $fldOtherCostsCurrName1 = $row['other_costs_1_curr'];
        $fldOtherCosts1 = $row['other_costs_1'];
        $fldOtherCosts1Base = $row['other_costs_1_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts1Label = "
            <input id='other_costs_1_label' class='inputBox2' type='text' name='other_costs_1_label' value='{$row['other_costs_1_label']}' />
            ";

            $fldOtherCostsCurrName1 = "
            <select id='other_costs_1_curr' class='other_cost_curr' name='other_costs_1_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_1_curr'])}
            </select>
            ";
            $fldOtherCosts1 = "
            <input id='other_costs_1' class='inputBox2' type='text' name='other_costs_1' value='{$row['other_costs_1']}' />
            ";

            $fldOtherCosts1Base = "<div id='t_other_costs_1_base'>{$row['other_costs_1_base']}</div>";
        }

        $textOtherCosts1 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 1');
        $textOtherCosts1 = str_replace('[[col1_value]]', $fldOtherCosts1Label, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col2_value]]', $fldOtherCostsCurrName1, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col3_value]]', $fldOtherCosts1, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col4_value]]', $fldOtherCosts1Base, $textOtherCosts1);


        //----------------------------------------------//
        $fldOtherCosts2Label = $row['other_costs_2_label'];
        $fldOtherCostsCurrName2 = $row['other_costs_2_curr'];
        $fldOtherCosts2 = $row['other_costs_2'];
        $fldOtherCosts2Base = $row['other_costs_2_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts2Label = "
            <input id='other_costs_2_label' class='inputBox2' type='text' name='other_costs_2_label' value='{$row['other_costs_2_label']}' />
            ";

            $fldOtherCostsCurrName2 = "
            <select id='other_costs_2_curr' class='other_cost_curr' name='other_costs_2_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_2_curr'])}
            </select>
            ";
            $fldOtherCosts2 = "
            <input id='other_costs_2' class='inputBox2' type='text' name='other_costs_2' value='{$row['other_costs_2']}' />
            ";

            $fldOtherCosts2Base = "<div id='t_other_costs_2_base'>{$row['other_costs_2_base']}</div>";
        }

        $textOtherCosts2 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 2');
        $textOtherCosts2 = str_replace('[[col1_value]]', $fldOtherCosts2Label, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col2_value]]', $fldOtherCostsCurrName2, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col3_value]]', $fldOtherCosts2, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col4_value]]', $fldOtherCosts2Base, $textOtherCosts2);

        //----------------------------------------------//
        $fldOtherCosts3Label = $row['other_costs_3_label'];
        $fldOtherCostsCurrName3 = $row['other_costs_3_curr'];
        $fldOtherCosts3 = $row['other_costs_3'];
        $fldOtherCosts3Base = $row['other_costs_3_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts3Label = "
            <input id='other_costs_3_label' class='inputBox2' type='text' name='other_costs_3_label' value='{$row['other_costs_3_label']}' />
            ";

            $fldOtherCostsCurrName3 = "
            <select id='other_costs_3_curr' class='other_cost_curr' name='other_costs_3_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_3_curr'])}
            </select>
            ";
            $fldOtherCosts3 = "
            <input id='other_costs_3' class='inputBox2' type='text' name='other_costs_3' value='{$row['other_costs_3']}' />
            ";

            $fldOtherCosts3Base = "<div id='t_other_costs_3_base'>{$row['other_costs_3_base']}</div>";
        }

        $textOtherCosts3 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 3');
        $textOtherCosts3 = str_replace('[[col1_value]]', $fldOtherCosts3Label, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col2_value]]', $fldOtherCostsCurrName3, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col3_value]]', $fldOtherCosts3, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col4_value]]', $fldOtherCosts3Base, $textOtherCosts3);

        //------------------------------------------------------------------------------//
        $textSellCurrency = $this->getMultiFieldValueHTMLTemplate('Sell Currency', true);
        $textSellCurrency = str_replace('[[col1_value]]', $row['sell_currency'], $textSellCurrency);
        $textSellCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textSellCurrency);

        $fldSellUnitPrice = $row['sell_unit_price'];
        if ($formObj->mode == 'edit') {
            $fldSellUnitPrice = "
            <input id='sell_unit_price' class='inputBox2' type='text'
                   name='sell_unit_price' value='{$row['sell_unit_price']}' />
            ";
        }
        $fldSellUnitPriceBase = "<div id='t_sell_unit_price_base'>{$row['sell_unit_price_base']}</div>";
        $textSellUnitPrice = $this->getMultiFieldValueHTMLTemplate('Unit Sell Price');
        $textSellUnitPrice = str_replace('[[col1_value]]', $fldSellUnitPrice, $textSellUnitPrice);
        $textSellUnitPrice = str_replace('[[col2_value]]', $fldSellUnitPriceBase, $textSellUnitPrice);

        $fldSellPrice    = "<div id='t_sell_price'>{$row['sell_price']}</div>";
        $fldSellPriceBase = "<div id='t_sell_price_base'>{$row['sell_price_base']}</div>";
        $textSellPrice = $this->getMultiFieldValueHTMLTemplate('Total Sell Price');
        $textSellPrice = str_replace('[[col1_value]]', $fldSellPrice, $textSellPrice);
        $textSellPrice = str_replace('[[col2_value]]', $fldSellPriceBase, $textSellPrice);

        $expMarkup = array('isEditable' => 0
                          ,'extraHtml' => "<input type='button' value='restore to default value' id='recalculate' />");
        if ($formObj->mode == 'detail') {
            unset($expMarkup['extraHtml']);
        }

        $markupFld = "<div id='t_markup'>{$row['markup']}</div>";
        $markupText = "
        {$formObj->getTBRow('Markup (%)', 'markup', $markupFld, $expMarkup)}
        ";

        $expNoEdit = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $expEnqLineNo = array('displayText' => $row['enquiry_line_no'], 'target' => '_blank');
        $enqLineNoText = $fn->getRecordDetailLink('ezTrade_enquiry', 'record_id', $row['enquiry_id'], $expEnqLineNo);

        $expQuoteReqLineNo = array('displayText' => $row['quote_request_line_no'], 'target' => '_blank');
        $quoteReqLineNoText = $fn->getRecordDetailLink('ezTrade_rfq', 'record_id',
                                                  $row['quote_request_id'], $expQuoteReqLineNo);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Enquiry Line #', 'line_no', $enqLineNoText, $expNoEdit)}
            {$formObj->getTBRow('RFQ Line #', 'line_no', $quoteReqLineNoText, $expNoEdit)}
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            {$formObj->getTBRow('Quantity', 'quantity', $row['quantity'], $expNoEdit)}
            {$formObj->getTBRow('Minimum Order Quantity', 'min_order_quantity', $row['min_order_quantity'], $expNoEdit)}
            {$formObj->getTBRow('Order Multiplier', 'order_multiplier', $row['order_multiplier'], $expNoEdit)}
            {$formObj->getTBRow('Lead Time', 'lead_time', $row['lead_time'])}
            {$textBuyCurrency}
            {$textBuyUnitPrice}
            {$textBuyPrice}
            {$markupText}
            {$textOtherCostsLbl}
            {$textOtherCosts1}
            {$textOtherCosts2}
            {$textOtherCosts3}
            {$textSellCurrency}
            {$textSellUnitPrice}
            {$textSellPrice}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.quoteProductStatusArr'], $row['status'])}
            {$formObj->getDateRow('Valid Until', 'valid_until', $row['valid_until'])}
            {$formObj->getTARow('Note to Customer', 'note_to_customer', $row['note_to_customer'])}
            {$formObj->getTBRow('Packing Details', 'packing_details', $row['packing_details'])}
            {$formObj->getTBRow('Carton Dimensions (cm)', 'carton_dimensions', $row['carton_dimensions'])}
            {$formObj->getTBRow('Gross Weight (kg)', 'gross_weight', $row['gross_weight'])}
            {$formObj->getTBRow('Country of Origin', 'country_of_origin', $row['country_of_origin'], $expNoEdit)}
            {$formObj->getDDRowBySQL('Delivery Terms', 'delivery_terms', $sqlDeliveryTermsCustomer, $row['delivery_terms'], $expVl)}
            {$formObj->getDDRowByArr('Shipping Method', 'shipping_method', $cpCfg['m.trading.quote.shippingMethodArr'], $row['shipping_method'])}
            {$formObj->getTBRow('Net Weight (kg)', 'net_weight', $row['net_weight'])}
            {$formObj->getTBRow('Total Volume (cbm)', 'total_volume', $row['total_volume'])}
            <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
            <input type='hidden' id='quantity' name='quantity' value='{$row['quantity']}' />
            <input type='hidden' id='buy_unit_price' name='buy_unit_price' value='{$row['buy_unit_price']}' />
            <input type='hidden' id='buy_unit_price_base' name='buy_unit_price_base' value='{$row['buy_unit_price_base']}' />
            <input type='hidden' name='buy_currency' value='{$row['buy_currency']}' />
            <input type='hidden' id='sell_unit_price_base' name='sell_unit_price_base' value='{$row['sell_unit_price_base']}' />
            <input type='hidden' name='sell_currency' value='{$row['sell_currency']}' />
            <input type='hidden' id='markup' name='markup' value='{$row['markup']}' />
            <input type='hidden' id='other_costs_1_base' name='other_costs_1_base' value='{$row['other_costs_1_base']}' />
            <input type='hidden' id='other_costs_2_base' name='other_costs_2_base' value='{$row['other_costs_2_base']}' />
            <input type='hidden' id='other_costs_3_base' name='other_costs_3_base' value='{$row['other_costs_3_base']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromSO(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = "index.php?_spAction=savePortalFromSO&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $sales_order_items_id = $fn->getReqParam('id');

        $SQL = "
        SELECT soi.sales_order_items_id
              ,soi.quantity
              ,soi.buy_unit_price
              ,soi.buy_unit_price_base
              ,soi.buy_unit_price * soi.quantity AS buy_price
              ,soi.buy_unit_price_base * soi.quantity AS buy_price_base
              ,soi.markup
              ,soi.sell_unit_price
              ,soi.sell_unit_price_base
              ,soi.sell_unit_price * soi.quantity AS sell_price
              ,soi.sell_unit_price_base * soi.quantity AS sell_price_base
              ,soi.other_costs_1_label
              ,soi.other_costs_2_label
              ,soi.other_costs_3_label
              ,soi.other_costs_1_curr
              ,soi.other_costs_2_curr
              ,soi.other_costs_3_curr
              ,soi.other_costs_1
              ,soi.other_costs_2
              ,soi.other_costs_3
              ,soi.other_costs_1_base
              ,soi.other_costs_2_base
              ,soi.other_costs_3_base
              ,soi.status
              ,soi.remarks
              ,soi.request_date
              ,soi.promised_delivery_date
              ,soi.estimated_delivery_date
              ,soi.estimated_arrival_date

              ,(SELECT SUM(si.quantity_shipped)
                FROM shipment_items si
                WHERE si.sales_order_items_id = soi.sales_order_items_id) AS quantity_shipped
              ,soi.line_no
              ,soi.shipping_method
              ,soi.unit_ctn
              ,soi.country_of_origin
              ,soi.packing_details
              ,soi.carton_dimensions
              ,soi.gross_weight
              ,soi.net_weight
              ,soi.total_volume
              ,soi.delivery_terms
              ,soi.note_from_customer
              ,soi.customer_po_line_no
              ,p.unit
              ,p.title AS product_name
              ,qri.min_order_quantity
              ,qri.lead_time
              ,qri.order_multiplier
              ,qr.buy_currency
              ,so.sell_currency
              ,so.company_id_customer
              ,q.quote_id
              ,CONCAT_WS('-', q.quote_code, qi.line_no) AS quote_line_no
        FROM sales_order_items soi
        JOIN product p               ON (p.product_id = soi.product_id)
        JOIN sales_order so          ON (so.sales_order_id = soi.sales_order_id)
        JOIN quote_items qi          ON (qi.quote_items_id = soi.quote_items_id)
        JOIN quote q                 ON (q.quote_id = qi.quote_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qi.quote_request_id)
        JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        WHERE soi.sales_order_items_id = {$sales_order_items_id}
        ";

        $row = $fn->getRecordBySQL($SQL);

        $modDeliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $sqlDeliveryTermsCustomer = $modDeliveryTerms->model->getDeliveryTermsSupplierSQL($row['company_id_customer']);

        $textBuyCurrency = $this->getMultiFieldValueHTMLTemplate('Buy Currency', true);
        $textBuyCurrency = str_replace('[[col1_value]]', $row['buy_currency'], $textBuyCurrency);
        $textBuyCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textBuyCurrency);

        $textBuyUnitPrice = $this->getMultiFieldValueHTMLTemplate('Unit Buy Price');
        $textBuyUnitPrice = str_replace('[[col1_value]]', $row['buy_unit_price'], $textBuyUnitPrice);
        $textBuyUnitPrice = str_replace('[[col2_value]]', $row['buy_unit_price_base'], $textBuyUnitPrice);

        $fldBuyPrice    = "<div id='t_buy_price'>{$row['buy_price']}</div>";
        $fldBuyPriceBase = "<div id='t_buy_price_base'>{$row['buy_price_base']}</div>";
        $textBuyPrice = $this->getMultiFieldValueHTMLTemplate('Total Buy Price');
        $textBuyPrice = str_replace('[[col1_value]]', $fldBuyPrice, $textBuyPrice);
        $textBuyPrice = str_replace('[[col2_value]]', $fldBuyPriceBase, $textBuyPrice);

        //--------------------------------------------------------------------------//
        $SQLCurr = $fn->getValueListSQL('currency');

        $textOtherCostsLbl = $this->getMultiFieldValueHTMLTemplateOtherCost('', true);
        $textOtherCostsLbl = str_replace('[[col1_value]]', 'Label', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col2_value]]', 'Currency', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col3_value]]', 'Amount', $textOtherCostsLbl);
        $textOtherCostsLbl = str_replace('[[col4_value]]', $cpCfg['m.trading.companyCurrency'], $textOtherCostsLbl);


        //----------------------------------------------//
        $fldOtherCosts1Label = $row['other_costs_1_label'];
        $fldOtherCostsCurrName1 = $row['other_costs_1_curr'];
        $fldOtherCosts1 = $row['other_costs_1'];
        $fldOtherCosts1Base = $row['other_costs_1_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts1Label = "
            <input id='other_costs_1_label' class='inputBox2' type='text' name='other_costs_1_label' value='{$row['other_costs_1_label']}' />
            ";

            $fldOtherCostsCurrName1 = "
            <select id='other_costs_1_curr' class='other_cost_curr' name='other_costs_1_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_1_curr'])}
            </select>
            ";
            $fldOtherCosts1 = "
            <input id='other_costs_1' class='inputBox2' type='text' name='other_costs_1' value='{$row['other_costs_1']}' />
            ";

            $fldOtherCosts1Base = "<div id='t_other_costs_1_base'>{$row['other_costs_1_base']}</div>";
        }

        $textOtherCosts1 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 1');
        $textOtherCosts1 = str_replace('[[col1_value]]', $fldOtherCosts1Label, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col2_value]]', $fldOtherCostsCurrName1, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col3_value]]', $fldOtherCosts1, $textOtherCosts1);
        $textOtherCosts1 = str_replace('[[col4_value]]', $fldOtherCosts1Base, $textOtherCosts1);


        //----------------------------------------------//
        $fldOtherCosts2Label = $row['other_costs_2_label'];
        $fldOtherCostsCurrName2 = $row['other_costs_2_curr'];
        $fldOtherCosts2 = $row['other_costs_2'];
        $fldOtherCosts2Base = $row['other_costs_2_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts2Label = "
            <input id='other_costs_2_label' class='inputBox2' type='text' name='other_costs_2_label' value='{$row['other_costs_2_label']}' />
            ";

            $fldOtherCostsCurrName2 = "
            <select id='other_costs_2_curr' class='other_cost_curr' name='other_costs_2_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_2_curr'])}
            </select>
            ";
            $fldOtherCosts2 = "
            <input id='other_costs_2' class='inputBox2' type='text' name='other_costs_2' value='{$row['other_costs_2']}' />
            ";

            $fldOtherCosts2Base = "<div id='t_other_costs_2_base'>{$row['other_costs_2_base']}</div>";
        }

        $textOtherCosts2 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 2');
        $textOtherCosts2 = str_replace('[[col1_value]]', $fldOtherCosts2Label, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col2_value]]', $fldOtherCostsCurrName2, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col3_value]]', $fldOtherCosts2, $textOtherCosts2);
        $textOtherCosts2 = str_replace('[[col4_value]]', $fldOtherCosts2Base, $textOtherCosts2);

        //----------------------------------------------//
        $fldOtherCosts3Label = $row['other_costs_3_label'];
        $fldOtherCostsCurrName3 = $row['other_costs_3_curr'];
        $fldOtherCosts3 = $row['other_costs_3'];
        $fldOtherCosts3Base = $row['other_costs_3_base'];
        if ($formObj->mode == 'edit') {
            $fldOtherCosts3Label = "
            <input id='other_costs_3_label' class='inputBox2' type='text' name='other_costs_3_label' value='{$row['other_costs_3_label']}' />
            ";

            $fldOtherCostsCurrName3 = "
            <select id='other_costs_3_curr' class='other_cost_curr' name='other_costs_3_curr'>
                <option value=''></option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLCurr, $row['other_costs_3_curr'])}
            </select>
            ";
            $fldOtherCosts3 = "
            <input id='other_costs_3' class='inputBox2' type='text' name='other_costs_3' value='{$row['other_costs_3']}' />
            ";

            $fldOtherCosts3Base = "<div id='t_other_costs_3_base'>{$row['other_costs_3_base']}</div>";
        }

        $textOtherCosts3 = $this->getMultiFieldValueHTMLTemplateOtherCost('Other Cost 3');
        $textOtherCosts3 = str_replace('[[col1_value]]', $fldOtherCosts3Label, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col2_value]]', $fldOtherCostsCurrName3, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col3_value]]', $fldOtherCosts3, $textOtherCosts3);
        $textOtherCosts3 = str_replace('[[col4_value]]', $fldOtherCosts3Base, $textOtherCosts3);

        //------------------------------------------------------------------------------//
        $textSellCurrency = $this->getMultiFieldValueHTMLTemplate('Sell Currency', true);
        $textSellCurrency = str_replace('[[col1_value]]', $row['sell_currency'], $textSellCurrency);
        $textSellCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textSellCurrency);

        $fldSellUnitPrice = $row['sell_unit_price'];
        if ($formObj->mode == 'edit') {
            $fldSellUnitPrice = "
            <input id='sell_unit_price' class='inputBox2' type='text'
                   name='sell_unit_price' value='{$row['sell_unit_price']}' />
            ";
        }
        $fldSellUnitPriceBase = "<div id='t_sell_unit_price_base'>{$row['sell_unit_price_base']}</div>";
        $textSellUnitPrice = $this->getMultiFieldValueHTMLTemplate('Unit Sell Price');
        $textSellUnitPrice = str_replace('[[col1_value]]', $fldSellUnitPrice, $textSellUnitPrice);
        $textSellUnitPrice = str_replace('[[col2_value]]', $fldSellUnitPriceBase, $textSellUnitPrice);

        $fldSellPrice    = "<div id='t_sell_price'>{$row['sell_price']}</div>";
        $fldSellPriceBase = "<div id='t_sell_price_base'>{$row['sell_price_base']}</div>";
        $textSellPrice = $this->getMultiFieldValueHTMLTemplate('Total Sell Price');
        $textSellPrice = str_replace('[[col1_value]]', $fldSellPrice, $textSellPrice);
        $textSellPrice = str_replace('[[col2_value]]', $fldSellPriceBase, $textSellPrice);

        $expMarkup = array('isEditable' => 0
                          ,'extraHtml' => "<span class='float_left' id='t_markup'>{$row['markup']}</span>
                                           <input type='button' value='restore to default value' id='recalculate' />");
        if ($formObj->mode == 'detail') {
            $expMarkup['extraHtml'] = "<span class='float_left' id='t_markup'>{$row['markup']}</span>";
        }

        $markupText = "
        {$formObj->getTBRow('Markup (%)', 'markup', '', $expMarkup)}
        ";

        $expNoEdit = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $expQuoteLineNo = array('displayText' => $row['quote_line_no'], 'target' => '_blank');
        $quoteLineNoText = $fn->getRecordDetailLink('ezTrade_quote', 'record_id', $row['quote_id'], $expQuoteLineNo);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Quote Line #', 'line_no', $quoteLineNoText, $expNoEdit)}
            {$formObj->getTBRow('Customer PO Line #', 'customer_po_line_no', $row['customer_po_line_no'])}
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            {$formObj->getTBRow('Quantity', 'quantity', $row['quantity'])}
            {$textSellCurrency}
            {$textSellUnitPrice}
            {$textSellPrice}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.salesOrderProductStatusArr'], $row['status'])}
            {$formObj->getTBRow('Packing Details', 'packing_details', $row['packing_details'])}
            {$formObj->getTBRow('Carton Dimensions (cm)', 'carton_dimensions', $row['carton_dimensions'])}
            {$formObj->getTBRow('Gross Weight (kg)', 'gross_weight', $row['gross_weight'])}
            {$formObj->getTBRow('Country of Origin', 'country_of_origin', $row['country_of_origin'], $expNoEdit)}
            {$formObj->getDDRowBySQL('Delivery Terms', 'delivery_terms', $sqlDeliveryTermsCustomer, $row['delivery_terms'], $expVl)}
            {$formObj->getDDRowByArr('Shipping Method', 'shipping_method', $cpCfg['m.trading.salesOrder.shippingMethodArr'], $row['shipping_method'])}
            {$formObj->getTBRow('Net Weight (kg)', 'net_weight', $row['net_weight'])}
            {$formObj->getTBRow('Total Volume (cbm)', 'total_volume', $row['total_volume'])}
            {$formObj->getDateRow('Request Date', 'request_date', $row['request_date'])}
            {$formObj->getDateRow('Promised Delivery Date', 'promised_delivery_date', $row['promised_delivery_date'])}
            {$formObj->getDateRow('Estimated Delivery Date', 'estimated_delivery_date', $row['estimated_delivery_date'])}
            {$formObj->getDateRow('Estimated Arrival Date', 'estimated_arrival_date', $row['estimated_arrival_date'])}
            {$formObj->getTARow('Note from Customer', 'note_from_customer', $row['note_from_customer'])}

            <input type='hidden' name='sales_order_items_id' value='{$sales_order_items_id}' />
            <input type='hidden' id='buy_unit_price' name='buy_unit_price' value='{$row['buy_unit_price']}' />
            <input type='hidden' id='buy_unit_price_base' name='buy_unit_price_base' value='{$row['buy_unit_price_base']}' />
            <input type='hidden' name='buy_currency' value='{$row['buy_currency']}' />
            <input type='hidden' id='sell_unit_price_base' name='sell_unit_price_base' value='{$row['sell_unit_price_base']}' />
            <input type='hidden' name='sell_currency' value='{$row['sell_currency']}' />
            <input type='hidden' id='markup' name='markup' value='{$row['markup']}' />
            <input type='hidden' id='other_costs_1_base' name='other_costs_1_base' value='{$row['other_costs_1_base']}' />
            <input type='hidden' id='other_costs_2_base' name='other_costs_2_base' value='{$row['other_costs_2_base']}' />
            <input type='hidden' id='other_costs_3_base' name='other_costs_3_base' value='{$row['other_costs_3_base']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromShipment(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=savePortalFromShipment&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $shipment_items_id = $fn->getReqParam('id');

        $SQL = "
        SELECT si.shipment_items_id
              ,p.title AS product_name
              ,p.unit
              ,p.product_code
              ,si.shipment_id
              ,si.product_id
              ,si.quantity_delivered
              ,si.quantity_shipped
              ,si.status
              ,si.no_of_carton
              ,si.carton_sequence
              ,si.packing_details
              ,si.dimension_h
              ,si.dimension_w
              ,si.dimension_d
              ,si.net_weight
              ,si.gross_weight
              ,si.total_volume
              ,si.country_of_origin
              ,si.remarks
              ,soi.customer_po_line_no
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS so_line_no
        FROM shipment_items si
        JOIN product p   ON (si.product_id = p.product_id)
        JOIN shipment s  ON (s.shipment_id = si.shipment_id)
        JOIN sales_order_items soi  ON (soi.sales_order_items_id = si.sales_order_items_id)
        JOIN sales_order so         ON (so.sales_order_id = soi.sales_order_id)
        WHERE si.shipment_items_id = {$shipment_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Sales Order Line #', 'so_line_no', $row['so_line_no'], $expNoEdit)}
            {$formObj->getTBRow('Customer PO Line #', 'customer_po_line_no', $row['customer_po_line_no'], $expNoEdit)}
            {$formObj->getTBRow('Item Number', 'product_code', $row['product_code'], $expNoEdit)}
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('Ship Quantity', 'quantity_shipped', $row['quantity_shipped'])}
            {$formObj->getTBRow('Dimensions H', 'dimension_h', $row['dimension_h'])}
            {$formObj->getTBRow('Dimensions W', 'dimension_w', $row['dimension_w'])}
            {$formObj->getTBRow('Dimensions D', 'dimension_d', $row['dimension_d'])}
            {$formObj->getTBRow('Net Weight (kg)', 'net_weight', $row['net_weight'])}
            {$formObj->getTBRow('Gross Weight (kg)', 'gross_weight', $row['gross_weight'])}
            {$formObj->getTBRow('Total Volume (cbm)', 'total_volume', $row['total_volume'])}
            {$formObj->getTARow('Notes', 'remarks', $row['remarks'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.shipmentProductStatusArr'], $row['status'])}
            <input type='hidden' name='shipment_items_id' value='{$shipment_items_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromInvoice(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=savePortalFromInvoice&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $invoice_items_id = $fn->getReqParam('id');

        $SQL = "
        SELECT ii.invoice_items_id
              ,CONCAT_WS('-', i.invoice_code, ii.line_no) AS line_no
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS so_line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,soi.quantity
              ,so.sell_currency
              ,soi.sell_unit_price
              ,soi.quantity * soi.sell_unit_price AS sell_price_total
              ,ROUND( ((ii.sell_price / (soi.sell_unit_price * soi.quantity) ) * 100), 2) AS invoice_percentage
              ,soi.customer_po_line_no
              ,ii.sell_price
              ,ii.status
              ,ii.remarks
        FROM invoice_items ii
        JOIN invoice i      ON (i.invoice_id = ii.invoice_id)
        JOIN sales_order so ON (so.sales_order_id = i.sales_order_id)
        JOIN sales_order_items soi ON (soi.sales_order_items_id = ii.sales_order_items_id)
        JOIN product p      ON (ii.product_id = p.product_id)
        WHERE ii.invoice_items_id = {$invoice_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Invoice Line #', 'line_no', $row['line_no'], $expNoEdit)}
            {$formObj->getTBRow('Sales Order Line #', 'so_line_no', $row['so_line_no'], $expNoEdit)}
            {$formObj->getTBRow('Customer PO Line #', 'customer_po_line_no', $row['customer_po_line_no'], $expNoEdit)}
            {$formObj->getTBRow('Item Number', 'product_code', $row['product_code'], $expNoEdit)}
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('Order Quantity', 'quantity', $row['quantity'], $expNoEdit)}
            {$formObj->getTBRow('Sell Currency', 'sell_currency', $row['sell_currency'], $expNoEdit)}
            {$formObj->getTBRow('Unit Sell Price', 'sell_unit_price', $row['sell_unit_price'], $expNoEdit)}
            {$formObj->getTBRow('Total Sell Price', 'sell_price_total', $row['sell_price_total'], $expNoEdit)}
            {$formObj->getTBRow('Invoice %', 'invoice_percentage', $row['invoice_percentage'], $expNoEdit)}
            {$formObj->getTBRow('Invoice Amount', 'sell_price', $row['sell_price'])}
            {$formObj->getTARow('Notes', 'remarks', $row['remarks'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.invoiceProductStatusArr'], $row['status'])}
            <input type='hidden' name='invoice_items_id' value='{$invoice_items_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromPO() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=savePortalFromPO&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $purchase_order_items_id = $fn->getReqParam('id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier', 0);
        $company_id_customer = $fn->getReqParam('company_id_customer', 0);

        //======================================================================//
        $SQL = "
        SELECT poi.purchase_order_items_id
              ,poi.purchase_order_id
              ,CONCAT_WS('-', po.po_code, poi.line_no) AS po_line_no
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS so_line_no
              ,poi.product_id
              ,poi.quantity
              ,poi.status
              ,p.unit
              ,p.title AS product_name
              ,p.product_code
              ,po.buy_currency
              ,poi.buy_unit_price
              ,poi.buy_unit_price_base
              ,poi.buy_unit_price * poi.quantity AS buy_price
              ,poi.buy_unit_price_base * poi.quantity AS buy_price_base
              ,poi.notes_to_supplier
              ,poi.delivery_terms
              ,poi.shipping_method
              ,poi.packing_details
              ,poi.carton_dimensions
              ,poi.gross_weight
              ,poi.net_weight
              ,poi.request_date
              ,poi.promised_delivery_date
              ,poi.estimated_delivery_date
              ,poi.quantity_delivered
              ,poi.total_paid_amount
              ,po.company_id_supplier
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
        FROM purchase_order_items poi
        JOIN product p                    ON (poi.product_id = p.product_id)
        JOIN purchase_order po            ON (po.purchase_order_id = poi.purchase_order_id)
        JOIN sales_order_items soi        ON (soi.sales_order_items_id = poi.sales_order_items_id)
        JOIN sales_order so               ON (so.sales_order_id = soi.sales_order_id)
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = poi.quote_request_items_id)
        LEFT JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_items_id)
        WHERE poi.purchase_order_items_id = {$purchase_order_items_id}
        ";

        $row = $fn->getRecordBySQL($SQL);

        $modDeliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $sqlDeliveryTermsSupplier = $modDeliveryTerms->model->getDeliveryTermsSupplierSQL($row['company_id_supplier']);

        $textBuyCurrency = $this->getMultiFieldValueHTMLTemplate('Buy Currency', true);
        $textBuyCurrency = str_replace('[[col1_value]]', $row['buy_currency'], $textBuyCurrency);
        $textBuyCurrency = str_replace('[[col2_value]]', $cpCfg['m.trading.companyCurrency'], $textBuyCurrency);

        $fldBuyUnitPrice = $row['buy_unit_price'];
        if ($formObj->mode == 'edit') {
            $fldBuyUnitPrice = "
            <input id='buy_unit_price' class='inputBox2' type='text' name='buy_unit_price' value='{$row['buy_unit_price']}' />
            ";
        }
        $fldBuyUnitPriceBase = "<div id='t_buy_unit_price_base'>{$row['buy_unit_price_base']}</div>";
        $textBuyUnitPrice = $this->getMultiFieldValueHTMLTemplate("Unit Buy Price");
        $textBuyUnitPrice = str_replace('[[col1_value]]', $fldBuyUnitPrice, $textBuyUnitPrice);
        $textBuyUnitPrice = str_replace('[[col2_value]]', $fldBuyUnitPriceBase, $textBuyUnitPrice);

        $fldBuyPrice    = "<div id='t_buy_price'>{$row['buy_price']}</div>";
        $fldBuyPriceBase = "<div id='t_buy_price_base'>{$row['buy_price_base']}</div>";
        $textBuyPrice = $this->getMultiFieldValueHTMLTemplate('Total Buy Price');
        $textBuyPrice = str_replace('[[col1_value]]', $fldBuyPrice, $textBuyPrice);
        $textBuyPrice = str_replace('[[col2_value]]', $fldBuyPriceBase, $textBuyPrice);

        $expNoEdit = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('SO Line #', 'po_code', $row['so_line_no'], $expNoEdit)}
            {$formObj->getTBRow('RFQ Number', 'quote_request_line_no', $row['quote_request_line_no'], $expNoEdit)}
            {$formObj->getDateRow('Request Date', 'request_date', $row['request_date'])}
            {$formObj->getTBRow('Item Number', 'product_code', $row['product_code'], $expNoEdit)}
            {$formObj->getTBRow('Item Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('Order Quantity', 'quantity', $row['quantity'])}
            {$textBuyCurrency}
            {$textBuyUnitPrice}
            {$textBuyPrice}
            {$formObj->getDateRow('Estimated Delivery Date', 'estimated_delivery_date', $row['estimated_delivery_date'])}
            {$formObj->getDDRowBySQL('Delivery Terms', 'delivery_terms', $sqlDeliveryTermsSupplier, $row['delivery_terms'], $expVl)}
            {$formObj->getTARow('Notes to Supplier', 'notes_to_supplier', $row['notes_to_supplier'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.purchaseOrderProductStatusArr'], $row['status'])}
            <input type='hidden' name='purchase_order_id' value='{$row['purchase_order_id']}' />
            <input type='hidden' name='purchase_order_items_id' value='{$row['purchase_order_items_id']}' />
            <input type='hidden' id='buy_unit_price_base' name='buy_unit_price_base' value='{$row['buy_unit_price_base']}' />
            <input type='hidden' name='buy_currency' value='{$row['buy_currency']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getMultiFieldValueHTMLTemplate($fieldLabel = '', $header = false, $divClass = '') {
        $formObj = Zend_Registry::get('formObj');

        if ($formObj->mode != 'edit') {
            $divClass = 'non-editable';
        }

        $headerClassText = '';
        if ($header) {
            $headerClassText = '-header';
        }
        $text = '';
        $text = "
        <div class='type-text {$divClass}'>
            <label for=''>{$fieldLabel}</label>
            <div class='txt'>
                <table class='multi-field'>
                    <tr>
                        <td class='col1{$headerClassText}'>[[col1_value]]</td>
                        <td class='col2{$headerClassText}'>[[col2_value]]</td>
                    </tr>
                </table>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMultiFieldValueHTMLTemplateOtherCost($fieldLabel = '', $header = false) {
        $headerClassText = '';
        if ($header) {
            $headerClassText = '-header';
        }

        $text = "
        <div class='type-text'>
            <label>{$fieldLabel}</label>
            <div class='txt'>
                <table class='multi-field2'>
                    <tr>
                        <td class='col1{$headerClassText}'>[[col1_value]]</td>
                        <td class='col2{$headerClassText}'>[[col2_value]]</td>
                        <td class='col3{$headerClassText}'>[[col3_value]]</td>
                        <td class='col4{$headerClassText}'>[[col4_value]]</td>
                    </tr>
                </table>
            </div>
        </div>
        ";

        return $text;
    }

}
