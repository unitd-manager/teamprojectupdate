<?
class CP_Admin_Modules_Tradingsg_Pos_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_pos');
        $modules->registerModule($modObj, array(
            'tableName' => 'order'
           ,'keyField'  => 'order_id'
           ,'actBtnsList' => array()
           ,'title'     => 'POS'
        ));
    }
}