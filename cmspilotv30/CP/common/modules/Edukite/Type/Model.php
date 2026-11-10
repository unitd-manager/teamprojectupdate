<?
class CP_Common_Modules_Edukite_Type_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT nt.*
        FROM notice_type nt
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
        $searchVar->mainTableAlias = 'nt';

        //$searchVar->sqlSearchVar[] = "s.academic_year = {$cpCfg['m.edukite.current_academic_year']}";

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "nt.notice_type_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'nt.notice_type_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   nt.title LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "nt.title ASC";
        }
    }
}
