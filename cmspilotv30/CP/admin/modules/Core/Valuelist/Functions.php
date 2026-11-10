<?
class CP_Admin_Modules_Core_Valuelist_Functions extends CP_Common_Modules_Core_Valuelist_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('core_valuelist');
        $modules->registerModule($modObj, array(
             'hasMultiLang' => 1
            ,'titleField'   => 'value'
        ));
    }
}