<?
class CP_Admin_Modules_AgileIms_Refund_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_refund');
        $modules->registerModule($modObj, array(
            'title'         => 'Refund'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('agileIms_refund', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}