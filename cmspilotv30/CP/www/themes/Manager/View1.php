<?
class CP_Www_Themes_Manager_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqForm-2.69', 'jqUploadify3.2');
    /**
     *
     */
    function getHeaderPanel(){
        return;
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $pLogin = getCPPluginObj('member_login');
        $mainNav = getCPWidgetObj('core_mainNav');

        $text = "
        {$pLogin->view->getLoginInfoText()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $mainNav = Zend_Registry::get('mainNav');

        $text = "
        <!--<nav id='nav'>
            <a id='navigation' name='navigation'></a>
            {$mainNav->getWidget(array(
                 'btnPos' => 'Top'
            ))}
        </nav>-->
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel2() {
        $tv         = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');
        $cpUrl = Zend_Registry::get('cpUrl');

        $searchText = '';
        $actBtns = '';

        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        $goToList = $cpUrl->getUrlBySecType($tv['secType']);
        if ($tv['action'] == 'noticeOptions') {
            $actBtns = "
            <div>
                <a href='{$goToList}' class='cpBack'></a>
            </div>
            ";
        }

        $text = "
        <div class='navPanel'>
            {$searchText}
        </div>
        {$actBtns}
        ";

        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList
        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                     'clsName' => $clsName
                    ,'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $text = "
        <div class='bodyPanel'>
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

        $btns = '';
        $leftDefaultContent = '';

        if (method_exists($clsInst, 'getLeftPanel')) {
            $btns = $clsInst->getLeftPanel();
        }

        //TO SHOW DEFAULT CONTENT IN THE LEFT PANEL FOR ALL MODULES
        if (method_exists($clsInst, 'getLeftPanelDefaultContent') &&  $tv['action']== 'edit') {
            $leftDefaultContent = $clsInst->getLeftPanelDefaultContent();
        }

        $text = "
        <div class='leftInfoTitle'>
        {$btns}
        </div>
        <div class='leftInfoList'>
            {$leftDefaultContent}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');
        $clsInst = Zend_Registry::get('currentModule');
        $cpCfg = Zend_Registry::get('cpCfg');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        $rightPanelDisplay = '';
        $rightPanelDefaultContent = '';

        //TO SHOW DEFAULT CONTENT IN THE RIGHT PANEL FOR ALL MODULES
        if (method_exists($clsInst, 'getRightPanelDefaultContent') &&  $tv['action']== 'edit') {
            $rightPanelDefaultContent = $clsInst->getRightPanelDefaultContent();
        }

        $activityGroupBtn = '';
        if ($cpCfg['showAcheivement'] == 1){
            if ($tv['module'] == 'edukite_achievement') {
                $activityGroupBtn = "
                <div class='achivementGroup'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-group.png'>
                </div>
                ";
            }
        }

        if ($tv['module'] == 'edukite_subject'){
            $rightPanelDisplay = '';
        } else if ($tv['action']== 'edit'){
            $noticeReadSummary = '';
            //if ($cpCfg['cp.noticeReadSummary'] == 1){
            if($tv['module'] == 'edukite_notice'){
                if($tv['record_id'] != ''){
                    $notice_id = $tv['record_id'];
                } else {
                    $notice_id  = $fn->getReqParam('notice_id');
                }
                $noticeReadSummary = "
                <div class='noticeread summaryLinkbutton'>
                    <a href='#' class='noticeRead' notice_id='{$notice_id}'>Notice Read Summary</a>
                </div>
                ";
            }
            //}

            $rightPanelDisplay = "
            <div class='rightInfoTitle'>
                {$activityGroupBtn}
                {$noticeReadSummary}
                <div class='classLinkedImg'></div>
                <div class='childLinkedImg'></div>
                <div class='staffLinkedImg'></div>
                <div class='parentLinkedImg'></div>
                <div class='cohertLinkedImg'></div>
            </div>
            <div class='rightInfoList'>{$rightPanelDefaultContent}</div>
            ";
        }

        if (method_exists($modObj->view, 'getRightPanel')) {
            $text = $modObj->view->getRightPanel();
        } else {
            $text = "
            {$rightPanelDisplay}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMainThemeOutput() {
        //body panel must be in the top since (in twopresents) there are some variables
        //set in here which is re-used in the left panel
        $bodyPanel       = $this->getBodyPanel();
        $headerPanel     = $this->getHeaderPanel();
        $navPanel        = $this->getNavPanel();
        $navPanel2       = '';
        $leftPanel       = $this->getLeftPanel();
        $rightPanel      = $this->getRightPanel();
        $mainBottomPanel = $this->getMainBottomPanel();
        $footerPanel     = $this->getFooterPanel();
        $extendedPanel   = $this->getExtendedPanel();
        $lastPanel       = $this->getLastPanelOutsideTemplate();

        $tv              = Zend_Registry::get('tv');
        $cpCfg           = Zend_Registry::get('cpCfg');
        $viewHelper      = Zend_Registry::get('viewHelper');
        $pageCSSClass    = $viewHelper->getPageCSSClass();
        $mainNav         = getCPWidgetObj('core_mainNav');
        $pLogin          = getCPPluginObj('member_login');
        $fn              = Zend_Registry::get('fn');
        $topNav          = getCPWidgetObj('core_mainNav');
        $cpUrl           = Zend_Registry::get('cpUrl');
        $pager           = Zend_Registry::get('pager');

        $banner          = $this->getBannerPanel();

        $bannerInCol3 = '';
        //to show banner for respective modules
        if ($cpCfg['cp.showBannerInCol3Top'] && $tv['secType'] != 'Home'){
            $wBanner = getCPWidgetObj('media_banner');
            $bannerInCol3 = $wBanner->getWidget();
        }

        if (method_exists($this, 'getNavPanel2')) {
            $navPanel2 = $this->getNavPanel2();
            if ($navPanel2 != '') {
                $navPanel2 = "
                <nav id='nav2'>
                    {$navPanel2}
                    {$this->getNavButtons(10)}
                </nav>
                ";
            }
        }

        $footerText = "
        <footer id='footer' class='clearfix ym-clearfix'>
            {$footerPanel}
        </footer>
        ";

        if($cpCfg['cp.fullWidthTemplte']){;
            $footerText = "
            <footer id='footer'>
                <a id='navigation' name='navigation'></a>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            ";
        }

        $navInsideHeader = '';
        $navOutsideHeader = '';
        if($cpCfg['cp.placeNavInsideHeaderTag']){
            $navInsideHeader = $navPanel;
        } else {
            $navOutsideHeader = $navPanel;
        }

        $footerInside = '';
        $footerOutside = '';
        if($cpCfg['cp.placeFooterOutsidePageTag']){
            $footerOutside = $footerText;
        } else {
            $footerInside = $footerText;
        }

        if ($mainBottomPanel != ''){
            $mainBottomPanel = "
            <div class='mainBottom'>
                {$mainBottomPanel}
            </div>
            ";
        }

        $teacherRole = '';
        $class = '';
        $classSave = '';
        $sessionLoginType = isset($_SESSION['cpLoginTypeWWW']) ? $_SESSION['cpLoginTypeWWW']  : '';
        if($sessionLoginType == 'edukite_teacher'){
            $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
            $teacherRole = $teacherRec['role'];
            if($teacherRole == 'Teacher'){
                $class = 'newBtnMargin';
                $classSave = 'saveBtnMargin';
            }
        }

        $logoLink = $this->getLogoLink();
        $newUrl = "new/";
        $goToList = $cpUrl->getUrlBySecType($tv['secType']);

        if($tv['module'] == 'edukite_notice') {
            $newUrl = $cpUrl->getUriWithNoQstr() . '?_action=noticeOptions';
        }

        $saveStudentUrl = "save/";
        $deleteStudentUrl = "javascript:Actions.deleteRecord('edukite_student')";
        $listUrl = "cancel/";

        $sessionTeacherType = isset($_SESSION['cpContactId']) ? $_SESSION['cpContactId']  : '';
        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];
        $urlArray['sitePfxId'] = $sessionTeacherType;
        $kiteUrl = $cpUrl->make_seo_url($urlArray);
        $kiteUrl = $kiteUrl.'?teacherKite=1';
        $hostName   = $_SERVER['HTTP_HOST'];

        $actBtns= '';
        $goKite= '';
        $teacherKiteIcon = '';
        //to show action buttons in list and edit.
        if ($tv['action']== 'list'){
            $actBtns= "
            <div class='btn_new {$class}'><a href='{$newUrl}'>New</a></div>
            ";

            //if(strpos($hostName, 'tss') !== false || strpos($hostName, 'essing') !== false){
                $teacherKiteIcon= "
                <div class='teacherKiteIcon'>
                    <a href='{$kiteUrl}' class='teacherkiteIcon'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/teacher-kite-icon.png'></a>
                </div>
                ";
            //}
            /*else{
                $teacherKiteIcon= "
                <div class='teacherKiteIcon'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/teacher-kite-icon.png'>
                </div>
                ";
            }*/

        } else if ($tv['action']== 'edit')  {
            $actBtns= "
            <div>
                <a href='{$goToList}' class='cpBack'></a>
            </div>
            <div class='floatbox ym-contain-dt mt5'>
                <div class='float_left'>
                    <div class='btn_save'>
                        <a href='javascript:void(0);' id='btnSaveRecord'></a>
                    </div>
                </div>
                <div class='float_left'>
                    <div class='btn_delete'>
                        <a href=\"javascript:Actions.deleteRecord('{$tv['module']}')\"'></a>
                    </div>
                </div>
            </div>
            ";

        } else if ($tv['action']== 'new')  {
            $actBtns= "
            <div class='floatbox ym-contain-dt'>
                <div class='float_left'>
                    <div class='btn_save {$classSave}'><a href='javascript:void(0);' id='btnAddRecord'></a></div>
                </div>
                <!--
                <div class='float_left'>
                    <div class='btn_cancel'><a href='javascript:history.back()'></a></div>
                </div>
                -->
            </div>
            <div>
                <a href='{$goToList}' class='cpBack'></a>
            </div>
            ";
        }

        if ($tv['action']== 'edit' ||
            $tv['action']== 'noticeOptions' ||
            $teacherRole == 'Teacher' ||
            $tv['action']== 'achievementPanel' ||
            $tv['action']== 'achievementOptions') {
            $topMostBtn = "";
        } else {
            $topMostBtn ="
            {$topNav->getWidget(array(
                  'btnPos'  => 'Top Most'
                 ,'class'   => 'topMost hlist'
            ))}
            ";
        }

        $student_id = $fn->getReqParam('student_id');
        $listPanelTitle= '';
        $editPanelTitle= '';
        $achievementListTitle ='';
        if ($tv['module'] == 'edukite_student' && $tv['action']== 'list'){
            $listPanelTitle= "
            <div class='listPanelTitle'>Viewing : Student List</div>
            ";
        } else if ($tv['module'] == 'edukite_notice' && $tv['action']== 'list') {
            $listPanelTitle= "
            <div class='listPanelTitle'>Viewing : Notice List</div>
            ";
        } else if ($tv['module'] == 'edukite_student' && $tv['action']== 'achievementOptions') {
            /*$listPanelTitle= "
            <div class='listPanelTitle'><img src='/cmspilotv30/CP/www/themes/Manager/images/child-achievement-list.png'></div>
            ";*/
            $achievementListTitle= "
            <div class='achievementListTitle'>Viewing : Child Achievement List</div>
            ";

            $actBtns= "
            <div>
                <a href='{$goToList}' class='cpBack'></a>
            </div>
            ";
        } else if ($tv['module'] == 'edukite_notice' && $tv['action']== 'achievementPanel') {
            $listPanelTitle= "
            <div class='listPanelTitle'></div>
            ";
            $achievementListTitle= "
            <div class='achievementListTitle'>Viewing : EYLF Learning Outcomes</div>
            ";

            if($student_id == ''){
                /*$achievementListTitle= "
                <div class='achievementListTitle'><img src='/cmspilotv30/CP/www/themes/Manager/images/select-progress.png'></div>
                ";*/
            }
        }

        if ($tv['module'] == 'edukite_student' && $tv['action']== 'edit'){
            /*$editPanelTitle= "
            <div class='editPanelTitle'><img src='/cmspilotv30/CP/www/themes/Manager/images/student-form.png'></div>
            ";*/
            $editPanelTitle= "
            <div class='editPanelTitle'><a href='{$goToList}' class=''><img src='/cmspilotv30/CP/www/themes/Manager/images/return-list-notice.png'></a></div>
            ";
        } else if ($tv['module'] == 'edukite_notice' && $tv['action']== 'edit') {
            $editPanelTitle= "
            <div class='editPanelTitle'><a href='{$goToList}' class=''><img src='/cmspilotv30/CP/www/themes/Manager/images/return-list-notice.png'></a></div>
            ";
            $achievementListTitle= "
            <div class='achievementListTitle'>Viewing : Notice Form</div>
            ";
        } else if ($tv['action']== 'edit') {
            $editPanelTitle= "
            <div class='editPanelTitle'><a href='{$goToList}' class=''><img src='/cmspilotv30/CP/www/themes/Manager/images/return-list-notice.png'></a></div>
            ";
        }

        if ($tv['module'] == 'edukite_achievement' && $tv['action']== 'edit') {
            $editPanelTitle= "";
            $actBtns = "";
            $achievementListTitle= "
            <div class='achievementListTitle'>Viewing : Outcomes Audience</div>
            ";
        }
        $topBtn = "
        {$mainNav->getWidget(array(
             'btnPos' => 'Top'
        ))}
        ";

        //Code for Navigating main sections ( Student, Notice)
        $topMenu = "
        <nav id='nav'>
            <a id='navigation' name='navigation'></a>
            {$topBtn}
            {$listPanelTitle}
            {$achievementListTitle}
            {$editPanelTitle}
            {$topMostBtn}
            {$actBtns}
            {$teacherKiteIcon}
        </nav>
        ";

        $mainInner = "
        <div class='mainInner'>
            <aside id='col1' class='ym-col1'>
                <div id='col1_content' class='clearfix ym-clearfix ym-cbox-left'>
                    {$leftPanel}
                </div>
            </aside>
            <aside id='col2' class='ym-col2'>
                <div id='col2_content' class='clearfix ym-clearfix ym-cbox-right'>
                    {$rightPanel}
                </div>
            </aside>
            <div id='' class='col-md-3'>
            </div>
            <div id='' class='col-md-9 col-xs-12'>
                <div id='col3_content' class='clearfix ym-clearfix ym-cbox'>
                    <a id='contentMain' name='contentMain'></a>
                    {$pLogin->view->getLoginInfoText()}
                    {$topMenu}
                    {$navPanel2}
                    {$bannerInCol3}
                    {$bodyPanel}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";
        //<div class='help'><a href='http://help.edukite.com' target=_blank><img src='/cmspilotv30/CP/www/themes/Manager/images/help.png'></a></div>

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <header id='header'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                      {$headerPanel}
                      <a id='logo' href='{$logoLink}'><span class='hideme ym-hideme'>Logo</span></a>
                      {$navInsideHeader}
                    </div>
                </div>
            </header>
            {$navOutsideHeader}
            {$banner}
            <div id='main' class='{$pageCSSClass} clearfix ym-clearfix'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <div id='extended'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$extendedPanel}
                    </div>
                </div>
            </div>
            {$footerInside}
            {$lastPanel}
            ";
        } else {
            $text = "
            <div id='page_margins' class='page_margins ym-wrapper'>
                <div class='page ym-wbox'>
                    <header id='header'>
                        {$headerPanel}
                        <a id='logo' href='{$logoLink}'><span class='hideme ym-hideme'>Logo</span></a>
                        {$navInsideHeader}
                    </header>
                    {$navOutsideHeader}
                    {$banner}
                    <div id='main' class='{$pageCSSClass} clearfix ym-clearfix'>
                        {$mainInner}
                        {$mainBottomPanel}
                    </div>
                </div>
                {$footerOutside}
            </div>
            {$lastPanel}
            ";
        }

        $themeObj = Zend_Registry::get('currentTheme');

        if (method_exists($themeObj, 'init')) {
            $themeObj->init();
        }

        return $text;
    }

     /**
     *
     */
    function getNavButtons($numPages, $action = '', $linkRecType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module  = Zend_Registry::get('currentModule')->name;

        $moduleArr = $modulesArr[$module];

        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);

        $text = "";

        $numPages = $numPages - 1;
        $action = ($action !== "") ? $action : $tv['action'];

        if ($pager->page == ''){
            return;
        }

        if ($action == 'list' || $action == 'detail' || ($action == 'edit' && $moduleArr['hasAutoSave']) ){
            $startRange = $pager->page;
            $endRange   = ($startRange + $numPages) > $pager->totalPages ? $pager->totalPages : ($startRange + $numPages);

            if ($endRange - $startRange <= $numPages){
                $startRange = ($endRange - $numPages) <= 0 ? 1 : ($endRange - $numPages);
            }

            $firstPage     = $pager->getPageNumbersLinks(1, 1, '...&nbsp;', $linkRecType, $exp);
            $firstPageText = ($startRange > 1) ? $firstPage : '';
            $lastPage      = $pager->getPageNumbersLinks($pager->totalPages, '', '', $linkRecType, $exp);
            $lastPageText  = ($endRange < $pager->totalPages) ? "...{$lastPage}" : "";

            $backToList = '';

            if ($action == "detail"){
                $backToList = "
                <div class='float_right backToList'>
                    {$pager->getBackButton()}
                </div>
                ";
            }

            $totalRecordsText = $pager->totalRecords;
            $showRecordCount = $fn->getReqParam('showRecordCount');
            if ($showRecordCount || $moduleArr['showRecordCount']) {
                $showRecordCount = true;
            }
            if (!$showRecordCount) {
                $totalRecordsText = 'Not counted';
            }

            $separator = '&';
            $urlViewAll = $_SERVER['REQUEST_URI'];
            if (strpos($urlViewAll, '?') === false) {
                $separator = '?';
            }
            $urlViewAll .= $separator . 'showAll=1';

            $prevBtn = "<img src='/cmspilotv30/CP/www/themes/Manager/images/ArrowL.png'>";
            $nextBtn = "<img src='/cmspilotv30/CP/www/themes/Manager/images/ArrowR.png'>";

            $text = "
            <div class='pagelinks'>
                <div class='floatbox'>
                    <!--
                    <div class='float_left viewAll'>
                        <a class='viewAll' href='{$urlViewAll}'>view all</a>
                    </div>
                    -->
                    <div class='float_left preBtn'>
                        {$pager->getPrevRecordsText($prevBtn, '', '', $linkRecType, $exp)}
                    </div>
                    <!--
                    <div class='float_left linkNos'>
                        {$firstPageText}
                        {$pager->getPageNumbersLinks($startRange, $endRange, '', $linkRecType, $exp)}
                        {$lastPageText}
                    </div>
                    -->
                    {$backToList}
                    <div class='float_left nxtBtn'>
                        {$pager->getNextRecordsText($nextBtn, '', '', $linkRecType, $exp)}
                    </div>
                    <div class='float_right totalRecs'>
                        [{$totalRecordsText}]
                    </div>
                </div>
            </div>
          ";
        }

        return $text;
    }

}