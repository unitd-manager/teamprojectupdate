<?
class CP_Admin_Modules_Core_AdminTranslation_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('core_adminTranslation');
        $modules->registerModule($modObj, array(
            'tableName' => 'admin_translation',
            'keyField' => 'admin_translation_id',
            'moduleGroup' => 'core',
            'actBtnsList' => array('new', 'deleteList'),
            'hasFlagInList' => 0,
            'hasCheckboxInList' => 1,
            'hasMultiLang' => 1,
        ));
    }

    /**
     *
     */
    function getAdminTranslationGroupArray(){
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