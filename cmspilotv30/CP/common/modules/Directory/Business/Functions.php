<?
class CP_Common_Modules_Directory_Business_Functions
{
	function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_businessContactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'business_contact_link'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'fieldlabel' => array('Name', 'Email', 'Country')

           ,'additionalFieldsArray' => array(
                'a.email'
               ,'a.country_code'
           )
           ,'hasModalChoose' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_contactLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'my_business'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'fieldlabel' => array('Name', 'Email', 'Country')

           ,'additionalFieldsArray' => array(
                'a.email'
               ,'a.country_code'
           )
           ,'hasModalChoose' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_guideLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'guide_business'
           ,'displayTitleFieldName' => 'a.title'
           ,'fieldlabel' => array('Name')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_promotionLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' 	=> 'promotion'
           ,'linkingType'      	=> 'portal'
           ,'hasPortalNew'		=> 1
           ,'hasPortalNew'		=> 1
           ,'hasPortalEdit'		=> 1
           ,'hasPortalDelete'   => 1
           ,'portalDialogHeight' => 700
           ,'portalDialogWidth' => 600
           ,'fieldlabel' => array('Title', 'Start Date', 'End Date', 'Start Time',
                                  'End Time', 'Days of Week', 'Custom Text')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_promotion3PartyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' 	=> 'promotion'
           ,'linkingType' => 'portal'
           ,'hasPortalNew' => 1
           ,'hasPortalEdit'	=> 1
           ,'hasPortalDelete' => 1
           ,'portalDialogHeight' => 700
           ,'portalDialogWidth' => 600
           ,'fieldlabel' => array('Card', 'Start Date', 'End Date', 'Days of Week', 'Custom Text')
        ));

        //------------------------------------------------------------------------------//
        $linkHeaderHyperLink = array('url' => '#', 'title' => 'Copy hours', 'class' => 'copyHours');

        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_businessHoursLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'business_hours'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'business_hours_id'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalNew'           => 0
            ,'hasPortalDelete'        => 0
            ,'fieldlabel'             => array('Day', 'Morning open', 'Morning close',
                                               'Evening open', 'Evening close')
            ,'fieldClassArray'        => array()
            ,'linkHeaderHyperLink' => $linkHeaderHyperLink
            ,'showAnchorInLinkPortal' => false
            ,'showRowSerialNo'        => false
            ,'additionalFieldsArray'  => array(
                 'b.week_day'
                ,"DATE_FORMAT(b.start_time, '%H:%i') AS start_time"
                ,"DATE_FORMAT(b.end_time, '%H:%i') AS end_time"
                ,"DATE_FORMAT(b.start_time2, '%H:%i') AS start_time2"
                ,"DATE_FORMAT(b.end_time2, '%H:%i') AS end_time2"
            )
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'textbox'
                       ,'editable' => false
                       ,'associtaiveArr' => $cpUtil->getWeekDaysArr()
                  )
                 ,array('type' => 'textbox')
                 ,array('type' => 'textbox')
                 ,array('type' => 'textbox')
                 ,array('type' => 'textbox')
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_externalSourceLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_externalSource');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'business_external_source'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'business_external_source_id'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalDelete'        => 1
            ,'fieldlabel'             => array('Source', 'Url')
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray'  => array(
                 'b.source_id'
                ,'b.source_url'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_bookingLink');

        $extSourceArr = $fn->getDdDataAsArray('directory_booking');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'business_booking'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'business_booking_id'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalDelete'        => 1
            ,'fieldlabel'             => array('Source', 'Url')
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray'  => array(
                 'b.external_source_id'
                ,'b.source_url'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_advertLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_advert');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'business_advert'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'business_advert_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 1
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Advert', 'Description', 'Advert Date')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
                ,array('type' => 'date')
            )
            ,'additionalFieldsArray' => array(
                 'b.advert_id'
                ,'b.description'
                ,'b.advert_date'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_paymentLink');
        $sqlPayment = getCPModuleObj('directory_payment')->model->getPayementSQL();
        $result = $db->sql_query($sqlPayment);
        $sqlPaymentArr = $dbUtil->getResultsetAsArrayForForm($result);

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'business_payment'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'business_payment_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 0
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Title')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $sqlPaymentArr)
            )
            ,'additionalFieldsArray' => array(
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_socialMediaLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_socialMedia');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'business_social_media'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'business_social_media_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 0
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Title', 'URL')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray' => array(
                'b.url'
            )
            ,'showAnchorInLinkPortal' => false
            ,'fieldsArr' => array(
                'open_url' => $inst->getFieldObj('open_url', array(
                    'isGridFldEditable' => false
                ))
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_ambianceLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_ambiance');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'business_ambiance'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'business_ambiance_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 1
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Ambiance')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
            )
            ,'additionalFieldsArray' => array(
                 'b.ambiance_id'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_deliveryLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_delivery');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'business_delivery'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'business_delivery_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 1
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('delivery')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
            )
            ,'additionalFieldsArray' => array(
                 'b.delivery_id'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_business', 'directory_addressLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'business'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple' => 0
        ));

    }

    function setMediaArray($mediaArr) {

        $hasDelete = (CP_SCOPE == 'www') ? false : true;
        $hasNew    = (CP_SCOPE == 'www') ? false : true;

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_business', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 360
            ,'maxHeightN' => 360
            ,'maxWidthL' => 1000
            ,'maxHeightL' => 1000
            ,'count' => 'single'
            ,'isMediaLangSpecific' => false
            ,'hasDelete' => $hasDelete
            ,'hasNew' => $hasNew
            ,'hasWatermark' => true
            ,'watermarkText' => '© www.nearer.com'
            ,'showNormalImg' => true
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_business', 'logo', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 360
            ,'maxHeightN' => 360
            ,'maxWidthL' => 1000
            ,'maxHeightL' => 1000
            ,'count'      => 'single'
            ,'isMediaLangSpecific' => false
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_business', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 360
            ,'maxHeightN' => 360
            ,'maxWidthL' => 1000
            ,'maxHeightL' => 1000
            ,'isMediaLangSpecific' => false
            ,'hasWatermark' => true
            ,'watermarkText' => '© www.nearer.com'
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_business', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'isMediaLangSpecific' => false
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_business', 'menu', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 360
            ,'maxHeightN' => 360
            ,'maxWidthL' => 1000
            ,'maxHeightL' => 1000
            ,'isMediaLangSpecific' => false
            ,'hasWatermark' => true
            ,'watermarkText' => '© www.nearer.com'
        ));

    }
}