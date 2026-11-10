<?
class CP_Common_Modules_Directory_Street_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT s.*
        	  ,c.title AS country_title
        	  ,st.title AS state_title
        	  ,ci.title AS city_title
        	  ,a.title AS area_title
        	  ,b.title AS borough_title
        FROM street s
        LEFT JOIN country c ON s.country_id = c.country_id
        LEFT JOIN state st ON s.state_id = st.state_id
        LEFT JOIN city ci ON s.city_id = ci.city_id
        LEFT JOIN area a ON s.area_id = a.area_id
        LEFT JOIN borough b ON a.borough_id = b.borough_id
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
        $searchVar->mainTableAlias = 's';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');
        $area_id = $fn->getReqParam('area_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "s.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.street_id = {$tv['record_id']}";
        }
		
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.country_id = '{$country_id}'";
        }

        if ($state_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.state_id = '{$state_id}'";
        }

        if ($city_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.city_id = '{$city_id}'";
        }

        if ($area_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.area_id = '{$area_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( s.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }
        
		$searchVar->sortOrder = "s.title";
    }
}
