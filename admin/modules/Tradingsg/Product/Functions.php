<?
class CPL_Admin_Modules_Tradingsg_Product_Functions extends CP_Admin_Modules_Tradingsg_Product_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        /* Import Functionality already done in list. Activating button will do product import - ARIF */
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_product');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('import', 'export')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
        ));
    }


     /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_product', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_product', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

           //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_product', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

}