<?
class CP_Common_Modules_Directory_SocialMedia_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT sm.*
        	  ,c.title AS country_title
        FROM social_media sm
        LEFT JOIN country c ON c.country_id = sm.country_id
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

        if ($tv['record_id'] != '' && $linkRecType == '') {
            $searchVar->sqlSearchVar[] = "sm.social_media_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sm.social_media_id');
    		
            $country_id = $fn->getSessionParam('cp_country_id');
            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "(sm.country_id = '{$country_id}' OR sm.country_id IS NULL)";
            }            

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    sm.title LIKE '%{$tv['keyword']}%'  
                 OR sm.url LIKE '%{$tv['keyword']}%'  
                )";
            }
    
    		$searchVar->sortOrder = "sm.title";
    	}
    }
}
