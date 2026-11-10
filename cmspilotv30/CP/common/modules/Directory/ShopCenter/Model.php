<?
class CP_Common_Modules_Directory_ShopCenter_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT sc.*
        	  ,c.title AS country_title
        	  ,s.title AS state_title
        	  ,ci.title AS city_title
        	  ,b.title AS borough_title
        	  ,a.title AS area_title
        	  ,st.title AS street_title
              ,(SELECT COUNT(*) 
                FROM business bs
                JOIN address ad ON ad.address_id = bs.business_id
                WHERE ad.shop_center_id = sc.shop_center_id) AS business_count              
        FROM shop_center sc
        LEFT JOIN country c ON sc.country_id = c.country_id
        LEFT JOIN state s ON sc.state_id = s.state_id
        LEFT JOIN city ci ON sc.city_id = ci.city_id
        LEFT JOIN borough b ON sc.borough_id = b.borough_id
        LEFT JOIN area a ON sc.area_id = a.area_id
        LEFT JOIN street st ON st.street_id = sc.street_id
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
        $searchVar->mainTableAlias = 'sc';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');
        $area_id = $fn->getReqParam('area_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sc.shop_center_id = {$tv['record_id']}";
        }
		
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "sc.country_id = '{$country_id}'";
        }

        if ($state_id != '' ) {
            $searchVar->sqlSearchVar[] = "sc.state_id = '{$state_id}'";
        }

        if ($city_id != '' ) {
            $searchVar->sqlSearchVar[] = "sc.city_id = '{$city_id}'";
        }

        if ($area_id != '' ) {
            $searchVar->sqlSearchVar[] = "sc.area_id = '{$area_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(sc.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }

		$searchVar->sortOrder = "sc.title";
    }
}
