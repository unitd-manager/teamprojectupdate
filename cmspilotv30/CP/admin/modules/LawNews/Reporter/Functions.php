<?
class CP_Admin_Modules_LawNews_Reporter_Functions
{
    /**
     *
     */    
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('lawNews_reporter');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */    
     function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_reporter', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_reporter', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

     /**
     *
     */
    function setLinksArray($inst) {
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('lawNews_reporter', 'webBasic_contentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'reporter_content'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}