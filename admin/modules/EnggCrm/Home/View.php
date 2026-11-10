<?
class CPL_Admin_Modules_EnggCrm_Home_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();

        $text = "
        <div class='col-md-12'>
        <div class='panel-group'>
        <div class='row mb20 mt20'>
            <div class='col-md-4 mb20'>
                <div class=''>
                    <div class='panel panel-warning'>
                        <div class='panel-heading'>
                            <a href='/admin/index.php?_topRm=project&module=common_dashboard'>Tender / Project</a>
                        </div>
                        <div class='panel-body'>
                            <div class='col-md-6'><a href='/admin/index.php?_topRm=project&module=common_dashboard'><img class='img-responsive' src='/admin/images/project-management.png'/></a></div>
                            <div class='overallHomeText col-md-6'>
                                {$this->getModuleTitle1('project')}
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>

            <div class='col-md-4 mb20'>
                <div class=''>
                    <div class='panel panel-success'>
                        <div class='panel-heading'>
                            <a href='/admin/index.php?_topRm=finance&module=enggCrm_order'>Finance / Admin / Purchaser</a>
                        </div>
                        <div class='panel-body'>
                            <div class='col-md-6'><a href='/admin/index.php?_topRm=finance&module=enggCrm_order'><img class='img-responsive' src='/admin/images/admin.png'/></a></div>
                            <div class='overallHomeText col-md-6'>
                                {$this->getModuleTitle1('finance')}
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>

           
        </div>
        </div>
        </div>
        ";
        /*<div class='row'>
            <div class='col-md-4 mb20'>
                <div class=''>
                    <div class='panel panel-info'>
                        <div class='panel-heading'>
                            <a href='/admin/index.php?_topRm=admin&module=core_valuelist'>Admin</a>
                        </div>
                        <div class='panel-body'>
                            <div class='col-md-6'><a href='/admin/index.php?_topRm=admin&module=core_valuelist'><img class='img-responsive' src='/admin/images/finance-manage.png'/></a></div>
                            <div class='overallHomeText col-md-6'>
                                {$this->getModuleTitle1('admin')}
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>*/

        return $text;
    }

    /**
     *
     */
    function getModuleTitle($topRm){
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
            $moduleTitle = '';
        //foreach($topRoomsArray as $key => $value) {
            $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];

            foreach($arr as $module) {
                if (array_key_exists($module, $modulesArr)) {
                    $moduleName = $roomsArrayTemp[] = $modulesArr[$module]['name'];
                    $moduleTitle .= "
                    <div class='modHeading'>
                        <div class='modHeadingBg'>{$modulesArr[$moduleName]['title']}</div>
                    </div>
                    ";
                }
            }
        //}

        return $moduleTitle;
    }

    /**
     *
     */
    function getModuleTitleOLD($topRm){
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
            $moduleTitle = '';
        //foreach($topRoomsArray as $key => $value) {
            $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];

            foreach($arr as $module) {
                if (array_key_exists($module, $modulesArr)) {
                    $moduleName = $roomsArrayTemp[] = $modulesArr[$module]['name'];
                    $moduleTitle .= "
                    <div class='homeModuleTitle'><a href='/admin/index.php?_topRm={$topRm}&module={$modulesArr[$module]['name']}'>{$modulesArr[$moduleName]['title']}</a></div>
                    ";
                }
            }
        //}

        return $moduleTitle;
    }

    /**
     *
     */
    function getModuleTitle1($topRm){
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arrTr = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
        $rowsTr  = '';
        $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];
        $rows  = '';
        foreach ($arr as $module) {
            if ($cpCfg['cp.hasAccessModule']) {
                $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                if ($hasAccess == 0) {
                    continue;
                }
            }

            $title = $modulesArr[$module]['title'];
            //$url   = $modulesArr[$module]['url'];
            $url = "index.php?_topRm={$topRm}&module={$module}";

            if($title != 'Home'){
                $rows .= "
                <div class=''><a href='{$url}'><span class='glyphicon glyphicon-play-circle mr10'></span>{$title}</a></div>
                ";
            } 
        }

        $rowsTr .= "
        {$rows}
        ";

        $text = "
        {$rowsTr}
        ";

        return $text;
    }
}