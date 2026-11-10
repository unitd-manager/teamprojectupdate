<?
class CP_Admin_Themes_Pos_Model extends CP_Admin_Lib_ThemeModelAbstract
{
    /**
     *
     */
	function getSmartCardLoginSubmit() {
        $login = getCPPluginObj('common_login');
        return $login->model->getLoginSubmit();
    }

    /**
     *
     */
    function getTerminalsByShopJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $shop_id   = $fn->getReqParam('shop_id');

        $json  = array();
        
        if ($shop_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT terminal_id
              ,title
        FROM terminal 
        WHERE shop_id = '{$shop_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['terminal_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

}