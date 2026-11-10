<?
class CP_Www_Modules_Edukite_YearGroup_Model extends CP_Common_Modules_Edukite_YearGroup_Model
{
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
    function getFields() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
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
        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getDeleteLinkedStudents() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $year_group_id = $fn->getReqParam('year_group_id');
        $student_id    = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM student_year_group
        WHERE student_id = {$student_id}
            AND year_group_id = {$year_group_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_yearGroup');
        $text    = $viewObj->getRightPanelDefaultContent($year_group_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteAllLinkedStudents() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $year_group_id   = $fn->getReqParam('year_group_id');

        $deleteSQL     = "
        DELETE FROM student_year_group
        WHERE year_group_id = {$year_group_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_yearGroup');
        $text    = $viewObj->getRightPanelDefaultContent($year_group_id);

        return $text;
    }
}
