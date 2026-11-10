<?
class CP_Admin_Modules_WebBasic_CallRegistry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_callRegistry');
        $modObj['tableName'] = 'call_registry';
        $modObj['keyField']  = 'call_registry_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Call Registry'
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('webBasic_callRegistry', 'project_contactLink');

       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'call_registry_contact'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'call_registry_contact_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 1
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

        //--------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('webBasic_callRegistry', 'project_companyLink');

       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'call_registry_company'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'call_registry_company_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 1
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

   }    
}