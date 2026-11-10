<?
class CP_Admin_Modules_EnggCrm_Order_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_order');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array()
           ,'actBtnsEdit' => array('save', 'apply','cancel')
           ,'actBtnsDetail' => array('edit')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('enggCrm_order', 'enggCrm_orderItemLink');
        $productArr = $fn->getDdDataAsArray($cpCfg['m.enggCrm.order.itemsMainModule']);

        if($cpCfg['m.enggCrm.product.hasProductItem']){
            $additionalFldsArr = array(
                 'b.item_title'
                ,'b.sku_no'
                ,'b.unit_price'
                ,'b.qty'
                ,'b.qty * b.unit_price'
            );

            $fieldlabelArr = array(
                 'Product'
                ,'SKU No'
                ,'Unit Price'
                ,'Qty'
                ,'Sub-Total'
            );

        } else {
            $additionalFldsArr = array(
                 'b.item_title'
                ,'b.unit_price'
                ,'b.qty'
                ,'b.qty * b.unit_price'
            );

            $fieldlabelArr = array(
                 'Description'
                ,'Quantity'
                ,'Unit Price'
                ,'Sub-Total'
                ,'Discount'
                ,'Total'
                ,'Remarks'
            );
        }

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'order_item'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'order_item_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
            ,'fieldlabel'             => $fieldlabelArr
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $productArr)
            )
            ,'additionalFieldsArray' => $additionalFldsArr
            ,'fieldClassArray' => array('', 'txtRight', 'txtRight', 'txtRight', 'txtRight', 'txtRight')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_order', 'enggCrm_invoiceLink');
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'invoice_item'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'invoice_item_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
           ,'fieldlabel'              => array('Invoice Code'
                                              ,'Invoice Status'
                                              ,'Invoice Date'
                                              ,'Staff Name'
                                              ,'Invoice Amount'
                                              )
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'fieldClassArray' => array('', '', '', '', 'txtRight')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_order', 'enggCrm_receiptLink');
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'receipt'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'receipt_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
           ,'fieldlabel'              => array('Receipt Code'
                                              ,'Receipt Date'
                                              ,'Mode of Payment'
                                              ,'Receipt Amount'
                                              )
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'fieldClassArray' => array('', '', '', 'txtRight')
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_order', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('enggCrm_order', 'label', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }


    /**
     *
     */
    function getTotal($product_group, $order_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT SUM(oi.qty * oi.unit_price) AS total
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN product p ON (p.product_id = oi.record_id)
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        WHERE o.order_id = {$order_id}
          AND pg.title = '{$product_group}'
        ";
        $result = $db->sql_query($SQL);
        $rowTotal = $db->sql_fetchrow($result);

        return $rowTotal['total'];
    }

    /**
     *
    */
    function getEnggCrmOrderEnggCrmOrderItemLinkPortalSearch1() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $invoice_id = $fn->getReqParam('invoice_id');

        $sqlInvoice = "
        SELECT i.invoice_id, i.invoice_code
        FROM invoice i
        ";

        $text = "
        <select name='invoice_id' class='float_right m5'>
            <option value=''>Invoice</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlInvoice, $invoice_id)}
        </select>
        ";

        return $text;
    }
}