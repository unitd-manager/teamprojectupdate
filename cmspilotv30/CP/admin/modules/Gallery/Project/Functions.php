<?
class CP_Admin_Modules_Gallery_Project_Functions extends CP_Common_Modules_Gallery_Project_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('gallery_project');
        $modules->registerModule($modObj, array(
            'hasFlagInList'  => 0
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'hasMultiLang'  => 1
        ));
    }
}