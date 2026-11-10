<?
class CP_Admin_Modules_Ecard_Ecard_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecard_ecard');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 0
        ));
    }
}