<?
class CP_Common_Modules_Directory_BusinessContact_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_businessContact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('directory_businessContact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'interest_business_contact'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_businessContact', 'directory_businessLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'            => 'business_contact_link'
            ,'displayTitleFieldName'       => 'a.business_name'
            ,'fieldlabel'                  => array('Business', 'Position')
            ,'hideFieldsArray'             => array('business_contact_link_id')
            ,'hasPortalEdit'               => true
            ,'recordIdFldName'             => 'business_contact_link_id'
            ,'additionalFieldsArray'       => array(
                 'b.position'
                ,'b.business_contact_link_id'
            )
        ));
    }
}