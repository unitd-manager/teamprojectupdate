<?
class CP_Www_Modules_Membership_Contact_Functions extends CP_Common_Modules_Membership_Contact_Functions
{

    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('membership_contact');
        $modules->registerModule($modObj, array(
             'actBtnsDetail' => array('changePassword', 'edit')
            ,'showActBtnsBelowForm' => true
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('membership_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('membership_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('membership_contact', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}
