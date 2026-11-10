<?
class CP_Admin_Modules_Accountsg_Category_Functions {


    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('accountsg_category');
        $modules->registerModule($modObj, array(
            'tableName'     => 'acc_category'
           ,'keyField'      => 'acc_category_id'
           ,'hasFlagInList' => 0
           ,'hasMultiLang'  => 1
           ,'title'         => 'Account Map'
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'accountsg_category');
        }         
    }    
}