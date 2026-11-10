<?
class CP_Admin_Modules_Trading_SalesOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_salesOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'sales_order'
           ,'keyField' => 'sales_order_id'
           ,'title' => 'Sales Order'
           ,'relatedTables' => array('media', 'sales_order_items')
           ,'actBtnsList' => array('tradingNewSO', 'tradingNewSOR', 'tradingNewInternalSO')
           ,'actBtnsDetail' => array('tradingPrintSO', 'tradingPrintDeliveryNoteSO'
                                    ,'tradingRaisePO', 'tradingRaiseInvoice'
                                    ,'edit', 'delete', 'duplicate')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('trading_salesOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('trading_salesOrder', 'trading_productLink', array(
            'historyTableName' => 'sales_order_items'
           ,'hasPortalDetail' => 1
           ,'hasPortalEdit' => 1
           ,'hasHistoryCallback' => true
           ,'recordTypeForHistory' => 'product'
           ,'portalDialogWidth' => 800
           ,'portalDialogHeight' => 750
           ,'chooseLinkValidateJsMethod' => 'cpm.trading.salesOrder.validateChooseProductLink'
           ,'editLinkItemValidateJsMethod' => 'cpm.trading.salesOrder.validateEditProductItemLink'
           ,'showAnchorInLinkPortal' => false
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id')
                                      ,'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id'))
           ,'fieldlabel' => array('Line #'
                                ,'Product Code'
                                ,'Web Code'
                                ,'Product Name'
                                ,'Color'
                                ,'Order Quantity'
                                ,'UOM'
                                ,'Sell Currency'
                                ,'Sell Unit Price'
                                ,'Total Sell Price'
                                ,'Request Date'
                                ,'Promised Delivery Date'
                            )
            ,'fieldClassArray' => array(
                 5 => 'al-right'
                ,7 => 'al-right'
                ,8 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'quantity_ordered'
              ,'sell_price'
            )
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_salesOrder', 'trading_inventoryLink', array(

            'historyTableName'   => 'sales_order_inventory'
           ,'hasPortalDetail'    => 0
           ,'hasPortalEdit'      => 0
           ,'keyFieldForHistory' => 'inventory_id'
           ,'keyFieldForLinking' => 'inventory_id'
           ,'portalDialogWidth'  => 800
           ,'portalDialogHeight' => 750
           ,'portalDialogWidthInternal' => '1550'
//           ,'chooseLinkValidateJsMethod' => 'cpm.trading.salesOrder.validateInventoryEditLink'
           ,'anchorFieldsArr' => array(
               'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id', false, 'trading_product')
              ,'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id', false, 'trading_product')
              ,'serial_no' => $inst->getLinkAnchorObj('serial_no', 'inventory_id', true, 'trading_inventory')
           )
           ,'hideFieldsArray' => array('inventory_id')
           ,'fieldlabel' => array('Product Code'
                                 ,'Web Code'
                                 ,'Inventory<br>Serial'
                                 ,'Product Name'
                                 ,'Status'
                                 ,'Location'
                                 ,'UOM'
                                 ,'Sell Currency'
                                 ,'Sell Price Actual'
                            )
            ,'fieldClassArray' => array(
                8 => 'al-right'
            )
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_salesOrder', 'trading_invoiceLink', array(
            'historyTableName'   => 'invoice'
           ,'linkingType' => 'portal'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'keyFieldForHistory' => 'invoice_id'
           ,'keyFieldForLinking' => 'invoice_id'
           ,'anchorFieldsArr' => array(
               'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id', false, 'trading_product')
              ,'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id', false, 'trading_product')
              ,'serial_no' => $inst->getLinkAnchorObj('serial_no', 'inventory_id', true, 'trading_inventory')
           )
           ,'hideFieldsArray' => array('inventory_id')
           ,'fieldlabel' => array('Invoice Number'
                                 ,'Invoice Type'
                                 ,'Invoice Date'
                                 ,'Invoice Amount'
                                 ,'Invoice Status'
                            )
            ,'fieldClassArray' => array(
                3 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'invoice_amount'
           )
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_salesOrder', 'trading_purchaseOrderLink', array(
            'linkingType' => 'portal'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'anchorFieldsArr' => array(
               'supplier_name' => $inst->getLinkAnchorObj('supplier_name', 'company_id_supplier', false, 'trading_company')
           )
           ,'hideFieldsArray' => array('inventory_id')
           ,'fieldlabel' => array('PO Number'
                                 ,'PO Date'
                                 ,'Supplier'
                                 ,'PO Amount'
                                 ,'PO Status'
                            )
            ,'fieldClassArray' => array(
                3 => 'al-right'
            )
        ));
        $inst->registerLinksArray($linkObj);
    }

    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');

        $repInst->setReportArrayObj('trading_salesOrder', 'salesOrder');
        $arr = &$repInst->reportsArray['trading_salesOrder']['salesOrder'];
        $arr['jasperFileName'] = 'salesOrder.jasper';

        $repInst->setReportArrayObj('trading_salesOrder', 'salesOrderInventory');
        $arr = &$repInst->reportsArray['trading_salesOrder']['salesOrderInventory'];
        $arr['jasperFileName'] = 'salesOrderInventory.jasper';

        $repInst->setReportArrayObj('trading_salesOrder', 'salesOrderInventorySerial');
        $arr = &$repInst->reportsArray['trading_salesOrder']['salesOrderInventorySerial'];
        $arr['jasperFileName'] = 'salesOrderInventorySerial.jasper';

        $repInst->setReportArrayObj('trading_salesOrder', 'deliveryNoteSalesOrder');
        $arr = &$repInst->reportsArray['trading_salesOrder']['deliveryNoteSalesOrder'];
        $arr['jasperFileName'] = 'deliveryNoteSalesOrder.jasper';
    }

    /**
     *
     */
    function getTradingSalesOrderTradingProductLinkHistoryCallback($rowSOI) {

        return '';

        $text = "
        <div class='showHide' title='show / hide invoices & Shipments'></div>
        <div class='container'>
        {$this->getSalesOrderProductShipment($rowSOI)}
        {$this->getSalesOrderProductInvoice($rowSOI)}
        {$this->getSalesOrderProductPO($rowSOI)}
        </div>
        ";

        return $text;
    }

    function getTradingSalesOrderTradingInventoryLinkHistoryCallback($rowSOI) {
        return $this->getTradingSalesOrderTradingProductLinkHistoryCallback($rowSOI);
    }

    /**
     *
     */
    private function getSalesOrderProductShipment($rowSOI) {
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        //--------------------------------------------------------//
        $SQL = "
        SELECT s.shipment_code
              ,s.shipment_id
              ,CONCAT_WS('-', s.shipment_code, si.line_no) AS shipment_line_no
              ,s.shipment_date
              ,s.scheduled_ship_date
              ,s.estimated_arrival_date
              ,si.quantity_shipped
              ,si.status
        FROM shipment_items si
        JOIN shipment s ON (s.shipment_id = si.shipment_id)
        WHERE si.sales_order_items_id = {$rowSOI['sales_order_items_id']}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;
        if ($numRows > 0) {
            while ($row = $db->sql_fetchrow($result)) {
                $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';

                $expShipment = array('displayText' => $row['shipment_line_no']);

                $shipmentCodeText = $fn->getRecordDetailLink('trading_shipment', 'record_id', $row['shipment_id'], $expShipment);

                $rows .= "
                <tr class='{$bgClass}'>
                    <td>{$shipmentCodeText}</td>
                    <td>{$row['shipment_date']}</td>
                    <td>{$row['quantity_shipped']}</td>
                    <td>{$row['estimated_arrival_date']}</td>
                    <td>{$row['scheduled_ship_date']}</td>
                    <td>{$row['status']}</td>
                </tr>
                ";
                $rowCounter++;

            }

            $text = "
            <table>
                <tr>
                <th>Shipment #</th>
                <th>Shipment Creation Date</th>
                <th>Ship Qty</th>
                <th>Actual Ship Date</th>
                <th>Estimated Arrival Date (ETA)</th>
                <th>Status</th>
                </tr>

                <tbody>
                {$rows}
                </tbody>
            </table>
            ";
        }

        return $text;

    }

    /**
     *
     */
    private function getSalesOrderProductInvoice($rowSOI) {
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        //--------------------------------------------------------//
        $SQL = "
        SELECT i.invoice_code
              ,i.invoice_id
              ,CONCAT_WS('-', i.invoice_code, ii.line_no) AS invoice_line_no
              ,i.invoice_date
              ,i.invoice_due_date
              ,i.sell_currency
              ,ii.sell_price
              ,ii.status
              ,ROUND( ((ii.sell_price / (soi.sell_unit_price * soi.quantity) ) * 100), 2) AS invoice_percentage
        FROM invoice_items ii
        JOIN invoice i ON (i.invoice_id = ii.invoice_id)
        JOIN sales_order_items soi ON (soi.sales_order_items_id = ii.sales_order_items_id)
        WHERE ii.sales_order_items_id = {$rowSOI['sales_order_items_id']}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;
        if ($numRows > 0) {
            while ($row = $db->sql_fetchrow($result)) {
                $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';

                $expInvoice = array('displayText' => $row['invoice_line_no']);

                $invoiceCodeText = $fn->getRecordDetailLink('trading_invoice', 'record_id', $row['invoice_id'], $expInvoice);

                $rows .= "
                <tr class='{$bgClass}'>
                    <td>{$invoiceCodeText}</td>
                    <td>{$row['invoice_date']}</td>
                    <td>{$row['sell_currency']}</td>
                    <td>{$row['sell_price']}</td>
                    <td>{$row['invoice_percentage']}</td>
                    <td>{$row['status']}</td>
                </tr>
                ";
                $rowCounter++;

            }

            $text = "
            <table>
                <tr>
                <th>Invoice #</th>
                <th>Invoice Date</th>
                <th>Sell Currency</th>
                <th>Invoice Amount</th>
                <th>Invoice %</th>
                <th>Invoice Line Status</th>
                </tr>

                <tbody>
                {$rows}
                </tbody>
            </table>
            ";
        }

        return $text;

    }

    /**
     *
     */
    private function getSalesOrderProductPO($rowSOI) {
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        //--------------------------------------------------------//
        $SQL = "
        SELECT poi.purchase_order_items_id
              ,poi.purchase_order_id
              ,CONCAT_WS('-', po.po_code, poi.line_no) AS po_line_no
              ,poi.product_id
              ,poi.quantity
              ,poi.status
              ,po.buy_currency
              ,po.purchase_order_date
              ,poi.buy_unit_price
              ,poi.buy_unit_price * poi.quantity AS buy_price
              ,poi.quantity_delivered
              ,poi.total_paid_amount
              ,poi.status
        FROM purchase_order_items poi
        JOIN purchase_order po            ON (po.purchase_order_id = poi.purchase_order_id)
        JOIN sales_order_items soi        ON (soi.sales_order_items_id = poi.sales_order_items_id)
        JOIN sales_order so               ON (so.sales_order_id = soi.sales_order_id)
        WHERE poi.sales_order_items_id = {$rowSOI['sales_order_items_id']}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;
        if ($numRows > 0) {
            while ($row = $db->sql_fetchrow($result)) {
                $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';

                $expPO = array('displayText' => $row['po_line_no']);

                $poCodeText = $fn->getRecordDetailLink('trading_purchaseOrder', 'record_id', $row['purchase_order_id'], $expPO);

                $rows .= "
                <tr class='{$bgClass}'>
                    <td>{$poCodeText}</td>
                    <td>{$row['purchase_order_date']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['buy_currency']}</td>
                    <td>{$row['buy_unit_price']}</td>
                    <td>{$row['buy_price']}</td>
                    <td>{$row['status']}</td>
                </tr>
                ";
                $rowCounter++;

            }

            $text = "
            <table>
                <tr>
                <th>PO #</th>
                <th>PO Creation Date</th>
                <th>Quantity</th>
                <th>Buy Currency</th>
                <th>Unit Buy Price</th>
                <th>Total Buy Price</th>
                <th>PO Line Status</th>
                </tr>

                <tbody>
                {$rows}
                </tbody>
            </table>
            ";
        }

        return $text;
    }

    function getTradingSalesOrderTradingProductLinkAddLinkCallback($sales_order_items_id, $rowSOI) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        $line_no = $fnsModGrp->getNextItemLineNo('sales_order_id',
                                                 $rowSOI['sales_order_id'],
                                                 'sales_order_items');
        $fnsModGrp->getUpdateHistoryTableLineNo('sales_order_items',
                                                'sales_order_items_id',
                                                $sales_order_items_id,
                                                $line_no);

        $rowProd = $fn->getRecordRowByID('product', 'product_id', $rowSOI['product_id']);
        $rowPT = getCPModelObj('trading_pricingType')
                 ->getRecordByType('no_tax');

        $SQL = "
        SELECT *
        FROM product_pricing_type
        WHERE pricing_type_id = {$rowPT['pricing_type_id']}
          AND product_id = {$rowSOI['product_id']}
        ";
        $rowPPT = $fn->getRecordBySQL($SQL);

        $SQL = "
        SELECT qri.buy_unit_price
              ,qri.buy_unit_price_base
        FROM quote_request_items qri
        JOIN product p ON p.quote_request_items_id = qri.quote_request_items_id
        WHERE p.product_id = {$rowSOI['product_id']}
        ";
        $rowQRI = $fn->getRecordBySQL($SQL);

        //set default values
        $fa = array();
        $fa['status'] = 'new';
        $fa['buy_unit_price']       = $rowQRI['buy_unit_price'];
        $fa['buy_unit_price_base']  = $rowQRI['buy_unit_price_base'];
        $fa['sell_unit_price']      = $rowPPT['sell_unit_price_base'];
        $fa['sell_unit_price_base'] = $rowPPT['sell_unit_price_base'];

        $whereCondition = "
        WHERE sales_order_items_id = {$sales_order_items_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'sales_order_items', $whereCondition);
        $db->sql_query($SQL);
    }

    function getTradingSalesOrderTradingInventoryLinkAddLinkCallback($sales_order_items_id, $rowSOI) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $rowSOI['sales_order_id']);
        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $rowSOI['inventory_id']);

        //update inventory record
        $modInv = getCPModuleObj('trading_inventory');
        $sell_unit_price_actual = 0;

        $inventory_id = $rowSOI['inventory_id'];
        $rowComp = $fn->getRecordRowByID('company', 'company_id', $rowSO['company_id_customer']);
        $rowPT = $fn->getRecordRowByID('pricing_type', 'pricing_type_id', $rowComp['pricing_type_id']);

        // $SQL = "SELECT * FROM pricing_type WHERE record_type = 'trade'";
        // $rowPTTrade = $fn->getRecordBySQL($SQL);

        // $sell_unit_price_actual = $modInv->model->getActualSellPrice($inventory_id);

        // $discount_percent = $rowPT['discount_percent'];
        // $sell_unit_price_actual = $sell_unit_price_actual -
        //                           $sell_unit_price_actual * ($discount_percent / 100);

        $SQL = "
        SELECT *
        FROM product_pricing_type
        WHERE pricing_type_id = {$rowPT['pricing_type_id']}
          AND product_id = {$rowInv['product_id']}
        ";
        $rowPPT = $fn->getRecordBySQL($SQL);

        $fa = array();
        //set default values
        $fa['sales_order_id_inventory']      = $rowSOI['sales_order_id'];
        $fa['company_id_customer_inventory'] = $rowSO['company_id_customer'];
        $fa['sell_unit_price_actual']        = $rowPPT['sell_unit_price_base'];

        $status = '';
        $location = '';
        if ($rowSO['order_type'] == 'SOR') {
            $fa['status']   = 'available';
            $fa['location'] = 'SOR';
        } else {
            $status = 'on enquiry';
        }
        $whereCondition = "
        WHERE inventory_id = {$rowSOI['inventory_id']}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        $db->sql_query($SQL);
    }

    function getTradingSalesOrderTradingInventoryLinkDeleteLinkCallback($sales_order_id, $inventory_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //set default values
        $fa = array();
        $fa['status'] = 'available';
        $fa['sales_order_id_inventory']      = 0;
        $fa['company_id_customer_inventory'] = 0;
        $fa['status'] = 'available';

        $whereCondition = "
        WHERE inventory_id = {$inventory_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        $db->sql_query($SQL);
    }
}