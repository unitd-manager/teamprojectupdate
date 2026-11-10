<?
class CP_Common_Modules_Common_GeoRegion_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL   = "
        SELECT gr.*
              ,gc.name AS country_name
        FROM geo_region gr
        LEFT JOIN (geo_country gc) ON (gr.country_code = gc.country_code)
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
        $searchVar->mainTableAlias = 'gr';

        $country_code  = $fn->getReqParam('country_code');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "gr.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "gr.geo_region_id = {$tv['record_id']}";

        } else {
            if ($country_code != '') {
                $searchVar->sqlSearchVar[] = "gr.country_code = '{$country_code}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    gr.country_code LIKE '%{$tv['keyword']}%'
                    OR gr.name LIKE '%{$tv['keyword']}%'
                    OR gr.region_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getRegionDDSQL($country_code = '') {

        $append = '';
        if($country_code != ''){
            $append .= " gr.country_code = '{$country_code}'";
        }

        if(CP_SCOPE == 'www'){
            $append .= ($append != '') ? ' AND' : '';
            $append .= ' AND gr.published = 1';
        }
        $append = ($append != '') ? "WHERE {$append}" : '';

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
