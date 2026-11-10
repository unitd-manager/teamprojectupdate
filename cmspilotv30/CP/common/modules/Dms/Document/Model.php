<?
class CP_Common_Modules_Dms_Document_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $tagsSQL = "";
        $countryAppendSQL = '';
        $countryJoinSQL = '';

        if ($cpCfg['m.dms.document.hasCountryId'] == 1) {
            $countryAppendSQL = ",co.title AS country_name";
            $countryJoinSQL = "LEFT JOIN (country co) ON (d.country_id = co.country_id)";
        }

        $SQL = "
        SELECT d.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
              {$countryAppendSQL}
        FROM document d
        {$countryJoinSQL}
        LEFT JOIN (category ca) ON (d.category_id = ca.category_id)
        LEFT JOIN (sub_category sc)ON (d.sub_category_id  = sc.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'd';

        $special_search = $fn->getReqParam('special_search');
        $country_id     = $fn->getReqParam('country_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "d.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "d.document_id = '{$tv['record_id']}'";
        } else {
            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "d.country_id = {$country_id}";
            }

            if ($tv['category_id'] != '') {
                $searchVar->sqlSearchVar[] = "d.category_id = {$tv['category_id']}";
            }
    
            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar[] = "d.sub_category_id = {$tv['sub_category_id']}";
            }
            
 
            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "d.published = 1";
                }
    
                if ($special_search == 'Not-Published' ) {
                    $searchVar->sqlSearchVar[] = "d.published = 0 OR d.published IS NULL OR d.published = ''";
                }

                //------------------------------------------------------------------------//
                if ($tv['special_search'] == "Flagged") {
                    $searchVar->sqlSearchVar[] = "d.flag = 1";
                }
        
                if ($tv['special_search'] == "Not-Flagged") {
                    $searchVar->sqlSearchVar[] = "(d.flag != 1 OR d.flag IS null)";
                }
            }
    
            //------------------------------------------------------------------------//
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    d.title       LIKE '%{$tv['keyword']}%'  OR
                    d.description LIKE '%{$tv['keyword']}%'
                )";
            }
    
            $searchVar->sortOrder = "d.sort_order ASC";
        }
        
    }
}
