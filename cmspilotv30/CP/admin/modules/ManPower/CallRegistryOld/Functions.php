<?
class CP_Admin_Modules_ManPower_CallRegistry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_callRegistry');
        $modObj['tableName'] = 'call_registry';
        $modObj['keyField']  = 'call_registry_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Call Registry'
            ,'actBtnsList' => array('new')
            ,'actBtnsEdit'  => array('save', 'apply', 'delete')
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {

       $linkObj = $inst->getLinksArrayObj('manPower_callRegistry', 'manPower_opportunityLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'opportunity'
           ,'fieldlabel'             => array('Opportunity Code', 'Title', 'Status')
           ,'showAnchorInLinkPortal' => 0
           ,'hasModalChoose'         => 0
           ,'anchorFieldsArr'       => array(
                'opportunity_code' => $inst->getLinkAnchorObj(
                     'opportunity_code'
                    ,'opportunity_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
       ));

        //--------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('manPower_callRegistry', 'project_contactLink');
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
       $linkObj = $inst->getLinksArrayObj('manPower_callRegistry', 'project_companyLink');

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

    //==================================================================//
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_callRegistry', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}