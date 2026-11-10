<?
class CP_Admin_Modules_Wine_Grape_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('wine_grape');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
//           ,'actBtnsList'   => array('import', 'new','delete')
           ,'actBtnsList'   => array('new','delete')
        ));
    }
}