<?
class CP_Admin_Modules_ManPower_Documents_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_documents');
        $modules->registerModule($modObj, array(
             'title'         => 'Documents'
            ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_documents', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

}