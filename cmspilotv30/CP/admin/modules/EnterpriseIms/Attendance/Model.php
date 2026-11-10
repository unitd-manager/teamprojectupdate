<?
class CP_Admin_Modules_EnterpriseIms_Attendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.*
              ,b.title AS batch_title
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        FROM attendance a
        LEFT JOIN (batch b) ON (a.batch_id = b.batch_id)
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
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
        $searchVar->mainTableAlias = 'a';

        $attendance_id   = $fn->getReqParam('attendance_id');

        if ($attendance_id != "") {
            $searchVar->sqlSearchVar[] = "a.attendance_id = '{$attendance_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.attendance_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.attendance_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
																c.first_name      LIKE '%{$tv['keyword']}%' OR
																c.last_name       LIKE '%{$tv['keyword']}%' OR
																b.title           LIKE '%{$tv['keyword']}%' 
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
        $validate->validateData('date', 'Please select the date');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        
        return $fa;
    }

}
