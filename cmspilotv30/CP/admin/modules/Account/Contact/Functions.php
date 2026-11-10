<?
class CP_Admin_Modules_Account_Contact_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('account_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => array('new', 'export', 'import')
           ,'hasMultiLang'  => 1
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('account_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('account_contact', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('account_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'interest_contact'
            ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('account_contact', 'webBasic_contentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'contact_content'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}