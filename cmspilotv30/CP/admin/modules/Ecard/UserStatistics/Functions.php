<?
class CP_Admin_Modules_Ecard_UserStatistics_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecard_userStatistics');
        $modObj['tableName'] = 'sub_category';
        $modObj['keyField']  = 'sub_category_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'User Statistics'
           ,'hasMultiLang'  => 0
           ,'actBtnsList'   => array()
        ));
    }

    //==================================================================//
}
