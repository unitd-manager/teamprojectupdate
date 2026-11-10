<?
class CP_Common_Modules_Museum_Collection_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $SQL = "
        SELECT c.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
        FROM collection c
        LEFT JOIN (category ca)      ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc) ON (c.sub_category_id  = sc.sub_category_id)
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

        $collection_id   = $fn->getReqParam('collection_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');

        if ($collection_id != '') {
            $searchVar->sqlSearchVar[] = "c.collection_id = {$collection_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.collection_id = {$tv['record_id']}";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.collection_id');
            
            if($tv['linkName'] == 'collection#collection'){
                $searchVar->sqlSearchVar[] = "c.collection_id != {$tv['linkMasterTableID']}";
            }
            
            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }
    
            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['sub_category_id']}'";
            }
    
            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "c.collection_id = '{$tv['record_id']}'";
            }
    
            if ($special_search != '' ) {
    
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "c.published = 1";
                }
    
                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
                }
    
                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "c.latest = 1";
                }
    
                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "c.flag = 1";
                }
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( c.title        LIKE '%{$tv['keyword']}%'  OR
                                                c.description  LIKE '%{$tv['keyword']}%'
                                              )";
            }
        }
    }

    /**
     *
     */
    function getCollectionSQL() {
        $SQL = '
        SELECT c.colledction_id
              ,c.title
        FROM collection c
        ORDER BY c.title
        ';
        return $SQL;
    }
}
