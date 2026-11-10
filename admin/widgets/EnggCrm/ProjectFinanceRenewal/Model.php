<?
class CPL_Admin_Widgets_EnggCrm_ProjectFinanceRenewal_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
    }

        /**
     * Generate order records from Project
     */
    function getGenerateOrderRecords($quote_id='', $renewal_id=''){
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');


        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }


        if($quote_id == "") {
            $quote_id = $fn->getReqParam('quote_id');
        }

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']      = 'Awarded';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by']       = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $quote_id);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']             = $quote_id;
        $faOrder['renewal_id']           = $renewal_id;
        $faOrder['company_id']           = $projRec['company_id'];
        $faOrder['contact_id']           = $projRec['contact_id'];
        $faOrder['project_type']         = $projRec['category'];
        $faOrder['quote_title']          = $quoteRec['title'];
        $faOrder['cust_company_name']    = $companyRow['company_name'];
        $faOrder['cust_address1']        = $companyRow['address_flat'];
        $faOrder['cust_address2']        = $companyRow['address_street'];
        $faOrder['cust_address_country'] = $companyRow['address_country'];
        $faOrder['cust_address_po_code'] = $companyRow['address_po_code'];
        $faOrder['cust_email']           = $companyRow['email'];
        $faOrder['cust_phone']           = $companyRow['phone'];
        $faOrder['cust_fax']             = $companyRow['fax'];
        $faOrder['record_type']          = $projRec['category'];

        if ($companyRow['address_flat'] != '') {
            $faOrder['shipping_first_name']      = $companyRow['company_name'];
            $faOrder['shipping_address1']        = $companyRow['address_flat'];
            $faOrder['shipping_address2']        = $companyRow['address_street'];
            $faOrder['shipping_address_country'] = $companyRow['address_country'];
            $faOrder['shipping_address_po_code'] = $companyRow['address_po_code'];
            $faOrder['shipping_email']           = $companyRow['email'];
            $faOrder['shipping_phone']           = $companyRow['phone'];
            $faOrder['shipping_fax']             = $companyRow['fax'];
        } else {
            $faOrder['shipping_first_name']      = $companyRow['company_name'];
            $faOrder['shipping_address1']        = $companyRow['billing_address_flat'];
            $faOrder['shipping_address2']        = $companyRow['billing_address_street'];
            $faOrder['shipping_address_country'] = $companyRow['billing_address_country'];
            $faOrder['shipping_address_po_code'] = $companyRow['billing_address_po_code'];
            $faOrder['shipping_email']           = $companyRow['billing_email'];
            $faOrder['shipping_phone']           = $companyRow['billing_phone'];
            $faOrder['shipping_fax']             = $companyRow['billing_fax'];
        }

        $faOrder['creation_date']             = date('Y-m-d H:i:s');
        $faOrder['created_by']                = $fn->getSessionParam('userName');
        $faOrder['order_status']              = 'New';
        $faOrder['order_date']                = date('Y-m-d');

        if ($projRec['category'] == 'Maintenance') {
            $faOrder['start_date']            = $projRec['start_date'];
            $faOrder['end_date']              = $projRec['estimated_finish_date'];
        }

        //check if the order record already exist or not
        $orderRec = $fn->getRecordByCondition('order', "renewal_id = '{$renewal_id}'");
        if(is_array($orderRec)){
            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($faOrder, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faOrder, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        /* Creation of Order Item record */
        $SQLSelect = "
        SELECT qi.*
        FROM quote_items qi
        WHERE qi.quote_id = '{$quote_id}'
        ORDER BY qi.quote_items_id ASC
        ";
        $resultSelect = $db->sql_query($SQLSelect);
        while ($row = $db->sql_fetchrow($resultSelect)) {
            $faOi = array();
            $faOi['part_no']          = $row['part_no'];
            $faOi['item_title']       = $row['title'];
            $faOi['qty']              = $row['quantity'];
            $faOi['unit']             = $row['unit'];
            $faOi['unit_price']       = $row['amount'];
            $faOi['description']      = $row['description'];
            $faOi['remarks']          = $row['remarks'];
            $faOi['record_id']        = $row['quote_items_id'];
            $faOi['order_id']         = $order_id;
            $faOi['quote_id']         = $quote_id;
            $faOi['drawing_number']   = $row['drawing_number'];
            $faOi['drawing_title']    = $row['drawing_title'];
            $faOi['drawing_revision'] = $row['drawing_revision'];

            $orderItemRec = $fn->getRecordByCondition('order_item', "record_id = '{$row['quote_items_id']}' AND order_id = {$order_id}");
            if(is_array($orderItemRec)){
                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($faOi, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $SQLOI = $dbUtil->getInsertSQLStringFromArray($faOi, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
        }
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectQuote');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

}