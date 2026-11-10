<?
class CP_Admin_Modules_ELearn_Submission_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_submission');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'hasEditInList' => 0
           ,'actBtnsList'   => array()
           ,'actBtnsDetail' => array('delete')
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_submission', 'audio', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}