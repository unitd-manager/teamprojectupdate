<?
class CP_Admin_Modules_Project_InvoiceItem_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['invoice_id']       = $fn->getPostParam('invoice_id') ;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('description' , 'Please enter the description');
        $validate->validateData('amount' , 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['modification_date']    = date("Y-m-d H:i:s");
        
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDelete() {
        $db = Zend_Registry::get('db');

        $quote_items_id      = isset($_REQUEST['item_id']    )  ? $_REQUEST['item_id']   : '';

        if ($quote_items_id > 0){
            $SQL = "
            DELETE 
            FROM quote_items 
            WHERE quote_items_id = {$quote_items_id}
            ";
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa['description']  = $fn->getPostParam('description');
        $fa['amount']       = $fn->getPostParam('amount');

        return $fa;
    }
}