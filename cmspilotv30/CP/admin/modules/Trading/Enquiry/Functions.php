<?
class CP_Admin_Modules_Trading_Enquiry_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_enquiry');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media', 'enquiry_product')
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('tradingRaiseRfqList', 'tradingRaiseQuote', 'edit', 'delete', 'duplicate')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('trading_enquiry', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('trading_enquiry', 'trading_productLink', array(
            'historyTableName' => 'enquiry_product'
           ,'hasPortalEdit'    => 1
           ,'hasPortalDetail'  => 1
           ,'hasHistoryCallback' => true
           ,'showAlternativeRowColor' => false
           ,'recordTypeForHistory' => 'product'
           ,'chooseLinkValidateJsMethod' => 'cpm.trading.enquiry.validateEditLink'
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'showAnchorInLinkPortal' => false
           ,'fieldlabel'      => array('Line #'
                                      ,'Product Code'
                                      ,'Web Code'
                                      ,'Product Name'
                                      ,'Quantity'
                                      ,'Colour'
                                      ,'Colour Inside'
                                      ,'Status'
                                      ,''
                                      ,''
                                 )
            )
        );
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_enquiry', 'trading_inventoryLink', array(
                'historyTableName' => 'enquiry_product'
               ,'hasPortalEdit'    => 1
               ,'hasPortalDetail'  => 1
               ,'keyFieldForHistory' => 'product_id'
               ,'keyFieldForLinking' => 'product_id'
               ,'showAlternativeRowColor' => false
               ,'recordTypeForHistory' => 'inventory'
               ,'chooseLinkValidateJsMethod' => 'cpm.trading.enquiry.validateEditLink'
               ,'anchorFieldsArr' => array(
                    'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id')
                   ,'po_line_no' => $inst->getLinkAnchorObj('po_line_no', 'purchase_order_id', false, 'trading_purchaseOrder')
                )
               ,'fieldlabel' => array(
                    'Line #'
                   ,'Item #'
                   ,'Product Name'
                   ,'Quantity'
                   ,'Colour'
                   ,'Colour Inside'
                   ,'Status'
                   ,'PO Line #'
                )
            )
        );
        $inst->registerLinksArray($linkObj);
        $linkObj = $inst->getLinksArrayObj('trading_enquiry', 'trading_quoteLink', array(
            'historyTableName'   => 'quote'
           ,'linkingType' => 'portal'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'keyFieldForHistory' => 'quote_id'
           ,'keyFieldForLinking' => 'quote_id'
           ,'fieldlabel' => array('Quote Number'
                                 ,'Quote Date'
                                 ,'Quote Status'
                            )
        ));
        $inst->registerLinksArray($linkObj);
    }

    /**
     *
     */
    function getTradingEnquiryTradingProductLinkAddLinkCallback($enquiry_product_id, $enqProdRow) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        $line_no = $fnsModGrp->getNextItemLineNo('enquiry_id', $enqProdRow['enquiry_id'], 'enquiry_product', $enquiry_product_id);
        $fnsModGrp->getUpdateHistoryTableLineNo('enquiry_product', 'enquiry_product_id', $enquiry_product_id, $line_no);

        //set default values

        $fa = array();
        $fa['status'] = 'new';

        $whereCondition = "
        WHERE enquiry_product_id = {$enquiry_product_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'enquiry_product', $whereCondition);
        $db->sql_query($SQL);
    }

    /**
     *
     */
    function getTradingEnquiryTradingProductLinkHistoryCallback($enqProd) {
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        //--------------------------------------------------------//
        $SQL = "
        SELECT DISTINCT
               qr.quote_request_code
              ,qr.quote_request_id
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,c.company_name AS supplier_name
              ,qr.quote_request_date
              ,qri.status
              ,qri.quote_request_items_id
              ,qr.buy_currency

              ,(SELECT 1 FROM enquiry_product ep
                WHERE ep.quote_request_items_id = qri.quote_request_items_id
                  AND ep.enquiry_product_id = {$enqProd['enquiry_product_id']}
                LIMIT 1) AS selected

              ,qri.buy_unit_price
              ,qri.lead_time
              ,qri.quantity
              ,qri.min_order_quantity
              ,qri.order_multiplier
              ,qri.buy_unit_price_base
              ,qr.valid_until
              ,qr.delivery_terms_supplier
              ,qr.shipping_method
        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c ON (c.company_id = qr.company_id_supplier)
        JOIN quote_request_items_selected qris
          ON (    qris.enquiry_product_id = {$enqProd['enquiry_product_id']}
              AND qris.quote_request_items_id = qri.quote_request_items_id)
        WHERE qris.enquiry_product_id = {$enqProd['enquiry_product_id']}
        ORDER BY qr.valid_until DESC
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        //FB::log($SQL);

        $checkImage = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_checked.gif' />";

        $rows = '';
        $rowCounter = 0;
        if ($numRows > 0) {
            while ($row = $db->sql_fetchrow($result)) {
                $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';

                $expRfq  = array('displayText' => $row['quote_request_line_no']);

                $rfqCodeText = $fn->getRecordDetailLink('trading_rfq', 'record_id', $row['quote_request_id'], $expRfq);


                $selectedText = $row['selected'] ? $checkImage : '';
                if ($tv['action'] == 'edit') {
                    $checkedText = $row['selected'] ? "checked='checked'" : '';
                    $selectedText = "<input type='checkbox'
                                     class='select-rfq select-{$enqProd['enquiry_product_id']}'
                                     quote_request_items_id='{$row['quote_request_items_id']}'
                                     enquiry_product_id='{$enqProd['enquiry_product_id']}'
                                     {$checkedText} />";
                }

                $rows .= "
                <tr class='{$bgClass}'>
                    <td>{$rfqCodeText}</td>
                    <td>{$row['supplier_name']}</td>
                    <td>{$row['quote_request_date']}</td>
                    <td>{$row['buy_currency']}</td>
                    <td>{$row['buy_unit_price']}</td>
                    <td>{$row['valid_until']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['min_order_quantity']}</td>
                    <td>{$row['buy_unit_price_base']}</td>
                    <td>{$selectedText}</td>
                </tr>
                ";
                $rowCounter++;

            }

            $text = "
            <div class='showHide' title='show / hide RFQs'></div>
            <div class='quote-req-cont'>
                <table>
                <tr>
                <th>RFQ #</th>
                <th>Supplier</th>
                <th>RFQ Creation Date</th>
                <th>Buy Currency</th>
                <th>Unit Buy Price</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th>Qty</th>
                <th>MOQ</th>
                <th>Unit Buy Price ({$cpCfg['m.trading.companyCurrency']})</th>
                <th>Selected</th>
                </tr>

                <tbody>
                {$rows}
                </tbody>
                </table>
            </div>
            ";
        } //if numRows > 0

        return $text;
    }

    function getTradingEnquiryTradingInventoryLinkAddLinkCallback($enquiry_product_id, $enqProdRow) {
        return $this->getTradingEnquiryTradingProductLinkAddLinkCallback($enquiry_product_id, $enqProdRow);
    }

    function getTradingEnquiryTradingInvetoryLinkHistoryCallback($enqProd) {
        return $this->getTradingEnquiryTradingProductLinkHistoryCallback($enqProd);
    }
}