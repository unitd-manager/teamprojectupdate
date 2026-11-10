<?
class CP_Admin_Modules_Project_QuoteCategory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getAdd() {  
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
                              
        $valArr = $this->getNewValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];
                              
        if ($hasError){       
            return $xmlText;  
        }                     
                              
        $quote_id = $fn->getPostParam('quote_id') ;
                              
        $fa = array();        
        $fa['quote_id']         = $quote_id;
        $fa['valuelist_id']     = $fn->getPostParam('valuelist_id');
        $fa['category_type']    = $fn->getPostParam('category_type');
        $fa['sort_order']       = $fn->getPostParam('sort_order');
        $fa['creation_date']    = date("Y-m-d H:i:s");
        
        $SQL                    = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_category');
        $result                 = $db->sql_query($SQL);
        $quote_category_id      = $db->sql_nextid();
                              
        return $xmlText;      
    }                         
                              
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
                              
        $validate->resetErrorArray();
                              
        $validate->validateData('valuelist_id'      , 'Please select the category name');
        $validate->validateData('category_type'     , 'Please select the category type');
        $validate->validateData('sort_order'        , 'Please enter sort order');
                              
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
                              
        $valArr = $this->getNewValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];
                              
        if ($hasError){       
            return $xmlText;  
        }                     
                              
        $category_id    = $fn->getPostParam('category_id') ;
        $valuelist_id   = $fn->getPostParam('valuelist_id') ;
        $category_type  = $fn->getPostParam('category_type') ;
        $sort_order     = $fn->getPostParam('sort_order') ;

        $fa = array();        
        $fa['quote_category_id']    =  $category_id;
        $fa['valuelist_id']         =  $valuelist_id;
        $fa['category_type']        =  $category_type;
        $fa['sort_order']           =  $sort_order;
        $fa['modification_date']    =  date("Y-m-d H:i:s");
                              
        $whereCondition = "
        WHERE quote_category_id = {$category_id}
        ";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote_category', $whereCondition);
        $result         = $db->sql_query($SQL);
        $quote_id       = $db->sql_nextid();
                              
        //*********************************************************//

        return $xmlText;      
    }                         
    
    /**
     *
     */
    function getDelete() {
        $db = Zend_Registry::get('db');
                              
        $quote_category_id      = isset($_REQUEST['category_id']    )  ? $_REQUEST['category_id']   : '';
                              
        if ($quote_category_id > 0){   
            $SQL = "
            DELETE 
            FROM quote_category 
            WHERE quote_category_id = {$quote_category_id}
            ";
            $result = $db->sql_query($SQL);
            
            $SQL = "
            DELETE 
            FROM quote_items 
            WHERE quote_category_id = {$quote_category_id}
            ";
            $result     = $db->sql_query($SQL);
        }                     
    }                              
}