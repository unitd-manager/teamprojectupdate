<?
class CP_Admin_Modules_AceIms_Class_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT c.*
        FROM class c
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

        $class_id = $fn->getReqParam('class_id');
        $status   = $fn->getReqParam('status');

        if ($class_id != "") {
            $searchVar->sqlSearchVar[] = "c.class_id = '{$class_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.class_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.class_id');

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.title       LIKE '%{$tv['keyword']}%'
                    OR c.description LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('title', 'Please enter the class name');

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
        return $this->getNewValidate();
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

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'class_id');
        
        return $fa;
    }

    /**
     *
     */
    function getAceImsClassAceImsContactLinkSQL($id) {
        return $SQL = "
        SELECT sc.student_class_id
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        FROM student_class sc
            ,contact c
        WHERE sc.contact_id = c.contact_id
          AND sc.class_id = $id
        ORDER BY contact_name
        ";
    }

    /**
     *
     */
    function getCourseByProgramGroupJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $program_group_id   = $fn->getReqParam('program_group_id');

        $json  = array();
        
        if ($program_group_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT course_id
              ,title
        FROM course 
        WHERE program_group_id = '{$program_group_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['course_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }
}
