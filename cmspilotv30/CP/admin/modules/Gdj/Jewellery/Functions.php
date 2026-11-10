<?
class CP_Admin_Modules_Gdj_Jewellery_Functions extends CP_Common_Modules_Gdj_Jewellery_Functions
{
    function setMediaArray($mediaArr) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'relatedPicture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'certificate', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('gdj_jewellery', 'gdj_jewelleryLink', array(
            'historyTableName'    => 'related_product'
           ,'keyFieldForHistory'  => 'related_product_id'
           ,'keyField'            => 'product_id'
           ,'keyFieldForLinking'  => 'product_id_rel'
           ,'linkRoomActual'      => 'jewellery'
           ,'showAnchorInLinkPortal' => 0
           ,'anchorFieldsArr'     => array('title' => $inst->getLinkAnchorObj('title', 'product_id'))
           ,'fieldlabel'          => array('Product Name'
                                          ,'Category'
                                     )
        ));
        $inst->registerLinksArray($linkObj);
    }
}