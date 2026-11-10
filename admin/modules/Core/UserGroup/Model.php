<?
class CPL_Admin_Modules_Core_UserGroup_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT ug.*
        FROM {$cpCfg['cp.modAccessUserGroupTable']} ug
        ";


        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'ug';

        $user_group_id = $fn->getReqParam('user_group_id');

        if ($user_group_id != '' ) {
            $searchVar->sqlSearchVar[] = "ug.user_group_id = '{$user_group_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "ug.user_group_id = '{$tv['record_id']}'";
        } else {

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        ug.title LIKE '%{$tv['keyword']}%'
                                      )";
            }

            $searchVar->sortOrder = "ug.title";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $id = $fn->addRecord($fa);

        $this->getCreateAccessRecords();

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $this->_updateRoomUserGroupHistory($id);
        if($cpCfg['cp.hasAccessModuleOtherAction'] == 1) {
            $this->_updateOtherActionUserGroupHistory($id);
        }

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'user_group_type');

        return $fa;
    }

    //========================================================//
    //========================================================//
    private function _createRoomUserGroupHistory($user_group_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        
        $SQL = "SELECT a.room_id FROM mod_acc_room a ORDER BY a.title";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['room_id']       = $row['room_id'];
            $fa['user_group_id'] = $user_group_id;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_room_user_group");
            $result2 = $db->sql_query($SQL);
        }
        $this->_updateRoomUserGroupHistory($user_group_id);
    }

    /**
     */
    private function _updateRoomUserGroupHistory($user_group_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        
        foreach ($_POST as $fieldName => $value) {
            if (substr($fieldName, 0, 5) == "room_") {
                $room_id = substr($fieldName, 5);
                $list      = isset($_POST['chk_list_' . $room_id])      ? $_POST['chk_list_' . $room_id]      : 0;
                $detail    = isset($_POST['chk_detail_' . $room_id])    ? $_POST['chk_detail_' . $room_id]    : 0;
                $new       = isset($_POST['chk_new_' . $room_id])       ? $_POST['chk_new_' . $room_id]       : 0;
                $edit      = isset($_POST['chk_edit_' . $room_id])      ? $_POST['chk_edit_' . $room_id]      : 0;
                $delete    = isset($_POST['chk_delete_' . $room_id])    ? $_POST['chk_delete_' . $room_id]    : 0;
                $publish   = isset($_POST['chk_publish_' . $room_id])   ? $_POST['chk_publish_' . $room_id]   : 0;
                $unpublish = isset($_POST['chk_unpublish_' . $room_id]) ? $_POST['chk_unpublish_' . $room_id] : 0;
                $export    = isset($_POST['chk_export_' . $room_id])    ? $_POST['chk_export_' . $room_id]   : 0;
                $import    = isset($_POST['chk_import_' . $room_id])    ? $_POST['chk_import_' . $room_id] : 0;

                $fa = array();
                $fa['room_id']       = $room_id;
                $fa['user_group_id'] = $user_group_id;
                $fa['list']          = $list;
                $fa['detail']        = $detail;
                $fa['new']           = $new;
                $fa['edit']          = $edit;
                $fa['delete']        = $delete;
                $fa['publish']       = $publish;
                $fa['unpublish']     = $unpublish;
                $fa['export']        = $export;
                $fa['import']        = $import;
                $fa['modification_date'] = date("Y-m-d H:i:s");
                
                $whereCondition = "WHERE user_group_id = {$user_group_id} AND room_id = {$room_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_room_user_group", $whereCondition);
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     * update room user records
     * @param <type> $user_group_id 
     */
    private function _createOtherActionUserGroupHistory($user_group_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        
        $SQL = "SELECT a.other_action_id FROM mod_acc_other_action a ORDER BY a.title";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['other_action_id']   = $row['other_action_id'];
            $fa['user_group_id']     = $user_group_id;
            $fa['creation_date']     = date("Y-m-d H:i:s");
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_user_group_other_action");
            $result2 = $db->sql_query($SQL);
        }
        $this->_updateOtherActionUserGroupHistory($user_group_id);

    }

    /**
     *
     * update room user records
     * @param <type> $user_group_id
     */
    private function _updateOtherActionUserGroupHistory($user_group_id) {
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        foreach ($_POST as $fieldName => $value) {
            if (substr($fieldName, 0, 17) == "other_act_access_") {
                $other_action_id = substr($fieldName, 17);
                $has_access      = isset($_POST[$fieldName]) ? $_POST[$fieldName] : 0;

                $fa = array();
                $fa['other_action_id'] = $other_action_id;
                $fa['user_group_id']   = $user_group_id;
                $fa['has_access']      = $has_access;
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $whereCondition = "WHERE user_group_id = {$user_group_id} AND other_action_id = {$other_action_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_user_group_other_action", $whereCondition);
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=admin&module=core_userGroup&_spAction=createAccessRecords
     */
    function getCreateAccessRecords() {
        $cpCfg          = Zend_Registry::get('cpCfg');
        $modulesArr     = Zend_Registry::get('modulesArr');
        $widgetsArr     = Zend_Registry::get('widgetsArr');
        $topRoomsArray  = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();

        //*** clean up $roomsArray. Only get the rooms which are used. Remove not used ones
        foreach($topRoomsArray as $key => $value) {
            $arr = $cpCfg['cp.topRooms'][$key]['modules'];

            foreach($arr as $module) {
                if (array_key_exists($module, $modulesArr)) {
                    $roomsArrayTemp[] = $modulesArr[$module]['name'];
                }
            }
        }

        //print "<h3>removing deleted rooms...</h3>";
        $this->_mod_acc_deleteRemovedRooms($roomsArrayTemp);

        //print "<h3>create rooms...</h3>";
        $this->_mod_acc_createRoomRecords($roomsArrayTemp);

        $widgetArrayTemp = array();
        $widgetArray = $cpCfg['cp.availableWidgets'];
        foreach($widgetArray as $widget) {
            if (array_key_exists($widget, $widgetsArr)) {
                $widgetArrayTemp[] = $widgetsArr[$widget]['name'];
            }
        }

        //print "<h3>removing deleted rooms...</h3>";
        $this->_mod_acc_deleteRemovedWidget($widgetArrayTemp);

        //print "<h3>create rooms...</h3>";
        $this->_mod_acc_createWidgetRecords($widgetArrayTemp);

        //print "<h3>create user group / room history...</h3>";
        $this->_mod_acc_createRoomUserGroupRecord();

        //print "<h3>removing deleted other actions...</h3>";
        $this->_mod_acc_deleteRemovedOtherActions();

        //print "<h3>create other actions...</h3>";
        $this->_mod_acc_createOtherActionRecords($roomsArrayTemp);

        //print "<h3>create other action / room history...</h3>";
        $this->_mod_acc_createOtherActionUserGroupRecord();

        //print "<h2>process done.</h2>";
    }

    /**
     * delete removed room records (that were created earlier)
     */
    function _mod_acc_deleteRemovedRooms($roomsArray) {
        $db = Zend_Registry::get('db');
        $SQL = "SELECT * FROM mod_acc_room WHERE room_type = 'module'";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            //*** if the room does not exist in the $roomsArray then delete the corresponding records create earlier
            if (!in_array($row['room_name'], $roomsArray)) {
                //*** delete room/user group history records
                $SQL    = "DELETE FROM mod_acc_room_user_group WHERE room_id = {$row['room_id']}";
                $result2 = $db->sql_query($SQL);

                //*** delete room record itself
                $SQL    = "DELETE FROM mod_acc_room WHERE room_id = {$row['room_id']}";
                $result2 = $db->sql_query($SQL);
            }
        }
    }


    /**
     * create room records
     */
    function _mod_acc_createRoomRecords($roomsArray) {
        $db         = Zend_Registry::get('db');
        $dbUtil     = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        foreach ($roomsArray as $module) { //*** foreach 1
            $moduleArr = $modulesArr[$module];

            $SQL = "
            SELECT *
            FROM mod_acc_room
            WHERE room_name ='{$module}'
              AND room_type = 'module'
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            
            $actions = array_merge($moduleArr['actBtnsList'], $moduleArr['actBtnsDetail'], 
                                   $moduleArr['actBtnsEdit'], $moduleArr['actBtnsNew']);

            $fa = array();
            $fa['room_name']     = $moduleArr['name'];
            $fa['title']         = $moduleArr['title'];
            $fa['room_type']     = 'module';

            $fa['list']          = 1;
            $fa['detail']        = $moduleArr['hasOnlyListView'] ? 0 : 1;
            $fa['new']           = in_array('new', $actions) ? 1 : 0;
            $fa['edit']          = in_array('edit', $actions) ? 1 : 0;
            $fa['delete']        = in_array('delete', $actions) ? 1 : 0;

            $fa['publish']       = in_array('publish', $actions) ? 1 : 0;
            $fa['unpublish']     = in_array('unPublish', $actions) ? 1 : 0;
            $fa['print']         = in_array('print', $actions) ? 1 : 0;
            $fa['import']        = in_array('import', $actions) ? 1 : 0;
            $fa['export']        = in_array('export', $actions) ? 1 : 0;

            if ($numRows == 0) {
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_room");
                $result2 = $db->sql_query($SQL);

            } else {
                $row = $db->sql_fetchrow($result);
                $fa['modification_date'] = date("Y-m-d H:i:s");
                $whereCondition = "WHERE room_id = {$row['room_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_room", $whereCondition);
                $result2 = $db->sql_query($SQL);
            }

        } //*** foreach 1

    }

    /**
     * creates the room/user group history records
     */
    function _mod_acc_createRoomUserGroupRecord() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "SELECT * FROM mod_acc_room a";
        $result = $db->sql_query($SQL);
        while ($rowRm = $db->sql_fetchrow($result)) {
            $SQL = "SELECT * FROM user_group a";
            $resultUg = $db->sql_query($SQL);

            while ($rowUg = $db->sql_fetchrow($resultUg)) {
                $room_user_group_id = 0;

                $SQL = "
                SELECT * FROM mod_acc_room_user_group a
                WHERE a.room_id = {$rowRm['room_id']}
                  AND a.user_group_id = {$rowUg['user_group_id']}
                ";
                $resultRug = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($resultRug);
                $rowRug = "";
                if ($numRows == 0) {
                    $fa = array();
                    $fa['room_id']       = $rowRm['room_id'];
                    $fa['user_group_id'] = $rowUg['user_group_id'];
                    $fa['creation_date'] = date("Y-m-d H:i:s");

                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_room_user_group");
                    $resultTemp = $db->sql_query($SQL);
                    $room_user_group_id = $db->sql_nextid();

                    $SQL        = "SELECT * FROM mod_acc_room_user_group a WHERE a.room_user_group_id = {$room_user_group_id}";
                    $resultTemp = $db->sql_query($SQL);
                    $rowRug = $db->sql_fetchrow($resultTemp);
                } else {
                    $rowRug = $db->sql_fetchrow($resultRug);
                }
                $room_user_group_id = $rowRug['room_user_group_id'];

                if ($rowUg['user_group_id'] == $cpCfg['cp.superAdminUGId']) {
                    $fa = array();
                    $fa['list']      = $rowRm['list'];
                    $fa['detail']    = $rowRm['detail'];
                    $fa['new']       = $rowRm['new'];
                    $fa['edit']      = $rowRm['edit'];
                    $fa['delete']    = $rowRm['delete'];
                    $fa['publish']   = $rowRm['publish'];
                    $fa['unpublish'] = $rowRm['unpublish'];
                    $fa['print']     = $rowRm['print'];
                    $fa['import']    = $rowRm['import'];
                    $fa['export']    = $rowRm['export'];
                }
                else {
                    $fa = array();
                    $fa['list']    = $rowRug['list']    ? $rowRm['list']    : $rowRug['list'];
                    $fa['detail']  = $rowRug['detail']  ? $rowRm['detail']  : $rowRug['detail'];
                    $fa['new']     = $rowRug['new']     ? $rowRm['new']     : $rowRug['new'];
                    $fa['edit']    = $rowRug['edit']    ? $rowRm['edit']    : $rowRug['edit'];
                    $fa['delete']  = $rowRug['delete']  ? $rowRm['delete']  : $rowRug['delete'];
                    $fa['publish'] = $rowRug['publish'] ? $rowRm['publish'] : $rowRug['publish'];
                    $fa['print']   = $rowRug['print']   ? $rowRm['print']   : $rowRug['print'];
                    $fa['import']  = $rowRug['import']  ? $rowRm['import']  : $rowRug['import'];
                    $fa['export']  = $rowRug['export']  ? $rowRm['export']  : $rowRug['export'];
                }
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $whereCondition = "WHERE room_user_group_id = {$room_user_group_id}";
                $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_room_user_group", $whereCondition);
                $resultTemp     = $db->sql_query($SQL);
            }
        }

    }

    /**
     * delete removed other actions (that were created earlier)
     */
    function _mod_acc_deleteRemovedOtherActions() {
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');
            
        $SQL = "SELECT * FROM mod_acc_room WHERE room_type = 'module'";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $module = $row['room_name'];
            $otherActions = $modulesArr[$module]['otherActions'];

            $SQL = "
            SELECT * FROM mod_acc_other_action
            WHERE room_id = {$row['room_id']}
            ";
            $resultOtherAct = $db->sql_query($SQL);
            while ($rowOtherAct = $db->sql_fetchrow($resultOtherAct)) {
                if (array_key_exists($rowOtherAct['name'], $otherActions) == false) {
                    //*** delete other action record
                    $SQL    = "DELETE FROM mod_acc_other_action
                               WHERE other_action_id = {$rowOtherAct['other_action_id']}";
                    $result2 = $db->sql_query($SQL);

                    //*** delete user group other action records
                    $SQL    = "DELETE FROM mod_acc_user_group_other_action
                               WHERE other_action_id ={$rowOtherAct['other_action_id']}";
                    $result2 = $db->sql_query($SQL);
                }
            }
        }
    }

    //----------------------------------------------//
    function _mod_acc_createOtherActionRecords($roomsArray) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        $actionButArray = Zend_Registry::get('actionButArray');
        
        foreach ($roomsArray as $module) { //*** foreach 1
            $otherActions = $modulesArr[$module]['otherActions'];

            $SQL = "
            SELECT * FROM mod_acc_room
            WHERE room_name = '{$module}'
            AND room_type = 'module'
            ";
            $result = $db->sql_query($SQL);
            $rowRm = $db->sql_fetchrow($result);

            foreach ($otherActions as $actionName) {  //*** foreach 2
                $SQL = "
                SELECT * 
                FROM mod_acc_other_action
                WHERE name = '{$actionName}'
                AND room_id = {$rowRm['room_id']}
                ";
                $result = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($result);
                $actionArr = $actionButArray[$actionName];
                
                if ($numRows == 0) {
                    $fa = array();
                    $fa['name']          = $actionArr['name'];
                    $fa['title']         = $actionArr['title'];
                    $fa['room_id']       = $rowRm['room_id'];
                    $fa['creation_date'] = date("Y-m-d H:i:s");

                    //-----------------------------------------------------------------------//
                    $SQL = $dbUtilCommon->getInsertSQLStringFromArray($fa, "mod_acc_other_action");
                    $result2 = $db->sql_query($SQL);
                }

            }  //*** foreach 2

        } //*** foreach 1

    }

    /**
     * creates the other action/user group history records
     */
    function _mod_acc_createOtherActionUserGroupRecord() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "SELECT * FROM mod_acc_other_action a";
        $result = $db->sql_query($SQL);

        while ($rowOtherAct = $db->sql_fetchrow($result)) {
            $SQL = "SELECT * FROM mod_acc_user_group a";
            $resultUg = $db->sql_query($SQL);
            while ($rowUg = $db->sql_fetchrow($resultUg)) {
                $room_user_group_id = 0;

                $SQL = "
                SELECT * 
                FROM mod_acc_user_group_other_action a
                WHERE a.other_action_id = {$rowOtherAct['other_action_id']}
                  AND a.user_group_id = {$rowUg['user_group_id']}
                ";
                $resultUGOA = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($resultUGOA);
                $rowRug = "";
                if ($numRows == 0) {
                    $fa = array();
                    $fa['other_action_id'] = $rowOtherAct['other_action_id'];
                    $fa['user_group_id'] = $rowUg['user_group_id'];
                    $fa['creation_date'] = date("Y-m-d H:i:s");

                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_user_group_other_action");
                    $resultTemp = $db->sql_query($SQL);
                    $user_group_other_action_id = $db->sql_nextid();

                    $SQL = "SELECT * FROM mod_acc_user_group_other_action a
                            WHERE a.user_group_other_action_id = {$user_group_other_action_id}";
                    $resultTemp = $db->sql_query($SQL);
                    $rowUGOA = $db->sql_fetchrow($resultTemp);
                } else {
                    $rowUGOA = $db->sql_fetchrow($resultUGOA);
                }
                $user_group_other_action_id = $rowUGOA['user_group_other_action_id'];

                if (strtolower($rowUg['title']) == "administrator") {
                    $fa = array();
                    $fa['has_access'] = "1";
                } else {
                    $fa = array();
                    $fa['has_access'] = "0";
                }

                $fa['modification_date'] = date("Y-m-d H:i:s");

                $whereCondition = "WHERE user_group_other_action_id = {$user_group_other_action_id}";
                $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_user_group_other_action", $whereCondition);
                $resultTemp     = $db->sql_query($SQL);
            }
        }
    }

    /**
     * 
     */
    function hasAccessToAction($module, $action) {
        $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
        $hasAcess = isset($modulesArrAccess[$module][$action]) ? $modulesArrAccess[$module][$action] : false;
        return $hasAcess;
    }

    /**
     * delete removed room records (that were created earlier)
     */
    function _mod_acc_deleteRemovedWidget($widgetArrayTemp) {
        $db = Zend_Registry::get('db');
        $SQL = "SELECT * FROM mod_acc_room WHERE room_type = 'widget'";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            //*** if the room does not exist in the $widgetArrayTemp then delete the corresponding records create earlier
            if (!in_array($row['room_name'], $widgetArrayTemp)) {
                //*** delete room/user group history records
                $SQL    = "DELETE FROM mod_acc_room_user_group WHERE room_id = {$row['room_id']}";
                $result2 = $db->sql_query($SQL);

                //*** delete room record itself
                $SQL    = "DELETE FROM mod_acc_room WHERE room_id = {$row['room_id']}";
                $result2 = $db->sql_query($SQL);
            }
        }
    }

    /**
     * create room records
     */
    function _mod_acc_createWidgetRecords($widgetArrayTemp) {
        $db         = Zend_Registry::get('db');
        $dbUtil     = Zend_Registry::get('dbUtil');
        $widgetsArr = Zend_Registry::get('widgetsArr');

        foreach ($widgetArrayTemp as $widget) { //*** foreach 1
            $widgetArr = $widgetsArr[$widget];

            $SQL = "
            SELECT * FROM mod_acc_room
            WHERE room_name = '{$widget}'
              AND room_type = 'widget'
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            
            $fa = array();
            $fa['room_name'] = $widgetArr['name'];
            $fa['title']     = $widgetArr['title'];
            $fa['room_type'] = 'widget';
            $fa['list']      = 1;

            if ($numRows == 0) {
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, "mod_acc_room");
                $result2 = $db->sql_query($SQL);

            } else {
                $row = $db->sql_fetchrow($result);
                $fa['modification_date'] = date("Y-m-d H:i:s");
                $whereCondition = "WHERE room_id = {$row['room_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "mod_acc_room", $whereCondition);
                $result2 = $db->sql_query($SQL);
            }

        } //*** foreach 1
    }
}
