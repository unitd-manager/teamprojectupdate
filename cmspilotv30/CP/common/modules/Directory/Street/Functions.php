<?
class CP_Common_Modules_Directory_Street_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_street');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
        ));
    }
}