<?
class CP_Www_Modules_Edukite_Class_Model extends CP_Common_Modules_Edukite_Class_Model
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
        //$fa['academic_year'] = date('Y');
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

        $class_id    = $fn->getReqParam('class_id');
        $student_id  = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM class_student
        WHERE student_id = {$student_id}
            AND class_id = {$class_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_class');
        $text    = $viewObj->getRightPanelDefaultContent($class_id);

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

        $class_id   = $fn->getReqParam('class_id');

        $deleteSQL     = "
        DELETE FROM class_student
        WHERE class_id = {$class_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_class');
        $text    = $viewObj->getRightPanelDefaultContent($class_id);

        return $text;
    }

}
