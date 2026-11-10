<?
class CP_Admin_Modules_Ecommerce_Industry_Functions extends CP_Common_Modules_Ecommerce_Industry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_industry');
        $modules->registerModule($modObj, array(
            'title'         => 'Industry'
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