<?
class CP_Admin_Modules_Labsg_Home_View extends CP_Common_Lib_ModuleViewAbstract
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
        <div class='subcolumns homePageSummary'>
            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=main&module=labsg_company'><img src='images/pm.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=main&module=labsg_company'>Patient Management</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('main')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=utils&module=labsg_contact'><img src='images/utils.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=utils&module=labsg_contact'>Utils</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('utils')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=finance&module=labsg_contact'><img src='images/finance.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=finance&module=labsg_contact'>Finance</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('finance')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=admin&module=core_translation'><img src='images/admin.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=admin&module=core_translation'>Admin</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('admin')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=reports&module=labsg_contact'><img src='images/reports.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=reports&module=labsg_contact'>Reports Module</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('reports')}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        ";

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
    function getModuleTitle1($topRm){
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
}