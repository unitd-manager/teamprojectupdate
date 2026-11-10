<?
class CP_Common_Modules_Edukite_YearGroup_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT yg.*
        FROM year_group yg
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
        $searchVar->mainTableAlias = 'yg';

        $searchVar->sqlSearchVar[] = "yg.status = 'Active'";

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "yg.year_group_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'yg.year_group_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   yg.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
