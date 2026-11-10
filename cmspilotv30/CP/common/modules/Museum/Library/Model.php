<?
class CP_Common_Modules_Museum_Library_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $SQL = "
        SELECT l.*
        FROM library l
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

        $library_id   = $fn->getReqParam('library_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');

        if ($library_id != '') {
            $searchVar->sqlSearchVar[] = "l.library_id = {$library_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.library_id = {$tv['record_id']}";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'l.library_id');
            
            if($tv['linkName'] == 'library#library'){
                $searchVar->sqlSearchVar[] = "l.library_id != {$tv['linkMasterTableID']}";
            }
                
            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "l.library_id = '{$tv['record_id']}'";
            }
    
            if ($special_search != '' ) {
    
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "l.published = 1";
                }
    
                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "l.published = 0 OR l.published IS NULL OR l.published = ''";
                }
    
                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "l.latest = 1";
                }
    
                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "l.flag = 1";
                }
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( l.title        LIKE '%{$tv['keyword']}%'  OR
                                                l.description  LIKE '%{$tv['keyword']}%'
                                              )";
            }
        }
    }


    /**
     *
     */
    function getLibrarySQL() {
        $SQL = '
        SELECT l.library_id
              ,l.title
        FROM library l
        ORDER BY l.title
        ';
        return $SQL;
    }
}
