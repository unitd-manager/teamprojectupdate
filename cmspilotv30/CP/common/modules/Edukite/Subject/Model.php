<?
class CP_Common_Modules_Edukite_Subject_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
        FROM subject s
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
        $searchVar->mainTableAlias = 's';

        //$searchVar->sqlSearchVar[] = "s.academic_year = {$cpCfg['m.edukite.current_academic_year']}";

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.subject_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.subject_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   s.title LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "s.title ASC";
        }
    }
}
