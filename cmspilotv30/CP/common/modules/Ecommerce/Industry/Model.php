<?
class CP_Common_Modules_Ecommerce_Industry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT i.*
              ,d.title AS division_title
        FROM industry i
        LEFT JOIN (division d) ON (i.division_id = d.division_id)
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
        $searchVar->mainTableAlias = 'i';

        $industry_id   = $fn->getReqParam('industry_id');
        $division_id      = $fn->getReqParam('division_id');

        if ($industry_id != '') {
            $searchVar->sqlSearchVar[] = "i.industry_id = {$industry_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.industry_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.industry_id');
            $fn->setSearchVarForSpecialSearch($searchVar, 'dr');
    
            if ($division_id != '') {
                $searchVar->sqlSearchVar[] = "d.division_id = {$division_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.title LIKE '%{$tv['keyword']}%' OR
                    d.title LIKE '%{$tv['keyword']}%' OR
                    i.email LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
