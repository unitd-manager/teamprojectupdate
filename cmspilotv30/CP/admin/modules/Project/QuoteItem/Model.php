<?
class CP_Admin_Modules_Project_QuoteItem_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $valArr   = $this->getNewValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            return $xmlText;
        }

        $category_id  = $fn->getPostParam('category_id') ;

        if ($category_id == ''){
            return;
        }

        $fa = array();
        $fa['quote_category_id']    = $category_id;
        $fa['quote_id']             = $fn->getPostParam('quote_id') ;
        $fa['title']                = $fn->getPostParam('title');
        $fa['item_type']            = $fn->getPostParam('item_type');
        $fa['amount']               = $fn->getPostParam('amount');
        $fa['amount_other']         = $fn->getPostParam('amount_other');
        $fa['description']          = $fn->getPostParam('description');
        $fa['sort_order']           = $fn->getPostParam('sort_order');
        $fa['creation_date']        = date("Y-m-d H:i:s");

        $SQL                        = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
        $result                     = $db->sql_query($SQL);
        $quote_items_id             = $db->sql_nextid();

        $mQuote = getCPModuleObj('project_quote');
        $mQuote->fns->refreshValuesBasedOnConfirmedQuote($fa['quote_id']);

        return $xmlText;

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $item_type = $fn->getPostParam('item_type');

        if ($item_type != 'text' && $item_type != 'blank' ){
            $validate->validateData('amount', 'Please enter the amount');
        }

        if ($item_type != 'blank' ){
            $validate->validateData('title', 'Please enter the title');
        }
        
        $validate->validateData('item_type' , 'Please select type');
        $validate->validateData('sort_order', 'Please enter sort order');

        if (count($validate->errorArray) == 0){
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }

    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $valArr   = $this->getEditValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            return $xmlText;
        }

        $quote_items_id = $fn->getPostParam('quote_items_id') ;
        if (trim($quote_items_id) == ''){
            return;
        }

        $fa = array();
        $fa['title']             = $fn->getPostParam('title');
        $fa['amount']            = $fn->getPostParam('amount');
        $fa['amount_other']      = $fn->getPostParam('amount_other');
        $fa['actual_amount']     = $fn->getPostParam('actual_amount');
        $fa['supplier_amount']   = $fn->getPostParam('supplier_amount');
        $fa['sort_order']        = $fn->getPostParam('sort_order');
        $fa['description']       = $fn->getPostParam('description');
        $fa['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE quote_items_id = {$quote_items_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote_items', $whereCondition);
        $result = $db->sql_query($SQL);

        $quoteItem = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);

        $fnMod = includeCPClass('ModuleFns', 'project_quote');
        $fnMod->refreshValuesBasedOnConfirmedQuote($quoteItem['quote_id']);

        //*********************************************************//

        return $xmlText;
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title' , 'Please enter the title');
        $validate->validateData('amount' , 'Please enter the amount');
        $validate->validateData('sort_order' , 'Please enter sort order');

        if (count($validate->errorArray) == 0){
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }

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
}