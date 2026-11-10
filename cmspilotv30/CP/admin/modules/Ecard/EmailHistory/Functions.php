<?
class CP_Admin_Modules_Ecard_EmailHistory_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecard_emailHistory');
        $modObj['tableName'] = 'sub_category';
        $modObj['keyField']  = 'sub_category_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Email History'
           ,'hasMultiLang'  => 0
           ,'actBtnsList'   => array()
        ));
    }

    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $searchVar->sqlSearchVar[] = "eh.sent = 1";
        $searchVar->sqlSearchVar[] = "e.music_id IS NOT NULL";
        $searchVar->sqlSearchVar[] = "e.picture_id IS NOT NULL";
        $searchVar->sortOrder = "eh.sent_date DESC";
    }

    //==================================================================//
}