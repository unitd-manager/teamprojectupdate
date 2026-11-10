<?
class CP_Www_Modules_EdukiteWeb_Task_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT n.*
        FROM notice n
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'n';

        if($_SESSION['cpStatus'] == 'Active'){
            $searchVar->sqlSearchVar[] = "n.status = 'Active'";
        } else {
            $searchVar->sqlSearchVar[] = "n.status = 'Archive'";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "n.notice_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'n.notice_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   n.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getUploadTaskSubmit() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $notice_id   = $fn->getPostParam('notice_id');
        $student_id  = $fn->getPostParam('student_id');
        $task_student_id  = $fn->getPostParam('task_student_id');
        $student_comment = $fn->getPostParam('student_comment');
        $links = $fn->getPostParam('links');

        if (!$this->getUploadTaskValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = array();
        $fa['student_comment']   = $student_comment;
        $fa['task_id']    = $notice_id;
        $fa['student_id'] = $student_id;
        $fa['links'] = $links;
        $fa['creation_date']= date('Y-m-d h:i:s');

        $whereCondition = "WHERE task_student_id = '{$task_student_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "task_student", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUploadTaskValidate() {
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
    function getAddCommentSubmit() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $teacherKite     = $fn->getPostParam('teacherKite');
        $notice_id       = $fn->getPostParam('notice_id');
        $student_id      = $fn->getPostParam('student_id');
        $task_student_id = $fn->getPostParam('task_student_id');
        $comments        = $fn->getPostParam('comments');
        $commentsToClass = $fn->getPostParam('studentPost');

        $teacher_id = '';
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
            $teacher_id  = $_SESSION['cpContactId'];
        }

        if (!$this->getAddCommentValidate()){
            return $validate->getErrorMessageXML();
        }

        $SQLCT="
        SELECT max(comments_tag) AS comments_tag
        FROM student_comment_history
        ";
        $resultCT = $db->sql_query($SQLCT);
        $rowCT = $db->sql_fetchrow($resultCT);
        if($rowCT['comments_tag'] > 0){
            $comment_tag = $rowCT['comments_tag'] + 1;
        } else {
            $comment_tag = 1;
        }

        if($teacherKite == 1 || $commentsToClass == 1){
            $sqlNoticeStudent = "
            SELECT ns.*
            FROM notice_student ns
            WHERE ns.notice_id = {$notice_id}
            GROUP BY ns.student_id
            ";
            $result = $db->sql_query($sqlNoticeStudent);

            while ($row = $db->sql_fetchrow($result)) {
                $taskStudentRec = $fn->getRecordByCondition('task_student', "student_id = '{$row['student_id']}' AND task_id = '{$notice_id}'");

                if($taskStudentRec['task_student_id'] != ''){
                    $fa = array();
                    $fa['student_id']      = $row['student_id'];
                    $fa['comments']        = $comments;
                    $fa['record_id']       = $notice_id;
                    $fa['teacher_id']      = $teacher_id;
                    $fa['task_student_id'] = $taskStudentRec['task_student_id'];
                    $fa['record_type']     = $_SESSION['cpLoginTypeWWW'];
                    $fa['creation_date']   = date('Y-m-d h:i:s');
                    $fa['comment_date']    = date('Y-m-d h:i:s');
                    $fa['comments_tag']    = $comment_tag;
                    $fa['source_id']       = $_SESSION['cpContactId'];

                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'student_comment_history');
                    $db->sql_query($SQL);
                }
            }
        } else {
            $fa = array();
            $fa['student_id']      = $student_id;
            $fa['comments']        = $comments;
            $fa['record_id']       = $notice_id;
            $fa['teacher_id']      = $teacher_id;
            $fa['task_student_id'] = $task_student_id;
            $fa['record_type']     = $_SESSION['cpLoginTypeWWW'];
            $fa['creation_date']   = date('Y-m-d h:i:s');
            $fa['comment_date']    = date('Y-m-d h:i:s');
            $fa['comments_tag']    = $comment_tag;
            $fa['source_id']       = $_SESSION['cpContactId'];

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'student_comment_history');
            $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddCommentValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('comments', 'Please enter the comment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
