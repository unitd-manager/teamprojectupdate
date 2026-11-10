<?
class CP_Common_Modules_WebBasic_Content_Functions
{

    //==================================================================//
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'otherPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('webBasic_content', 'webBasic_contentLink', array(
             'historyTableName'    => 'related_content'
            ,'keyFieldForHistory'  => 'related_content_id'
            ,'keyField'            => 'content_id'
            ,'keyFieldForLinking'  => 'content_id_rel'
            ,'className'           => 'Content'
            ,'showAnchorInLinkPortal' => 0
        ));
        
        $inst->registerLinksArray($linkObj);
        
    }
}
