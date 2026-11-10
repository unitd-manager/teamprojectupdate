<?
class CP_Common_Modules_Ecommerce_Distributor_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT dr.*
              ,d.title AS division_title
        FROM distributor dr
        LEFT JOIN (division d) ON (dr.division_id = d.division_id)
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
        $searchVar->mainTableAlias = 'dr';

        $distributor_id   = $fn->getReqParam('distributor_id');
        $division_id      = $fn->getReqParam('division_id');

        if ($distributor_id != '') {
            $searchVar->sqlSearchVar[] = "dr.distributor_id = {$distributor_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "dr.distributor_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'dr.distributor_id');
            $fn->setSearchVarForSpecialSearch($searchVar, 'dr');
    
            if ($division_id != '') {
                $searchVar->sqlSearchVar[] = "d.division_id = {$division_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    dr.title LIKE '%{$tv['keyword']}%' OR
                    d.title LIKE '%{$tv['keyword']}%' OR
                    dr.email LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
