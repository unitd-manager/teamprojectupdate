<?
class CP_Admin_Modules_Party_Charity_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('party_charity');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array('new')
        ));
    }

    function setMediaArray($mediaArr) {
        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_charity', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj);

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_charity', 'logoImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_charity', 'otherThumbImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));
    }
}
