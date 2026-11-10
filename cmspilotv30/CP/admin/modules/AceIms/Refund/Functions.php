<?
class CP_Admin_Modules_AceIms_Refund_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_refund');
        $modules->registerModule($modObj, array(
            'title'         => 'Refund'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('aceIms_refund', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}