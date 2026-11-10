<?
class CPL_Admin_Modules_Payroll_Dormitory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL   = "
        SELECT d.*
              ,gc.name AS country_name
        FROM dormitory d
        LEFT JOIN geo_country gc ON (d.country = gc.country_code)
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

        $dormitory_id        = $fn->getReqParam('dormitory_id');

        if ($dormitory_id != "") {
            $searchVar->sqlSearchVar[] = "d.dormitory_id = '{$dormitory_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "d.dormitory_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'd.dormitory_id');

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       d.name  LIKE '%{$tv['keyword']}%'
                    OR d.phone       LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "d.name ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $name = $fn->getPostParam('name');

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter name');

        if ($name != ''){
            $rec = $fn->getRecordByCondition('dormitory', "name = '{$name}'");
            if (is_array($rec)){
                $validate->errorArray['name']['name'] = "name";
                $validate->errorArray['name']['msg']  = "Name already exists";
            }
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['country'] = 'SG';
        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('name', 'Please enter the name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        //$fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'country');
        $fa = $fn->addToFieldsArray($fa, 'postal_code');
        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'designation');

        return $fa;
    }
}
