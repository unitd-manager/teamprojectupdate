<?
class CP_Admin_Modules_WebBasic_Category_Functions extends CP_Common_Modules_WebBasic_Category_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_category');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'hasMultiLang'  => 1
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'webBasic_category');
        }         
    }    
}