<?
class CP_Www_Modules_WebBasic_Content_Functions extends CP_Common_Modules_WebBasic_Content_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('webBasic_content');
        $modules->registerModule($modObj, array(
        ));
    }
}
