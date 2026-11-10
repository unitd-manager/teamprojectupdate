<?
class CP_Admin_Modules_Ek_Book_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_book');
        $modules->registerModule($modObj, array(
        ));

    }
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_book', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}