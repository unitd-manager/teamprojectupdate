<?
class CP_Common_Modules_Directory_Ambiance_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $SQL = "
        SELECT a.*
        	  ,co.title AS country_title
        FROM ambiance a
        LEFT JOIN country co ON co.country_id = a.country_id
        ";

        return $SQL;
    }

    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $relationalDataOnly  = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');

        $country_id = $fn->getSessionParam('cp_country_id');

        if ($relationalDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.ambiance_id');
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.ambiance_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.ambiance_id');
            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "co.country_id = '{$country_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.title LIKE '%{$tv['keyword']}%'
                )";
            }

    		$searchVar->sortOrder = "a.title";
    	}
    }
}
