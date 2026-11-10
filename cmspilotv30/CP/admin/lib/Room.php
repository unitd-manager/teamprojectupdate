<?
class CP_Admin_Lib_Room {
    var $modulesArr  = array();

    //==================================================================//
    function getTopRooms($seperator = "") {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arr = $cpCfg['cp.topRooms'];

        $rows = "";

        foreach ($arr as $key => $value) {
            $url = "index.php?_topRm={$key}&module={$value['default']}";

            if ($cpCfg['cp.hasAccessModule']) {
                if (!$topRoomsArrAccess[$key]['hasAccess']) {
                    continue;
                }
            }

            if ($tv['topRm'] == $key) {
                $rows .= "
                <li class='active'>
                    <a class='selected nav_{$key}' href='{$url}'><span>{$value['title']}</span></a>
                </li>\n
                ";
            } else {
                $rows .= "
                <li>
                    <a href='{$url}' class='nav_{$key}'><span>{$value['title']}</span></a>
                </li>\n
                ";
            }
        }

        if ($rows != ""){
            $text = "<ul>{$rows}</ul>";
        } else {
            $text = "";
        }

        return $text;
    }

    /**
     *
     */
    function getRooms($seperator = "") {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.topRooms'][$tv['topRm']]['modules'];
        $rows  = '';

        foreach ($arr as $key => $module) {
            if ($cpCfg['cp.hasAccessModule']) {
                $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                if ($hasAccess == 0) {
                    continue;
                }
            }

            $title = $modulesArr[$module]['title'];
            $url   = $modulesArr[$module]['url'];

            if ($tv['module'] == $module) {
                $rows .= "
                <li class='active'>
                    <a class='selected nav_{$module}' href='{$url}'><span>{$title}</span></a>
                </li>\n
                ";
            } else {
                $rows .= "
                <li>
                    <a href='{$url}' class='nav_{$module}'><span>{$title}</span></a>
                </li>\n
                ";
            }
        }

        if ($rows != ''){
            $text = "<ul>{$rows}</ul>";
        } else {
            $text = '';
        }

        return $text;
    }
}

