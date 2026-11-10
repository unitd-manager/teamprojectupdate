<?
class CP_Www_Modules_Edukite_Achievement_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.*
        FROM achievement a
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'a';


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.achievement_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.achievement_id');

        }


        /*if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.first_name LIKE '%{$tv['keyword']}%'
            OR s.last_name LIKE '%{$tv['keyword']}%'
            )";
        }*/

        //$searchVar->sortOrder = "s.last_name";
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
    function getNewValidate() {
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
    function getClassList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $sqlClass = getCPModuleObj('edukite_class')->model->getSQL();
        $result = $db->sql_query($sqlClass);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td>
                <a href='#' class='classLinkArrow'>
                <img src='/cmspilotv30/CP/www/themes/{$cpCfg['cp.theme']}/images/arrow.jpg'>
                </a>
                </td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <table class='list'>{$rows}</table>
        </div>
        ";

        return $text;
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
    function getDeleteLinkedClasses() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $achievement_id = $fn->getReqParam('achievement_id');
        $class_id       = $fn->getReqParam('class_id');
        $notice_id      = $fn->getReqParam('notice_id');

        $deleteSQL     = "
        DELETE FROM achievement_student
        WHERE class_id = {$class_id}
            AND notice_id      = {$notice_id}
            AND achievement_id = {$achievement_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_achievement');
        $text    = $viewObj->getLinkedClassList($achievement_id, $notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $achievement_id = $fn->getReqParam('achievement_id');
        $year_group_id  = $fn->getReqParam('year_group_id');
        $notice_id      = $fn->getReqParam('notice_id');

        $deleteSQL     = "
        DELETE FROM achievement_student
        WHERE year_group_id = {$year_group_id}
            AND notice_id      = {$notice_id}
            AND achievement_id = {$achievement_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_achievement');
        $text    = $viewObj->getLinkedCohortList($achievement_id, $notice_id);

        return $text;
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

        $achievement_id  = $fn->getReqParam('achievement_id');
        $student_id      = $fn->getReqParam('student_id');
        $notice_id       = $fn->getReqParam('notice_id');

        $deleteSQL     = "
        DELETE FROM achievement_student
        WHERE student_id = {$student_id}
            AND achievement_id = {$achievement_id}
            AND notice_id      = {$notice_id}
            AND (class_id = '' OR class_id IS NULL)
            AND (year_group_id = '' OR  year_group_id IS NULL)
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_achievement');
        $text    = $viewObj->getLinkedStudentList($achievement_id, $notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedStudentsFromClass() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $achievement_student_id  = $fn->getReqParam('achievement_student_id');
        $student_id = $fn->getReqParam('student_id');
        $achievement_id = $fn->getReqParam('achievement_id');

        $deleteSQL = "
        DELETE FROM achievement_student
        WHERE achievement_student_id = {$achievement_student_id}
          AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_achievement');
        $text    = $viewObj->getLinkedClassList($achievement_id, $notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedStudentsFromCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $achievement_student_id  = $fn->getReqParam('achievement_student_id');
        $student_id = $fn->getReqParam('student_id');
        $achievement_id = $fn->getReqParam('achievement_id');

        $deleteSQL     = "
        DELETE FROM achievement_student
        WHERE achievement_student_id = {$achievement_student_id}
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_achievement');
        $text    = $viewObj->getLinkedCohortList($achievement_id, $notice_id);

        return $text;
    }
}
