<?
class CP_Common_Modules_Ecommerce_Stockist_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT s.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS contact_name
              ,gc.name AS country_name
        FROM stockist s
        LEFT JOIN geo_country gc ON (s.address_country_code = gc.country_code)
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
        $searchVar->mainTableAlias = 's';

        $stockist_id   = $fn->getReqParam('stockist_id');
        $stockist_type = $fn->getReqParam('stockist_type');
        $country       = $fn->getReqParam('country');

        if ($stockist_id != '') {
            $searchVar->sqlSearchVar[] = "s.stockist_id = {$stockist_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.stockist_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.stockist_id');
            $fn->setSearchVarForSpecialSearch($searchVar, 's');
    
            if ($stockist_type != "") {
                $searchVar->sqlSearchVar[] = "s.stockist_type   = '{$stockist_type}'";
            }

            if ($country != '' ) {
                $searchVar->sqlSearchVar[] = "s.address_country_code = '{$country}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.first_name LIKE '%{$tv['keyword']}%' OR
                    s.last_name  LIKE '%{$tv['keyword']}%' OR
                    s.company_name  LIKE '%{$tv['keyword']}%' OR
                    s.email LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
