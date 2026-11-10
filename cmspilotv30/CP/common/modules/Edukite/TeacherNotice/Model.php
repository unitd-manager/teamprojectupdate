<?
class CP_Common_Modules_Edukite_TeacherNotice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT tn.*
        FROM teacher_notice tn
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
        $searchVar->mainTableAlias = 'tn';

        //$searchVar->sqlSearchVar[] = "tn.academic_year = {$cpCfg['m.edukite.current_academic_year']}";

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "tn.teacher_notice_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'tn.teacher_notice_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   tn.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
