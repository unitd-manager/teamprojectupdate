<?
class CP_Admin_Modules_Tuitionsg_ProgramGroup_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT pg.*
        FROM program_group pg
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
        $searchVar->mainTableAlias = 'pg';

        $program_group_id = $fn->getReqParam('program_group_id');

        if ($program_group_id != "") {
            $searchVar->sqlSearchVar[] = "pg.program_group_id = '{$program_group_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pg.program_group_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pg.program_group_id');

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       pg.title   LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "pg.program_group_id DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

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
        $validate->validateData('title', 'Please enter the title');

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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');

        return $fa;
    }

    /**
     *
     */
    function getProgramGroupSQL() {
        
        return "
        SELECT program_group_id
              ,title
        FROM program_group
        ";
    }

    /**
     *
     */
    function getTuitionsgProgramGroupTuitionsgSubsidyDiscountLinkSQL($id) {

        return "
        SELECT pgsd.program_group_subsidy_discount_id
              ,sd.title
        FROM program_group_subsidy_discount pgsd 
        LEFT JOIN subsidy_discount sd ON (sd.subsidy_discount_id = pgsd.subsidy_discount_id)
        WHERE pgsd.program_group_id = '{$id}'
        ORDER BY pgsd.program_group_subsidy_discount_id
        ";
    }
}