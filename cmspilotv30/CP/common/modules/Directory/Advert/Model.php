<?
class CP_Common_Modules_Directory_Advert_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT a.*
        	  ,co.title AS country_title
        FROM advert a
        LEFT JOIN country co ON co.country_id = a.country_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.advert_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.advert_id');
    		
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.title   LIKE '%{$tv['keyword']}%'  
                )";
            }
    
    		$searchVar->sortOrder = "a.title";
    	}
    }
}
