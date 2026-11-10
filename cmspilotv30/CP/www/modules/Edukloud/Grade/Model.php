<?
class CP_Www_Modules_Edukloud_Grade_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT er.*,
               c.title as class_title
        FROM exam_result er
        LEFT JOIN (class c)   ON (er.class_id = c.class_id)
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'er';

        $staff_id = $fn->getReqParam('staff_id');
        $current_date =  date('Y-m-d');
        $current_time =  date('H:i');

        //$searchVar->sqlSearchVar['published'] = "t.published = 1";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar['exam_result_id'] = "er.exam_result_id = '{$tv['record_id']}'";
        }
        
        if ($staff_id != '' ){
            $this->sqlSearchVar[] = "st.staff_id = '{$staff_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               c.title LIKE '%{$tv['keyword']}%' OR
               t.description LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    /**
     *
     */
    function getNewGradeSubmit() {
        checkLoggedIn();
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewGradeSubmitValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa2 = array();

        $class_id    = $fn->getReqParam('class_id');

        $SQLStudentClass = "
        SELECT sc.*
        FROM student_class sc
        WHERE sc.class_id = {$class_id}
        ";
        $resultStudentClass = $db->sql_query($SQLStudentClass);
        //-----------------------------------------------------------------------//
        
        $fa['class_id']    = $fn->getPostParam('class_id');
        $fa['term']        = $fn->getPostParam('term');
        $fa['staff_id']    = $fn->getPostParam('staff_id');
        $fa['creation_date'] = date('Y-m-d H:i:s');
        
        $SQL            = $dbUtil->getInsertSQLStringFromArray($fa, 'exam_result');
        $result         = $db->sql_query($SQL);
        $exam_result_id = $db->sql_nextid();
        
        while ($row = $db->sql_fetchrow($resultStudentClass)){
            $fa2['staff_id']       = $fn->getPostParam('staff_id');
            $fa2['student_id']     = $row['student_id'];
            $fa2['exam_result_id'] = $exam_result_id;
            $fa2['subject_id']     = $fn->getPostParam('subject_id');;
            $fa2['creation_date']  = date('Y-m-d H:i:s');
    
            $SQL            = $dbUtil->getInsertSQLStringFromArray($fa2, 'exam_result_history');
            $result         = $db->sql_query($SQL);
            $exam_result_history_id = $db->sql_nextid();
        }

        //$editUrl = $cpUrl->getUrlByCatType('Grade') . '?_action=editGrade&exam_result_id=' . $exam_result_id&class_id='{$class_id}';
        $editUrl = "/index.php?module=edukloud_grade&_action=editGrade&exam_result_id={$exam_result_id}&class_id={$class_id}";
        
        $xmlText = $validate->getSuccessMessageXML($editUrl);

        return $xmlText;
    }

    /**
     *
     */
    function getNewGradeSubmitValidate() {
        checkLoggedIn();
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('class_id', $ln->gd('classError'));
        $validate->validateData('term', $ln->gd('termError'));
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditGradeSubmit() {
        checkLoggedIn();
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getEditGradeValidate()){
            return $validate->getErrorMessageXML();
        }
        //-----------------------------------------------------------------------//

        $grade     = $fn->getReqParam('grade');
        //$class_id  = $fn->getReqParam('class_id');
        $exam_result_history_id  = $fn->getReqParam('exam_result_history_id');
        $exam_result_id  = $fn->getReqParam('exam_result_id');
        //-----------------------------------------------------------------------//

        $fa = array();
        $fa['grade']           = $grade;
        $fa['modification_date'] = date('Y-m-d H:i:s');


        $whereCondition = "WHERE exam_result_history_id = {$exam_result_history_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'exam_result_history', $whereCondition);
        $result = $db->sql_query($SQL);

        //$editUrl = $cpUtil->redirect("index.php?&module={$tv['module']}&_action=list&exam_result_id={$exam_result_id}");
        //$cpUtil->redirect("/index.php?module=edukloud_grade&_spAction=list&showHTML=0");
        //$editUrl = $cpUrl->getUrlWithAction('list');
        $editUrl = $cpUrl->getUrlByCatType('Grade');
        $xmlText = $validate->getSuccessMessageXML($editUrl);

        return $xmlText;
    }

    /**
     *
     */
    function getEditGradeValidate() {
        checkLoggedIn();
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('grade', $ln->gd('gradeError'));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEdukloudTaskEdukloudStudentLinkSQL($id) {

        $SQL = "
        SELECT ts.task_student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS name
              ,c.title
              ,ts.star_rating
        FROM task_student ts
        LEFT JOIN student s ON (s.student_id = ts.student_id)
        LEFT JOIN class c ON (c.class_id = s.class_id)
        WHERE ts.task_id = {$id}
        ";

        return $SQL;
    }

}
