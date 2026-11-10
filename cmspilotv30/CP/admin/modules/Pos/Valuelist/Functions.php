<?
class CP_Admin_Modules_Pos_Valuelist_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pos_valuelist');
        $modules->registerModule($modObj, array(
             'hasMultiLang' => 1
            ,'titleField'   => 'value'
            ,'hasFlagInList' => false
            ,'actBtnsList' => array('new', 'printListScreen')
        ));
    }
}