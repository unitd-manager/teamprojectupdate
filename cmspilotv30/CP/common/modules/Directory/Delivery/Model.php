<?
class CP_Common_Modules_Directory_Delivery_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT d.*
        	  ,c.title AS country_title
        FROM delivery d
        LEFT JOIN country c ON c.country_id = d.country_id
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
        $relationalDataOnly  = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');

        if ($relationalDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'd.delivery_id');
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "d.delivery_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'd.delivery_id');

            $country_id = $fn->getSessionParam('cp_country_id');
            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "d.country_id = '{$country_id}'";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    d.title LIKE '%{$tv['keyword']}%'
                 OR d.url LIKE '%{$tv['keyword']}%'
                )";
            }

    		$searchVar->sortOrder = "d.title";
    	}
    }

}
