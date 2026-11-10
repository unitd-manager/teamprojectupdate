<?
class CPL_Admin_Widgets_EnggCrm_ProjectDuctDelivery_Model extends CP_Common_Lib_WidgetModelAbstract
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
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectQuote');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getCreateDeliveryOrder() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $deliveryOrderId  = $fn->getReqParam('deliveryOrderChecked', array());
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $project_id = $fn->getReqParam('project_id');
        $projectRow   = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $company_id = $projectRow['company_id'];

        $fa = array();
        $fa['project_id'] = $project_id;
        $fa['purchase_order_id']   = $purchase_order_id;
        $fa['company_id']   = $company_id;
        $fa['date']      = date("Y-m-d");
        $fa['creation_date']   = date("Y-m-d H:i:s");
        $fa['created_by']      = $fn->getSessionParam('userName');

        $SQLInsert         = $dbUtil->getInsertSQLStringFromArray($fa, 'delivery_order');
        $resultInsert      = $db->sql_query($SQLInsert);
        $delivery_order_id = $db->sql_nextid();
        foreach($deliveryOrderId AS $po_product_id){
            if($po_product_id != ''){
                $rowPoItem   = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
                $fadoh = array();
                $fadoh['product_id'] = $rowPoItem['product_id'];
                $fadoh['purchase_order_id']   = $rowPoItem['purchase_order_id'];
                $fadoh['delivery_order_id']   = $delivery_order_id;
                $fadoh['status']      = 'In Progress';
                $fadoh['quantity']      = $rowPoItem['qty'];
                $fadoh['creation_date']   = date("Y-m-d H:i:s");

                $SQLInsertdoh         = $dbUtil->getInsertSQLStringFromArray($fadoh, 'delivery_order_history');
                $resultInsertdoh      = $db->sql_query($SQLInsertdoh);
            }
        }

    }

    /**
     *
     */
    function getEditForDOValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');


        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Line Item Edit Form Submit
     */
    function getEditForDOSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $quantity_arr    = $fn->getPostParam('quantity', array());
        $do_status_arr    = $fn->getPostParam('do_status', array());
        $remarks_arr    = $fn->getPostParam('remarks', array());
        $delivery_order_history_id_arr    = $fn->getPostParam('delivery_order_history_id', array());

        if (!$this->getEditForDOValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($delivery_order_history_id_arr);
        $totalAmount = 0;
        for ($i= 0; $i < $count; $i++) {
            $quantity  = $quantity_arr[$i];
            $remarks  = $remarks_arr[$i];
            $delivery_order_history_id  = $delivery_order_history_id_arr[$i];
            $status  = $do_status_arr[$i];

            $fa = array();
            $fa['quantity'] = $quantity;
            $fa['status']   = $status;
            $fa['remarks']  = $remarks;
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'delivery_order_history');

            $whereCondition = "WHERE delivery_order_history_id = {$delivery_order_history_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "delivery_order_history", $whereCondition);
            $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();
    }
}