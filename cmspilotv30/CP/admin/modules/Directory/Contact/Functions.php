<?
class CP_Admin_Modules_Directory_Contact_Functions extends CP_Common_Modules_Directory_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('directory_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media', 'contact_card', 'contact_preference',
                                     'interest_contact', 'broadcast_contact')
           ,'title' => 'General Contact'
           ,'actBtnsList' => array('new', 'export')
        ));
    }
}