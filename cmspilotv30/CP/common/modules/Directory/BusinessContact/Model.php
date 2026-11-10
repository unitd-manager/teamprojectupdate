<?
class CP_Common_Modules_Directory_BusinessContact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $interest_id = $fn->getReqParam('interest_id');
        $business_id = $fn->getReqParam('business_id');
        
        $extraTableNames = "";
        if ($interest_id != "") {
            $extraTableNames .= "JOIN interest_business_contact ibc ON (bc.business_contact_id = ibc.business_contact_id)";
        }

        if ($business_id != "") {
            $extraTableNames .= "JOIN business_contact_link bcl ON (bc.business_contact_id = bcl.business_contact_id)";
        }

        $SQL   = "
        SELECT bc.*
              ,CONCAT_WS(' ', bc.first_name, bc.last_name ) AS contact_name
        	  ,c.title AS city_name
        	  ,a.title AS area_name
        	  ,gc.name AS country_name
        FROM business_contact bc
        {$extraTableNames}
        LEFT JOIN (city c) ON (bc.city_id = c.city_id)
        LEFT JOIN (area a) ON (bc.area_id = a.area_id)
        LEFT JOIN (geo_country gc) ON (bc.country_code = gc.country_code)
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

        $interest_id = $fn->getReqParam('interest_id');
        $business_id = $fn->getReqParam('business_id');
        $business_contact_id = $fn->getReqParam('business_contact_id');
        $subscribe = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        if ($business_contact_id != "") {
            $searchVar->sqlSearchVar[] = "bc.business_contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bc.business_contact_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'bc.business_contact_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "bc.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(bc.subscribe != 1 OR bc.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "bc.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(bc.flag != 1 OR bc.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "bc.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "bc.published = 0 OR bc.published IS NULL OR bc.published = ''";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       bc.first_name   LIKE '%{$tv['keyword']}%'
                    OR bc.last_name    LIKE '%{$tv['keyword']}%'
                    OR bc.company_name LIKE '%{$tv['keyword']}%'
                    OR bc.email        LIKE '%{$tv['keyword']}%'
                )";
            }
        
            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ibc.interest_id = {$interest_id}";
            }
            
            if ($business_id != '' ) {
                $searchVar->sqlSearchVar[] = "bcl.business_id = {$business_id}";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "bc.subscribe = 1";
            }
    
            $searchVar->sortOrder = "bc.last_name, bc.first_name";
        }
    }
}
