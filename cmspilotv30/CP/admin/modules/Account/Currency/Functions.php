<?
class CP_Admin_Modules_Account_Currency_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_currency');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList' => array('new')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('account_currency', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj);
    }
}