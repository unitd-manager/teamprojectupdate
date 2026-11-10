<?
class CP_Common_Modules_Edukite_Notice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN teacher t ON (t.teacher_id = n.teacher_id)
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

		$template   = $fn->getReqParam ('template');
		$subject_id = $fn->getReqParam ('subject_id');
        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        $teacher_id = $fn->getReqParam ('teacher_id');
        $notice_id   = $fn->getReqParam('notice_id');
        $status = $fn->getReqParam('status');

        if($status == 'Archive'){
            $searchVar->sqlSearchVar[] = "n.status = 'Archive'";
        } else {
            $searchVar->sqlSearchVar[] = "n.status = 'Active'";
        }

        if ($notice_id != "") {
            $searchVar->sqlSearchVar[] = "n.notice_id = '{$notice_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "n.notice_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'n.notice_id');

            if ($template != '') {
                $searchVar->sqlSearchVar[] = "n.template = '{$template}'";
            }

            if ($subject_id != '') {
                $searchVar->sqlSearchVar[] = "n.subject_id = '{$subject_id}'";
            }

            if ($teacher_id != '') {
                $searchVar->sqlSearchVar[] = "n.teacher_id = '{$teacher_id}'";
            }

            if ($teacherRec['role'] == 'Teacher') {
                $searchVar->sqlSearchVar[] = "n.teacher_id = '{$_SESSION['cpContactId']}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   n.title LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "n.notice_id DESC";
        }
    }

}
