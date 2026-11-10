<?
class CP_Admin_Modules_Edukloud_OrderLink_Functions
{
    function setModuleArray($modules){
    
        $modObj = $modules->getModuleObj('edukloud_orderLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'order'
           ,'keyField'  => 'order_id'
        ));
    }

    /**
     * @param type $record_id
     * @return string
    */
    function beforeDeletePortalHandler($hist_record_id, $linkName){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $delateSql = "
        DELETE FROM course_contact WHERE order_id =  {$hist_record_id}
        ";
        $result = $db->sql_query($delateSql);
        
        $fa = array();
        $fa['order_status'] = 'Cancelled';
        $fn->saveRecord($fa, 'order', 'order_id', $hist_record_id);
    }    
}
