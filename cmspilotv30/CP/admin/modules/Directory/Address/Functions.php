<?
class CP_Admin_Modules_Directory_Address_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_address');
        $modules->registerModule($modObj, array(
            'tableName' => 'address'
           ,'keyField'  => 'address_id'
           ,'hasFlagInList' => 0
           ,'title'  => 'Address'
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }

}