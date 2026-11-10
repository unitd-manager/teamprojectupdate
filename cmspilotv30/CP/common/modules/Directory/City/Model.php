<?
class CP_Common_Modules_Directory_City_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT c.*
        	  ,a.title AS area_name
        FROM city c
        LEFT JOIN (area a) ON (c.area_id = a.area_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'c';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.city_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.city_id');
            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "c.country_id = '{$country_id}'";
            }

            if ($state_id != '' ) {
                $searchVar->sqlSearchVar[] = "c.state_id = '{$state_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title   LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "c.title";
    }
}
