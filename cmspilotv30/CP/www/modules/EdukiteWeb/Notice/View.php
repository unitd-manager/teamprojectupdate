<?
class CP_Www_Modules_EdukiteWeb_Notice_View extends CP_Common_Lib_ModuleViewAbstract
{
    /*
     *
     */
    function getList() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $teacherKite = $fn->getReqParam('teacherKite');
        $statusUrl = $fn->getReqParam('status');
        $summaryLink = '';
        $leftSlug = '';
        $calendarClass = '';
        $student_status = '';
        $middleSlugButton = '';

        $pic_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $_SESSION['cpContactId'];
        //this condition is to pass student id to calendar plug in, in case if the teacher is viewing the student kite calendar.
        if($tv['sitePfxId']){
            $_SESSION['cpTempContactId'] = $pic_id;
        }

        if($teacherKite == 1){
            $studRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $pic_id );
            $_SESSION['teacherKiteId'] = $teacherKite;
        } else {
            $studRec    = $fn->getRecordRowByID('student', 'student_id', $pic_id );
            $_SESSION['teacherKiteId'] = '';
            $student_status = $studRec['status'] ;
        }
        $name   = $studRec['first_name'] .' ' . $studRec['last_name'] ;

        $studentFilter = '';
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $histRec    = $fn->getRecordRowByID('student_parent', 'parent_id', $_SESSION['cpContactId']);

            $sqlParentStudent = "
            SELECT s.student_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
            FROM student_parent sp
            LEFT JOIN (student s) ON (s.student_id = sp.student_id)
            WHERE sp.parent_id = {$_SESSION['cpContactId']}
            ORDER BY s.status
            ";
            $result      = $db->sql_query($sqlParentStudent);
            $numRows = $db->sql_numrows($result);
            $row = $db->sql_fetchrow($result);

            $session_student_id = $_SESSION['student_id'];
            if($session_student_id != ''){
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $_SESSION['student_id'] );
            } else {
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $row['student_id'] );
            }
            $name   = $studRec['first_name'] .' ' . $studRec['last_name'] ;
            $pic_id = $studRec['student_id'];
            $_SESSION['cpTempContactId'] =  $studRec['student_id'];
            $student_status = $studRec['status'] ;

            $expStudent = array('hideFirstOption' => 1);

            if($numRows > 1){
                $studentFilter = "
                <div class='studentFilter'>
                    {$formObj->getDDRowBySQL('', 'student_id', $sqlParentStudent, $pic_id, $expStudent)}
                </div>
                ";
            }
        }

        $wCalendarDisplay = getCPWidgetObj('edukite_calendarDisplay');
        $exp = array('style' => '', 'folder' => 'normal', 'showCaption' => 0);
        if($teacherKite == 1){
            $pic = $media->getMediaPicture('edukite_teacher', 'picture', $pic_id, $exp);
        } else {
            $pic = $media->getMediaPicture('edukite_student', 'picture', $pic_id, $exp);
        }

        if($pic == ''){
            $pic = "<img src='/cmspilotv30/CP/www/themes/Kite/images/no-image.jpg'/ title=''>";
        }

        //<div class='dailyDairyBanner'><img src='/cmspilotv30/CP/www/themes/Kite/images/learning_banner.png'/></div>
        //<div class='kitePostBanner'><img src='/cmspilotv30/CP/www/themes/Kite/images/kite_post.png'/></div>
        //<div class='galleryBanner'><img src='/cmspilotv30/CP/www/themes/Kite/images/gallery.png'/></div>


        if($cpCfg['cp.primarySchool'] == 1){
            $calendar ='';

            $summaryLink = "
            <div class='txtCenter summaryLink'><a href='#'>GO TO SUMMARY</a></div>
            ";

            if($teacherKite == 1){
                $leftSlug = "
                <div class='leftSlug'>
                    <div class='ym-gl staffCalendar'>
                        <a href='#' teacherKite_status={$teacherKite} class='active' student_id={$pic_id} status='{$student_status}' archive='{$statusUrl}'>Staff Calendar</a>
                    </div>
                </div>
                ";

            }else{
                $leftSlug = "
                <div class='leftSlug'>
                    <div class='ym-gl dailyNotice'>
                        <a href='#' class='active' student_id={$pic_id}><span>CLASS NEWS</span></a>
                    </div>
                    <div class='ym-gl dailyCalendar'>
                        <a href='#' student_id={$pic_id} status='{$student_status}' archive='{$statusUrl}'><span>CALENDAR</span></a>
                    </div>
                </div>
                ";
            }

            $calendarClass='dailyCalendarShowHide';

            $news = "
            <div class='ym-gl news'>
                <a href='#' class='active' student_id={$pic_id}></a>
            </div>
            ";

            if($teacherKite == 1){

                $news = "
                Staff News
                ";

                $classStaffButton = "staffNews";
                $middleSlugButton = "middleBorder";

                $calendar ="
                <div class='mt10 mb10'>
                    {$wCalendarDisplay->getWidget(array(
                        'executeScript' => false
                    ))}
                </div>
                ";
                $templateLeft = 'Daily Diary';

            }else{

                $news = "
                <div class='ym-gl news'>
                    <a href='#' class='active' student_id={$pic_id}><span>NEWS ITEMS</span></a>
                </div>
                ";

                $classStaffButton = "centerPanelTitle";
                $templateLeft = 'Kite Post Left';
            }

            $middleSlug ="
            <div class='middleSlug'>
                <div class='centerPanelTitle'>
                    Notices
                </div>
            </div>
            ";
            //$templateLeft = 'Kite Post Left';
        } else {
            if($teacherKite == 1){
                $leftSlug = "
                <div class='leftSlug'>
                    <div class='ym-gl staffCalendar'>
                        <a href='#' teacherKite_status={$teacherKite} class='active' student_id={$pic_id} status='{$student_status}' archive='{$statusUrl}'>Staff Calendar</a>
                    </div>
                </div>
                ";

            }else{
                $leftSlug = "
                <div class='leftSlug'>
                    <div class='ym-gl dailyDairyTitle'>
                    </div>
                </div>
                ";
            }
            if($teacherKite == 1){

                $news = "
                Staff News
                ";

                $classStaffButton = "staffNews";

            }else{
                $news = "
                <div class='ym-gl learningJourney'>
                </div>
                ";
                $classStaffButton = "centerPanelTitle";
            }

            $templateLeft = 'Daily Diary';
            $calendar ="
            <div class='mt10 mb10'>
                {$wCalendarDisplay->getWidget(array(
                    'executeScript' => true
                ))}
            </div>
            ";
        }

        $_SESSION['student_status'] = $student_status;
        $statusLink = '';

        $archiveLinkUrl = '?status=Archive';
        $activeLinkUrl = '?status=Active';
        if($student_status == 'Active' && $cpCfg['cp.schoolEnrolledCurrentYear'] != 1){
            if($statusUrl != 'Archive'){
                $statusLink = "
                <div class='archiveLink'>
                    <a href='{$archiveLinkUrl}' id='archiveLink' class='archive' student_id={$pic_id}>View Archive</a>
                </div>
                ";
                $_SESSION['student_status'] = 'Active';
            } else {
                $statusLink = "
                <div class='archiveLink'>
                    <a href='{$activeLinkUrl}' id='archiveLink' class='active' student_id={$pic_id}>View Active</a>
                </div>
                ";
                $_SESSION['student_status'] = 'Archive';
            }
        }

        $homework = '';
        $unreadHomework = '';
        if($teacherKite != 1 && $cpCfg['cp.showHomework'] == 1){
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                $sqlParentStudent = "
                SELECT s.student_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
                FROM student_parent sp
                LEFT JOIN (student s) ON (s.student_id = sp.student_id)
                WHERE sp.parent_id = {$_SESSION['cpContactId']}
                ORDER BY s.status
                ";
                $result      = $db->sql_query($sqlParentStudent);
                $row = $db->sql_fetchrow($result);

                $session_student_id = $_SESSION['student_id'];
                if($session_student_id != ''){
                    $session_student_id = $session_student_id;
                }else {
                    $session_student_id =$row['student_id'];
                }
                $sqlHomeworkRead = "
                SELECT np.notice_parent_id
                FROM notice_parent np
                LEFT JOIN (notice n) ON (n.notice_id = np.notice_id)
                WHERE np.parent_id = {$_SESSION['cpContactId']}
                AND (np.homework_read is null OR np.homework_read = 0)
                AND n.template = 'Task'
                AND n.status = '{$_SESSION['student_status']}'
                AND np.student_id = {$session_student_id}
                ";
                $resultHr  = $db->sql_query($sqlHomeworkRead);
                $numRowsHr = $db->sql_numrows($resultHr);
                if($numRowsHr){
                    $unreadHomework = "
                    <div class='ym-gr unreadHomework'>
                        $numRowsHr Unread Notices
                    </div>
                    ";
                }
            }

            $homework ="
            <div class='ym-gl task'>
                <a href='#' student_id={$pic_id} status='{$student_status}' archive='{$statusUrl}'><span>HOMEWORK</span></a>
            </div>
            ";
        }

        $ParentFeedbackLink = '';
        $subjectFilter = '';

        if($cpCfg['cp.showSubjectFilterInKite'] == 1){
            $subjectFilter = "<div class='filter'>{$this->getSearch()}</div>";
        }

        //if($teacherKite == 1 && $cpCfg['cp.parentFeedbackinTeacher'] == 1){
        if($teacherKite == 1){
            $subjectFilter ='';
            /*$ParentFeedbackLink = "
            <div class='parentFeedbackLink'>
                <a href='/index.php?module=edukiteWeb_notice&_spAction=parentFeedbackForm&teacher_id={$_SESSION['cpContactId']}&showHTML=0' id='parentFeedback' class='FeedbackButton'>View Parent Feedback</a>
            </div>
            ";*/
            $ParentFeedbackLink = "
            {$this->getParentFeedbackForm($_SESSION['cpContactId'])}
            ";
        }

        $dailyActivity = '';

        if($cpCfg['cp.dailyActivity'] == 1){
            $dailyActivity ="
            <div class='dailyActivityForTeacher'>{$this->getDailyActivityForTeacherForm()}</div>
            ";
        }

        /*$downloadAsPdf = '';
        if($cpCfg['cp.galleryPdfExportinRight'] == 1){
            if($teacherKite != 1){
                $downloadAsPdfLink = '/'. "kite/home/?_action=printGalleryAsPdf&status={$_SESSION['student_status']}&contact_id={$_SESSION['cpTempContactId']}&teacherKiteId={$teacherKite}&showHTML=0";
                $downloadAsPdf = "<div class='galleryDownloadPdf'>
                                    <a target='_blank' href={$downloadAsPdfLink}>Download as PDF</a>
                                  </div>";
            }
        }*/

        //$statusLink = '';
        $text = "
        {$studentFilter}
        <div class='ym-grid'>
            <div class='ym-g310 ym-gl'>
            <div class='dailyDairyBanner'></div>
                {$leftSlug}
                <div class='homeLeft ym-gbox mt10'>
                    <div class='mb10 mt10'>
                        {$calendar}
                        {$summaryLink}
                        {$this->getListRow($templateLeft, $teacherKite)}
                    </div>
                </div>
                {$dailyActivity}
            </div>
            <div class='ym-g390 ym-gl'>
                <div class='ym-gbox'>
                    <div class='kitePostBanner'></div>
                    <div class='middle_slug {$middleSlugButton}'>
                        <div class='{$classStaffButton} ym-contain-dt'>
                            {$news}
                            {$homework}
                        </div>
                    </div>
                    <div class='ym-contain-dt mt5'>{$unreadHomework}</div>
                    {$statusLink}
                    {$ParentFeedbackLink}
                    <div class='homeMiddle mt10'>
                        {$this->getListRow('Kite Post', $teacherKite)}
                    </div>

                </div>
            </div>
            <div class='ym-g150 ym-gr'>
                <div class='galleryBanner'></div>
                <div class=''>
                    <div class='loginImage'>{$pic}
                        <div class='loginName'>{$name}</div>
                    </div>
                    <div class='rightSlug'>
                        <div class='rightPanelTitle'>
                        GALLERY
                        </div>
                    </div>
                    <div class='mt10 rightColumnImage'>
                        {$this->getListRow('Gallery', $teacherKite)}
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
    function getDetail($row){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUrl = Zend_Registry::get('cpUrl');
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $statusUrl = $fn->getReqParam('status');
        //teacher id added for parent feedback in teacher kite
        $teacherId  = $fn->getReqParam('teacher_id');

        //$wImagesSlider = getCPWidgetObj('media_imagesSlider');
        $wImagesSlider = getCPWidgetObj('media_bsCarousel');
        $desc = nl2br($row['description']);

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $histRec    = $fn->getRecordRowByID('student_parent', 'parent_id', $_SESSION['cpContactId']);

            $sqlParentStudent = "
            SELECT s.student_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
            FROM student_parent sp
            LEFT JOIN (student s) ON (s.student_id = sp.student_id)
            WHERE sp.parent_id = {$_SESSION['cpContactId']}
            ORDER BY s.status
            ";
            $result      = $db->sql_query($sqlParentStudent);
            $numRows = $db->sql_numrows($result);
            $rowPS = $db->sql_fetchrow($result);

            if($_SESSION['student_id'] != ''){
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $_SESSION['student_id'] );
            } else {
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $rowPS['student_id'] );
            }
            $student_id = $studRec['student_id'] ;
            //------------- TO MAKE HOMEWORK STATUS READ ------------
            $sqlHomeworkRead = "
            UPDATE notice_parent SET homework_read = 1
            WHERE parent_id = {$_SESSION['cpContactId']}
            AND notice_id = {$row['notice_id']}
            ";
            $resultHr  = $db->sql_query($sqlHomeworkRead);

        }
        else{
            $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $_SESSION['cpContactId'];
        }

        //$studentRec    = $fn->getRecordRowByID('student', 'student_id', $student_id );
        //$name   = $studentRec['first_name'] .' ' . $studentRec['last_name'] ;

        if($teacherId != '') {
            $kite_id = $teacherId;
        } else {
            $kite_id = $student_id;
        }
        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];
        $urlArray['sitePfxId'] = $kite_id;
        $kiteUrl = $cpUrl->make_seo_url($urlArray);

        $links = "";
        $website = "";
        $youtube = "";
        $vimeo = "";
        $addFeedback = '';

        if($teacherKiteId == 1){
            $teacherRec = $fn->getRecordRowByID('teacher', 'teacher_id', $kite_id );
            $name       = $teacherRec['first_name'] .' ' . $teacherRec['last_name'] ;
            $returnUrl = $kiteUrl . '?teacherKite=1';
        } else {
            $studentRec    = $fn->getRecordRowByID('student', 'student_id', $kite_id );
            $name   = $studentRec['first_name'] .' ' . $studentRec['last_name'] ;
            $returnUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  '/kite/home/';
            $returnUrl = $returnUrl . '?status='.$statusUrl;
        }

        $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);

        if (count($attArr) > 0){
            $links = "
            <div class='links'>
                {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
            </div>
            ";
        }

        if($row['links'] != ''){
            $website_link = $row['links'];
            $seplinks = '';
            $linkarray   = explode("\n", $row['links']);
            foreach ($linkarray as &$alink) {
                $webHttp = substr($alink, 0, 4);
                if($webHttp == 'http'){
                    $alink = $alink;
                } else {
                    $alink = 'http://' . $alink;
                }
                $seplinks .= "
                    <a href='{$alink}' target='_blank'>{$alink}</a><br>
                ";
            }
                $website = "
                <div class='links'>
                {$seplinks}
                </div>
                ";
        }

        if($row['youtube_links'] != ''){
            $ytarray     =explode("/", $row['youtube_links']);
            $ytendstring =end($ytarray);
            $ytendarray  =explode("?v=", $ytendstring);
            $ytendstring =end($ytendarray);
            $ytendarray  =explode("&", $ytendstring);
            $ytcode      =$ytendarray[0];
            $youtube ="
            <div class='youTube'>
                <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
            </div>
            ";
        }
        if($row['vimeo_links'] != ''){
            $ytarray     =explode("/", $row['vimeo_links']);
            $ytendstring =end($ytarray);
            /*$ytendarray  =explode("?v=", $ytendstring);
            $ytendstring =end($ytendarray);*/
            $ytendarray  =explode("&", $ytendstring);
            $ytcode      =$ytendarray[0];
            $vimeo ="
            <div class='youTube'>
                <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
            </div>
            ";
        }

        $activeTab = '';
        if($cpCfg['cp.primarySchool'] == 1){
            if($row['template'] == 'Daily Diary'){
                $activeTab = "<div class='calendarBannerActive'></div>";
            } else if ($row['template'] == 'Kite Post'){
                $activeTab = "<div class='noticeBannerActive'></div>";
            } else if ($row['template'] == 'Gallery'){
                $activeTab = "<div class='lockerBannerActive'></div>";
            }
        } else {
            if($row['template'] == 'Daily Diary'){
                $activeTab = "<div class='dailyDairyBannerActive'></div>";
            } else if ($row['template'] == 'Kite Post'){
                $activeTab = "<div class='kitePostBannerActive'></div>";
            } else if ($row['template'] == 'Gallery'){
                $activeTab = "<div class='galleryBannerActive'></div>";
            }
        }

        if(($_SESSION['cpLoginTypeWWW'] == 'edukite_parent' && $row['parent_feedback'] == 1) || ($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['parent_feedback'] == 1)){
            $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

            $feedbackTitle = '';
            if($histRec['parent_id'] != ''){
                $commentChk = $this->getDisplayFeedback($row['notice_id']);
                if($commentChk != '' || $row['teacher_id'] == $_SESSION['cpContactId'] || $_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                    if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                        $feedbackTitle = 'Parent Comment';
                    } else if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                        $feedbackTitle = 'Parent Comment';
                    }
                }

                $feedbackBox = '';
                if($row['teacher_id'] == $_SESSION['cpContactId'] || $_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                    $feedbackBox ="{$this->getAddFeedback($row['notice_id'], $student_id)}";
                }
                $addFeedback ="
                <div class='feedbackTitle'>{$feedbackTitle}</div>
                <div class='feedbackDisplay'>
                    {$this->getDisplayFeedback($row['notice_id'])}
                </div>
                {$feedbackBox}
                ";
            }
        }
        $acheivementRow  = '';

        if ($cpCfg['showAcheivement'] == 1){
                $acheivementRow = $this->getAchievementDisplay($row['notice_id'], $student_id);
        }

        $mediaRec = $fn->getRecordByCondition('media',
                                                    "record_id = {$row['notice_id']} AND
                                                     media_type = 'image' AND
                                                     record_type = 'picture'
                                                    ");

        if($mediaRec){
            $pic = "
            {$wImagesSlider->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'useRecType1Only' => true
            ))}
            ";
        } else {
            $pic ="
            <h1>{$row['title']}</h1>
            <p>{$desc}</p>
            ";
        }

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $row['teacher_id']);

        if($row['template'] == 'Daily Diary'){
            $date = $row['activity_date'];
        } else {
            $date = $row['launch_date'];
        }

        /*$text = "
        {$activeTab}
        <div class='returnHome'>
            <a href='{$returnUrl}' class='backToList txtCenter'>return</a>
        </div>
        <div class='header'>
        </div>
        <div class='productDetail'>
            {$wImagesSlider->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 380
                ,'height'     => 320
                ,'zoom'      => true
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
            ))}
            <h1>{$ln->gfv($row, 'title')}</h1>
            <p>{$desc}</p>
            {$acheivementRow}
            {$links}
            {$website}
            {$youtube}
            {$addFeedback}
        </div>
        ";*/

        $downloadAsPdf = '';
        //if($cpCfg['cp.galleryPdfExportinRight'] == 1){
            if($teacherKiteId != 1){
                $downloadAsPdfLink = '/'. "kite/kite-notice/?_action=printGalleryAsPdf&status={$_SESSION['student_status']}&contact_id={$_SESSION['cpTempContactId']}&teacherKiteId={$teacherKiteId}&notice_id={$row['notice_id']}&showHTML=0";
                $downloadAsPdf = "<div class='galleryDownloadPdf'>
                                    <a target='_blank' href={$downloadAsPdfLink}>Download as PDF</a>
                                  </div>";
            }
        //}

        $text = "
        {$activeTab}
        <div class='returnHome'>
            <a href='{$returnUrl}' class='backToList txtCenter'>return</a>
        </div>
        <div class='header'>
        </div>
        <div class='productDetail'>
            <div class='clearfix'>
                <div class='childName'>Kite: {$name}</div>
                {$downloadAsPdf}
            </div>
            <div class='mt10 mb10 ym-contain-dt'>
                <div class='float_left date'><i>{$teacherRec['first_name']} {$teacherRec['last_name']}</i></div>
                <div class='float_right date'><i>{$fn->getCPDate($date, 'D d F Y')}</i></div>
            </div>
            {$pic}
            {$acheivementRow}
            {$links}
            {$website}
            {$youtube}
            {$vimeo}
            {$addFeedback}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getListRow($template, $teacherKite) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rows = '';
        $activityDate = '';
        $subjectId = '';
        $noticeType = '';
        $edit='';
        $readNotice = '';
        $class='';
        $addFeedback = '';
        $expiryDate = '';
        $teacherRole = '';
        $student_status = '';

        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $_SESSION['cpContactId'];

        $currentYear = date("Y");
        $currentDate = date('Y-m-d');
        $notice_type_id = $fn->getReqParam('notice_type_id');
        $subject_id     = $fn->getReqParam('subject_id');
        $teacherKite     = $fn->getReqParam('teacherKite');
        $statusUrl = $fn->getReqParam('status');

        if($teacherKite != 1){
            $studRec    = $fn->getRecordRowByID('student', 'student_id', $student_id );
            $student_status = $studRec['status'] ;
        }

        // For Parent Login we need to get the student id , below code is for the same
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $histRec    = $fn->getRecordRowByID('student_parent', 'parent_id', $_SESSION['cpContactId']);
            $sqlParentStudent = "
            SELECT s.student_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
            FROM student_parent sp
            LEFT JOIN (student s) ON (s.student_id = sp.student_id)
            WHERE sp.parent_id = {$_SESSION['cpContactId']}
            ORDER BY s.status
            ";
            $result      = $db->sql_query($sqlParentStudent);
            $rowStudent = $db->sql_fetchrow($result);

            if($_SESSION['student_id'] != ''){
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $_SESSION['student_id'] );
            } else {
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $rowStudent['student_id'] );
            }
            $student_id = $studRec['student_id'] ;
            $student_status = $studRec['status'];
        }

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
            $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
            $teacherRole = $teacherRec['role'];
        }

        if($teacherKite == 1){
            $kite_id = "AND ns.teacher_id = {$student_id}";
        } else {
            $kite_id = "AND ns.student_id = {$student_id}";
        }

        $numRowsNoticeCurYear = $this->getCurrYearNoticeRecord($student_id);

        if(($student_status == 'Archive' && $numRowsNoticeCurYear > 0) || ($statusUrl == 'Archive' && $numRowsNoticeCurYear > 0)){
            $status = "AND n.status IN ('Active', 'Archive')";
        }else if($student_status == 'Archive' || $statusUrl == 'Archive'){
            $status = "AND n.status = 'Archive'";
        } else {
            $status = "AND n.status = 'Active'";
        }

        //This sql used to find the latest activity_date to display all the records for that date in daily dairy
        $SQLDailyDairy = "
        SELECT n.*
        FROM notice n
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Daily Diary'
          {$status}
          AND n.launch_now = 1
          {$kite_id}
          GROUP BY ns.notice_id
          ORDER BY n.notice_id DESC
        ";
        $resultDailyDairy  = $db->sql_query($SQLDailyDairy);
        $rowDailyDairy = $db->sql_fetchrow($resultDailyDairy);
        $activity_date = $rowDailyDairy['activity_date'];

        if($template == 'Daily Diary'){
            if($cpCfg['cp.primarySchool'] == 1){
                $activityDate = "AND n.activity_date >= '{$currentDate}'";
            } else {
                $activityDate = "AND n.activity_date = '{$activity_date}'";
            }
        }

        if($notice_type_id != '' && $template == 'Kite Post'){
            $notice_type_id = "AND n.notice_type_id = '{$notice_type_id}'";
        }
        if($subject_id != '' && $template == 'Kite Post'){
            $subjectId = "AND n.subject_id = {$subject_id}";
        }

        if($template == 'Kite Post' || $template == 'Gallery' || $template == 'Kite Post Left'){
            $expiryDate = "AND (n.expiry_date >= '{$currentDate}' OR n.expiry_date = '' OR n.expiry_date IS NULL)";
        }

        if($template == 'Gallery' || $template == 'Daily Diary'){
            $notice_type_id  ='';
        }

        if($cpCfg['cp.primarySchool'] == 1 && $template == 'Daily Diary'){
            $orderBy = "ORDER BY n.activity_date";
        } else if($template == 'Gallery'){
            $orderBy = "ORDER BY n.launch_date DESC, n.notice_id DESC";
        } else {
            $orderBy = "ORDER BY n.launch_date DESC, n.notice_id DESC";
        }

        //in the sql group by is used to eliminate duplicates if the same notice is linked through class and cohort and individual or either two of them.
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = '{$template}'
          {$status}
          AND n.launch_now = 1
          {$activityDate}
          {$kite_id}
          {$subjectId}
          {$expiryDate}
          {$notice_type_id}
          GROUP BY ns.notice_id
          {$orderBy}
        ";
         $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $exp = array('secType' => 'Kite Notice');
            $detailUrl = $cpUrl->getUrlByRecord($row, 'notice_id', $exp);
            //$detailUrl = "/index.php?module=edukiteWeb_notice&_spAction=detailPopUp&notice_id={$row['notice_id']}&showHTML=0";
            if($statusUrl == ''){
                $statusUrl = $student_status;
            }
            $detailUrl = $detailUrl . '?status='.$statusUrl.'&recordRow='.$numRowsNoticeCurYear;

            $instName = 'wImagesSlider' . $row['notice_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');

            $website = '';
            $links = '';
            $youtube = '';
            $vimeo = '';
            if($template == 'Kite Post' || $template == 'Daily Diary' || $template == 'Kite Post Left'){
                if($template == 'Kite Post'){
                    /*$pic = "
                    {$$instName->getWidget(array(
                         'module'    => 'edukite_notice'
                        ,'record_id' => $row['notice_id']
                        ,'width'     => 380
                        ,'height'    => 320
                        ,'handle'    => 'slider' . $row['notice_id']
                        ,'zoom'      => false
                        ,'thumbnails' => false
                        ,'showCaption' => false
                        ,'useRecType1Only' => true
                    ))}
                    ";*/
                    $exp = array('style' => 'imgPanel', 'folder' => 'normal', 'limit' => 1, 'showCaption' => 0);
                    $pic = $media->getMediaPicture('edukite_notice', 'picture', $row['notice_id'], $exp);
                } else {
                    /*$pic = "
                    {$$instName->getWidget(array(
                         'module'    => 'edukite_notice'
                        ,'record_id' => $row['notice_id']
                        ,'width'     => 287
                        ,'height'    => 200
                        ,'handle'    => 'slider' . $row['notice_id']
                        ,'zoom'      => false
                        ,'thumbnails' => false
                        ,'showCaption' => false
                        ,'useRecType1Only' => true
                    ))}
                    ";*/
                    $exp = array('style' => 'imgPanel', 'folder' => 'normal', 'limit' => 1, 'showCaption' => 0);
                    $pic = $media->getMediaPicture('edukite_notice', 'picture', $row['notice_id'], $exp);
                }

                if($row['links'] != ''){
                    $website_link = $row['links'];
                    $seplinks = '';
                    $linkarray   = explode("\n", $row['links']);
                    foreach ($linkarray as &$alink) {
                        $webHttp = substr($alink, 0, 4);
                        if($webHttp == 'http'){
                            $alink = $alink;
                        } else {
                            $alink = 'http://' . $alink;
                        }
                        $seplinks .= "
                            <a href='{$alink}' target='_blank'>{$alink}</a><br>
                        ";
                    }
                        $website = "
                        <div class='links'>
                        {$seplinks}
                        </div>
                        ";
                }
                $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);
                if (count($attArr) > 0){
                    $links = "
                    <div class='links'>
                        {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
                    </div>
                    ";
                }
                if($row['youtube_links'] != ''){
                    $ytarray     =explode("/", $row['youtube_links']);
                    $ytendstring =end($ytarray);
                    $ytendarray  =explode("?v=", $ytendstring);
                    $ytendstring =end($ytendarray);
                    $ytendarray  =explode("&", $ytendstring);
                    $ytcode      =$ytendarray[0];
                    $youtube ="
                    <div class='youTube'>
                        <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
                    </div>
                    ";
                }
                if($row['vimeo_links'] != ''){
                    $ytarray     =explode("/", $row['vimeo_links']);
                    $ytendstring =end($ytarray);
                    //$ytendarray  =explode("?v=", $ytendstring);
                    //$ytendstring =end($ytendarray);
                    $ytendarray  =explode("&", $ytendstring);
                    $ytcode      =$ytendarray[0];
                    $vimeo ="
                    <div class='youTube'>
                        <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    </div>
                    ";
                }

                $readNotice = '';
                $addFeedback = '';
                $openExpanded = '';
                $acheivementRow = '';
                $addTeacherFeedback = '';

                if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                    $noticeParent = $fn->getRecordByCondition('notice_parent',
                                                                "notice_id = {$row['notice_id']} AND
                                                                 student_id = {$student_id} AND
                                                                 parent_id = {$_SESSION['cpContactId']}
                                                                ");
                    $viewed_tag_fld = 'viewed_tag_' . $row['notice_id'];
                    $class='';
                    if($noticeParent['viewed_tag'] == 1){
                        $class='hideContent';
                        $arrow = "<img src='/www/themes/Kite/images/up.png'/ title=''>";
                    } else {
                        $arrow = "<img src='/www/themes/Kite/images/down.png'/ title=''>";
                    }
                    $readNotice = "
                    <div class='ym-contain-dt'>
                        <div class='readNotice float_left' rec_id='{$noticeParent['notice_parent_id']}'>
                            {$formObj->getYesNoRRow('I have read this notice:', $viewed_tag_fld , $noticeParent['viewed_tag'])}
                        </div>
                    </div>
                    ";

                    if($noticeParent['viewed_tag'] == 1){
                        $openExpanded = 0;
                    }
                    else{
                        $openExpanded = 1;
                    }
                }
                if(($_SESSION['cpLoginTypeWWW'] == 'edukite_parent' && $row['parent_feedback'] == 1) || ($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['parent_feedback'] == 1)){
                    $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

                    $feedbackTitle = '';
                    if($histRec['parent_id'] != ''){
                        $commentChk = $this->getDisplayFeedback($row['notice_id']);
                        if($commentChk != '' || $row['teacher_id'] == $_SESSION['cpContactId'] || $_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                            if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                                $feedbackTitle = 'Parent - Teacher Comment';
                            } else if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                                $feedbackTitle = 'Parent - Teacher Comment';
                            }
                        }

                        $feedbackBox = '';
                        if($row['teacher_id'] == $_SESSION['cpContactId'] || $_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                            $feedbackBox ="{$this->getAddFeedback($row['notice_id'], $student_id)}";
                        }
                        $addFeedback ="
                        <div class='feedbackPanel'>
                            <div class='feedbackTitle'>{$feedbackTitle}</div>
                            <div class='feedbackDisplay'>
                                {$this->getDisplayFeedback($row['notice_id'], $student_id)}
                            </div>
                            {$feedbackBox}
                        </div>
                        ";
                    }
                }

                if($teacherKite == 1 && $row['teacher_feedback'] == 1){
                    $addTeacherFeedback ="
                    <div class='feedbackPanel'>
                        <div class='feedbackTitle redText'><div class='redText'>Teacher - Teacher Feedback</div></div>
                        <div class='feedbackDisplay'>
                            {$this->getDisplayTeacherFeedback($row['notice_id'])}
                        </div>
                        {$this->getAddTeacherFeedback($row['notice_id'])}
                    </div>
                    ";
                }
            }

            $edit='';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $statusUrl != 'Archive'){
                $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
                if($teacherRec['role'] == 'Kite Master'){
                    $edit="
                    <div class='ym-contain-dt'>
                    <div class='mb5 float_left'>
                        <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                    </div>
                    </div>
                    ";
                } else {
                    if($row['teacher_id'] == $_SESSION['cpContactId']){
                        $edit="
                        <div class='ym-contain-dt'>
                        <div class='mb5 float_left'>
                            <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                        </div>
                        </div>
                        ";
                    }
                }
            }

            if ($cpCfg['showAcheivement'] == 1 && ($template == 'Kite Post' || $template == 'Daily Diary' || $template == 'Kite Post Left') ){
                $acheivementRow = $this->getAchievementDisplay($row['notice_id'], $student_id);
            }
            if($template == 'Gallery'){
                $exp = array('style' => '', 'folder' => 'normal', 'limit' => 1, 'showCaption' => 0);
                $pic = $media->getMediaPicture('edukite_notice', 'picture', $row['notice_id'], $exp);
                $rows .= "
                <div class='galleryContent'>
                    {$edit}
                    <div class='mt5'>
                        <a href='{$detailUrl}' class='' wrapperId='detail'>{$pic}</a>
                    </div>
                    <h4><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h4>
                </div>
                ";
            } else if($template == 'Kite Post' || $template == 'Kite Post Left' || ($template == 'Daily Diary' && $cpCfg['cp.primarySchool'] == 0)){
                $hostName   = $_SERVER['HTTP_HOST'];
                //if(strpos($hostName, 'edukitedev') !== false){
                    $desc = $cpUtil->getSubString($row['description'], 300) . '...';
                    $readMore = "<div class='mb10'><a href='{$detailUrl}'><u><b><i>Click here to read & view more images...</i></b></u></a></div>";
                /*} else {
                    $desc = $row['description'];
                    $readMore = '';
                }*/
                $desc = nl2br($desc);

                if($template == 'Daily Diary'){
                    $date = $row['activity_date'];
                } else {
                    $date = $row['launch_date'];
                }

                if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                    $rows .= "
                    <div class='innerContent'>
                        <div class='mt10 mb10 ym-contain-dt'>
                            <div class='float_left date'><i>{$row['teacher_name']}</i></div>
                            <div class='float_right date'><i>{$fn->getCPDate($date, 'D d F Y')}</i></div>
                        </div>
                        <h1><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h1>
                        <div class='linkPortalWrapper'>
                            <div expanded='{$openExpanded}' class='header'>
                                <div class='toggle minus'>&nbsp;</div>
                            </div>
                            <div class='mediaFilesDisplayWrap'>
                            <div class='toggleContent'>
                                <div class='description'>
                                 <i>(Please click on above Title to view slide show and other details of the record)</i><br><br>
                                    <p>{$desc}</p>
                                </div>
                                {$readMore}
                                {$pic}
                                {$links}
                                {$website}
                                {$youtube}
                                {$vimeo}
                                {$addFeedback}
                                {$acheivementRow}
                            </div>
                            </div>
                        </div>
                        {$readNotice}
                    </div>
                    ";
                } else if ($template == 'Daily Diary' && $teacherKite == 1){
                    $date = $row['activity_date'];
                    $rows .= "
                    <div class='innerContent typeCalendar'>
                        {$edit}
                        <div class='mb5 ym-contain-dt'>
                            <div class='date'><a href='{$detailUrl}' class='' wrapperId='detail'>{$fn->getCPDate($date, 'd.F')}</a></div>
                        </div>
                        <h4><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h4>
                    </div>
                    ";
                } else {
                    $rows .= "
                    <div class='innerContent'>
                        <div class='mt10 mb10 ym-contain-dt'>
                            <div class='float_left date'><i>{$row['teacher_name']}</i></div>
                            <div class='float_right date'><i>{$fn->getCPDate($date, 'D d F Y')}</i></div>
                        </div>
                        {$edit}
                        <h1><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h1>
                        <div class='mt10 mb10 ym-contain-dt'>
                            <div class='float_left date'>
                                <i>Please click the Notice Title above to view all images</i>
                            </div>
                        </div>
                        <div class='description'>
                            <p>{$desc}</p>
                        </div>
                        {$readMore}
                        {$pic}
                        {$links}
                        {$website}
                        {$youtube}
                        {$vimeo}
                        {$addFeedback}
                        {$addTeacherFeedback}
                        {$acheivementRow}
                   </div>
                    ";
                }
            } else {
                if ($template == 'Daily Diary' && $teacherKite == 1){
                    $date = $row['activity_date'];
                    $rows .= "
                    <div class='innerContent typeCalendar'>
                        {$edit}
                        <div class='mb5 ym-contain-dt'>
                            <div class='date'><a href='{$detailUrl}' class='' wrapperId='detail'>{$fn->getCPDate($date, 'd.F')}</a></div>
                        </div>
                        <h4><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h4>
                    </div>
                    ";
                }else{
                    $desc = nl2br($row['description']);
                    $date = $row['activity_date'];
                    $rows .= "
                    <div class='innerContent'>
                        <div class='mt10 mb10 ym-contain-dt'>
                            <div class='float_left date'><i>{$row['teacher_name']}</i></div>
                            <div class='float_right date'><i>{$fn->getCPDate($date, 'D d F Y')}</i></div>
                        </div>
                        {$edit}
                        <h1><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h1>
                        <div class='description'>
                            <i>Please click the Notice Title above to view all images</i>
                            <p>{$desc}</p>
                        </div>
                        {$pic}
                        {$links}
                        {$website}
                        {$youtube}
                        {$vimeo}
                        {$addFeedback}
                        {$addTeacherFeedback}
                        {$acheivementRow}
                    </div>
                    ";
                }
            }
        }

        $text = "
        <div class='inner'>
            {$rows}

        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getDailyActivityForTeacherForm() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $teacherKite = $fn->getReqParam('teacherKite');

        $formAction = "/index.php?module=edukiteWeb_notice&_spAction=dailyActivityFormSubmit&showHTML=0";
        if($teacherKite == 1){
            $teacher_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $_SESSION['cpContactId'];
        } else {
            $teacher_id = $_SESSION['cpContactId'];
        }

        $teacherCondition = '';
        $email = '';

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
            $teacherCondition = "AND d.teacher_id = {$teacher_id}";
        }
        if($_SESSION['cpLoginTypeWWW'] != 'edukite_teacher'){
            $email="AND t.email != 'admin@edukite.com'";
        }

        $today = date('Y-m-d');

        $SQL="
        SELECT d.*
        FROM daily_activity d
        LEFT JOIN (teacher t) ON (t.teacher_id = d.teacher_id)
        WHERE d.daily_activity_date = '{$today}'
          {$teacherCondition}
          {$email}
        LIMIT 0,1
        ";
        $result = $db->sql_query($SQL);
        //$row = $db->sql_fetchrow($result);
        $numRows = $db->sql_numrows($result);

        $date=date("F jS");

        $text = '';

        if($numRows == 0){
            if($teacherKite == 1){
            $text = "
            <div class='homeLeft ym-gbox mt10 dailyActivity'>
                <h3 class='txtCenter'>What Happened at Kindy today :</h3>
                <form id='dailyActivityForm' class='yform columnar' method='post' action='{$formAction}'>
                    <fieldset>
                        {$formObj->getTBRow('Title', 'title', 'Daily Activity on ' . $date)}
                        {$formObj->getTARow('', 'notes')}
                        {$formObj->getTBRow('Jellyfish Group-time', 'jellyfish_group_time')}
                        {$formObj->getTBRow('Sea Turtles Group-time', 'sea_turtles_group_time')}
                        {$formObj->getTBRow('Whales Group-time', 'whales_group_time')}
                        {$formObj->getTBRow('Music', 'music')}
                        {$formObj->getTBRow('School Readiness Program', 'school_readiness_program')}
                        {$formObj->getTBRow('Today’s Meals', 'todays_meals')}
                        {$formObj->getTBRow('Morning Tea', 'morning_tea')}
                        {$formObj->getTBRow('Fruit Break', 'fruit_break')}
                        {$formObj->getTBRow('Lunch', 'lunch')}
                        {$formObj->getTBRow('Dessert', 'dessert')}
                    </fieldset>
                    <input type='hidden' name='teacher_id' value='{$teacher_id}' />
                    <div class='ym-gr'>
                        <input type='submit' value='Submit'/>
                    </div>
                    <input type='submit' name='x_submit' class='submithidden' />
                </form>
            </div>
            ";
            }
        } else {
            while ($row = $db->sql_fetchrow($result)) {
                $exp = array('isEditable' => 0);
                $edit = '';
                if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                    $edit="
                    <div class='ym-contain-dt'>
                        <div class='float_right mb10 mt5'>
                            <a href='/controller/daily-activity/edit/{$row['daily_activity_id']}/'><u>Edit</u></a>
                        </div>
                    </div>
                    ";
                }
                $text .= "
                <div class='homeLeft ym-gbox mt10 dailyActivity'>
                    {$edit}
                    <h3 class='txtCenter'>What Happened at Kindy today :</h3>
                    <form id='dailyActivityFormEdit' class='yform columnar' method='post' action='{$formAction}'>
                        <fieldset>
                            {$formObj->getTBRow('Title', 'title', $row['title'], $exp)}
                            {$formObj->getTARow('', 'notes', $row['notes'], $exp)}
                            {$formObj->getTBRow('Sea Turtles Group-time', 'sea_turtles_group_time', $row['sea_turtles_group_time'], $exp)}
                            {$formObj->getTBRow('Whales Group-time', 'whales_group_time', $row['whales_group_time'], $exp)}
                            {$formObj->getTBRow('Music', 'music', $row['music'], $exp)}
                            {$formObj->getTBRow('School Readiness Program', 'school_readiness_program', $row['school_readiness_program'], $exp)}
                            {$formObj->getTBRow('Today’s Meals', 'todays_meals', $row['todays_meals'], $exp)}
                            {$formObj->getTBRow('Morning Tea', 'morning_tea', $row['morning_tea'], $exp)}
                            {$formObj->getTBRow('Fruit Break', 'fruit_break', $row['fruit_break'], $exp)}
                            {$formObj->getTBRow('Lunch', 'lunch', $row['lunch'], $exp)}
                            {$formObj->getTBRow('Dessert', 'dessert', $row['dessert'], $exp)}
                        </fieldset>
                    </form>
                </div>
                ";
            }
        }
        return $text;
    }

    /**
     *
     */
    function getSearch() {

        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $notice_type_id = $fn->getReqParam('notice_type_id');
        $subject_id  = $fn->getReqParam('subject_id');

        $sqlSubject = "
        SELECT s.subject_id, s.title
        FROM subject s
        ";
        $sqlNoticeType = "
        SELECT nt.notice_type_id, nt.title
        FROM notice_type nt
        ";
        $expNoticeType = array('firstOptionLabel' => 'Type');
        $expSubject    = array('firstOptionLabel' => 'Subject');

        $formAction = CP_REQUEST_URI;
        //$formAction = "/eng/products/?author='{$author}'&keyword=&title=&isbn=";

        $text = "
        <form action='{$formAction}' method='get' id='advancedSearch' autoSubmitOnChange='1'>
            <div class='advancedSearch'>
                <div class='ym-contain-dt'>
                    <div class='float_left'>
                        {$formObj->getDDRowBySQL('', 'subject_id', $sqlSubject, $subject_id, $expSubject)}
                    </div>
                    <!--<div class='float_right'>
                        {$formObj->getDDRowBySQL('', 'notice_type_id', $sqlNoticeType, $notice_type_id, $expNoticeType)}
                    </div>-->
                </div>
            </div>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddFeedback($notice_id, $student_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        //print $student_id . 'aa';

        $formAction = "/index.php?module=webBasic_home&_spAction=addFeedbackSubmit&showHTML=0";
        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $student_id;
        //print $tv['sitePfxId'];
        $text = "
        <form id='portalForm_$notice_id' class='yform cpJqForm' method='post' action='{$formAction}'>
            <div class='ym-grid'>
                <div class='ym-g310 ym-gl'>
                    <div class='type-text ym-fbox-text row_notes'>
                        <textarea id='fld_notes' name='notes'></textarea>
                    </div>
                </div>
                <div class='ym-g35 ym-gr btnSubmit' notice_id={$notice_id} student_id={$student_id}>
                    <input type='submit' value='Post'/>
                </div>
            </div>
            <input type='hidden' name='notice_id' value='{$notice_id}' />
            <input type='hidden' name='student_id' value='{$student_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddTeacherFeedback($notice_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $formAction = "/index.php?module=edukiteWeb_notice&_spAction=addTeacherFeedbackSubmit&showHTML=0";
        $text = "
        <form id='portalForm_$notice_id' class='yform cpJqForm' method='post' action='{$formAction}'>
            <div class='ym-grid'>
                <div class='ym-g310 ym-gl'>
                    <div class='type-text ym-fbox-text row_notes'>
                        <textarea id='fld_notes' name='notes'></textarea>
                    </div>
                </div>
                <div class='ym-g35 ym-gr teacherCommentSubmit' notice_id={$notice_id}>
                    <input type='submit' value='Post'/>
                </div>
            </div>
            <input type='hidden' name='notice_id' value='{$notice_id}' />
            <input type='submit' name='z_submit' class='submithidden' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getDisplayFeedback($notice_id = '', $student_id = '') {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $text = '';
        $name = '';
        $class = '';
        $class1 = '';
        $contact_id ='';
        if($notice_id == ''){
            $notice_id  = $fn->getReqParam('notice_id');
        }
        if($student_id == ''){
            $student_id  = $fn->getReqParam('student_id');
        }
        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $student_id;

        $noticeRec    = $fn->getRecordRowByID('notice', 'notice_id', $notice_id );

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $contact_id="
            AND contact_id = {$_SESSION['cpContactId']}
            AND staff_id = {$noticeRec['teacher_id']}
            ";
        } else {
            $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
            $parent_id = '';
            $SQLHistRec = "
            SELECT parent_id
            FROM student_parent
            WHERE student_id = {$student_id}
            ";
            $resultHistRec = $db->sql_query($SQLHistRec);
            while ($rowHistRec = $db->sql_fetchrow($resultHistRec)) {
                $parent_id .= $rowHistRec['parent_id'] . ',';
            }
            $parentId = rtrim($parent_id, ',');

            if($teacherRec['role'] == 'Kite Master'){
                $contact_id="
                AND contact_id IN ({$parentId})
                ";
            } else {
                $contact_id="
                AND contact_id IN ({$parentId})
                AND staff_id = {$_SESSION['cpContactId']}
                ";
            }
        }
        $sql = "
        SELECT * FROM comment
        WHERE room_name = 'webBasic_home'
          AND record_id = {$notice_id}
          {$contact_id}
        ORDER BY comment_date
        ";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {
            if($row['record_type'] == 'edukite_parent'){
                $rowName    = $fn->getRecordRowByID('parent', 'parent_id', $row['contact_id']);
                $name = $rowName['first_name'] .' ' . $rowName['last_name'] ;
                $class = 'blueText';
                $class1 = 'blueBorder';
            } else if($row['record_type'] == 'edukite_teacher') {
                $rowName    = $fn->getRecordRowByID('teacher', 'teacher_id', $row['staff_id']);
                $name = $rowName['first_name'] .' ' . $rowName['last_name'] ;
                $class = 'redText';
                $class1 = 'redBorder';
            }
            $text .= "
            <div class='date feedbackDate'>
                {$fn->getCPDate($row['comment_date'], 'd/m/y')}
            </div>
            <div class='ym-grid'>
                <div class='feedbackContent ym-gl {$class1}'>
                    {$row['comments']}
                </div>
                <div class='ym-gr feedbackName {$class}'>
                    {$name}
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getDisplayTeacherFeedback($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $text = '';
        $name = '';
        $class = '';
        $class1 = '';
        $contact_id ='';
        if($notice_id == ''){
            $notice_id  = $fn->getReqParam('notice_id');
        }
        $noticeRec    = $fn->getRecordRowByID('notice', 'notice_id', $notice_id );

        $sql = "
        SELECT * FROM teacher_comment
        WHERE room_name = 'edukiteWeb_notice'
          AND record_id = {$notice_id}
        ORDER BY comment_date
        ";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {
            $rowName    = $fn->getRecordRowByID('teacher', 'teacher_id', $row['staff_id']);
            $name = $rowName['first_name'] .' ' . $rowName['last_name'] ;
            $class = 'redText';
            $class1 = 'redBorder';

            $text .= "
            <div class='date feedbackDate'>
                {$fn->getCPDate($row['comment_date'], 'd/m/y')}
            </div>
            <div class='ym-gr feedbackName {$class}'>
                {$name}
            </div>
            <div class='ym-grid'>
                <div class='feedbackContent ym-gl {$class1}'>
                    {$row['comments']}
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getDailyDairy() {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');

        $rows = '';
        $limit = '';
        $subjectId = '';
        $noticeType = '';
        $edit = '';
        $activity_date = $fn->getReqParam('activity_date');
        $student_id  = $fn->getReqParam('student_id');
        $status  = $fn->getReqParam('status');
        $tv['siteType'] = 'kite';
        $links = '';
        $website='';
        $youtube='';
        $vimeo='';
        $addFeedback ='';
        $currentYear = date("Y");
        $notice_type_id  ='';

        if($teacherKiteId == 1){
            $kite_id = "AND ns.teacher_id = {$student_id}";
        } else {
            $kite_id = "AND ns.student_id = {$student_id}";
        }

        //in the sql group by is used to eliminate duplicates if the same notice is linked through class and cohort and individual or either two of them.
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Daily Diary'
          AND n.activity_date = '{$activity_date}'
          {$kite_id}
          GROUP BY ns.notice_id
          ORDER BY n.notice_id DESC
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $numRowsNoticeCurYear = $this->getCurrYearNoticeRecord($student_id);

            $exp = array('secType' => 'Kite Notice');
            $url = $cpUrl->getUrlByRecord($row, 'notice_id', $exp);

            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Notice');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $student_id;
            $urlArray['record_id'] = $row['notice_id'];
            $urlArray['record_title'] = $row['title'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $detailUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  $kiteUrl;
            //$detailUrl = "/index.php?module=edukiteWeb_notice&_spAction=detailPopUp&notice_id={$row['notice_id']}&showHTML=0";
            $detailUrl = $detailUrl . '?status='.$status.'&recordRow='.$numRowsNoticeCurYear;

            $instName = 'wImagesSlider' . $row['notice_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');

            /*$pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 287
                ,'height'    => 200
                ,'handle'    => 'slider' . $row['notice_id']
                ,'zoom'      => false
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
                ,'executeScript' => false
            ))}
            ";*/
            $exp = array('style' => 'imgPanel', 'folder' => 'normal', 'limit' => 1, 'showCaption' => 0);
            $pic = $media->getMediaPicture('edukite_notice', 'picture', $row['notice_id'], $exp);

            $links = '';
            $website='';
            $youtube='';
            $vimeo='';
            $addFeedback ='';
            if($row['links'] != ''){
                $website_link = $row['links'];
                $seplinks = '';
                $linkarray   = explode("\n", $row['links']);
                foreach ($linkarray as &$alink) {
                    $webHttp = substr($alink, 0, 4);
                    if($webHttp == 'http'){
                        $alink = $alink;
                    } else {
                        $alink = 'http://' . $alink;
                    }
                    $seplinks .= "
                        <a href='{$alink}' target='_blank'>{$alink}</a><br>
                    ";
                }
                    $website = "
                    <div class='links'>
                    {$seplinks}
                    </div>
                    ";
            }
            $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);
            if (count($attArr) > 0){
                $links = "
                <div class='links'>
                    {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
                </div>
                ";
            }
            if($row['youtube_links'] != ''){
                $ytarray     =explode("/", $row['youtube_links']);
                $ytendstring =end($ytarray);
                $ytendarray  =explode("?v=", $ytendstring);
                $ytendstring =end($ytendarray);
                $ytendarray  =explode("&", $ytendstring);
                $ytcode      =$ytendarray[0];
                $youtube ="
                <div class='youTube'>
                    <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
                </div>
                ";
            }
                if($row['vimeo_links'] != ''){
                    $ytarray     =explode("/", $row['vimeo_links']);
                    $ytendstring =end($ytarray);
                    /*$ytendarray  =explode("?v=", $ytendstring);
                    $ytendstring =end($ytendarray);*/
                    $ytendarray  =explode("&", $ytendstring);
                    $ytcode      =$ytendarray[0];
                    $vimeo ="
                    <div class='youTube'>
                        <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    </div>
                    ";
                }

            if(($_SESSION['cpLoginTypeWWW'] == 'edukite_parent' && $row['parent_feedback'] == 1) || ($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['parent_feedback'] == 1)){
                $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

                $feedbackTitle = '';

                if($histRec['parent_id'] != ''){
                    $commentChk = $this->getDisplayFeedback($row['notice_id']);
                    if($commentChk != '' || $row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                            $feedbackTitle = 'Parent Comment';
                        } else if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                            $feedbackTitle = 'Parent Comment';
                        }
                    }

                    $feedbackBox = '';
                    if($row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        $feedbackBox ="{$this->getAddFeedback($row['notice_id'], $student_id)}";
                    }
                    $addFeedback ="
                    <div class='feedbackTitle'>{$feedbackTitle}</div>
                    <div class='feedbackDisplay'>
                        {$this->getDisplayFeedback($row['notice_id'])}
                    </div>
                    {$feedbackBox}
                    ";
                }
            }

            $edit='';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['status'] != 'Archive'){
                $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
                if($teacherRec['role'] == 'Kite Master'){
                    $edit="
                    <div class='ym-contain-dt'>
                    <div class='mb5 float_left'>
                        <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                    </div>
                    </div>
                    ";
                } else {
                    if($row['teacher_id'] == $_SESSION['cpContactId']){
                        $edit="
                        <div class='ym-contain-dt'>
                        <div class='mb5 float_left'>
                            <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                        </div>
                        </div>
                        ";
                    }
                }
            }

            $acheivementRow='';
            if ($cpCfg['showAcheivement'] == 1){
                $acheivementRow = $this->getAchievementDisplay($row['notice_id'], $student_id);
            }

            $hostName   = $_SERVER['HTTP_HOST'];
            //if(strpos($hostName, 'edukitedev') !== false){
                $desc = $cpUtil->getSubString($row['description'], 300) . '...';
                $readMore = "<div class='mb10'><a href='{$detailUrl}'><u><b><i>Click here to read & view more images...</i></b></u></a></div>";
            /*} else {
                $desc = $row['description'];
                $readMore = '';
            }*/
            $desc = nl2br($desc);
            $rows .= "
            <div class='innerContent'>
                <div class='mt10 mb10 ym-contain-dt'>
                    <div class='float_left date'><i>{$row['teacher_name']}</i></div>
                    <div class='float_right date'><i>{$fn->getCPDate($row['activity_date'], 'D d F Y')}</i></div>
                </div>
                {$edit}
                <h1><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h1>
                <div class='description'>
                    <p>{$desc}</p>
                </div>
                {$readMore}
                {$pic}
                {$links}
                {$website}
                {$youtube}
                {$vimeo}
                {$addFeedback}
                {$acheivementRow}
            </div>
            ";
        }

        $text = "
        {$rows}
        ";
        return $text;
    }

    /**
     *
     */
    function getUpdateNoticeParent() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $viewed_tag   = $fn->getReqParam('viewed_tag');
        $rec_id   = $fn->getReqParam('rec_id');
        $currentDate = date('Y-m-d');

        $updateSQL = "
        UPDATE notice_parent
        SET viewed_tag = {$viewed_tag},
        read_date = '{$currentDate}'
        WHERE notice_parent_id = {$rec_id}
        ";
        $result = $db->sql_query($updateSQL);
    }

    /**
     *
     */
    function getUpdateTaskReadNoticeParent() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $viewed_tag   = $fn->getReqParam('viewed_tag');
        $rec_id   = $fn->getReqParam('rec_id');
        $currentDate = date('Y-m-d');

        $updateSQL = "
        UPDATE notice_parent
        SET homework_read = {$viewed_tag}
        WHERE notice_parent_id = {$rec_id}
        ";
        $result = $db->sql_query($updateSQL);
    }

    /**
     *
     */
    function getStudentIdForParent() {
        $fn = Zend_Registry::get('fn');

        $student_id   = $fn->getReqParam('student_id');
        $_SESSION['student_id'] = $student_id;
    }

    /**
     *
     */
    function rotateImage() {
        $imgPath = 'E:/Projects/edukitedev/httpdocs/www/themes/Kite/images';
        $img = 'baby.JPG';
        $suffix = '';
        $quality = 100;
        $degrees = -90;

        //$mediaRec    = $fn->getRecordRowByID('media', 'media_id', 9035 );
        //$pic = $media->getMediaPicture('edukite_student', 'picture', $pic_id, $exp);

        // Open the original image.
        $original = imagecreatefromjpeg("$imgPath/$img") or die("Error Opening original");
        list($width, $height, $type, $attr) = getimagesize("$imgPath/$img");

        // Resample the image.
        $tempImg = imagecreatetruecolor($width, $height) or die("Cant create temp image");
        imagecopyresized($tempImg, $original, 0, 0, 0, 0, $width, $height, $width, $height) or die("Cant resize copy");

        // Rotate the image.
        $rotate = imagerotate($original, $degrees, 0);

        // Save.
        //if($save)
        //{
            // Create the new file name.
        $newNameE = explode(".", $img);
        $newName = ''. $newNameE[0] .''. $suffix .'.'. $newNameE[1] .'';

        // Save the image.
        imagejpeg($rotate, "$imgPath/$newName", $quality) or die("Cant save image");
        //}

        // Clean up.
        imagedestroy($original);
        imagedestroy($tempImg);
        return true;
    }

    /**
     *
     */
    function getContactSchoolContent() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $text ="
        <div class='contactSchoolContent'>{$ln->gd('cp.contactSchool')}</div>
        ";
        return $text;
    }

    /**
     *
     */
    function getParentProfileForm() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $parent_id   = $fn->getReqParam('parent_id');

        $formAction = "/index.php?module=edukiteWeb_notice&_spAction=parentProfileFormSubmit&showHTML=0";
        $parentRec    = $fn->getRecordRowByID('parent', 'parent_id', $parent_id );

        $text = "
        <form id='parentProfileForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('First Name', 'first_name', $parentRec['first_name'])}
                {$formObj->getTBRow('Surname', 'last_name', $parentRec['last_name'])}
                {$formObj->getTBRow('Email / Username', 'email', $parentRec['email'])}
                {$formObj->getTBRow('Password', 'pass_word', $parentRec['pass_word'])}
            </fieldset>
                <div>
                * Usernames must be a valid email address <br>
                * Passwords must contain at least six characters or digits
                </div>
            <input type='hidden' name='parent_id' value='{$parent_id}' />
        </form>
        ";
        return $text;
    }


    /**
     *
     */
    function getParentFeedbackForm($teacher_id) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $currentYear = date("Y");
        $teacherId = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $teacher_id;

        /*if($teacher_id == ''){
            $teacher_id   = $fn->getReqParam('teacher_id');
        }*/

        $rows = '';
        $parent_id = '';
        $student_id = '';
        $notice_id = '';
        $text = '';

        //$formAction = "/index.php?module=edukiteWeb_notice&_spAction=parentProfileFormSubmit&showHTML=0";
        //$parentRec    = $fn->getRecordRowByID('parent', 'parent_id', $parent_id );
        $SQLDetails = "
        SELECT n.notice_id
              ,n.title
              ,n.launch_date
              ,c.comments
              ,c.comment_id
              ,c.contact_id
              ,s.student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS child_name
        FROM `notice` n
        LEFT JOIN comment c ON (c.record_id = n.notice_id)
        LEFT JOIN student s ON (s.student_id = c.student_id)
        WHERE c.record_type = 'edukite_parent'
        AND n.teacher_id = {$teacherId}
        AND n.status = 'Active'
        AND DATE_FORMAT(c.comment_date, '%Y') = '{$currentYear}'
        ORDER BY n.notice_id DESC, c.comment_id DESC
        ";
        $resultDetails = $db->sql_query($SQLDetails);
        $numRows       = $db->sql_numrows($resultDetails);

        while ($rowDetails = $db->sql_fetchrow($resultDetails)) {
            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Notice');
            $urlArray['section_title'] = $secRec['title'];
            $status = 'Active';
            $urlArray['sitePfxId'] = $rowDetails['student_id'];
            $urlArray['record_id'] = $rowDetails['notice_id'];
            $urlArray['record_title'] = $rowDetails['title'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $detailUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  $kiteUrl;
            $detailUrl = $detailUrl . '?status='.$status.'&teacher_id='.$teacherId;

            $notice_Date = $fn->getCPDate($rowDetails['launch_date'], 'd-m-Y');

            if($rowDetails['child_name'] == ''){
                $parentSQL = "
                SELECT student_id
                FROM student_parent
                WHERE parent_id = {$rowDetails['contact_id']}
                ";
                $resultParent = $db->sql_query($parentSQL);
                while ($rowParent = $db->sql_fetchrow($resultParent)) {
                    $noticeRec = $fn->getRecordByCondition('notice_student',
                                                                "notice_id = {$rowDetails['notice_id']} AND
                                                                 student_id = {$rowParent['student_id']}");
                    if($noticeRec['notice_student_id'] != ''){
                        $studentRec = $fn->getRecordRowByID('student', 'student_id', $rowParent['student_id']);
                        $child_name = $studentRec['first_name'].' '.$studentRec['last_name'];
                    }
                }
            } else{
                $child_name = $rowDetails['child_name'];
            }

            if($rowDetails['contact_id'] == $parent_id && $rowDetails['student_id'] == $student_id && $rowDetails['notice_id'] == $notice_id){
            } else {
                $rows .= "
                <tr>
                    <td><u><a href='{$detailUrl}'>{$child_name}</a></u></td>
                    <td>{$rowDetails['title']}</td>
                    <td>{$notice_Date}</td>
                    <td>{$rowDetails['comments']}</td>
                </tr>
                ";
                $parent_id = $rowDetails['contact_id'];
                $student_id = $rowDetails['student_id'];
                $notice_id = $rowDetails['notice_id'];
            }
        }

        if($numRows > 0){
            $text = "
            <div class='mt10 feedbackHeading'><h1>FEEDBACK</h1></div>
            <div class='parentFeedbackDisplay mb20'>
            <table class='thinlist mt5' id='parentFeedbackForm'>
              <thead>
                   <tr>
                      <th>Student</th>
                      <th>Title</th>
                      <th>Date</th>
                      <th>Feedback</th>
                   </tr>
              </thead>
              <tbody>
                    {$rows}
              </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getActivityCalendarDisplay() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $statusUrl = $fn->getReqParam('status');

        $rows = '';
        $limit = '';
        $subjectId = '';
        $noticeType = '';
        $edit = '';
        $student_id  = $fn->getReqParam('student_id');
        $student_status  = $fn->getReqParam('status');
        $tv['siteType'] = 'kite';
        $currentYear = date("Y");
        $currentDate = date('Y-m-d');
        $links = '';
        $website='';
        $youtube='';
        $vimeo='';
        $addFeedback ='';
        $wCalendarDisplay = getCPWidgetObj('edukite_calendarDisplay');

        if($teacherKiteId == 1){
            $kite_id = "AND ns.teacher_id = {$student_id}";
        } else {
            $kite_id = "AND ns.student_id = {$student_id}";
        }

        $numRowsNoticeCurYear = $this->getCurrYearNoticeRecord($student_id);

        if(($student_status == 'Archive' && $numRowsNoticeCurYear > 0) || ($statusUrl == 'Archive' && $numRowsNoticeCurYear > 0)){
            $status = "AND n.status IN ('Active', 'Archive')";
        }else if($student_status == 'Archive' || $statusUrl == 'Archive'){
            $status = "AND n.status = 'Archive'";
        } else {
            $status = "AND n.status = 'Active'";
        }

        $notice_type_id  ='';
        //in the sql group by is used to eliminate duplicates if the same notice is linked through class and cohort and individual or either two of them.
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Daily Diary'
          {$status}
          AND n.launch_now = 1
          AND n.activity_date >= '{$currentDate}'
          {$kite_id}
          GROUP BY ns.notice_id
          ORDER BY n.activity_date
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            if($numRowsNoticeCurYear > 0){
                $retStatus = $student_status;
            } else {
                $retStatus = $row['status'];
            }

            $exp = array('secType' => 'Kite Notice');
            $url = $cpUrl->getUrlByRecord($row, 'notice_id', $exp);

            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Notice');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $student_id;
            $urlArray['record_id'] = $row['notice_id'];
            $urlArray['record_title'] = $row['title'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $detailUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  $kiteUrl;
            //$detailUrl = "/index.php?module=edukiteWeb_notice&_spAction=detailPopUp&notice_id={$row['notice_id']}&showHTML=0";
            $detailUrl = $detailUrl . '?status='.$retStatus.'&recordRow='.$numRowsNoticeCurYear;

            $instName = 'wImagesSlider' . $row['notice_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');

            $pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 287
                ,'height'    => 200
                ,'handle'    => 'slider' . $row['notice_id']
                ,'zoom'      => false
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
                ,'executeScript' => false
            ))}
            ";

            $links = '';
            $website='';
            $youtube='';
            $vimeo='';
            $addFeedback ='';
            if($row['links'] != ''){
                $website_link = $row['links'];
                $seplinks = '';
                $linkarray   = explode("\n", $row['links']);
                foreach ($linkarray as &$alink) {
                    $webHttp = substr($alink, 0, 4);
                    if($webHttp == 'http'){
                        $alink = $alink;
                    } else {
                        $alink = 'http://' . $alink;
                    }
                    $seplinks .= "
                        <a href='{$alink}' target='_blank'>{$alink}</a><br>
                    ";
                }
                    $website = "
                    <div class='links'>
                    {$seplinks}
                    </div>
                    ";
            }
            $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);
            if (count($attArr) > 0){
                $links = "
                <div class='links'>
                    {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
                </div>
                ";
            }
            if($row['youtube_links'] != ''){
                $ytarray     =explode("/", $row['youtube_links']);
                $ytendstring =end($ytarray);
                $ytendarray  =explode("?v=", $ytendstring);
                $ytendstring =end($ytendarray);
                $ytendarray  =explode("&", $ytendstring);
                $ytcode      =$ytendarray[0];
                $youtube ="
                <div class='youTube'>
                    <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
                </div>
                ";
            }
                if($row['vimeo_links'] != ''){
                    $ytarray     =explode("/", $row['vimeo_links']);
                    $ytendstring =end($ytarray);
                    /*$ytendarray  =explode("?v=", $ytendstring);
                    $ytendstring =end($ytendarray);*/
                    $ytendarray  =explode("&", $ytendstring);
                    $ytcode      =$ytendarray[0];
                    $vimeo ="
                    <div class='youTube'>
                        <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    </div>
                    ";
                }

            if(($_SESSION['cpLoginTypeWWW'] == 'edukite_parent' && $row['parent_feedback'] == 1) || ($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['parent_feedback'] == 1)){
                $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

                $feedbackTitle = '';
                if($histRec['parent_id'] != ''){
                    $commentChk = $this->getDisplayFeedback($row['notice_id']);
                    if($commentChk != '' || $row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                            $feedbackTitle = 'Parent Comment';
                        } else if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                            $feedbackTitle = 'Parent Comment';
                        }
                    }

                    $feedbackBox = '';
                    if($row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        $feedbackBox ="{$this->getAddFeedback($row['notice_id'], $student_id)}";
                    }
                    $addFeedback ="
                    <div class='feedbackTitle'>{$feedbackTitle}</div>
                    <div class='feedbackDisplay'>
                        {$this->getDisplayFeedback($row['notice_id'])}
                    </div>
                    {$feedbackBox}
                    ";
                }
            }

            $edit='';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['status'] != 'Archive'){
                $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
                if($teacherRec['role'] == 'Kite Master'){
                    $edit="
                    <div class='ym-contain-dt'>
                    <div class='mb5 float_left'>
                        <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                    </div>
                    </div>
                    ";
                } else {
                    if($row['teacher_id'] == $_SESSION['cpContactId']){
                        $edit="
                        <div class='ym-contain-dt'>
                        <div class='mb5 float_left'>
                            <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                        </div>
                        </div>
                        ";
                    }
                }
            }

            $date = $row['activity_date'];
            $rows .= "
            <div class='innerContent typeCalendar'>
                {$edit}
                <div class='mb5 ym-contain-dt'>
                    <div class='date'><a href='{$detailUrl}' class='' wrapperId='detail'>{$fn->getCPDate($date, 'd.F')}</a></div>
                </div>
                <h4><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}</a></h4>
            </div>
            ";

        }

        $text = "
        <div class='mt10 mb10'>
            {$wCalendarDisplay->getWidget(array(
                'executeScript' => false
            ))}
        </div>
        <div class='inner'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAchievementDisplay($notice_id, $student_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $currentYear = date("Y");
        $rows ='';
        $text ='';

        $studRec    = $fn->getRecordRowByID('student', 'student_id', $student_id );

        $SQL = "
        SELECT DISTINCT sa.student_id
              ,a.title AS achievement_title
              ,a.number
        FROM achievement_student sa
        LEFT JOIN achievement a ON (sa.achievement_id = a.achievement_id)
        WHERE sa.notice_id = {$notice_id}
        AND sa.student_id = {$student_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .="
            <tr>
                <td width='40'><img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-tick.jpg'></td>
                <td>{$row['number']} {$row['achievement_title']}</td>
            </tr>
            ";
        }

        if($numRows){
            $text ="
            <div class='achievementList'>
                <table>
                {$rows}
                </table>
            </div>
            ";
        }


        return $text;
    }

    /**
     *
     */
    function getDetailPopUp(){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');
        $notice_id = $fn->getReqParam('notice_id');
        $instName = 'wImagesSlider' . $notice_id;
        $$instName = getCPWidgetObj('media_imagesSlider');

        $links = "";
        $website = "";
        $youtube = "";
        $vimeo = "";

        $SQL = "
        SELECT n.*
        FROM notice n
        WHERE n.notice_id = {$notice_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $desc = nl2br($row['description']);
        $returnUrl = '';
        $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);

        if (count($attArr) > 0){
            $links = "
            <div class='links'>
                {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
            </div>
            ";
        }

        if($row['links'] != ''){
            $website_link = $row['links'];
            $seplinks = '';
            $linkarray   = explode("\n", $row['links']);
            foreach ($linkarray as &$alink) {
                $webHttp = substr($alink, 0, 4);
                if($webHttp == 'http'){
                    $alink = $alink;
                } else {
                    $alink = 'http://' . $alink;
                }
                $seplinks .= "
                    <a href='{$alink}' target='_blank'>{$alink}</a><br>
                ";
            }
                $website = "
                <div class='links'>
                {$seplinks}
                </div>
                ";
        }

        if($row['youtube_links'] != ''){
            $ytarray     =explode("/", $row['youtube_links']);
            $ytendstring =end($ytarray);
            $ytendarray  =explode("?v=", $ytendstring);
            $ytendstring =end($ytendarray);
            $ytendarray  =explode("&", $ytendstring);
            $ytcode      =$ytendarray[0];
            $youtube ="
            <div class='youTube'>
                <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
            </div>
            ";
        }
        if($row['vimeo_links'] != ''){
            $ytarray     =explode("/", $row['vimeo_links']);
            $ytendstring =end($ytarray);
            /*$ytendarray  =explode("?v=", $ytendstring);
            $ytendstring =end($ytendarray);*/
            $ytendarray  =explode("&", $ytendstring);
            $ytcode      =$ytendarray[0];
            $vimeo ="
            <div class='youTube'>
                <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
            </div>
            ";
        }


        $activeTab = '';
        if($cpCfg['cp.primarySchool'] == 1){
            if($row['template'] == 'Daily Diary'){
                $activeTab = "<div class='calendarBannerActive'></div>";
            } else if ($row['template'] == 'Kite Post'){
                $activeTab = "<div class='noticeBannerActive'></div>";
            } else if ($row['template'] == 'Gallery'){
                $activeTab = "<div class='lockerBannerActive'></div>";
            }
        } else {
            if($row['template'] == 'Daily Diary'){
                $activeTab = "<div class='dailyDairyBannerActive'></div>";
            } else if ($row['template'] == 'Kite Post'){
                $activeTab = "<div class='kitePostBannerActive'></div>";
            } else if ($row['template'] == 'Gallery'){
                $activeTab = "<div class='galleryBannerActive'></div>";
            }
        }

        $mediaRec = $fn->getRecordByCondition('media',
                                                    "record_id = {$row['notice_id']} AND
                                                     media_type = 'image' AND
                                                     record_type = 'picture'
                                                    ");

        if($mediaRec){
            $pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 560
                ,'height'    => 380
                ,'handle'    => 'slider' . $notice_id . 1
                ,'zoom'      => false
                ,'thumbnails' => false
                ,'showCaption' => true
                ,'useRecType1Only' => true
            ))}
            ";
        } else {
            $pic ="
            <h1>{$row['title']}</h1>
            <p>{$desc}</p>
            ";
        }

        $text = "
        <div class='popUpContent'>
            {$activeTab}
            <div class='sliderCaption'>
                {$pic}
            </div>
            {$links}
            {$website}
            {$youtube}
            {$vimeo}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getTaskDisplay() {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $statusUrl = $fn->getReqParam('status');

        $rows = '';
        $limit = '';
        $subjectId = '';
        $noticeType = '';
        $edit = '';
        $activity_date = $fn->getReqParam('activity_date');
        $student_id  = $fn->getReqParam('student_id');
        $tv['siteType'] = 'kite';
        $links = '';
        $website='';
        $youtube='';
        $vimeo = '';
        $addFeedback ='';
        $currentYear = date("Y");
        $notice_type_id  ='';
        $student_status  = $fn->getReqParam('status');

        if($teacherKiteId == 1){
            $kite_id = "AND ns.teacher_id = {$student_id}";
        } else {
            $kite_id = "AND ns.student_id = {$student_id}";
        }

        $numRowsNoticeCurYear = $this->getCurrYearNoticeRecord($student_id);

        if(($student_status == 'Archive' && $numRowsNoticeCurYear > 0) || ($statusUrl == 'Archive' && $numRowsNoticeCurYear > 0)){
            $status = "AND n.status IN ('Active', 'Archive')";
        }else if($student_status == 'Archive' || $statusUrl == 'Archive'){
            $status = "AND n.status = 'Archive'";
        } else {
            $status = "AND n.status = 'Active'";
        }

        //in the sql group by is used to eliminate duplicates if the same notice is linked through class and cohort and individual or either two of them.
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Task'
          {$status}
          {$kite_id}
          GROUP BY ns.notice_id
          ORDER BY n.notice_id DESC
        ";
         $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $exp = array('secType' => 'Kite Notice');
            $url = $cpUrl->getUrlByRecord($row, 'notice_id', $exp);

            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Notice');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $student_id;
            $urlArray['record_id'] = $row['notice_id'];
            $urlArray['record_title'] = $row['title'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $detailUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  $kiteUrl;
            $detailUrl = $detailUrl . '?status='.$statusUrl.'&recordRow='.$numRowsNoticeCurYear;

            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Task');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $student_id;
            $urlArray['record_id'] = $row['notice_id'];
            $urlArray['record_title'] = $row['title'];
            $goToUpload = $cpUrl->make_seo_url($urlArray);
            /*if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                $goToUploadLink = "{$goToUpload}?teacherKite=1";
            } else {
                $goToUploadLink = "{$goToUpload}";
            }*/

            $instName = 'wImagesSlider' . $row['notice_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');

            $pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 287
                ,'height'    => 200
                ,'handle'    => 'slider' . $row['notice_id']
                ,'zoom'      => false
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
                ,'executeScript' => false
            ))}
            ";
            //To show indicators in the title for parent login if there is a unrad homework
             $homeworkRead =  '';
             $readNotice = '';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                $homeworkReadRow = $fn->getRecordByCondition('notice_parent', "notice_id = '{$row['notice_id']}' AND parent_id = '{$_SESSION['cpContactId']}'");
                if($homeworkReadRow['homework_read'] != 1){
                    $homeworkRead  = '(*****)';
                }
                else{
                     $homeworkRead =  '';
                }

                $viewed_tag_fld = 'homework_read_' . $row['notice_id'];

                $readNotice = "
                <div class='ym-contain-dt'>
                    <div class='readNoticeTask float_left' rec_id='{$homeworkReadRow['notice_parent_id']}'>
                        {$formObj->getYesNoRRow('I have read this notice:', $viewed_tag_fld , $homeworkReadRow['homework_read'])}
                    </div>
                </div>
                ";
            }

            $links = '';
            $website='';
            $youtube='';
            $vimeo='';
            $addFeedback ='';
            if($row['links'] != ''){
                $website_link = $row['links'];
                $seplinks = '';
                $linkarray   = explode("\n", $row['links']);
                foreach ($linkarray as &$alink) {
                    $webHttp = substr($alink, 0, 4);
                    if($webHttp == 'http'){
                        $alink = $alink;
                    } else {
                        $alink = 'http://' . $alink;
                    }
                    $seplinks .= "
                        <a href='{$alink}' target='_blank'>{$alink}</a><br>
                    ";
                }
                $website = "
                <div class='links'>
                {$seplinks}
                </div>
                ";
            }
            $attArr = $media->getFirstMediaRecord('edukite_notice', 'attachment', $row['notice_id']);
            if (count($attArr) > 0){
                $links = "
                <div class='links'>
                    {$media->getMediaFilesDisplayThin('edukite_notice', 'attachment', $row['notice_id'])}
                </div>
                ";
            }
            if($row['youtube_links'] != ''){
                $ytarray     =explode("/", $row['youtube_links']);
                $ytendstring =end($ytarray);
                $ytendarray  =explode("?v=", $ytendstring);
                $ytendstring =end($ytendarray);
                $ytendarray  =explode("&", $ytendstring);
                $ytcode      =$ytendarray[0];
                $youtube ="
                <div class='youTube'>
                    <iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
                </div>
                ";
            }
            if($row['vimeo_links'] != ''){
                $ytarray     =explode("/", $row['vimeo_links']);
                $ytendstring =end($ytarray);
                /*$ytendarray  =explode("?v=", $ytendstring);
                $ytendstring =end($ytendarray);*/
                $ytendarray  =explode("&", $ytendstring);
                $ytcode      =$ytendarray[0];
                $vimeo ="
                <div class='youTube'>
                    <iframe src=\"https://player.vimeo.com/video/$ytcode?autoplay=0&loop=0&autopause=0\" width=\"500\" height=\"281\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                </div>
                ";
            }

            if(($_SESSION['cpLoginTypeWWW'] == 'edukite_parent' && $row['parent_feedback'] == 1) || ($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher' && $row['parent_feedback'] == 1)){
                $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

                $feedbackTitle = '';

                if($histRec['parent_id'] != ''){
                    $commentChk = $this->getDisplayFeedback($row['notice_id']);
                    if($commentChk != '' || $row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                            $feedbackTitle = 'Parent Comment';
                        } else if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                            $feedbackTitle = 'Parent Comment';
                        }
                    }

                    $feedbackBox = '';
                    if($row['teacher_id'] == $_SESSION['cpContactId'] || $histRec['parent_id'] != ''){
                        $feedbackBox ="{$this->getAddFeedback($row['notice_id'], $student_id)}";
                    }
                    $addFeedback ="
                    <div class='feedbackTitle'>{$feedbackTitle}</div>
                    <div class='feedbackDisplay'>
                        {$this->getDisplayFeedback($row['notice_id'])}
                    </div>
                    {$feedbackBox}
                    ";
                }
            }

            $edit='';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'  && $row['status'] != 'Archive'){
                $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
                if($teacherRec['role'] == 'Kite Master'){
                    $edit="
                    <div class='ym-contain-dt'>
                    <div class='mb5 float_left'>
                        <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                    </div>
                    </div>
                    ";
                } else {
                    if($row['teacher_id'] == $_SESSION['cpContactId']){
                        $edit="
                        <div class='ym-contain-dt'>
                        <div class='mb5 float_left'>
                            <a href='/controller/notice/edit/{$row['notice_id']}/'><u>Edit</u></a>
                        </div>
                        </div>
                        ";
                    }
                }
            }

            $acheivementRow='';
            if ($cpCfg['showAcheivement'] == 1){
                $acheivementRow = $this->getAchievementDisplay($row['notice_id'], $student_id);
            }

            $hostName   = $_SERVER['HTTP_HOST'];
            //if(strpos($hostName, 'edukitedev') !== false){
                $desc = $cpUtil->getSubString($row['description'], 300) . '...';
                $readMore = "<div class='mb10'><a href='{$detailUrl}'><u><b><i>Click here to read & view more images...</i></b></u></a></div>";
            /*} else {
                $desc = $row['description'];
                $readMore = '';
            }*/
            $desc = nl2br($desc);
            $rows .= "
            <div class='innerContent'>
                <div class='mt10 mb10 ym-contain-dt'>
                    <div class='float_left date'><i>{$row['teacher_name']}</i></div>
                    <div class='float_right date'><i>{$fn->getCPDate($row['activity_date'], 'D d F Y')}</i></div>
                </div>
                {$edit}
                <a href='{$goToUpload}'><img src='/cmspilotv30/CP/www/themes/Kite/images/go_to_upload_button.png'/ title=''></a>
                <h1><a href='{$detailUrl}' class='' wrapperId='detail'>{$ln->gfv($row, 'title', '0')}{$homeworkRead}</a></h1>
                <div class='description'>
                    <p>{$desc}</p>
                </div>
                {$readMore}
                {$pic}
                {$links}
                {$website}
                {$youtube}
                {$vimeo}
                {$addFeedback}
                {$acheivementRow}
                {$readNotice}
            </div>
            ";
        }

        $text = "
        <div class='inner'>
            {$rows}
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getUpdateStudentIdInComment(){
        $db = Zend_Registry::get('db');

        //http://edukitedev.localhost/index.php?module=edukiteWeb_notice&_spAction=updateStudentIdInComment&showHTML=0

        $SQL = "
        SELECT c.*
        FROM comment c
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLParent = "
            SELECT sp.*
            FROM student_parent sp
            WHERE sp.parent_id = {$row['contact_id']}
            ";
            $resultParent = $db->sql_query($SQLParent);
            $numRows = $db->sql_numrows($resultParent);

            if($numRows == 1 && $rowParent['student_id'] != ''){
                $rowParent = $db->sql_fetchrow($resultParent);

                $updateSQL = "
                UPDATE comment
                SET student_id = {$rowParent['student_id']}
                WHERE comment_id = {$row['comment_id']}
                ";
                $result1 = $db->sql_query($updateSQL);
            } else {
                while ($rowParent = $db->sql_fetchrow($resultParent)) {
                    $SQLNotice = "
                    SELECT ns.*
                    FROM notice_student ns
                    WHERE ns.notice_id = {$row['record_id']}
                     AND ns.student_id = {$rowParent['student_id']}
                    ";
                    $resultNotice = $db->sql_query($SQLNotice);
                    $numRowsNotice = $db->sql_numrows($resultNotice);
                    if($numRowsNotice > 0){
                        $updateSQL = "
                        UPDATE comment
                        SET student_id = {$rowParent['student_id']}
                        WHERE comment_id = {$row['comment_id']}
                        ";
                        $result1 = $db->sql_query($updateSQL);
                    }
                }
            }

        }
   }
    /**
     *
     */
    function getCurrYearNoticeRecord($student_id) {
        $db = Zend_Registry::get('db');
        $currentYear = date("Y");

        $SQLNoticeCurYear = "
        SELECT s.student_id
              ,n.notice_id
        FROM student s
        LEFT JOIN (notice_student ns) ON (ns.student_id = s.student_id)
        LEFT JOIN (notice n) ON (n.notice_id = ns.notice_id)
        WHERE s.status = 'Archive'
          AND n.launch_now = 1
          AND s.student_id = {$student_id}
          AND n.academic_year = '{$currentYear}'
          ORDER BY n.notice_id DESC
        ";
        $resultNoticeCurYear  = $db->sql_query($SQLNoticeCurYear);
        $numRowsNoticeCurYear = $db->sql_numrows($resultNoticeCurYear);

        return $numRowsNoticeCurYear;
    }

    /**
     *TCPDF FORMAT
     */
    function getPrintGalleryAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

       // include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
       include_once(CP_CORE_PATH.'CP/www/modules/WebBasic/Home/headfoot.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Edukite Gallery');
        $pdf->SetTitle('Edukite Gallery');
        $pdf->SetSubject('Edukite Gallery');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER, 10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER, 0);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // convert TTF font to TCPDF format and store it on the fonts folder
        $fontname = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Arial/arial.ttf', 'TrueTypeUnicode', '', 96);

        // ---------------------------------------------------------QUOTE QUERY START

        //$pdf->SetFont('Arial','B',10);
        $pdf->AddPage();
        $pdf->SetFont($fontname, 'B', 11, '', false);

        $contact_id    = $fn->getReqParam('contact_id');
        $status        = $fn->getReqParam('status');
        $notice_id     = $fn->getReqParam('notice_id');
        $currentDate   = date('Y-m-d');
        $teacherKiteId = $fn->getReqParam('teacherKiteId');

        if($status != ''){
            $status = "AND n.status = '{$status}'";
        }

        $kite_id = "AND ns.student_id = {$contact_id}";

        $SQL = "
        SELECT n.* ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.launch_now = 1
        {$kite_id}
        AND (n.expiry_date >= '{$currentDate}'
            OR n.expiry_date = ''
            OR n.expiry_date IS NULL)
        AND n.notice_id = {$notice_id}
        {$status}
        GROUP BY ns.notice_id
        ORDER BY n.launch_date DESC, n.notice_id DESC
        ";
        $result         = $db->sql_query($SQL);
        $numRows        = $db->sql_numrows($result);
        $row            = $db->sql_fetchrow($result);
        $today = date("Y-m-d");
        $count = 0;
        $studentImage = '';

        $SQL = "
        SELECT *
        FROM media
        WHERE record_id = '{$contact_id}'
        AND room_name = 'edukite_student'
        AND record_type =  'picture'
        ";

        $resultStudentImage  = $db->sql_query($SQL);
        $numRows1            = $db->sql_numrows($resultStudentImage);
        if($numRows1){
            $rowStudentImage     = $db->sql_fetchrow($resultStudentImage);
            $studentImage = '<img border="0" src="/media/large/'.$rowStudentImage['file_name'].'" width="60"/>';
        }

        $studentRec    = $fn->getRecordRowByID('student', 'student_id', $contact_id);
        $tb1Title ='
        <table border="0" cellpadding="4" width="100%">
          <tr>
             <td width="85%" color="#447EB5" align="centre" style="font-size:24px; font-weight:bold;"><br/><br/>'.$row['title'].'</td>
             <td width="15%" style="font-weight:bold;" align ="centre">'.$studentImage.'<br/>'.$studentRec['first_name'].' '.$studentRec['last_name'].'</td>
          </tr>
        </table>
        ';

        $pdf->writeHTML($tb1Title, true, false, false, false, '');
        $desc = nl2br($row['description']);

        $tb1Gallery ='<table border="0" cellpadding="4" width="100%">';
        $Image = '';
        //while ($row = $db->sql_fetchrow($result)) {
            $launch_date = $fn->getCPDate($row['launch_date'], 'D d F Y');
            $tb1Gallery = $tb1Gallery.'
            <tr bgcolor="#A8C6FE" style="color: #FE4114;font-weight:bold;">
                <td width="9%">TITLE:</td>
                <td width="36%">'.$row['title'].'</td>
                <td width="5%">by</td>
                <td width="25%">'.$row['teacher_name'].'</td>
                <td width="25%">'.$launch_date.'</td>
            </tr>
            <tr>
                <td width="100%">'.$desc.'</td>
            </tr>
            ';

            $SQL = "
            SELECT *
            FROM media m
            LEFT JOIN (notice n) ON (n.notice_id = m.record_id)
            WHERE m.record_id = {$row['notice_id']}
            AND m.record_type = 'picture'
            AND m.room_name = 'edukite_notice'
            ORDER BY COALESCE(sort_order, 999999999) ASC
            ";

            $resultImage     = $db->sql_query($SQL);
            $tb1Gallery = $tb1Gallery.'<tr nobr="true">';
            $i = 0;
            while ($rowImage = $db->sql_fetchrow($resultImage)) {
               if($i == 2){
                    $tb1Gallery = $tb1Gallery.'</tr><tr nobr="true">';
                    $i = 1;
               }else{
                    $i++;
               }

               $tb1Gallery = $tb1Gallery.'
                <td width="50%" align="left">
                    <img src="/media/large/'.$rowImage['file_name'].'" width="360" height="300" />
                </td>
                ';
            }
            $tb1Gallery = $tb1Gallery.'</tr>';

        ///$count++;
        //}

        $tb1Gallery = $tb1Gallery.'</table>';

        if($numRows == 0){
            $tb1noResult ='<div style = "font-size:18px; font-weight:bold;">No Gallery record found for this student.
            </div>';
             $pdf->writeHTML($tb1noResult, true, false, false, false, '');
        }else{
            $pdf->writeHTML($tb1Gallery, true, false, false, false, '');
        }

        $pdf->Output('edukite_gallery.pdf', 'I');

    }
}