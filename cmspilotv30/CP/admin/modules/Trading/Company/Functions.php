<?
class CP_Admin_Modules_Trading_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_company');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array('new', 'search', 'export', 'import')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'relatedTables' => array('media')
           ,'titleField' => 'company_name'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_company', 'trading_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', c.first_name, c.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'portalDialogWidth'      => 700
           ,'portalDialogHeight'     => 500
           ,'anchorFieldsArr' => array('first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
                                      ,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))
           ,'fieldlabel'      => array('Title'
                                      ,'First name'
                                      ,'Last name'
                                      ,'Email'
                                      ,'Phone (Direct)'
                                      ,'Mobile'
                                      ,'Position'
                                      ,'Dept.'
                                 )
        ));

        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_company', 'trading_deliveryAddressLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'delivery_address'
           ,'linkingType'           => 'portal'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', c.first_name, c.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'showAnchorInLinkPortal' => 0
           ,'fieldlabel'            => array('Address Line 1', 'Address Line 2',
                                             'Town/City', 'State', 'Country', 'Post Code/Zip')
        ));

        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_company', 'trading_deliveryTermsLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'delivery_terms'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'showAnchorInLinkPortal' => 0
           ,'fieldlabel'            => array('Description')
        ));

        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_company', 'trading_paymentTermsLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'payment_terms'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'showAnchorInLinkPortal' => 0
           ,'fieldlabel'            => array('Description')
        ));
        
        //-----------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_company', 'trading_bankLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'bank'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'showAnchorInLinkPortal' => 0
           ,'fieldlabel'            => array('Description')
        ));

    }
}