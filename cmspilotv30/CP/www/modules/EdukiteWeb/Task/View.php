<?
class CP_Www_Modules_EdukiteWeb_Task_View extends CP_Common_Lib_ModuleViewAbstract
{
    /*
     *
     */
    function getDetail($row) {
        $db = Zend_Registry::get('db');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $teacherKite = $fn->getReqParam('teacherKite');
        $notice_id  = $row['notice_id'];

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $histRec    = $fn->getRecordRowByID('student_parent', 'parent_id', $_SESSION['cpContactId']);
            if($_SESSION['student_id'] != ''){
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $_SESSION['student_id'] );
            } else {
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $histRec['student_id'] );
            }
            $student_id = $studRec['student_id'] ;

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
        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];
        $urlArray['sitePfxId'] = $student_id;
        $kiteUrl = $cpUrl->make_seo_url($urlArray);

        if($teacherKite == 1){
            $returnUrl = '/controller/notice/edit/'. $notice_id . '/';
            //$returnUrl = "javascript:history.back();";
        } else {
            $returnUrl = ($tv['sitePfxId'] != '') ? $kiteUrl :  '/kite/home/';
        }

        $fa = array();
        $task_student_id = '';

        $taskStudentRec = $fn->getRecordByCondition('task_student', "student_id = '{$student_id}' AND task_id = '{$notice_id}'");
        $task_student_id = $taskStudentRec['task_student_id'];

        $displayComment = "";
        /*if($teacherKite == 1){
            $sqlNoticeStudent = "
            SELECT ns.*
            FROM notice_student ns
            WHERE ns.notice_id = {$notice_id}
            ";
            $resultNoticeStudent = $db->sql_query($sqlNoticeStudent);
            while ($rowNS = $db->sql_fetchrow($resultNoticeStudent)) {
                $taskStudentRec = $fn->getRecordByCondition('task_student', "student_id = '{$rowNS['student_id']}' AND task_id = '{$notice_id}'");

                if($taskStudentRec['task_student_id'] == ''){
                    $fa['task_id']    = $notice_id;
                    $fa['student_id'] = $rowNS['student_id'];
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'task_student');
                    $db->sql_query($SQL);
                    //$task_student_id = $db->sql_nextid();
                }
            }
        }
        if($taskStudentRec['task_student_id'] == '' && $teacherKite != 1){
            $fa['task_id']    = $notice_id;
            $fa['student_id'] = $student_id;
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'task_student');
            $db->sql_query($SQL);
            $task_student_id = $db->sql_nextid();
        }*/

        $sqlNoticeStudent = "
        SELECT ns.*
        FROM notice_student ns
        WHERE ns.notice_id = {$notice_id}
        ";
        $resultNoticeStudent = $db->sql_query($sqlNoticeStudent);
        while ($rowNS = $db->sql_fetchrow($resultNoticeStudent)) {
            $taskStudentRec = $fn->getRecordByCondition('task_student', "student_id = '{$rowNS['student_id']}' AND task_id = '{$notice_id}'");
            $task_student_id = $taskStudentRec['task_student_id'];

            if($taskStudentRec['task_student_id'] == ''){
                $fa['task_id']    = $notice_id;
                $fa['student_id'] = $rowNS['student_id'];
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'task_student');
                $db->sql_query($SQL);
                $task_student_id = $db->sql_nextid();
            }
        }

        if($taskStudentRec['task_student_id'] != ''){
            $displayComment = $this->getDisplayComment($notice_id, $student_id, $task_student_id);
        }

        $addComment = "";
        if($row['homework_chat'] == 1){
            $addComment = "{$this->getAddComment($notice_id, $student_id, $task_student_id)}";
        }
        if($teacherKite == 1){
            $homeworkList = "<div class='myHomeworkList'>{$this->getMyHomeworkTeacherView($notice_id)}</div>";
            $displayComment = $this->getDisplayComment($notice_id, $student_id, $task_student_id);
        } else {
            $homeworkList = "<div class='myHomeworkList'>{$this->getMyHomework($notice_id, $student_id)}</div>";
        }

        $text = "
        <div class='returnHome'>
            <a href='{$returnUrl}' class='backToList txtCenter'>return</a>
        </div>
        <div class='header'>
        </div>
        <div class='ym-grid'>
            <div class='ym-g273 ym-gl'>
                <div class='yourTask'>
                    <img src='/cmspilotv30/CP/www/themes/Kite/images/YourTask.png'/ title=''>
                    {$this->getYourTask($notice_id)}
                </div>
            </div>
            <div class='ym-g281 ym-gl'>
                <div class='ym-gbox'>
                    <div class='myHomework'>
                        <img src='/cmspilotv30/CP/www/themes/Kite/images/MyHomework.png'/ title=''>
                        {$homeworkList}
                    </div>
                </div>
            </div>
            <div class='ym-g208 ym-gl'>
                <div class='myChat'>
                    <img src='/cmspilotv30/CP/www/themes/Kite/images/MyChat.png'/ title=''>
                    {$addComment}
                    <div class='commentDisplay'>
                        {$displayComment}
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
    function getYourTask($notice_id) {
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
        $currentYear = date("Y");

        //in the sql group by is used to eliminate duplicates if the same notice is linked through class and cohort and individual or either two of them.
        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        WHERE n.notice_id = {$notice_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $hostName   = $_SERVER['HTTP_HOST'];
            $desc = nl2br($row['description']);
            $links = '';
            $website='';
            $youtube='';

            $instName = 'wImagesSlider' . $row['notice_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');

            $pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_notice'
                ,'record_id' => $row['notice_id']
                ,'width'     => 257
                ,'height'    => 200
                ,'zoom'      => true
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
                ,'executeScript' => true
            ))}
            ";

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
                    <iframe width=\"253\" height=\"215\" src=\"https://www.youtube.com/embed/$ytcode?rel=0\" frameborder=\"0\" allowfullscreen></iframe>
                </div>
                ";
            }

            $rows .= "
            <div class='innerContent pt10'>
                <div class='mb10 ym-contain-dt'>
                    <div class='date'><i>Posted by: {$row['teacher_name']}</i></div>
                    <div class='date mt10'><i>{$fn->getCPDate($row['activity_date'], 'D d F Y')}</i></div>
                </div>
                <h1>{$ln->gfv($row, 'title', '0')}</h1>
                <div class='description'>
                    <p>{$desc}</p>
                </div>
                {$pic}
                {$links}
                {$website}
                {$youtube}
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
    function getMyHomework($notice_id='', $student_id='') {
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $student_id;

        $rows = '';
        $desc = '';
        $comments = '';
        $task_student_id = '';
        $website_link = '';
        $currentYear = date("Y");
        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        if($student_id == ''){
            $student_id = $fn->getReqParam('student_id');
        }

        /*$SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.notice_id = {$notice_id}
        ";
        $result  = $db->sql_query($SQL);*/

        $SQL = "
        SELECT t.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
        FROM task_student t
        LEFT JOIN (student s) ON (t.student_id = s.student_id)
        WHERE t.task_id = {$notice_id}
          AND t.student_id = {$student_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $comments = $row['student_comment'];
            $desc = nl2br($row['student_comment']);
            $pic = '';
            $links = '';
            $website='';

            $instName = 'wImagesSlider' . $row['task_student_id'];
            $$instName = getCPWidgetObj('media_imagesSlider');
            $pic = "
            {$$instName->getWidget(array(
                 'module'    => 'edukite_task'
                ,'record_id' => $row['task_student_id']
                ,'width'     => 252
                ,'height'    => 200
                ,'handle'    => 'slider' . $row['task_student_id']
                ,'zoom'      => true
                ,'thumbnails' => false
                ,'showCaption' => false
                ,'useRecType1Only' => true
                ,'executeScript' => true
            ))}
            ";
            $attArr = $media->getFirstMediaRecord('edukite_task', 'attachment', $row['task_student_id']);
            if (count($attArr) > 0){
                $links = "
                <div class='links'>
                    {$media->getMediaFilesDisplayThin('edukite_task', 'attachment', $row['task_student_id'])}
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

            $rows .= "
            <div class='innerContent'>
                <div class='mt10 mb10 ym-contain-dt'>
                    <div class='date'><i>Posted by: {$row['student_name']}</i></div>
                    <div class='date mt10'><i>{$fn->getCPDate($row['creation_date'], 'D d F Y')}</i></div>
                </div>
                <p>{$desc}</p>
                {$pic}
                {$links}
                {$website}
            </div>
            ";
            $task_student_id = $row['task_student_id'];
        }
        $expNotes  = array('rowCls' => 'textAreaDiv', 'fieldCls' => $notice_id);
        $formAction = "/index.php?module=edukiteWeb_task&_spAction=uploadTaskSubmit&showHTML=0";
        $uploadButton = '';
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_student'){
            $uploadButton = "<div class='uploadButton mt10'><img src='/cmspilotv30/CP/www/themes/Kite/images/upload_button.png'/ title=''></div>";
        }

        $rowTaskStudentId = array('task_id' => $task_student_id);

        $text = "
        <div class='inner'>
            {$uploadButton}
            <div class='uploadTask'>
                <form id='portalForm' class='yform cpJqForm' method='post' action='{$formAction}'>
                    <div class='type-button'>
                        <input type='submit' name='submit' value='' notice_id='{$notice_id}' student_id='{$student_id}'/>
                    </div>
                    <textarea name='student_comment' id='fld_student_comment' class='textAreaDiv'>{$comments}</textarea>
                    {$media->getRightPanelMediaDisplay('Picture', 'edukite_task', 'picture', $rowTaskStudentId)}
                    {$media->getRightPanelMediaDisplay('Attachment', 'edukite_task', 'attachment', $rowTaskStudentId)}
                    <label for='fld_links'>Copy Web Links Here</label>
                    <textarea name='links' id='fld_links' class='textAreaDiv'>{$website_link}</textarea>
                    <input type='submit' name='x_submit' class='submithidden' />
                    <input type='hidden' name='notice_id' value='{$notice_id}' />
                    <input type='hidden' name='student_id' value='{$student_id}' />
                    <input type='hidden' name='task_student_id' value='{$task_student_id}' />
                    <a href='#' id='reload'>Back to List</a>
                </form>
            </div>
            <div class='displayUploadedTask'>
                {$rows}
            </div>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getMyHomeworkTeacherView($notice_id) {
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        $task_student_id = '';

        $SQL = "
        SELECT t.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
              ,n.title
        FROM task_student t
        LEFT JOIN (student s) ON (t.student_id = s.student_id)
        LEFT JOIN (notice n) ON (n.notice_id = t.task_id)
        WHERE t.task_id = {$notice_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            if($row['student_comment'] != '' || $row['links'] != '' || $row['creation_date'] != ''){
                $urlArray = array();
                $urlArray['siteType'] = 'kite';
                $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Task');
                $urlArray['section_title'] = $secRec['title'];
                $urlArray['sitePfxId'] = $row['student_id'];
                $urlArray['record_id'] = $row['task_id'];
                $urlArray['record_title'] = $row['title'];
                $goToUpload = $cpUrl->make_seo_url($urlArray);

                $rows .= "
                <div class='innerContent homeworkTeacherView'>
                    <div class='mt10 mb10 ym-contain-dt'>
                        <div class=''><a href='{$goToUpload}'>{$row['student_name']}</a></div>
                        <div class='mt10'>{$fn->getCPDate($row['creation_date'], 'd F')}</div>
                    </div>
                </div>
                ";
            }
        }

        $text = "
        <div class='inner'>
            <div class='mt10'><p>The following students have posted homework their kites</p></div>
            {$rows}
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getAddComment($notice_id, $student_id, $task_student_id) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $teacherKite = $fn->getReqParam('teacherKite');

        $formAction = "/index.php?module=edukiteWeb_task&_spAction=addCommentSubmit&showHTML=0";
        //$student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $student_id;
        $commentheading = '';
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
            $commentheading = 'Post Comment to Student';
        }else{
            $commentheading = 'Post Comment to Teacher';
        }

        if($teacherKite == 1){
            $comments_to_class = '';
            $addCommentNote    = '';
        }
        else{
            $comments_to_class ="<div class='postcommentCheckBox'>
                                    <input type='checkbox' id='fld_studentPost' class='checkBox' name='studentPost' value='1' />
                                    <label for='fld_studentPost'>Please select the check box to post comments to all students of the class/cohort</label>
                                </div>";

            $addCommentNote  ="<div class='addCommentNote addCommentRemove'>
                                    <h6><b>Note:</b> This comment will be &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;posted to all students &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;of the class.</h6>
                                </div>";
        }

        $text = "
        <form id='portalForm_$notice_id' class='yform cpJqForm' method='post' action='{$formAction}'>
            <div class='txtCenter'>{$commentheading}</div>
            <div class='ym-grid'>
                <div class='PostAddComment type-text ym-fbox-text row_comments'>
                    <textarea id='fld_comments' name='comments'></textarea>
                </div>
                {$comments_to_class}
                <div class='postcommentSubmit ym-g35 ym-gr btnSubmit' notice_id='{$notice_id}' student_id='{$student_id}' task_student_id='{$task_student_id}' teacherKite='{$teacherKite}'>
                    <input type='submit' value='Post'/>
                </div>
            </div>
            {$addCommentNote}
            <input type='hidden' name='notice_id' value='{$notice_id}' />
            <input type='hidden' name='student_id' value='{$student_id}' />
            <input type='hidden' name='task_student_id' value='{$task_student_id}' />
            <input type='hidden' name='teacherKite' value='{$teacherKite}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getDisplayComment($notice_id = '', $student_id = '', $task_student_id = '') {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $teacherKite = $fn->getReqParam('teacherKite');

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
        if($task_student_id == ''){
            $task_student_id  = $fn->getReqParam('task_student_id');
        }
        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $student_id;

        if($teacherKite == 1){
            $sql = "
            SELECT sc.*
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
            FROM student_comment_history sc
            LEFT JOIN (student s) ON (sc.source_id = s.student_id)
            WHERE sc.record_id = {$notice_id}
            GROUP BY sc.comments_tag
            ORDER BY sc.comment_date DESC
            ";
        } else {
            $sql = "
            SELECT sc.*
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
            FROM student_comment_history sc
            LEFT JOIN (student s) ON (sc.source_id = s.student_id)
            WHERE sc.record_id = {$notice_id}
              AND sc.task_student_id = {$task_student_id}
              AND sc.student_id = {$student_id}
            ORDER BY sc.comment_date DESC
            ";
        }
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {
            if($row['record_type'] == 'edukite_student'){
                $class = 'chatContent';
                $name = $row['student_name'];
            } else if($row['record_type'] == 'edukite_teacher') {
                $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $row['source_id']);
                $class = 'chatContentblue';
                $name = $teacherRec['first_name'].' '.$teacherRec['last_name'];
            }
            $text .= "
            <div class='{$class} ym-grid'>
                <div class='ym-grid floatbox mb10'>
                    <div class='float_left date'>
                        <i>{$fn->getCPDate($row['comment_date'], 'd/m/y')}</i>
                    </div>
                    <div class='float_right date'>
                        <i>{$name}</i>
                    </div>
                </div>
                <div class=''>
                    <p>{$row['comments']}</p>
                </div>
            </div>
            ";
        }

        return $text;
    }
}