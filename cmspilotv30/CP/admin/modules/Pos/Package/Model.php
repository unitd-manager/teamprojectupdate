<?
class CP_Admin_Modules_Pos_Package_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT p.*
        FROM package p
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';


       
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.package_id = {$tv['record_id']}";

        }   
    
       if ($tv['keyword'] != "") {
           $searchVar->sqlSearchVar[] = "(
           p.code LIKE '%{$tv['keyword']}%'
           OR p.title  LIKE '%{$tv['keyword']}%'
           )";
       }        

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the package name');

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
            $validate->validateData('title', 'Please enter the package name');
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

        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'unit_price');
        $fa = $fn->addToFieldsArray($fa, 'cost');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'uom_code');
        $fa = $fn->addToFieldsArray($fa, 'unit_price_currency');
        $fa = $fn->addToFieldsArray($fa, 'cost_currency');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'allow_discount');
        $fa = $fn->addToFieldsArray($fa, 'allow_member_discount');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_from');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_to');

        return $fa;
    }

}
