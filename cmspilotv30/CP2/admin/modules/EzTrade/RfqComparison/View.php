<?
class CP_Admin_Modules_EzTrade_RfqComparison_View extends CP_Common_Lib_ModuleViewAbstract
{
    //========================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $checked = '';
            if ($row['selected'] == 1) {
                $checked = "checked='checked'";
            }

            $checkboxText = "
            <input type='checkbox' class='choose' name='ids[]'
                   value='{$row['enquiry_product_id']}-{$row['quote_request_items_id']}' {$checked} />
            ";

            $expRfq  = array('displayText' => $row['quote_request_line_no'], 'target' => '_blank');
            $expProd = array('displayText' => $row['product_code'], 'target' => '_blank');

            $rfqText     = $fn->getRecordDetailLink('ezTrade_rfq', 'record_id', $row['quote_request_id'], $expRfq);
            $productText = $fn->getRecordDetailLink('ezTrade_product', 'record_id', $row['product_id'], $expProd);

            $exp = array('hasFlagInList' => false
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
                   );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($rfqText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListDataCell($productText)}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['min_order_quantity'])}
            {$listObj->getListDataCell($row['order_multiplier'])}
            {$listObj->getListDataCell($row['lead_time'])}
            {$listObj->getListDataCell($row['delivery_terms_supplier'])}
            {$listObj->getListDataCell($row['quantity'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['buy_unit_price'])}
            {$listObj->getListDataCell($row['shipping_method'])}
            {$listObj->getListDataCell($row['payment_terms'])}
            {$listObj->getListDataCell($row['notes_from_supplier_header'])}
            {$listObj->getListDataCell($row['notes_from_supplier'])}
            {$listObj->getListDataCell($row['valid_until'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['enquiry_product_id'])}
            ";

            $count++;
        }

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );
        $text = "
        <div id='rfqComparisonList'>
        {$listObj->getListHeader($numRows, $exp)}
        {$listObj->getListHeaderCell('RFQ Line #', 'qr.quote_request_line_no')}
        {$listObj->getListHeaderCell('Select for<br>this Enquiry')}
        {$listObj->getListHeaderCell('Item Number', 'p.product_code')}
        {$listObj->getListHeaderCell('Item Name', 'p.title')}
        {$listObj->getListHeaderCell('Supplier', 'supplier_name')}
        {$listObj->getListHeaderCell('UOM', 'p.unit')}
        {$listObj->getListHeaderCell('Min Order Qty', 'qri.min_order_quantity')}
        {$listObj->getListHeaderCell('Order Multiplier', 'qri.order_multiplier')}
        {$listObj->getListHeaderCell('Lead Time', 'qri.lead_time')}
        {$listObj->getListHeaderCell('Delivery Terms', 'qri.delivery_terms')}
        {$listObj->getListHeaderCell('Quantity', 'qri.quantity')}
        {$listObj->getListHeaderCell('Buy Currency', 'qri.buy_currency')}
        {$listObj->getListHeaderCell('Unit Buy Price', 'qri.buy_unit_price')}
        {$listObj->getListHeaderCell('Shipping Method', 'qr.shipping_method')}
        {$listObj->getListHeaderCell('Payment Terms', 'qr.payment_terms')}
        {$listObj->getListHeaderCell('Note from Supplier (Header)')}
        {$listObj->getListHeaderCell('Note from Supplier (Line)')}
        {$listObj->getListHeaderCell('Valid Until', 'qr.valid_until')}
        {$listObj->getListHeaderCell('RFQ Line Status')}
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
    function getRfqComparisonValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ids        = $fn->getReqParam('ids', array());
        $enquiry_id = $fn->getReqParam('enquiry_id');

        $status = 'success';
        $errMsg = '';

        return array($status, $errMsg);

    }

    /**
     *
     */
    function getRfqComparisonSave() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        list($status, $errMsg) = $this->getRfqComparisonValidate();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $ids        = $fn->getReqParam('ids', array());
        $enquiry_id = $fn->getReqParam('enquiry_id');

        $enquiry_product_ids     = array();
        $quote_request_items_ids = array();
        list($enquiry_product_ids, $quote_request_items_ids) = $cpUtil->getSplitValuesFromArray($ids);

        $SQL = "
        SELECT DISTINCT
               qri.*
              ,qr.buy_currency
        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        WHERE qr.enquiry_id = {$enquiry_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            //check the current qr id is selected (checked)
            if (in_array($row['quote_request_items_id'], $quote_request_items_ids)) {
                if (!$row['selected']) {
                    //------------------------//
                    //create the link
                    $fa = array();
                    $fa['selected'] = 1;
                    $whereCondition = "WHERE quote_request_items_id = {$row['quote_request_items_id']}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote_request_items', $whereCondition);
                    $db->sql_query($SQL);
                }
            } else {
                if ($row['selected']) {
                    //remove the link
                    $SQL = "
                    UPDATE quote_request_items
                    SET selected = 0
                    WHERE quote_request_items_id = {$row['quote_request_items_id']}
                    ";
                    $db->sql_query($SQL);
                }
            }
        }

        $arr = array('status' => 'success');
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getRfqComparisonSave_bak() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ids        = $fn->getReqParam('ids', array());
        $enquiry_id = $fn->getReqParam('enquiry_id');

        $enquiry_product_ids     = array();
        $quote_request_items_ids = array();
        foreach ($ids as $val) {
            list($enquiry_product_id, $quote_request_items_id) = explode('-', $val);
            $enquiry_product_ids[]     = $enquiry_product_id;
            $quote_request_items_ids[] = $quote_request_items_id;
        }

        //create RFQ line items
        $SQL = "
        SELECT ep.*
        FROM enquiry_product ep
        WHERE ep.enquiry_id = {$enquiry_id}
        ";
        $SQL = "
        SELECT DISTINCT
               qri.*
              ,qr.quote_request_code
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,qr.company_id_supplier
              ,c.company_name AS supplier_name
              ,ep.choose_for_quote
        FROM enquiry_product ep
        JOIN quote_request_items qri ON (qri.enquiry_product_id = ep.enquiry_product_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        JOIN product p               ON (p.product_id = ep.product_id)
        LEFT JOIN company c          ON (c.company_id = qr.company_id_supplier)
        WHERE ep.enquiry_id = {$enquiry_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            //if enquiry_product_id
            if (in_array($row['quote_request_items_id'], $quote_request_items_ids)) {
                if (!$row['choose_for_quote']) {

                    //------------------------//
                    //create the link
                    $fa = array();
                    $fa['selected'] = 1;
                    $whereCondition = "WHERE quote_request_items_id = {$row['quote_request_items_id']}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote_request_items', $whereCondition);
                    $db->sql_query($SQL);

                    //------------------------//
                    $fa = array();
                    $fa['choose_for_quote'] = 1;

                    $whereCondition = "WHERE enquiry_product_id = {$row['enquiry_product_id']}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'enquiry_product', $whereCondition);
                    $db->sql_query($SQL);
                }
            } else {
                if ($row['choose_for_quote']) {
                    //remove the link
                    $SQL = "
                    UPDATE quote_request_items
                    SET selected = 0
                    WHERE quote_request_items_id = {$row['quote_request_items_id']}
                    ";
                    $db->sql_query($SQL);

                    //--------------------------------//
                    $SQL = "
                    UPDATE enquiry_product
                    SET choose_for_quote = 0
                    WHERE enquiry_product_id = {$row['enquiry_product_id']}
                    ";
                    $db->sql_query($SQL);
                }
            }
        }

    }



    /**
     *
     */
    function getQuickSearch() {
        $text = '';


        return $text;
    }
}
