<?
class CP_Common_Modules_Ads_Banner_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT b.*
        FROM banner b
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
        $searchVar->mainTableAlias = 'b';

        $category_id  = $fn->getReqParam('category_id');
        $sub_category_id  = $fn->getReqParam('sub_category_id');
        $banner_id     = $fn->getReqParam('banner_id');


        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "b.published = 1";
        }

        if ($banner_id != "") {
            $searchVar->sqlSearchVar[] = "b.banner_id = '{$banner_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.banner_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.banner_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "b.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(b.subscribe != 1 OR b.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "b.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(b.flag != 1 OR b.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "b.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "b.published = 0 OR b.published IS NULL OR b.published = ''";
            }
    
            //------------------------------------------------------------------------//
            
            if ($category_id!= '' ) {
                $searchVar->sqlSearchVar[] = "b.category_id = {$category_id}";
            }

            if ($sub_category_id!= '' ) {
                $searchVar->sqlSearchVar[] = "b.sub_category_id = {$sub_category_id}";
            }

            //------------------------------------------------------------------------//
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    b.title       LIKE '%{$tv['keyword']}%'  OR
                    b.description LIKE '%{$tv['keyword']}%'
                )";
            }            
        }
    }

}
