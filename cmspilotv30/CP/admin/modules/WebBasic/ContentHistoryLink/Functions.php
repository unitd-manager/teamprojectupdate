<?
class CP_Admin_Modules_WebBasic_ContentHistoryLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_contentHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'content_history'
           ,'keyField'      => 'content_history_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_contentHistory', 'histPic', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}
