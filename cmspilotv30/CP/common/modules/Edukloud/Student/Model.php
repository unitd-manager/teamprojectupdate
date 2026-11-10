<?
class CP_Common_Modules_Edukloud_Student_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {


        $SQL = "
        SELECT s.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS contact_name
        FROM student s
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
        $searchVar->mainTableAlias = 's';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.student_id');
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.first_name LIKE '%{$tv['keyword']}%'
            OR s.last_name LIKE '%{$tv['keyword']}%'
            )";
        }
    }
}
