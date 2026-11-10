<?
class CP_Admin_Modules_Gdj_Gemstone_Functions extends CP_Common_Modules_Gdj_Gemstone_Functions
{
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('gdj_gemstone', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'count'      => 'single'
            ,'maxWidthN'  => 250
            ,'maxHeightN' => 310
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_gemstone', 'relatedPicture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthN'  => 250
            ,'maxHeightN' => 310
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_gemstone', 'certificate', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthN'  => 250
            ,'maxHeightN' => 310
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('gdj_gemstone', 'gdj_gemstoneLink', array(
            'historyTableName'    => 'related_product'
           ,'keyFieldForHistory'  => 'related_product_id'
           ,'keyField'            => 'product_id'
           ,'keyFieldForLinking'  => 'product_id_rel'
           ,'linkRoomActual'      => 'gemstone'
           ,'showAnchorInLinkPortal' => 0
           ,'anchorFieldsArr'     => array('title' => $inst->getLinkAnchorObj('title', 'product_id'))
           ,'fieldlabel'          => array('Product Name'
                                          ,'Category'
                                     )
        ));
        $inst->registerLinksArray($linkObj);
    }
}