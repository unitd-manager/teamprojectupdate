<?
class CP_Admin_Modules_Directory_AddressLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_addressLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'address'
           ,'keyField'  => 'address_id'
        ));
    }

}
