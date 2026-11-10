<?
class CP_Admin_Modules_Ecard_DailyUsage_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecard_dailyUsage');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Daily Usage'
           ,'hasMultiLang'  => 0
           ,'actBtnsList'   => array()
        ));
    }

    //==================================================================//
}
