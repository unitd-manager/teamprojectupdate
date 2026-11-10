<?
class CP_Admin_Modules_Dms_Document_Functions extends CP_Common_Modules_Dms_Document_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('dms_document');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
        ));
    }
}