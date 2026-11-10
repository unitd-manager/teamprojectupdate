<?
class CP_Admin_Modules_Common_Comment_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_comment');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('common_comment', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}