<?
class CP_Admin_Modules_Pos_Currency_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_currency');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pos_currency', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

}