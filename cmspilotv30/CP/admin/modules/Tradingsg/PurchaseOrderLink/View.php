<?
class CP_Admin_Modules_Tradingsg_PurchaseOrderLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $expProdTitle = array('displayText' => $row['title'], 'target' => '_blank');
            $productTitle = $fn->getRecordDetailLink('trading_product',
                                                     'record_id',
                                                     $row['product_id'],
                                                     $expProdTitle);

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['po_code'])}
            {$listObj->getListDataCell($row['supplier_title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['purchase_order_id'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['purchase_order_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'PO Code', 'po.po_code')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Supplier', 'po.supplier_type')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
     *
     */
    function getListNotLinkedxx1($result, $linkRecType) {
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
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Product Code', 'p.product_code')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Product Name', 'p.title')}
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
    function getQuickSearch1() {
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
        if ($srcRoom == 'trading_shipment') {
            $SQLSO = "
            SELECT DISTINCT
                   so.sales_order_id
                  ,so.so_code
            FROM sales_order so
            JOIN inventory i ON i.sales_order_id = so.sales_order_id
            WHERE i.location = 'ready to ship'
            ORDER BY so.so_code
            ";
            $soOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSO);


            $text = "
            <td>
                <select name='order_id'>
                    <option value=''>Open Orders</option>
                    {$soOptions}
                </select>
            </td>

            ";

        } else {
            $product = getCPViewObj('trading_product');
            $text = $product->getQuickSearch();
        }

        return $text;
    }

    /**
     *
     */
    function getNewPortalFromEnquiryValidate1() {
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

    function getDetailPortal1(){
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'detail';
        return $this->getEdit();
    }

    /**
     *
     */
    function getEdit1($mode = 'edit'){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $text = '';

        if ($tv['srcRoom'] == 'trading_enquiry') {
            $text = $this->getEditPortalFromEnquiry();
        } else if ($tv['srcRoom'] == 'trading_rfq') {
            $text = $this->getEditPortalFromRfq();
        } else if ($tv['srcRoom'] == 'trading_quote') {
            $text = $this->getEditPortalFromQuote();
        } else if ($tv['srcRoom'] == 'trading_salesOrder') {
            $text = $this->getEditPortalFromSO();
        } else if ($tv['srcRoom'] == 'trading_invoice') {
            $text = $this->getEditPortalFromInvoice();
        } else if ($tv['srcRoom'] == 'trading_purchaseOrder') {
            $text = $this->getEditPortalFromPO();
        }

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromEnquiry1(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_spAction=savePortalFromEnquiry&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $enquiry_product_id = $fn->getReqParam('id');

        $fnsModProduct = includeCPClass('fnsMod', 'trading_product', 'FunctionsMod');

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
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.enquiryProductStatusArr'], $row['status'])}
            <input type='hidden' name='enquiry_product_id' value='{$enquiry_product_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditPortalFromRfq1(){
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

        $fnsModDeliveryTerms = getCPModelObj('trading_deliveryTermsLink');
        $fnsModProduct = getCPModelObj('trading_product');

        $sqlDeliveryTermsSupplier = $fnsModDeliveryTerms->getDeliveryTermsSQL($row['company_id_supplier']);

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
        {$formObj->getTBRow('Country of Origin', 'country_of_origin', $row['country_of_origin'])}
        {$formObj->getTBRow('Packing Details', 'packing_details', $row['packing_details'])}
        {$formObj->getTBRow('Total Volume (cbm)', 'total_volume', $row['total_volume'])}
        {$formObj->getTARow('Notes From Supplier', 'notes_from_supplier', $row['notes_from_supplier'])}

        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $exp)}
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
    function getEditPortalFromQuote1(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

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
              ,FORMAT(qi.other_costs_1, 2) AS other_costs_1
              ,FORMAT(qi.other_costs_2, 2) AS other_costs_2
              ,FORMAT(qi.other_costs_3, 2) AS other_costs_3
              ,qi.other_costs_1_base
              ,qi.other_costs_2_base
              ,qi.other_costs_3_base
              ,qi.sell_unit_price_total_net_cost_base
              ,qi.agent_comm_percentage
              ,qi.agent_comm_base
              ,qi.qc_comm_percentage
              ,qi.qc_comm_base
              ,qi.sell_unit_price_ex_fact_base
              ,qi.local_charges_percentage
              ,qi.local_charges_base
              ,qi.sell_unit_price_fob_base
              ,qi.shipping_cost_percentage
              ,qi.shipping_cost_base
              ,qi.insurance_cost_percentage
              ,qi.insurance_cost_base
              ,qi.sell_unit_price_cif_base
              ,qi.tax_percentage
              ,qi.tax_amount_base
              ,qi.sell_unit_price_base_vat
              ,qi.status
              ,p.title AS product_name
              ,qri.min_order_quantity
              ,qri.order_multiplier
              ,qi.lead_time
              ,q.sell_currency
              ,qr.buy_currency
              ,CONCAT_WS('-', e.enquiry_code, ep.line_no) AS enquiry_line_no
              ,p.unit
              ,p.product_id
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
        JOIN quote q ON (q.quote_id = qi.quote_id)
        JOIN enquiry e ON (e.enquiry_id = q.enquiry_id)
        JOIN enquiry_product ep ON (ep.enquiry_product_id = qi.enquiry_product_id)
        JOIN product p ON (qi.product_id = p.product_id)
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        LEFT JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        WHERE qi.quote_items_id = {$quote_items_id}
        ";
        
        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);

        $tradingProduct = getCPModuleObj('trading_product');

        $modDeliveryTerms = getCPModuleObj('trading_deliveryTermsLink');
        $sqlDeliveryTermsCustomer = $modDeliveryTerms->model->getDeliveryTermsSQL($row['company_id_customer']);

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
        $expVl = array('sqlType' => 'OneField');

        $expEnqLineNo = array('displayText' => $row['enquiry_line_no'], 'target' => '_blank');
        $enqLineNoText = $fn->getRecordDetailLink('trading_enquiry', 'record_id', $row['enquiry_id'], $expEnqLineNo);

        $expQuoteReqLineNo = array('displayText' => $row['quote_request_line_no'], 'target' => '_blank');
        $quoteReqLineNoText = $fn->getRecordDetailLink('trading_rfq', 'record_id',
                                                  $row['quote_request_id'], $expQuoteReqLineNo);

        //--------------------------------------------------------------------------//
        $SQLCurr = $fn->getValueListSQL('currency');
        $expCurr = array('ddSQL' => $SQLCurr);

        //---------------------------------------//
        //---------------------------------------//
        $buyCurrencyText = $formObj->getDisplayFldObj('buy_currency',
                                                      $row['buy_currency']);
        $baseCurr = '<b>' . $cpCfg['m.trading.companyCurrency'] . '</b>';
        $rowBuyCurr = $fnsModGrp->getCostingRowArr(
                          'Buy currency', $buyCurrencyText,
                          '', '', $baseCurr
                      );
        $rowBuyCurr = $fnsModGrp->getCostingTableRow($rowBuyCurr);

        //---------------------------------------//
        $unitBuyPriceText = $formObj->getDisplayFldObj('buy_unit_price', $row['buy_unit_price']);
        $unitBuyPriceBaseText = $formObj->getDisplayFldObj('buy_unit_price_base', $row['buy_unit_price_base']);
        $rowUnitBuyPrice = $fnsModGrp->getCostingRowArr(
                          'Unit buy price', $unitBuyPriceText,
                          '', '', $unitBuyPriceBaseText);
        $rowUnitBuyPrice = $fnsModGrp->getCostingTableRow($rowUnitBuyPrice);

        //---------------------------------------//
        $rowOtherCostLbl = $fnsModGrp->getCostingRowArr('', 'label', 'currency', 'amount', '');
        $rowOtherCostLbl = $fnsModGrp->getCostingTableRow($rowOtherCostLbl, 'th');

        //---------------------------------------//
        $otherCost1LblText = $formObj->getTextFldObj('other_costs_1_label', $row['other_costs_1_label']);
        $otherCost1CurrText = $formObj->getSelectFldObj('other_costs_1_curr', $row['other_costs_1_curr'],
                                                        '', $expCurr);
        $otherCost1Text = $formObj->getTextFldObj('other_costs_1', $row['other_costs_1']);
        $otherCost1BaseText = $formObj->getDisplayFldObj('other_costs_1_base', $row['other_costs_1_base']);
        $rowOtherCost1 = $fnsModGrp->getCostingRowArr('Other cost 1',
                                                 $otherCost1LblText,
                                                 $otherCost1CurrText,
                                                 $otherCost1Text,
                                                 $otherCost1BaseText);
        $rowOtherCost1 = $fnsModGrp->getCostingTableRow($rowOtherCost1);

        //---------------------------------------//
        $otherCost2LblText = $formObj->getTextFldObj('other_costs_2_label', $row['other_costs_2_label']);
        $otherCost2CurrText = $formObj->getSelectFldObj('other_costs_2_curr', $row['other_costs_2_curr'],
                                                        '', $expCurr);
        $otherCost2Text = $formObj->getTextFldObj('other_costs_2', $row['other_costs_2']);
        $otherCost2BaseText = $formObj->getDisplayFldObj('other_costs_2_base', $row['other_costs_2_base']);
        $rowOtherCost2 = $fnsModGrp->getCostingRowArr('Other cost 2',
                                                 $otherCost2LblText,
                                                 $otherCost2CurrText,
                                                 $otherCost2Text,
                                                 $otherCost2BaseText);
        $rowOtherCost2 = $fnsModGrp->getCostingTableRow($rowOtherCost2);

        //---------------------------------------//
        $otherCost3LblText = $formObj->getTextFldObj('other_costs_3_label', $row['other_costs_3_label']);
        $otherCost3CurrText = $formObj->getSelectFldObj('other_costs_3_curr', $row['other_costs_3_curr'],
                                                        '', $expCurr);
        $otherCost3Text = $formObj->getTextFldObj('other_costs_3', $row['other_costs_3']);
        $otherCost3BaseText = $formObj->getDisplayFldObj('other_costs_3_base', $row['other_costs_3_base']);
        $rowOtherCost3 = $fnsModGrp->getCostingRowArr('Other cost 3',
                                                 $otherCost3LblText,
                                                 $otherCost3CurrText,
                                                 $otherCost3Text,
                                                 $otherCost3BaseText);
        $rowOtherCost3 = $fnsModGrp->getCostingTableRow($rowOtherCost3);

        //---------------------------------------//
        $totalNetCostText = $formObj->getDisplayFldObj('sell_unit_price_total_net_cost_base',
                                                       $row['sell_unit_price_total_net_cost_base']);
        $rowTotalNetCost = $fnsModGrp->getCostingRowArr('Total net cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $totalNetCostText);
        $rowTotalNetCost = $fnsModGrp->getCostingTableRow($rowTotalNetCost);

        $tableRows = $rowBuyCurr
                   . $rowUnitBuyPrice
                   . $rowOtherCostLbl
                   . $rowOtherCost1
                   . $rowOtherCost2
                   . $rowOtherCost3
                   . $rowTotalNetCost;
        $tableNetCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $agentPercentText = $formObj->getTextFldObj('agent_comm_percentage',
                                                    $row['agent_comm_percentage']);
        $agentCommBaseText = $formObj->getDisplayFldObj('agent_comm_base',
                                                        $row['agent_comm_base']);
        $rowAgentPercent = $fnsModGrp->getCostingRowArr(
                           'Agent %', '', '',
                           $agentPercentText,
                           $agentCommBaseText);
        $rowAgentPercent = $fnsModGrp->getCostingTableRow($rowAgentPercent);

        //---------------------------------------//
        $qcPercentText = $formObj->getTextFldObj('qc_comm_percentage',
                                                 $row['qc_comm_percentage']);
        $qcCommBaseText = $formObj->getDisplayFldObj('qc_comm_base',
                                                     $row['qc_comm_base']);
        $rowQCPercent = $fnsModGrp->getCostingRowArr(
                           'QC %', '', '',
                           $qcPercentText,
                           $qcCommBaseText);
        $rowQCPercent = $fnsModGrp->getCostingTableRow($rowQCPercent);

        //---------------------------------------//
        $exFactoryCostText = $formObj->getDisplayFldObj('sell_unit_price_ex_fact_base',
                                                        $row['sell_unit_price_ex_fact_base']);
        $rowExFactoryCost = $fnsModGrp->getCostingRowArr('Ex factory cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $exFactoryCostText);
        $rowExFactoryCost = $fnsModGrp->getCostingTableRow($rowExFactoryCost);

        $tableRows = $rowAgentPercent
                   . $rowQCPercent
                   . $rowExFactoryCost;
        $tableExFactoryCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $localChargesPercentText = $formObj->getTextFldObj('local_charges_percentage',
                                                    $row['local_charges_percentage']);
        $localChargesBaseText = $formObj->getDisplayFldObj('local_charges_base',
                                                        $row['local_charges_base']);
        $rowLocalCharges = $fnsModGrp->getCostingRowArr(
                           'Local charges %', '', '',
                           $localChargesPercentText,
                           $localChargesBaseText);
        $rowLocalCharges = $fnsModGrp->getCostingTableRow($rowLocalCharges);

        //---------------------------------------//
        $fobCostText = $formObj->getDisplayFldObj('sell_unit_price_fob_base',
                                                  $row['sell_unit_price_fob_base']);
        $rowFobCostCost = $fnsModGrp->getCostingRowArr('FOB cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $fobCostText);
        $rowFobCostCost = $fnsModGrp->getCostingTableRow($rowFobCostCost);

        $tableRows = $rowLocalCharges
                   . $rowFobCostCost;
        $tableFobCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $shippingCostPercentText = $formObj->getTextFldObj('shipping_cost_percentage',
                                                           $row['shipping_cost_percentage']);
        $shippingCostBaseText = $formObj->getDisplayFldObj('shipping_cost_base',
                                                           $row['shipping_cost_base']);
        $rowShippingCost = $fnsModGrp->getCostingRowArr(
                           'Shipping %', '', '',
                           $shippingCostPercentText,
                           $shippingCostBaseText);
        $rowShippingCost = $fnsModGrp->getCostingTableRow($rowShippingCost);

        //---------------------------------------//
        $insuranceCostPercentText = $formObj->getTextFldObj('insurance_cost_percentage',
                                                            $row['insurance_cost_percentage']);
        $insuranceCostBaseText = $formObj->getDisplayFldObj('insurance_cost_base',
                                                            $row['insurance_cost_base']);
        $rowInsuranceCost = $fnsModGrp->getCostingRowArr(
                           'Insurance %', '', '',
                           $insuranceCostPercentText,
                           $insuranceCostBaseText);
        $rowInsuranceCost = $fnsModGrp->getCostingTableRow($rowInsuranceCost);

        //---------------------------------------//
        $cifCostText = $formObj->getDisplayFldObj('sell_unit_price_cif_base',
                                                  $row['sell_unit_price_cif_base']);
        $rowCifCost = $fnsModGrp->getCostingRowArr('CIF cost',
                                              '',
                                              '',
                                              '',
                                              $cifCostText);
        $rowCifCost = $fnsModGrp->getCostingTableRow($rowCifCost);

        $tableRows = $rowShippingCost
                   . $rowInsuranceCost
                   . $rowCifCost;
        $tableCifCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        //((ppt.sell_unit_price_base / {$pt_sell_unit_price_ex_fact_base}) - 1) * 100

        $sell_unit_price_base = $row['sell_unit_price_base'];
        $sell_unit_price_ex_fact_base = $row['sell_unit_price_ex_fact_base'];
        $sell_unit_price_fob_base = $row['sell_unit_price_fob_base'];
        $sell_unit_price_cif_base = $row['sell_unit_price_cif_base'];

        $ex_fact_markup = 0;
        $fob_markup = 0;
        $cif_markup = 0;

        if ($sell_unit_price_ex_fact_base > 0) {
            $ex_fact_markup = (($sell_unit_price_base / $sell_unit_price_ex_fact_base) - 1) * 100;
        }
        if ($sell_unit_price_fob_base > 0) {
            $fob_markup = (($sell_unit_price_base / $sell_unit_price_fob_base) - 1) * 100;
        }
        if ($sell_unit_price_cif_base > 0) {
            $cif_markup = (($sell_unit_price_base / $sell_unit_price_cif_base) - 1) * 100;
        }
        $ex_fact_markup = $fnsModGrp->getRoundAmount($ex_fact_markup);
        $fob_markup = $fnsModGrp->getRoundAmount($fob_markup);
        $cif_markup = $fnsModGrp->getRoundAmount($cif_markup);

        $ex_fact_markup_amount = $sell_unit_price_base - $sell_unit_price_ex_fact_base;
        $fob_markup_amount = $sell_unit_price_base - $sell_unit_price_fob_base;
        $cif_markup_amount = $sell_unit_price_base - $sell_unit_price_cif_base;
        $ex_fact_markup_amount = $fnsModGrp->getRoundAmount($ex_fact_markup_amount);
        $fob_markup_amount = $fnsModGrp->getRoundAmount($fob_markup_amount);
        $cif_markup_amount = $fnsModGrp->getRoundAmount($cif_markup_amount);

        $sellUnitPriceBase = $formObj->getTextFldObj('sell_unit_price_base',
                                                     $row['sell_unit_price_base']);
        $curr = $cpCfg['m.trading.companyCurrency'];
        $rows = "
        <tr class='pt'>
            <th class='col0'>Sales unit price (-VAT)</th>
            <td class='ex_fact_markup'><span>{$ex_fact_markup}</span> %</td>
            <td class='fob_markup'><span>{$fob_markup}</span> %</td>
            <td class='cif_markup'><span>{$cif_markup}</span> %</td>
            <td></td>
            <td class='col4 sales_unit_price_base'>{$sellUnitPriceBase}</td>
        </tr>

        <tr class='pt'>
            <th class='col0'></th>
            <td class='ex_fact_markup_amount'>{$curr} {$ex_fact_markup_amount}</td>
            <td class='fob_markup_amount'>{$curr} {$fob_markup_amount}</td>
            <td class='cif_markup_amount'>{$curr} {$cif_markup_amount}</td>
            <td></td>
            <td class='col4'></td>
        </tr>

        ";

        $tableSalesUnitPrice = "
        <table id='tblSalesPrice' class='costing'>
        <tr>
            <th></th>
            <th>ex fac. markup</th>
            <th>FOB markup</th>
            <th>CIF markup</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        {$rows}
        </table>
        ";

        //---------------------------------------//
        //---------------------------------------//
        $taxPercentText = $formObj->getDisplayFldObj('tax_percentage',
                                                     $row['tax_percentage']);
        $taxPercentBaseText = $formObj->getDisplayFldObj('tax_amount_base',
                                                         $row['tax_amount_base']);
//        $rowTaxPercent = $fnsModGrp->getCostingRowArr('VAT %',
//                                                 $taxPercentText,
//                                                 '',
//                                                 '',
//                                                 $taxPercentBaseText);
        $rowTaxPercent = $fnsModGrp->getCostingRowArr('VAT %',
                                                 $taxPercentText,
                                                 '',
                                                 '',
                                                 '');
        $rowTaxPercent = $fnsModGrp->getCostingTableRow($rowTaxPercent);

        //---------------------------------------//
        $salesUnitVATBaseText = $formObj->getDisplayFldObj('sell_unit_price_base_vat',
                                                         $row['sell_unit_price_base_vat']);
        $rowsalesUnitVAT = $fnsModGrp->getCostingRowArr('Sales unit price (+VAT)',
                                                 '',
                                                 '',
                                                 '',
                                                 $salesUnitVATBaseText);
        $rowsalesUnitVAT = $fnsModGrp->getCostingTableRow($rowsalesUnitVAT);

        $tableRows = $rowTaxPercent .
                     $rowsalesUnitVAT;
        $tableVAT = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $quantityText = $formObj->getTextFldObj('quantity',
                                                     $row['quantity']);
        $rowQuantity = $fnsModGrp->getCostingRowArr('Quantity',
                                                 $quantityText,
                                                 '',
                                                 '',
                                                 '');
        $rowQuantity = $fnsModGrp->getCostingTableRow($rowQuantity);

        //---------------------------------------//
        $salesUnitNoVATBaseText = $formObj->getDisplayFldObj('sell_price_base',
                                                         $row['sell_price_base']);
        $rowSalesUnitNoVAT = $fnsModGrp->getCostingRowArr('Total sales price (-VAT)',
                                                 '',
                                                 '',
                                                 '',
                                                 $salesUnitNoVATBaseText);
        $rowSalesUnitNoVAT = $fnsModGrp->getCostingTableRow($rowSalesUnitNoVAT);

        $tableRows = $rowQuantity .
                     $rowSalesUnitNoVAT;
        $tableSalesPrice = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $tableProductGuidePrice = $tradingProduct->view->getProductGuidePrice($row['product_id']);

        //---------------------------------------//
        $nextPrevLinks = "
        <div class='floatbox'>
            <a href='#' class='button2 float_right next'>Next</a>
            <a href='#' class='button2 float_right previous'>Previous</a>
        </div>
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$nextPrevLinks}
            {$formObj->getTBRow('Enquiry Line #', 'line_no', $enqLineNoText, $expNoEdit)}
            {$formObj->getTBRow('RFQ Line #', 'line_no', $quoteReqLineNoText, $expNoEdit)}
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            {$formObj->getTBRow('Minimum Order Quantity', 'min_order_quantity', $row['min_order_quantity'], $expNoEdit)}
            <div class='costs'>
                <div>{$tableNetCost}</div>
                <div class='costing-section'>{$tableExFactoryCost}</div>
                <div class='costing-section'>{$tableFobCost}</div>
                <div class='costing-section'>{$tableCifCost}</div>
                <div class='costing-section'>{$tableSalesUnitPrice}</div>
                <div class='mt10'>{$tableVAT}</div>
                <div class='costing-section'>{$tableSalesPrice}</div>
                <div class='costing-section'>{$tableProductGuidePrice}</div>
                {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.quoteProductStatusArr'], $row['status'])}
            </div>
            <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
            <input type='hidden' name='sell_currency' value='{$row['sell_currency']}' />
            <input type='hidden' id='markup' name='markup' value='{$row['markup']}' />
            {$nextPrevLinks}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortalFromSO1(){
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
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS so_line_no
              ,p.unit
              ,p.title AS product_name
              ,qri.min_order_quantity
              ,qri.lead_time
              ,qri.order_multiplier

              ,CASE WHEN qr.quote_request_id != '' AND qr.quote_request_id IS NOT NULL
                        THEN qr.quote_request_id
                    ELSE qr2.quote_request_id
               END AS quote_request_id

              ,CASE WHEN qr.quote_request_id != '' AND qr.quote_request_id IS NOT NULL
                        THEN qr.buy_currency
                    ELSE qr2.buy_currency
               END AS buy_currency

              ,CASE WHEN qr.quote_request_id != '' AND qr.quote_request_id IS NOT NULL
                        THEN CONCAT_WS('-', qr2.quote_request_code, qri2.line_no)
                    ELSE CONCAT_WS('-', qr2.quote_request_code, qri2.line_no)
               END AS quote_request_line_no

              ,so.sell_currency
              ,so.company_id_customer
              ,q.quote_id
              ,CONCAT_WS('-', q.quote_code, qi.line_no) AS quote_line_no
        FROM sales_order_items soi
        JOIN product p ON p.product_id = soi.product_id
        JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
        LEFT JOIN quote_items qi ON qi.quote_items_id = soi.quote_items_id
        LEFT JOIN quote q ON q.quote_id = qi.quote_id
        LEFT JOIN quote_request_items qri ON qri.quote_request_items_id = soi.quote_request_items_id
        LEFT JOIN quote_request qr ON qr.quote_request_id = qri.quote_request_id
        LEFT JOIN quote_request_items qri2 ON qri2.quote_request_items_id = p.quote_request_items_id
        LEFT JOIN quote_request qr2 ON qr2.quote_request_id = qri2.quote_request_id
        WHERE soi.sales_order_items_id = {$sales_order_items_id}
        ";

        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);

        $modDeliveryTerms = getCPModuleObj('trading_deliveryTermsLink');
        $sqlDeliveryTermsCustomer = $modDeliveryTerms->model->getDeliveryTermsSQL($row['company_id_customer']);

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

        $markupFld = "<div id='t_markup'>{$row['markup']}</div>";
        $markupText = "
        {$formObj->getTBRow('Markup (%)', 'markup', $markupFld, $expNoEdit)}
        ";

        $expVl = array('sqlType' => 'OneField');

        $expQuoteLineNo = array('displayText' => $row['quote_line_no'], 'target' => '_blank');
        $quoteLineNoText = $fn->getRecordDetailLink('trading_quote', 'record_id', $row['quote_id'], $expQuoteLineNo);

        $expRFQLineNo = array('displayText' => $row['quote_request_line_no'], 'target' => '_blank');
        $rfqLineNoText = $fn->getRecordDetailLink('trading_rfq', 'record_id', $row['quote_request_id'], $expRFQLineNo);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('SO Line #', 'line_no', $row['so_line_no'], $expNoEdit)}
            {$formObj->getTBRow('Quote Line #', 'line_no', $quoteLineNoText, $expNoEdit)}
            {$formObj->getTBRow('RFQ Line #', 'rfq_line_no', $rfqLineNoText, $expNoEdit)}
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            {$formObj->getTBRow('Quantity', 'quantity', $row['quantity'])}
            <div class='multiFldTblWrapper'>
                {$textBuyCurrency}
                {$textBuyUnitPrice}
                {$textBuyPrice}
            </div>
            {$markupText}
            {$textOtherCostsLbl}
            {$textOtherCosts1}
            {$textOtherCosts2}
            {$textOtherCosts3}
            <div class='multiFldTblWrapper'>
                {$textSellCurrency}
                {$textSellUnitPrice}
                {$textSellPrice}
            </div>
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.salesOrderProductStatusArr'], $row['status'])}
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
    function getEditPortalFromInvoice1(){
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
            {$formObj->getTBRow('Product Code', 'product_code', $row['product_code'], $expNoEdit)}
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
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
    function getEditPortalFromPO1() {
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

        $modDeliveryTerms = getCPModuleObj('trading_deliveryTermsLink');
        $sqlDeliveryTermsSupplier = $modDeliveryTerms->model->getDeliveryTermsSQL($row['company_id_supplier']);

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
            {$formObj->getTBRow('Product Code', 'product_code', $row['product_code'], $expNoEdit)}
            {$formObj->getTBRow('Product Name', 'product_name', $row['product_name'], $expNoEdit)}
            {$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}
            {$formObj->getTBRow('Order Quantity', 'quantity', $row['quantity'])}
            {$textBuyCurrency}
            {$textBuyUnitPrice}
            {$textBuyPrice}
            {$formObj->getDateRow('Request Date', 'request_date', $row['request_date'])}
            {$formObj->getDateRow('Promised Delivery Date', 'promised_delivery_date', $row['promised_delivery_date'])}
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
    function getMultiFieldValueHTMLTemplate1($fieldLabel = '', $header = false, $divClass = '') {
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
            <label for=''>{$fieldLabel}&nbsp;</label>
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
    function getMultiFieldValueHTMLTemplateOtherCost1($fieldLabel = '', $header = false) {
        $headerClassText = '';
        if ($header) {
            $headerClassText = '-header';
        }

        $text = "
        <div class='type-text'>
            <label>{$fieldLabel}&nbsp;</label>
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
