<?
class CP_Admin_Modules_Logistics_Booking_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('logistics_booking');
        $modules->registerModule($modObj, array(
            'title'         => 'Booking'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('logistics_booking', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('logistics_booking', 'logistics_resourceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'resource'
           ,'displayTitleFieldName' => "r.resource_name"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => array('name' => $inst->getLinkAnchorObj('first_name', 'resource_id'))
           ,'fieldlabel'            => array('Resource Name'
                                            ,'Role'
                                            ,'Email'
                                            ,'Phone'
                                       )
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('logistics_booking', 'logistics_vehicleLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'vehicle'
           ,'displayTitleFieldName' => "vehicle_code"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => array('name' => $inst->getLinkAnchorObj('vehicle_code', 'vehicle_id'))
           ,'fieldlabel'            => array('Vehicle Code'
                                            ,'Vehicle Name'
                                            ,'Model'
                                            ,'Date'
                                       )
        ));
        //------------------------------------------------------------------------------//
    }
}