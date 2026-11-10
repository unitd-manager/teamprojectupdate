<?
class CP_Admin_Modules_Pos_CompanyProfile_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT cp.*
        FROM company_profile cp
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
        $searchVar->mainTableAlias = 'cp';


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "cp.company_profile_id = {$tv['record_id']}";

        } 
                
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            cp.title LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the Company Name');

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
            $validate->validateData('title', 'Please enter the Company Name');
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

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'address', '', true);
        $fa = $fn->addToFieldsArray($fa, 'telephone', '', true);
        $fa = $fn->addToFieldsArray($fa, 'email', '', true);
        $fa = $fn->addToFieldsArray($fa, 'company_code', '', true);

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        return $fa;
    }

    /**
     *
     */
    function getCompanyCodeSQL() {
        $SQL = "
        SELECT company_code 
        FROM company_profile
        ORDER BY company_code
        ";
        
        return $SQL;
    }

}
