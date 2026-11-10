<?
class CP_Admin_Modules_Tradingsg_PackingList_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_packingList');
        $modules->registerModule($modObj, array(
            'title'         => 'Packing List'
           ,'tableName' 	=> 'packing_list'
           ,'keyField'  	=> 'packing_list_id'
           ,'hasMultiLang' 	=> 1
           ,'hasFlagInList' => 0
        ));
    }
    
    /**
     *
     */
 /*    function setLinksArray($inst) 
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('tradingsg_productGroup', 'tradingsg_categoryLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'department_category'
           ,'displayTitleFieldName'  => "a.title"
           ,'linkingType'            => 'modal'
           ,'historyTableKeyField'   => 'department_category_id'
           ,'showLinkPanelInEdit'    => 1
           ,'hasPortalEdit'          => 0
           ,'showAnchorInLinkPortal' => false
           ,'hasGridEdit'            => 0
        ));
    }*/
}