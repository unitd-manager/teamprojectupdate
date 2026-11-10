<?
class CP_Www_Modules_EdukiteWeb_Notice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT n.*
        FROM notice n
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'n';
        $status = $fn->getReqParam('status');
        $recordRow = $fn->getReqParam('recordRow');

        if($status == 'Archive' && $recordRow > 0){
            $searchVar->sqlSearchVar[] = "n.status IN ('Active', 'Archive')";
        } else if($status == 'Archive'){
            $searchVar->sqlSearchVar[] = "n.status = 'Archive'";
        } else {
            $searchVar->sqlSearchVar[] = "n.status = 'Active'";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "n.notice_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'n.notice_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   n.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getAddFeedbackSubmit() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $notice_id   = $fn->getPostParam('notice_id');
        $student_id  = $fn->getPostParam('student_id');
        $notes       = $fn->getPostParam('notes');

        if (!$this->getAddFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = array();
        $noticeRec    = $fn->getRecordRowByID('notice', 'notice_id', $notice_id );

        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $fa['contact_id']   = $_SESSION['cpContactId'];
            $fa['staff_id']     = $noticeRec['teacher_id'];
            $parent_id          = $_SESSION['cpContactId'];
        } else {
            $histRec    = $fn->getRecordRowByID('student_parent', 'student_id', $student_id);

            $fa['contact_id']   = $histRec['parent_id'];
            $fa['staff_id']     = $_SESSION['cpContactId'];
            $parent_id          = $histRec['parent_id'];
        }

        $fa['comments']     = $notes;
        $fa['record_id']    = $notice_id;
        $fa['student_id']   = $student_id;
        $fa['room_name']    = $tv['module'];
        $fa['record_type']  = $_SESSION['cpLoginTypeWWW'];
        $fa['creation_date']= date('Y-m-d h:i:s');
        $fa['comment_date']= date('Y-m-d h:i:s');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'comment');
        $db->sql_query($SQL);
        $comment_id = $db->sql_nextid();

        //-----------------------------------------------------------------//
        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];
        $fromEmailId = '';

        if(strpos($hostName, 'masad') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
        }
        else if(strpos($hostName, 'queenwood') !== false){
            $fromEmailId = "autonotification3@kitesonacloud.com";
        }
        else if(strpos($hostName, 'wavesedukite') !== false){
            $fromEmailId = "autonotification4@kitesonacloud.com";
        }
        else if(strpos($hostName, 'weecare') !== false){
            $fromEmailId = "autonotification5@kitesonacloud.com";
        }
        else if(strpos($hostName, 'rosebay') !== false){
            $fromEmailId = "autonotification6@kitesonacloud.com";
        }
        else if(strpos($hostName, 'essington') !== false){
            $fromEmailId = "autonotification7@kitesonacloud.com";
        }
        else if(strpos($hostName, 'kincopedukite') !== false){
            $fromEmailId = "autonotification8@kitesonacloud.com";
        }
        else if(strpos($hostName, 'marli') !== false){
            $fromEmailId = "autonotification9@kitesonacloud.com";
        }
        else if(strpos($hostName, 'sandedukite') !== false){
            $fromEmailId = "autonotification10@kitesonacloud.com";
        }
        else if(strpos($hostName, 'stepedukite') !== false){
            $fromEmailId = "autonotification1@kitesonacloud.com";
        }
        else if(strpos($hostName, 'stpaulsedukite') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
        }
        else if(strpos($hostName, 'scbcedukite') !== false){
            $fromEmailId = "autonotification3@kitesonacloud.com";
        }
        else if(strpos($hostName, 'mary') !== false){
            $fromEmailId = "autonotification4@kitesonacloud.com";
        }
        else if(strpos($hostName, 'localhost') !== false){
            $fromEmailId = "autonotification2@kitesonacloud.com";
        }
        else if(strpos($hostName, 'gumnut') !== false){
            $fromEmailId = "autonotification5@kitesonacloud.com";
        }
        else if(strpos($hostName, 'edukiteweb') !== false){
            $fromEmailId = "autonotification6@kitesonacloud.com";
        }
        else{
            $fromEmailId = "autonotification9@kitesonacloud.com";
        }

        //-----------------------------------------------------------------//
        $teacherRec  = $fn->getRecordRowByID('teacher', 'teacher_id', $noticeRec['teacher_id']);
        $studentRec  = $fn->getRecordByCondition('student', "student_id = '{$student_id}'");
        $parentRec   = $fn->getRecordRowByID('parent', 'parent_id', $parent_id);
        $studentName = $studentRec['first_name'] . ' ' . $studentRec['last_name'] ;
        $parentName  = $parentRec['first_name'] . ' ' . $parentRec['last_name'] ;
        $teacherName = $teacherRec['first_name'] . ' ' . $teacherRec['last_name'] ;
        $link        = $_SERVER['HTTP_HOST'];

        $currentDate  = date('d-M-Y l h:i:s A');

        $message = $ln->gd('m.edukiteWeb.notice.form.parentFeedback.email.notifyBody');
        $message = str_replace('[[teacher_name]]', $teacherName, $message);
        $message = str_replace('[[student_name]]', $studentName, $message);
        $message = str_replace('[[notice_title]]', $noticeRec['title'], $message);
        $message = str_replace('[[parent_name]]', $parentName, $message);
        $message = str_replace('[[comments]]', $fa['comments'], $message);
        $message = str_replace('[[site_title]]', $cpCfg['cp.siteTitle'], $message);
        $message = str_replace('[[site_url]]', $link, $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = 'Edukite Feedback Notification - ' . $noticeRec['title'];
        $fromName  = 'Edukite';
        $fromEmail = $fromEmailId;
        $toName    = $teacherName;
        $toEmail   = $teacherRec['email'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $exp = array('showHeader' => false);

        if($cpCfg['cp.sendFeedbackEmailToTeacher'] == 1 && $_SESSION['cpLoginTypeWWW'] == 'edukite_parent') {
            $emailMsg->sendEmail($exp);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddFeedbackValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('notes', 'Please enter the feedback');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddTeacherFeedbackSubmit() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $notice_id   = $fn->getPostParam('notice_id');
        $notes       = $fn->getPostParam('notes');

        if (!$this->getAddTeacherFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = array();
        $noticeRec    = $fn->getRecordRowByID('notice', 'notice_id', $notice_id );

        $fa['staff_id']     = $_SESSION['cpContactId'];
        $fa['comments']     = $notes;
        $fa['record_id']    = $notice_id;
        $fa['room_name']    = $tv['module'];
        $fa['record_type']  = $_SESSION['cpLoginTypeWWW'];
        $fa['creation_date']= date('Y-m-d h:i:s');
        $fa['comment_date']= date('Y-m-d h:i:s');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'teacher_comment');
        $db->sql_query($SQL);
        $comment_id = $db->sql_nextid();

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddTeacherFeedbackValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('notes', 'Please enter the feedback');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getParentProfileFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $first_name = $fn->getPostParam('first_name');
        $last_name = $fn->getPostParam('last_name');
        $email     = $fn->getPostParam('email');
        $pass_word = $fn->getPostParam('pass_word');
        $parent_id = $fn->getPostParam('parent_id');

        if (!$this->getParentProfileValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = array();
        $fa['first_name'] = $first_name;
        $fa['last_name']  = $last_name;
        $fa['email']      = $email;
        $fa['pass_word']  = $pass_word;

        $whereCondition = "WHERE parent_id = '{$parent_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "parent", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getParentProfileValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('email', 'Usernames must be a valid email address', "email");
        $validate->validateData("pass_word", 'Passwords must contain at least six characters or digits', "empty", $field2 = "", $minCharLength = "6", $maxCharLength = "20");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDailyActivityFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $jellyfish_group_time = $fn->getPostParam('jellyfish_group_time');
        $sea_turtles_group_time = $fn->getPostParam('sea_turtles_group_time');
        $whales_group_time     = $fn->getPostParam('whales_group_time');
        $music = $fn->getPostParam('music');
        $school_readiness_program = $fn->getPostParam('school_readiness_program');
        $todays_meals = $fn->getPostParam('todays_meals');
        $morning_tea = $fn->getPostParam('morning_tea');
        $fruit_break = $fn->getPostParam('fruit_break');
        $lunch = $fn->getPostParam('lunch');
        $dessert = $fn->getPostParam('dessert');
        $teacher_id = $fn->getPostParam('teacher_id');
        $title = $fn->getPostParam('title');
        $notes = $fn->getPostParam('notes');

        if (!$this->getDailyActivityValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = array();
        $fa['jellyfish_group_time'] = $jellyfish_group_time;
        $fa['sea_turtles_group_time']  = $sea_turtles_group_time;
        $fa['whales_group_time']  = $whales_group_time;
        $fa['music']  = $music;
        $fa['school_readiness_program']  = $school_readiness_program;
        $fa['todays_meals']  = $todays_meals;
        $fa['morning_tea']  = $morning_tea;
        $fa['fruit_break']  = $fruit_break;
        $fa['lunch']  = $lunch;
        $fa['dessert'] = $dessert;
        $fa['teacher_id'] = $teacher_id;
        $fa['creation_date']= date('Y-m-d h:i:s');
        $fa['daily_activity_date']= date('Y-m-d');
        $fa['title']= $title;
        $fa['notes']= $notes;

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'daily_activity');
        $db->sql_query($SQL);
        $daily_activity_id = $db->sql_nextid();

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDailyActivityValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
