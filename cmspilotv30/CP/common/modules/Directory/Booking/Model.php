<?
class CP_Common_Modules_Directory_Booking_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $sql = "
        SELECT b.*
        	  ,co.title AS country_title
        FROM booking b
        LEFT JOIN country co ON co.country_id = b.country_id
        ";
        return $sql;
    }
    
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.booking_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.booking_id');
            
            $country_id = $fn->getSessionParam('cp_country_id');
            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "b.country_id = '{$country_id}'";
            }     		
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    b.title LIKE '%{$tv['keyword']}%'  
                )";
            }
    
    		$searchVar->sortOrder = "b.title";
    	}
    }
}
