<?
class CP_Admin_Lib_ArrayMaster extends CP_Common_Lib_ArrayMaster
{

    //==================================================================//
    function setTopVars(){
        parent::setTopVars();
        $tv['action'] = getReqParam('_action', 'list');
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    /**
     * The language of the content being edited.
     */
    function getCurrentContentLanguage(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.multiLang'] == '0'){
            $lang = $cpCfg['cp.defaultLanguage'];
        } else {
            $langReq  = $fn->getReqParam('lang');
            $langSess = $fn->getSessionParam('cpAdminCurrContentLang');
            $lang = '';
                        
            if ($langReq != ''){
                $lang = $langReq;
            } else if ($langSess != ''){
                $lang = $langSess;
            } else {
                $lang = $cpCfg['cp.defaultLanguage'];
            }

            if ($lang != '' && !array_key_exists($lang, $cpCfg['cp.availableLanguages'])){
                $lang = $cpCfg['cp.defaultLanguage'];
            }

            $_SESSION['cpAdminCurrContentLang'] = $cpCfg['cp.defaultLanguage'];
        }

        return $lang;
    }

    /**
     * The admin interface language that's being selected
     */
    function getAdminInterfaceLanguage(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $langReq  = $fn->getReqParam('langAdminInt');
        $langSess = $fn->getSessionParam('cpAdminIntLang');
        $lang = '';

        if ($langReq != ''){
            $lang = $langReq;
        } else if ($langSess != ''){
            $lang = $langSess;
        } else {
            $lang = $cpCfg['cp.defaultAdminIntLanguage'];
        }

        //if the lang is not in the adminInterfaceLangs array
        if ($lang != '' && !array_key_exists($lang, $cpCfg['cp.adminInterfaceLangs'])){
            $lang = $cpCfg['cp.defaultAdminIntLanguage'];
        }

        $_SESSION['cpAdminIntLang'] = $lang;

        return $lang;
    }

    //==================================================================//
    function getFirstTopRoom(){
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $arr = $cpCfg['cp.topRooms'];

        foreach($arr as $key => $value){
            return $key;
        }
    }

    /**
     *
     */
    function setArrays() {
        parent::setArrays();
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.hasAccessModule']){
            $this->setRoomsArrayAccess();
        }
        
        $tv = Zend_Registry::get('tv');
    }

    /**
     *
     */
    function setRoomsArrayAccess() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID'] : 0;

        $SQL = "
        SELECT a.room_name
              ,a.title
              ,b.list
              ,b.detail
              ,b.new
              ,b.edit
              ,b.delete
              ,a.publish
              ,a.unpublish
              ,a.room_id
              ,a.import
              ,a.export
        FROM mod_acc_room a
        JOIN mod_acc_room_user_group b ON (a.room_id = b.room_id)
        WHERE b.user_group_id = {$user_group_id}
        ORDER BY a.title
        ";
        
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $modulesArrAccess = array();
        
        while ($row = $db->sql_fetchrow($result)) {
            $arr2 = &$modulesArrAccess[$row['room_name']];
            $arr2['list']      = $row['list'];
            $arr2['detail']    = $row['detail'];
            $arr2['new']       = $row['new'];
            $arr2['edit']      = $row['edit'];
            $arr2['delete']    = $row['delete'];
            $arr2['publish']   = $row['publish'];
            $arr2['unpublish'] = $row['unpublish'];
            $arr2['import']    = $row['import'];
            $arr2['export']    = $row['export'];

            $arr2['hasAccess'] = 1;
            if ($arr2['list'] != 1 && $arr2['detail'] != 1 && $arr2['new'] != 1 &&
                $arr2['edit'] != 1 && $arr2['delete'] != 1) {
                $arr2['hasAccess'] = 0;
            }
        }

        //------------------------------//
        $topRoomsArray = $cpCfg['cp.topRooms'];
        $topRoomsArrAccess = array();
        
        foreach ($topRoomsArray as $topRoomName => $topRoomArr) {
            $topRoomsArrAccess[$topRoomName]['hasAccess'] = false;
            $modules = $topRoomArr['modules'];
            foreach ($modulesArrAccess as $module => $arrTemp) {
                if ($arrTemp['hasAccess'] == 1) {
                    if (in_array($module, $modules)) {
                        $topRoomsArrAccess[$topRoomName]['hasAccess'] = true;
                        break;
                    }
                }
            }
        }

        Zend_Registry::set('modulesArrAccess', $modulesArrAccess);
        Zend_Registry::set('topRoomsArrAccess', $topRoomsArrAccess);

        $tv['topRm'] = getReqParam('_topRm', $this->getFirstTopRoomModAccess(), true);
        $tv['module'] = getReqParam('module', $this->getFirstRoomInTopRoomModAccess($tv['topRm']), true);
        //ex: $tv['module'] is account_journalMaster
        list($tv['moduleGroup']) = explode('_', $tv['module']);

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    function getFirstTopRoomModAccess(){
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');
        $modulesArrAccess = Zend_Registry::get('modulesArrAccess');

        foreach ($topRoomsArrAccess as $topRmName => $topRmArr) {
            if ($topRmArr['hasAccess']) {
                return $topRmName;
            }
        }
    }

    function getFirstRoomInTopRoomModAccess($topRoom){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        
        if ($tv['spAction'] == '' && $topRoom != ''){
            $topRoomsArray = $cpCfg['cp.topRooms'];
            $modulesArrAccess = Zend_Registry::get('modulesArrAccess');

            $module = '';
            foreach ($topRoomsArray[$topRoom]["modules"] as $module) {
                $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                if ($hasAccess) {
                    return $module;
                }
            }

        } else {
            return '';
        }

    }
}