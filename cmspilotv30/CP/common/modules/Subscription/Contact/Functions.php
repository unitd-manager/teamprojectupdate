<?
class CP_Common_Modules_Subscription_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('subscription_contact');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('subscription_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}