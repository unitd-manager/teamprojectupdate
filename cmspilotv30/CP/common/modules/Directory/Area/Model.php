<?
class CP_Common_Modules_Directory_Area_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT a.*
        	  ,c.name AS country_name
        FROM area a
        LEFT JOIN (geo_country c) ON (a.country_code = c.country_code)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'a';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.area_id = {$tv['record_id']}";
        }
		
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.country_id = '{$country_id}'";
        }

        if ($state_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.state_id = '{$state_id}'";
        }

        if ($city_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.city_id = '{$city_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( a.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }

		$searchVar->sortOrder = "a.title";
    }
}
