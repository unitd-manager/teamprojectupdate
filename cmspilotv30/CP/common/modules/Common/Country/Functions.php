<?
class CP_Common_Modules_Common_Country_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){
    	
        $modObj = $modules->getModuleObj('common_country');
        $modules->registerModule($modObj, array(
        ));
    }
}