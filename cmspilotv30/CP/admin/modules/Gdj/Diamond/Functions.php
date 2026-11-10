<?
class CP_Admin_Modules_Gdj_Diamond_Functions extends CP_Common_Modules_Gdj_Diamond_Functions
{
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'relatedPicture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'certificate', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('gdj_diamond', 'gdj_diamondLink', array(
            'historyTableName'    => 'related_product'
           ,'keyFieldForHistory'  => 'related_product_id'
           ,'keyField'            => 'product_id'
           ,'keyFieldForLinking'  => 'product_id_rel'
           ,'linkRoomActual'      => 'diamond'
           ,'showAnchorInLinkPortal' => 0
           ,'anchorFieldsArr'     => array('title' => $inst->getLinkAnchorObj('title', 'product_id'))
           ,'fieldlabel'          => array('Product Name'
                                          ,'Category'
                                     )
        ));
        $inst->registerLinksArray($linkObj);
    }
}