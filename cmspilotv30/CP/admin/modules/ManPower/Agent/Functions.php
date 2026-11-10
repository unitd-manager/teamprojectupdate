<?
class CP_Admin_Modules_ManPower_Agent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_agent');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
           ,'title'         => 'Agent'
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_agent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_agent', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_agent', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_agent', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'b.company_name'
           ,'historyTableName'      => 'agent'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_agent', 'manPower_candidateLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'agent_candidate'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
           ,'anchorFieldsArr'       => array(
                'title' => $inst->getLinkAnchorObj(
                           'title'
                          ,'candidate_id'
                          ,false
                          ,''
                          ,array('showLinkInEdit' => true)
                )
           )
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_agent', 'manPower_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', c.first_name, c.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => array('name' => $inst->getLinkAnchorObj('first_name', 'contact_id'))
           ,'fieldlabel'            => array('First Name'
                                            , 'Last Name'
                                            , 'Email'
                                            , 'Phone (Direct)'
                                            , 'Mobile'
                                            , 'Position'
                                            , 'Primary Contact'
                                       )
        ));


    }
}