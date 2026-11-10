<?
class CP_Admin_Modules_Tradingus_Company_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingus_company');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
           ,'title'         => 'Client'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('tradingus_company', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('tradingus_company', 'tradingsg_contactLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'contact'
            ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'anchorFieldsArr'       => array(
                 'first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
                ,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))
            ,'fieldlabel' => array(
                 'Name'
                ,'Email'
                ,'Phone (Direct)'
                ,'Mobile'
                ,'Position'
                ,'Dept.'
            )
        ));

        $linkObj = $inst->getLinksArrayObj('tradingus_company', 'tradingsg_discountLink');

        if ($cpCfg['m.tradingsg.discountLink.showDiscount']) {
            $inst->registerLinksArray($linkObj, array(
                 'historyTableName'      => 'discount'
                ,'showLinkPanelInNew'    => 0
                ,'showLinkPanelInEdit'   => 1
                ,'linkingType'           => 'portal'
                ,'hasPortalEdit'         => 1
                ,'hasPortalDelete'       => 1
                ,'portalDialogWidth'     => 700
                ,'portalDialogHeight'    => 500
                ,'fieldlabel' => array(
                     'Product Group'
                    ,'Category'
                    ,'Service Cost%'
                    ,'Discount %'
                )
            ));
        } else {
            $inst->registerLinksArray($linkObj, array(
                 'historyTableName'      => 'discount'
                ,'showLinkPanelInNew'    => 0
                ,'showLinkPanelInEdit'   => 1
                ,'linkingType'           => 'portal'
                ,'hasPortalEdit'         => 1
                ,'hasPortalDelete'       => 1
                ,'portalDialogWidth'     => 700
                ,'portalDialogHeight'    => 500
                ,'fieldlabel' => array(
                     'Product Group'
                    ,'Category'
                    ,'Service Cost%'
                )
            ));
        }

    }
}
