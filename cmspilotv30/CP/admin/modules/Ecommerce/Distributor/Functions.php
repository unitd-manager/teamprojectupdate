<?
class CP_Admin_Modules_Ecommerce_Distributor_Functions extends CP_Common_Modules_Ecommerce_Distributor_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_distributor');
        $modules->registerModule($modObj, array(
            'title'         => 'Distributor'
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