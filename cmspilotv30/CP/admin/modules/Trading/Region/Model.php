<?
class CP_Admin_Modules_Trading_Region_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT r.*
        FROM region r
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "r.region_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                       r.region_code LIKE '%{$tv['keyword']}%'
                    OR r.region_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $region_code = $fn->getReqParam('region_code');

        $SQL = "
        SELECT COUNT(*) AS count
        FROM region r
        WHERE r.region_code   = '{$region_code}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $validate->errorArray['region_code']['name'] = 'region_code';
            $validate->errorArray['region_code']['msg']   = 'Duplicate region code.';
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
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $region_id = $fn->getReqParam('region_id');
        $rowRegion = $fn->getRecordRowByID('region', 'region_id', $region_id);

        $region_code = $fn->getReqParam('region_code');

        if ($rowRegion['region_code'] != $region_code) {
            $SQL = "
            SELECT COUNT(*) AS count
            FROM region r
            WHERE r.region_code   = '{$currency_to}'
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            if ($row['count'] > 0) {
                $validate->errorArray['region_code']['name'] = 'region_code';
                $validate->errorArray['region_code']['msg']  = 'Duplicate region code.';
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'region_code');
        $fa = $fn->addToFieldsArray($fa, 'region_name');
        $fa = $fn->addToFieldsArray($fa, 'agent_name');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

}
