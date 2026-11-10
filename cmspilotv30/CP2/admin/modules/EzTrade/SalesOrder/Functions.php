<?
class CP_Admin_Modules_EzTrade_SalesOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ezTrade_salesOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'sales_order'
           ,'keyField' => 'sales_order_id'
           ,'title' => 'Sales Order'
           ,'relatedTables' => array('media', 'sales_order_items')
           ,'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('tradingPrintBankInvoice', 'tradingRaisePO', 'tradingRaiseInvoice', 'edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ezTrade_salesOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('ezTrade_salesOrder', 'ezTrade_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'   => 'sales_order_items'
           ,'hasPortalDetail'    => 1
           ,'hasPortalEdit'      => 1
           ,'hasHistoryCallback' => true
           ,'portalDialogWidth'  => 800
           ,'portalDialogHeight' => 750
           ,'anchorFieldsArr'    => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id')
                                         ,'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id'))
           ,'fieldlabel' => array('Line #'
                                , 'Item Number'
                                , 'Item Name'
                                , 'Order Quantity'
                                , 'UOM'
                                , 'Shipped Quantity'
                                , 'Sell Currency'
                                , 'Sell Unit Price'
                                , 'Total Sell Price'
                                , 'Request Date'
                                , 'Promised Delivery Date'
                                , 'Status'
                            )
           ,'summaryFieldsArray' => array(
               'quantity_ordered'
              ,'sell_price'
            )
        ));
    }

    /**
     *
     */
    function getEzTradeSalesOrderEzTradeProductLinkHistoryCallback($rowSOI) {

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

                $shipmentCodeText = $fn->getRecordDetailLink('ezTrade_shipment', 'record_id', $row['shipment_id'], $expShipment);

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

                $invoiceCodeText = $fn->getRecordDetailLink('ezTrade_invoice', 'record_id', $row['invoice_id'], $expInvoice);

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

                $poCodeText = $fn->getRecordDetailLink('ezTrade_purchaseOrder', 'record_id', $row['purchase_order_id'], $expPO);

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
}