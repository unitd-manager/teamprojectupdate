<?
class CP_Www_Modules_Edukite_Notice_Functions extends CP_Common_Modules_Edukite_Notice_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_notice');
        $modObj['listLimit'] = 50;
        $modules->registerModule($modObj, array(
             'actBtnsList'   => array('new')
            ,'actBtnsDetail' => array('edit', 'delete')
            ,'actBtnsNew'    => array('cancelNew', 'addNew')
            ,'actBtnsEdit'   => array('save', 'cancel', 'delete')
        ));
    }
    /**
     *
     * @param type $record_id
     * @return string
     */
    function beforeDeleteHandler($record_id){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $hasAccess = true;
        $db = Zend_Registry::get('db');

        if($cpCfg['showAcheivement'] == 1){
            $SQL = "
            DELETE FROM achievement_student
            WHERE notice_id = {$record_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
        }

        if(!$hasAccess){
            $arr = array(
                 'status' => 'error'
                ,'message' => "You don't have access to delete this record"
            );
            return $arr;
        }
    }
}