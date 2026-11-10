<?
class CP_Admin_Modules_ELearn_Book_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_book');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $book_id = $fn->getReqParam('book_id');
        $color   = $fn->getReqParam('color');

        if ($book_id != "") {
            $searchVar->sqlSearchVar[] = "a.book_id = '{$book_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.book_id = '{$tv['record_id']}'";
        } else {

            if ($color != '') {
                $searchVar->sqlSearchVar[] = "a.color = '{$color}'";
            }

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.book_id');
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   a.title LIKE '%{$tv['keyword']}%' OR
                   a.book_no LIKE '%{$tv['keyword']}%'
                )";
            }
        }
        
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_book', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_book', 'elearn_klassLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'klass_book'
        ));

    }

}