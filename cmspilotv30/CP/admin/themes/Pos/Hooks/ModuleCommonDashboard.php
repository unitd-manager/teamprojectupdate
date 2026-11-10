<?
class CP_Admin_Themes_Pos_Hooks_ModuleCommonDashboard
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];
        $modulesArr = Zend_Registry::get('modulesArr');

        $widgets = '';
        foreach($arr as $widgetArr){
            $widget   = $widgetArr['name'];
            $subClass = $widgetArr['subClass'];
            $cssClass = $widgetArr['cssClass'];

            $clsInst = getCPWidgetObj($widget);
            
            $widgets .= "
            <div class='{$cssClass}'>
                <div class='{$subClass} widget' id='wd_{$widget}'>
                    {$clsInst->getWidget()}
                </div>
            </div>
            ";
        }

        $rows = '';
        $arr = $cpCfg['cp.topRooms'];
        $subcol = 100 / count($arr);
        $subcol = 25;
        
        foreach ($arr as $key => $value) {

            if ($cpCfg['cp.hasAccessModule']) {
                $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');
                if (!$topRoomsArrAccess[$key]['hasAccess']) {
                    continue;
                }
            }
            
            $rooms = '';
            $roomsArr = $cpCfg['cp.topRooms'][$key]['modules'];

            foreach ($roomsArr as $modKey => $module) {
                if ($cpCfg['cp.hasAccessModule']) {
                    $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                    $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                    if ($hasAccess == 0) {
                        continue;
                    }
                }

                $title = $modulesArr[$module]['title'];
                $url = "index.php?_topRm={$key}&module={$module}";
                
                $rooms .= "
                <li>
                    <a href='{$url}' class='nav_{$module}'><span>{$title}</span></a>
                </li>\n
                ";
            }            

            $rows .= "
            <div class='roomsWrapper'>
                <div class='hlist noBg'>
                    <ul>
                        <li class='title'>{$value['title']}</li>
                        {$rooms}
                    </ul>
                </div>
            </div>
            ";
        }
        
        $text = "
        <div id='dashboard'>
            {$rows}
            {$widgets}
        </div>
        ";
        
        return $text;
    }

    function getLeftPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $text = "
        <div class='profilePic'>
            {$media->getMediaPicture('core_staff', 'picture', $_SESSION['staff_id'], array('folder' => 'normal'))}
        </div>
        <h2 class='quickLinks'>Quick Links</h2>
        ";
        
        return $text;
    }
}