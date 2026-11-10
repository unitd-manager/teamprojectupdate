<?
class CP_Www_Modules_Web2_Feed_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('web2_feed');
        $modules->registerModule($modObj, array(
        ));
    }
}