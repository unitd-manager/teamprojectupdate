<?
class CP_Admin_Modules_Directory_TransportLnk_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('directory_transportLnk');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Transport Link'
           ,'tableName' => 'transport_link'
           ,'keyField' => 'transport_link_id'
           ,'titleField' => "CONCAT_WS(' - ', title, station_exit)"
           ,'actBtnsList' => array('new')
        ));
    }

}