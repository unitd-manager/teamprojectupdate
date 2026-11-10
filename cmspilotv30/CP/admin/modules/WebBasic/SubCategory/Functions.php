<?
class CP_Admin_Modules_WebBasic_SubCategory_Functions extends CP_Common_Modules_WebBasic_SubCategory_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_subCategory');
        $modObj['tableName'] = 'sub_category';
        $modObj['keyField']  = 'sub_category_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Sub Category'
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
            $siteObj->setLinksArrayForSiteLink($inst, 'webBasic_subCategory');
        }           
    }    
}