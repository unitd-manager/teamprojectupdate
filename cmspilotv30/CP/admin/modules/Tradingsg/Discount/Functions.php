<?
class CP_Admin_Modules_Tradingsg_Discount_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_discount');
        $modules->registerModule($modObj, array(
            'title'       => 'Margin / Discount'
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_discount', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}