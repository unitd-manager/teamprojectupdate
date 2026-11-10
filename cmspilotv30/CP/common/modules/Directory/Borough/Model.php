<?
class CP_Common_Modules_Directory_Borough_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT b.*
        	  ,c.title AS country_title
        	  ,s.title AS state_title
        	  ,ci.title AS city_title
        FROM borough b
        LEFT JOIN (country c) ON (b.country_id = c.country_id)
        LEFT JOIN (state s) ON (b.state_id = s.state_id)
        LEFT JOIN (city ci) ON (b.city_id = ci.city_id)
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
        $searchVar->mainTableAlias = 'b';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.borough_id = {$tv['record_id']}";
        }
		
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "b.country_id = '{$country_id}'";
        }

        if ($state_id != '' ) {
            $searchVar->sqlSearchVar[] = "b.state_id = '{$state_id}'";
        }

        if ($city_id != '' ) {
            $searchVar->sqlSearchVar[] = "b.city_id = '{$city_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(b.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }

		$searchVar->sortOrder = "b.title";
    }
}
