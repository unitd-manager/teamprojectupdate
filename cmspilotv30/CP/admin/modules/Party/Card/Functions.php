<?
class CP_Admin_Modules_Party_Card_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('party_card');
        $modules->registerModule($modObj, array(
        ));
    }

    function setMediaArray($mediaArr) {
        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_card', 'thumbImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_card', 'hoverImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_card', 'rsvpBgImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_card', 'previewBgImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));

        //--------------//
        $mediaObj = $mediaArr->getMediaObj('party_card', 'thankyouCardImage', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'resize' => false
        ));
    }
}