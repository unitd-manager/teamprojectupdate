<?
class CP_Admin_Modules_Core_Setting_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('new');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        
        $modObj = $modules->getModuleObj('core_setting');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'actBtnsList'   => $actBtnsList
           ,'hasFlagInList' => 0
        ));

        $modObj = $modules->getModuleObj('core_media');
        $modules->registerModule($modObj, array(
            'moduleGroup'   => 'common'
        ));
    }

    /**
     *
     */
    function getSettingsValueTypeArray(){
        $arr =
        array(
             'Yes No'
            ,'Text Field'
            ,'Text Area'
            ,'Number Field'
        );

        return $arr;
    }

    /**
     *
     */
    function getSettingsGroupArray(){
        $arr =
        array(
             'Email Settings'
            ,'Marketing'
            ,'Meta Data'
            ,'Pay Dollar'
            ,'Paypal'
            ,'Shopping Cart'
            ,'Site Properties'
            ,'Worldpay'
            ,'Admin'
        );

        return $arr;
    }
}