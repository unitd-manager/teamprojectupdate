<?
class CP_Admin_Modules_Ecommerce_Division_Functions extends CP_Common_Modules_Ecommerce_Division_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_division');
        $modules->registerModule($modObj, array(
            'title'         => 'Division'
           ,'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
    }    
}