<?
class CP_Admin_Modules_Pos_Shop_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT s.*
        FROM shop s
        ";
        
        return $SQL;
    }

    function getShopCodeSQL() {
        $SQL = "
        SELECT code 
        FROM shop
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
        $searchVar->mainTableAlias = 's';


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.shop_id = {$tv['record_id']}";

        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.shop_id');

            if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                s.code LIKE '%{$tv['keyword']}%'
                OR s.title  LIKE '%{$tv['keyword']}%'
            )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the Shop Name');

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
            $validate->validateData('title', 'Please enter the Shop Name');
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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'code', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'address', '', true);
        $fa = $fn->addToFieldsArray($fa, 'telephone', '', true);
        $fa = $fn->addToFieldsArray($fa, 'notes', '', true);
        $fa = $fn->addToFieldsArray($fa, 'currency', '', true);
        $fa = $fn->addToFieldsArray($fa, 'status', '', true);
        $fa = $fn->addToFieldsArray($fa, 'company_code', '', true);
        $fa = $fn->addToFieldsArray($fa, 'print_company_logo', '', true);
        $fa = $fn->addToFieldsArray($fa, 'print_shop_logo', '', true);
        $fa = $fn->addToFieldsArray($fa, 'print_shop_add_tele', '', true);
        $fa = $fn->addToFieldsArray($fa, 'print_invoice_remark', '', true);
        $fa = $fn->addToFieldsArray($fa, 'currency_sign', '', true);

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        return $fa;
    }

}
