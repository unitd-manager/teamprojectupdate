<?
class CPL_Admin_Modules_Common_Dashboard_View extends CP_Admin_Modules_Common_Dashboard_View
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];

        $hook = getCPModuleHook('common_dashboard', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }
        
        $rows = '';
        foreach($arr as $widgetArr){
            $widget   = $widgetArr['name'];
            $subClass = $widgetArr['subClass'];
            $cssClass = $widgetArr['cssClass'];

            $clsInst = getCPWidgetObj($widget);
            
            $rows .= "
            <div class='{$cssClass}'>
                <div class='{$subClass} widget' id='wd_{$widget}'>
                    {$clsInst->getWidget()}
                </div>
            </div>
            ";
        }
        
        $text = "
        <div id='dashboard' class='subcolumns'>
            {$rows}        
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
        ";
        
        return $text;
    }
}
