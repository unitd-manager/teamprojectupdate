<?
class CP_Common_Modules_Ecommerce_Division_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT d.*
        FROM division d
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
        $searchVar->mainTableAlias = 'd';

        $division_id   = $fn->getReqParam('division_id');

        if ($division_id != '') {
            $searchVar->sqlSearchVar[] = "d.division_id = {$division_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "d.division_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'd.division_id');
            $fn->setSearchVarForSpecialSearch($searchVar, 'd');
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    d.title LIKE '%{$tv['keyword']}%' OR
                    d.email LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
