<?
class CP_Admin_Modules_Ecommerce_WishList_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT w.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,p.title AS product_name
        FROM wish_list w
        LEFT JOIN (contact c) ON (c.contact_id = w.contact_id)
        LEFT JOIN (product p) ON (p.product_id = w.product_id)
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
            
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "w.wish_list_id = '{$tv['record_id']}'";
        }

        if ($tv['keyword'] != '') {
            $searchVar->sqlSearchVar[] = "(
            )";
        }
        
        $searchVar->sortOrder = 'w.wish_list_id';

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('wish_list_name', 'Please enter the name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('wish_list_name', 'Please enter the title');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'wish_list_name');
        $fa = $fn->addToFieldsArray($fa, 'product_id');
 
        return $fa;
    }
}
