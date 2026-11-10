<?
class CP_Admin_Modules_Subscription_Contact_Functions extends CP_Common_Modules_Subscription_Contact_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('subscription_contact');
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
        $mediaObj = $mediaArr->getMediaObj('subscription_contact', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('subscription_contact', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}