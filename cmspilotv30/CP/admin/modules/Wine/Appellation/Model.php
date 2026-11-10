<?
class CP_Admin_Modules_Wine_Appellation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL   = "
        SELECT a.*
              ,gc.name AS country_name
              ,r.title AS region_title
        FROM appellation a
        LEFT JOIN (geo_country gc) ON (a.country_code = gc.country_code)
        LEFT JOIN (region r) ON (a.region_id = r.region_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'a';

        $country_code = $fn->getReqParam('country_code', '', true);
        $region_id = $fn->getReqParam('region_id', '', true);

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.appellation_id = {$tv['record_id']}";

        } else {
            if ($country_code != '') {
                $searchVar->sqlSearchVar[] = "a.country_code = '{$country_code}'";
            }

            if ($region_id != '') {
                $searchVar->sqlSearchVar[] = "a.region_id = '{$region_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    gc.country_code LIKE '%{$tv['keyword']}%'
                    OR r.title LIKE '%{$tv['keyword']}%'  
                    OR a.title LIKE '%{$tv['keyword']}%'  
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

        $validate->validateData('country_code', 'Please choose the country');

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

        $validate->resetErrorArray();

        $validate->validateData('country_code', 'Please choose the country');
        $validate->validateData('region_id', 'Please choose the region');
        $validate->validateData('title', 'Please enter the appellation');

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
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'region_id');
        $fa = $fn->addToFieldsArray($fa, 'description');

        return $fa;
    }
}
