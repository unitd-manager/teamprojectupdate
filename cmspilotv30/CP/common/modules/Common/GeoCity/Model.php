<?
class CP_Common_Modules_Common_GeoCity_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL   = "
        SELECT gc.*
              ,gr.name AS region_name
              ,gc2.name AS country_name
        FROM geo_city gc
        LEFT JOIN (geo_region gr) ON (gr.region_code = gc.region_code)
        LEFT JOIN (geo_country gc2) ON (gr.country_code = gc2.country_code)
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
        $searchVar->mainTableAlias = 'gc';

        $country_code  = $fn->getReqParam('country_code');
        $region_code  = $fn->getReqParam('region_code');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "gc.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "gc.geo_city_id = {$tv['record_id']}";

        } else {
            if ($country_code != '') {
                $searchVar->sqlSearchVar[] = "gr.country_code = '{$country_code}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    gc.city_code LIKE '%{$tv['keyword']}%'
                    OR gr.country_code LIKE '%{$tv['keyword']}%'
                    OR gc.name LIKE '%{$tv['keyword']}%'  
                    OR gr.name LIKE '%{$tv['keyword']}%'  
                    OR gr.region_code LIKE '%{$tv['keyword']}%'  
                    OR gc2.name LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
    }

    /**
     *
     */
    function getRegionDDSQL() {
        $append = (CP_SCOPE == 'www') ? 'WHERE gr.published = 1' : '';
        
        $SQL = "
        SELECT gr.region_code
              ,gr.name
        FROM geo_region gr
        {$append}
        ORDER BY gr.name
        ";

        return $SQL;
    }
}
