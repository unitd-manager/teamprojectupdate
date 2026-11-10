<?
class CP_Admin_Modules_Edukloud_CourseLink_Functions
{
    function setModuleArray($modules){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
    
        $modObj = $modules->getModuleObj('edukloud_courseLink');
        if ($tv['srcRoom'] == 'edukloud_teacher' || $tv['srcRoom'] == 'edukloud_document'){
            $modules->registerModule($modObj, array(
                'tableName' => 'course'
               ,'keyField'  => 'course_id'
            ));
        } else {
            $modules->registerModule($modObj, array(
                'tableName' => 'course_contact'
               ,'keyField'  => 'course_contact_id'
            ));
        }
    }

    /**
     * @param type $record_id
     * @return string
    */
    function beforeDeletePortalHandler($hist_record_id, $linkName){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $hist_record_id);
        $delateSql = "
        DELETE FROM course_contact WHERE course_contact_id = {$hist_record_id}
        ";
        $result = $db->sql_query($delateSql);
        
        $fa = array();
        $fa['order_status'] = 'Cancelled';
        $fn->saveRecord($fa, 'order', 'order_id', $ccRec['order_id']);
    }    
}
