<?
class CP_Admin_Modules_Core_Translation_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('core_translation');
        $modules->registerModule($modObj, array(
            'moduleGroup'       => 'core'
           ,'actBtnsList'       => array('new', 'deleteList')
           ,'hasFlagInList'     => 0
           ,'hasCheckboxInList' => 1
           ,'hasMultiLang'      => 1
        ));
    }

    /**
     *
     */
    function getTranslationGroupArray(){
        $arr =
        array(
             'Email Message'
            ,'Error Message'
            ,'Form Field'
            ,'Global'
            ,'Membership Related'
            ,'Message'
            ,'Poll'
            ,'Shopping Cart'
            ,'Other'
        );

        return $arr;
    }
}