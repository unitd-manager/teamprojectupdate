<?
class CP_Www_Modules_Edukite_Parent_Model extends CP_Common_Modules_Edukite_Parent_Model
{
    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('email', 'Please enter the email');
        $validate->validateData("pass_word", 'Passwords must contain at least six characters or digits', "empty", $field2 = "", $minCharLength = "6");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the firstname');
        $validate->validateData('last_name', 'Please enter the lastname');
        $validate->validateData("pass_word", 'Passwords must contain at least six characters or digits', "empty", $field2 = "", $minCharLength = "6");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'age');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_postal_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'username');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getDeleteLinkedStudents() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $parent_id   = $fn->getReqParam('parent_id');
        $student_id  = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM student_parent
        WHERE student_id = {$student_id}
            AND parent_id = {$parent_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_parent');
        $text    = $viewObj->getRightPanelDefaultContent($parent_id);

        return $text;
    }
    /**
     *
     */
    function getDeleteAllLinkedStudents() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $parent_id   = $fn->getReqParam('parent_id');

        $deleteSQL     = "
        DELETE FROM student_parent
        WHERE parent_id = {$parent_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_parent');
        $text    = $viewObj->getRightPanelDefaultContent($parent_id);

        return $text;
    }
    /**
     *
     This function will send email alert to parent for relevant notices
     */
    function getSendParentEmailAlert() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rows = "";

        set_time_limit(10000000);
        $hostName   = $_SERVER['HTTP_HOST'];
        $fromEmailId = '';

        //http://edukitedev.localhost/index.php?module=edukite_parent&_spAction=sendParentEmailAlert&showHTML=0

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
        //print $fromEmailId;
        //exit();
        $today  = date('Y-m-d');

        //Code to update the creation date to todays date
        $SQLUpdate    = "UPDATE notice_student SET creation_date = '{$today}'
                           WHERE notice_id IN (
                           SELECT notice_id
                           FROM notice
                           WHERE launch_now = 1
                             AND launch_date = '{$today}'
                             AND parent_email_sent = 1
                            )
                           ";

        $resultUpdate = $db->sql_query($SQLUpdate);

        //Code to update the history record - notice_student - to tag all previous records to 1
        $SQLUpdate    = "UPDATE notice_student SET parent_email_sent = 1
                           WHERE creation_date < DATE_ADD(CURDATE(), INTERVAL -2 DAY)";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQL     = "SELECT
                        a.*
                        , b.title as notice_title
                        , b.parent_email_sent as parentEmailSent
                        , b.description
                        , b.parent_email_description
                        , b.template
                        , b.launch_now
                        , concat('', c.first_name, c.last_name) as teachername
                        , c.email as teacher_email
                    FROM  notice_student a, notice b , teacher c
                    WHERE
                        a.notice_id = b.notice_id
                        AND (a.parent_email_sent = 0 OR a.parent_email_sent IS NULL)
                        AND b.teacher_id = c.teacher_id
                        AND a.student_id > 0
                    ORDER BY a.notice_id DESC, a.class_id_hook";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        //global $utilCommon;
        if ($numRows == 0){
           return;
        }
        $parent_email = "";
        $count = 0;
        $notes = '';

        while ($row = $db->sql_fetchrow($result)) { //*** while loop 1
            if ($row['parentEmailSent'] == 1 && $row['launch_now'] == 1 ){
                $student_id  = $row['student_id'];

                if ($row['template'] == 'Daily Diary' || $row['template'] == 'Kite Post Left') {
                    $notes = 'Left Column';
                } else if ($row['template'] == 'Kite Post') {
                    $notes = 'Centre Column';
                } else if ($row['template'] == 'Gallery') {
                    $notes = 'Right Column';
                }

                $SQLParent   = "SELECT b.*
                             ,c.email as parent_email
                             ,c.pass_word as parent_password
                             ,c.first_name as parent_name
                             FROM student_parent b, parent c
                             WHERE b.student_id = {$student_id}
                                   AND c.parent_id = b.parent_id
                                   AND c.email != ''
                                   AND c.status = 'Active'
                             ORDER BY b.parent_id";

               $resultParent = $db->sql_query($SQLParent);
               $numParent    = $db->sql_numrows($resultParent);

               $toEmail = "";
               $first_parent_email  = '';
               $second_parent_email = '';
               $count = 0;
               while ($rowParent = $db->sql_fetchrow($resultParent)) { //*** while loop 2
                    $parent_email     = $rowParent['parent_email'];
                    $noticeStudentRow = $fn->getRecordRowByID('notice_student', 'notice_student_id', $row['notice_student_id']);

                    if ($parent_email != "" && $noticeStudentRow['parent_email_sent'] != 1){
                        $description = '';
                        $parent_name = $rowParent['parent_name'];
                        if($row['description']){
                            $description = $cpUtil->formatPara($cpUtil->urlise($row['description']));
                            $description = substr($description, 0 , 800);
                            $description =  $description . '...';
                        }

                        if($hostName == 'weecare2.edukite.com'){
                            $hostName = 'weecare.edukite.com';
                        }
                        /*$message     = "
                                        Dear {$parent_name},<br><br>
                                        New Edukite Notice:  <b>{$row['notice_title']} by {$row['teachername']} </b> <br><br>

                                        Notice Text:<br>
                                        {$description}<br><br>

                                        <b>To Read More</b> view the notice in Edukite, click the link below and login to EduKite using your user name and password.<br><br>
                                        <a href=\"http://{$hostName}\"><u>{$hostName}</u></a><br><br>

                                        Please find your notice in <b>{$notes}</b> of the Kite.<br><br>

                                        if you need any help please contact us at <u>info@edukite.com</u>. Please include the name of your school and child when contacting us.<br><br>
                                        Please <a href=\"http://{$hostName}\"><u>click here</u></a> to unsubscribe from the email alert.<br><br>

                                        Thanks<br>
                                        Edukite Team<br><br>

                                        ";*/
                        $message     = "
                                        Dear {$parent_name},<br><br>
                                        New Edukite Notice:  <b>{$row['notice_title']} by {$row['teachername']} </b> <br><br>

                                        Notice Text:<br>
                                        {$description}<br><br>

                                        <b>To Read More</b> view the notice in Edukite, click the link below and login to EduKite using your user name and password.<br><br>
                                        <a href=\"http://{$hostName}\"><u>{$hostName}</u></a><br><br>

                                        Please find your notice in <b>{$notes}</b> of the Kite.<br><br>

                                        if you need any help please contact us at <u>info@edukite.com</u>. Please include the name of your school and child when contacting us.<br><br>
                                        Please <a href=\"http://{$hostName}\"><u>click here</u></a> to unsubscribe from the email alert.<br><br>

                                        Thanks<br>
                                        Edukite Team<br><br>

                                        ";
                        $subject     = 'Edukite Notice - ' . $row['notice_title'];
                        $fromName    = $hostName;
                        $fromEmail   = $fromEmailId;
                        $toName      = $parent_name;
                        $toEmail     = $parent_email;

                        $args = array(
                                 'toName'    => $toName
                                ,'toEmail'   => $toEmail
                                ,'subject'   => $subject
                                ,'message'   => $message
                                ,'fromName'  => $fromName
                                ,'fromEmail' => $fromEmail
                            );

                        if($count == 0){
                           $first_parent_email = $parent_email;
                            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
                            $exp = array('showHeader' => false);
                            //print 1;
                            $emailMsg->sendEmail($exp);
                        }

                        if($count == 1 && $first_parent_email != $parent_email){
                            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
                            $exp = array('showHeader' => false);
                            //print 2;
                            $emailMsg->sendEmail($exp);
                        }

                        if($count > 1){
                            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
                            $exp = array('showHeader' => false);
                            //print 3;
                            $emailMsg->sendEmail($exp);
                        }

                        //to check if we are sending email to two parents who are having same email
                        //print $count . 'Parent:' . $rowParent['parent_id'] . ' Student : ' . $student_id . ' / Notice Studn : ' . $row['notice_student_id'] . ' / Notice  : ' . $row['notice_id'] ."<br>";

                        //This code is to check if the same parent has more students, linked to this notice and then avoid sending the same email for other students.
                        $SQLAllStudent ="SELECT student_id
                                     FROM student_parent
                                     WHERE parent_id ={$rowParent['parent_id']} AND student_id != {$student_id}";
                        $resultAllStudent = $db->sql_query($SQLAllStudent);
                        $numAllStudent    = $db->sql_numrows($resultAllStudent);
                        while ($rowAllStudent = $db->sql_fetchrow($resultAllStudent)) {
                            $allStudent = $rowAllStudent['student_id'] . ',';
                        }

                        if($numAllStudent){
                            $allStudent = substr($allStudent,0,-1);

                            $SQLUpdate1    = "
                            UPDATE notice_student SET parent_email_sent = 1
                            WHERE notice_id = {$row['notice_id']}
                                AND student_id IN ($allStudent)
                             ";
                            $resultUpdate1 = $db->sql_query($SQLUpdate1);
                        }

                        $count++;
                        //this will update that the email is sent to the parents.
                        //print $count . ' - Parent:' . ' No of Parent : ' . $numParent  ."<br>";
                        if($numParent == $count){
                            $SQLUpdate    = "UPDATE notice_student SET parent_email_sent = 1
                                            WHERE notice_student_id = {$row['notice_student_id']}";
                            $resultUpdate = $db->sql_query($SQLUpdate);

                            $SQLUpdate    = "UPDATE notice_student SET parent_email_sent = 1
                                            WHERE notice_id = {$row['notice_id']}
                                            AND student_id = {$student_id}";
                            $resultUpdate = $db->sql_query($SQLUpdate);
                        }

                    }
                }
            }
        }
    }
    /**
     *
     This function will send email alert to parent for relevant notices
     */
    function getSendParentEmailAlertWorkingFunctionSyedMay2014() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        set_time_limit(10000000);
        $hostName   = $_SERVER['HTTP_HOST'];
        $fromEmailId = '';

        //http://edukitedev.localhost/index.php?module=edukite_parent&_spAction=sendParentEmailAlert&showHTML=0

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
        //print $fromEmailId;
        //exit();
        $today  = date('Y-m-d');

        $SQLUpdate    = "UPDATE notice_student SET creation_date = '{$today}'
                           WHERE notice_id IN (
                           SELECT notice_id
                           FROM notice
                           WHERE launch_now = 1
                             AND launch_date = '{$today}'
                             AND parent_email_sent = 1
                            )
                           ";

        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLUpdate    = "UPDATE notice_student SET parent_email_sent = 1
                           WHERE creation_date < DATE_ADD(CURDATE(), INTERVAL -2 DAY)";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $SQL     = "SELECT
                        a.*
                        , b.title as notice_title
                        , b.parent_email_sent as parentEmailSent
                        , b.description
                        , b.parent_email_description
                        , b.launch_now
                        , concat('', c.first_name, c.last_name) as teachername
                        , c.email as teacher_email
                    FROM  notice_student a, notice b , teacher c
                    WHERE
                        a.notice_id = b.notice_id
                        AND (a.parent_email_sent = 0 OR a.parent_email_sent IS NULL)
                        AND b.teacher_id = c.teacher_id
                        AND a.student_id > 0
                    ORDER BY a.notice_id DESC";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        //global $utilCommon;
        if ($numRows == 0){
           return;
        }
         $parent_email = "";
            $count = 0;

        while ($row = $db->sql_fetchrow($result)) { //*** while loop 1
            if ($row['parentEmailSent'] == 1 && $row['launch_now'] == 1 ){
                $student_id  = $row['student_id'];

                $SQLParent   = "SELECT b.*
                             ,c.email as parent_email
                             ,c.pass_word as parent_password
                             ,c.first_name as parent_name
                             FROM student_parent b, parent c
                             WHERE b.student_id = {$student_id}
                                   AND c.parent_id = b.parent_id
                             ORDER BY b.parent_id";

               $resultParent = $db->sql_query($SQLParent);
               $numParent    = $db->sql_numrows($resultParent);

               $toEmail = "";
               while ($rowParent = $db->sql_fetchrow($resultParent)) { //*** while loop 2
                    $parent_email     = $rowParent['parent_email'];
                    $noticeStudentRow = $fn->getRecordRowByID('notice_student', 'notice_student_id', $row['notice_student_id']);

                    if ($parent_email != "" && $noticeStudentRow['parent_email_sent'] != 1){
                    //if ($parent_email != ""){
                        $parent_name = $rowParent['parent_name'];
                        if ($row['parent_email_description'] == 1){
                            $description = $row['description'];
                            //$description = $utilCommon->formatPara($utilCommon->urlise($row['description']));
                            //$description = substr($description, 0 , 400);
                        }
                        else{
                            $description = '';
                        }
                        $message     = "
                                        Dear {$parent_name},<br><br>
                                        A new notice has been placed on your child's kite by {$row['teachername']} <br><br>

                                        The title of the notice is : <b>{$row['notice_title']}</b><br>
                                        To view the notice in the kite click on the link below and login to EduKite using your user name and password.<br>
                                        <a href=\"http://{$hostName}\"><u>{$hostName}</u></a><br><br>

                                        if you need any help please contact us <u>info@edukite.com</u><br><br>
                                        Please <a href=\"http://{$hostName}\"><u>click here</u></a> to unsubscribe from the email alert.<br><br>

                                        Thanks<br>
                                        Edukite Team<br><br>

                                        ";
                        $subject     = 'Edukite Notice - ' . $row['notice_title'];
                        $fromName    = $hostName;
                        $fromEmail   = $fromEmailId;
                        $toName      = $parent_name;
                        $toEmail     = $parent_email;
                        //$toEmail     = 'syed@usoftsolutions.com';

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
                        $emailMsg->sendEmail($exp);
                        $count++;

                        //print $count . 'Parent:' . $rowParent['parent_id'] . ' Student : ' . $student_id . ' / Notice Studn : ' . $row['notice_student_id'] . ' / Notice  : ' . $row['notice_id'] ."<br>";

                        $SQLUpdate    = "UPDATE notice_student SET parent_email_sent = 1
                                        WHERE notice_student_id = {$row['notice_student_id']}";
                        $resultUpdate = $db->sql_query($SQLUpdate);

                        $SQLAllStudent ="SELECT student_id
                                     FROM student_parent
                                     WHERE parent_id ={$rowParent['parent_id']}";
                        $resultAllStudent = $db->sql_query($SQLAllStudent);
                        while ($rowAllStudent = $db->sql_fetchrow($resultAllStudent)) {
                            $allStudent = $rowAllStudent['student_id'] . ',';
                        }

                        $allStudent = substr($allStudent,0,-1);

                        $SQLUpdate1    = "
                        UPDATE notice_student SET parent_email_sent = 1
                        WHERE notice_id = {$row['notice_id']}
                            AND student_id IN ($allStudent)
                         ";
                        $resultUpdate1 = $db->sql_query($SQLUpdate1);
                    }
                }
            }
        }
    }

    /**
     *
     This function will send username and password to the parents
     */
    function getSendUsernameToParent() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        print 'You are about to send emails to all parents, to proceed remove the **exit** from the code<br>';
        exit();

        //edukitedev.localhost/index.php?module=edukite_parent&_spAction=sendUsernameToParent&showHTML=0


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
        print $fromEmailId . '<br>';
        //exit();
        $today  = date('Y-m-d');

      $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     FROM parent c
                     WHERE c.status= 'Active'
                     ORDER BY c.parent_id";
                     /*LEFT JOIN (student_parent sp) ON (sp.parent_id = c.parent_id)
                     LEFT JOIN (class_student s) ON (s.student_id = sp.student_id)*/

       //To Email Parents who are new to school
     /*
       $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     FROM parent c
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = c.parent_id)
                         LEFT JOIN (student s) ON (s.student_id = sp.student_id)
                     where s.academic_year ='2015'
                     ORDER BY c.parent_id
                     ";
         */
      $resultParent = $db->sql_query($SQLParent);
      $numParent    = $db->sql_numrows($resultParent);

      $toEmail = "";
      $count = 0;
      while ($rowParent = $db->sql_fetchrow($resultParent)) {
         $student_names = '';
         $parent_email = $rowParent['parent_email'];
         if ($parent_email != ""){

            /*
            $SQLStudParent   = "SELECT s.* , sp.student_parent_id
                         FROM student_parent sp
                         LEFT JOIN (student s) ON (s.student_id = sp.student_id)
                         WHERE sp.parent_id = {$rowParent['parent_id']}
                         ";
            $resultStudParent = $db->sql_query($SQLStudParent);

            while ($rowStudParent = $db->sql_fetchrow($resultStudParent)) {
                if($rowStudParent['student_id']){
                    $SQLYearGroup  = "SELECT yg.*
                                 FROM student_year_group yg
                                 WHERE yg.student_id = {$rowStudParent['student_id']}
                                 ";
                    $resultYearGroup = $db->sql_query($SQLYearGroup);

                    while ($rowYearGroup = $db->sql_fetchrow($resultYearGroup)) {
                        if($rowYearGroup['year_group_id'] == 18
                           OR $rowYearGroup['year_group_id'] == 19
                           OR $rowYearGroup['year_group_id'] == 20){
                            $student_names .="
                            Student Name : {$rowStudParent['first_name']} {$rowStudParent['last_name']} <br>
                            Username: {$rowStudParent['username']} <br>
                            Password: {$rowStudParent['pass_word']} <br><br>
                            ";
                        }
                    }
                }
            }
            */

            //print $student_names;
            //return;
            $parent_name = $rowParent['parent_name'];

            /*$message="
                Dear {$parent_name},<br><br>
                Please find the following login details below for Edukite<br><br>

                Please use the below details for Parent Login:<br>

                Username : {$parent_email}<br>
                Password : {$rowParent['parent_password']}<br><br>

                Please find the details for Student(s) Login:<br>
                {$student_names}

                To access your child's kite (or school web communication page), please click the following link, click on Parent, enter your email address and password, then click ENTER, further instructions are on the kite page.<br><br>

                Students click on the Student button and add their login details in the same way.<br><br>

                Edukite Link:  <a href=\"http://{$hostName}\"><u>{$hostName}</u></a><br><br>

                Any issues, please contact info@edukite.com.<br><br>

                With best regards,<br>
                The edukite team.<br>
             ";*/
            //$hostName = 'ballykinmosman.edukite.com';
             $studentheading = '';
             if($student_names){
                //$studentheading = "<b>Your Student Login:</b><br>";
             }
             $message="
                Dear {$parent_name},<br><br>

                <b>Welcome to Edukite</b><br><br>
                Please find the following login details for your child’s Edukite page.<br><br>
                Edukite is a secure and private E-Portfolio & communication system for Primary schools and Early Learning Centres.<br><br>

                Click on the Direct Edukite Link below, click <b>PARENT</b>, enter your email address and password as outlined below, then click ENTER to open to your child’s kite page.<br><br>

                <b>Your Parent Login:</b><br><br>

                Direct Edukite Link:  <a href=\"http://{$hostName}\"><u>{$hostName}</u></a><br><br>

                <b>Your Username : {$parent_email}</b><br><br>
                <b>Your Password : {$rowParent['parent_password']}</b><br><br>

                {$studentheading}
                {$student_names}
                You can change your login details at any time via the <b>PARENT PROFILE</b> button found in the Kite Masthead, if you have more than one child at the centre using Edukite, you can use the same login details and switch between each of your children’s kites using the sibling field found in your masthead.<br><br>

                If you have any concerns, or if any of the information is not correct with your login details, please contact us at <b>info@edukite.com</b>.<br><br>

                With best regards,<br><br>
                The Edukite team.<br>
            ";

             //print $message . $count . '----------------------------------------------<br>';
             //print $count . '----------------------------------------------<br>';

            $subject     = 'Edukite Login: Your Username / Password Details';
            $subject     = '​​EDUKITE E-Portfolio Log-in Details.';
            $fromName    = $hostName;
            $fromEmail   = $fromEmailId;
            $toName      = $parent_name;
            $toEmail     = $parent_email;
            //$toEmail     = 'moin@usoftsolutions.com';


            $args = array(
                         'toName'    => $toName
                        ,'toEmail'   => $toEmail
                        ,'subject'   => $subject
                        ,'message'   => $message
                        ,'fromName'  => $fromName
                        ,'fromEmail' => $fromEmail
                    );
            //for ($x = 0; $x <= 1000000; $x++) {
                //echo "The number is: $x <br>";
            //}
            //print date("Y/m/d/i/s")  . "<br>";
            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
            $exp = array('showHeader' => false);
            //if($count >= 0 && $count <= 50){
                $emailMsg->sendEmail($exp);
                print 'Email Sent to: ' . $rowParent['parent_email'] .'-' . $parent_name .'-' . $count . "<br>";
            //}

            if($count == 0){
              return;
            }

            $count++;

         }
      }
    }
    /**
     *
     This function will send username and password to the parents along the with student login details
     */
    function getSendUsernameToParentWithStudentDetails() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        print 'You are about to send emails to all parents, to proceed remove the **exit** from the code<br>';
        exit();

        //edukitedev.localhost/index.php?module=edukite_parent&_spAction=sendUsernameToParentWithStudentDetails&showHTML=0


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
        //print $fromEmailId . '<br>';
        //exit();
        $today  = date('Y-m-d');

        $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     FROM parent c
                     WHERE c.status= 'Active'
                     ORDER BY c.parent_id";

        /*$SQLParent   = "SELECT c.parent_id, sp.student_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     FROM parent c
                     LEFT JOIN (student_parent sp) ON (sp.parent_id = c.parent_id)
                         LEFT JOIN (class_student cs) ON (cs.student_id = sp.student_id)
                     WHERE c.status= 'Active'
                     AND cs.class_id = 95
                     AND c.email != ''
                     ORDER BY c.parent_id";*/

       //To Email Parents who are new to school
     /*
       $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     FROM parent c
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = c.parent_id)
                         LEFT JOIN (student s) ON (s.student_id = sp.student_id)
                     where s.academic_year ='2015'
                     ORDER BY c.parent_id
                     ";
         */
      $resultParent = $db->sql_query($SQLParent);
      $numParent    = $db->sql_numrows($resultParent);

      $toEmail = "";
      $count = 0;
      while ($rowParent = $db->sql_fetchrow($resultParent)) {
         $student_names = '';
         $parent_email = $rowParent['parent_email'];
         if ($parent_email != ""){
             $SQLStudParent   = "SELECT s.* , sp.student_parent_id
                                 FROM student_parent sp
                                 LEFT JOIN (student s) ON (s.student_id = sp.student_id)
                                 WHERE sp.parent_id = {$rowParent['parent_id']}
                                 ";
             $resultStudParent = $db->sql_query($SQLStudParent);

             while ($rowStudParent = $db->sql_fetchrow($resultStudParent)) {
                 if($rowStudParent['student_id']){
                     /*$SQLYearGroup  = "SELECT yg.*
                                  FROM student_year_group yg
                                  WHERE yg.student_id = {$rowStudParent['student_id']}
                                  ";
                     $resultYearGroup = $db->sql_query($SQLYearGroup);

                     while ($rowYearGroup = $db->sql_fetchrow($resultYearGroup)) {
                         if($rowYearGroup['year_group_id'] == 25
                            OR $rowYearGroup['year_group_id'] == 26
                            OR $rowYearGroup['year_group_id'] == 27){
                             $student_names .="
                             <b>STUDENT NAME : {$rowStudParent['first_name']} {$rowStudParent['last_name'] } </b><br><br>
                             <b>USERNAME: {$rowStudParent['username']} </b><br><br>
                             <b>PASSWORD: {$rowStudParent['pass_word']} </b><br><br>
                             ";
                         }
                     }*/

                     $SQLClass  = "SELECT cs.*
                                  FROM class_student cs
                                  WHERE cs.student_id = {$rowStudParent['student_id']}
                                  ";
                     $resultClass = $db->sql_query($SQLClass);

                     while ($rowClass = $db->sql_fetchrow($resultClass)) {
                         if($rowClass['class_id'] == 95){
                             $student_names .="
                             <b>STUDENT NAME : {$rowStudParent['first_name']} {$rowStudParent['last_name'] } </b><br><br>
                             <b>USERNAME: {$rowStudParent['username']} </b><br><br>
                             <b>PASSWORD: {$rowStudParent['pass_word']} </b><br><br>
                             ";
                         }
                     }
                 }
             }

            //print $student_names;
            //return;
            $parent_name = $rowParent['parent_name'];

            $studentheading = '';
            if($student_names){
               $studentheading = "<b>STUDENT LOGIN DETAILS:</b><br><br>";
            }

            $message="
            Dear {$parent_name},<br><br>

            <b>EDUKITE LOGIN DETAILS</b><br><br>
            Welcome to Edukite<br><br>
            Please find the following login details for your child’s Edukite account. Simply click on the direct link below, enter your email address and password as provided here and click <b>ENTER</b>.<br><br>

            You can click on the <b>REMEMBER MY PASSWORD</b> for it to be saved for future reference and easy access.<br><br>

            You can change your password at anytime via the <b>PARENT PROFILE</b> button, found in your masthead.<br><br>

            If you have a sibling using edukite you can use the same login and switch between kites using the Sibling field in the masthead.<br><br>

            Please let us know if any of the details are not correct, or if we can help you further.<br><br>

            Best Regards, the Edukite Team<br><br>

            <b>Direct Edukite Link: <a href=\"http://{$hostName}\"><u>{$hostName}</u></a></b><br><br>
            <b>PARENT LOGIN :</b><br><br>
            <b>USERNAME : {$parent_email}</b><br><br>
            <b>PASSWORD : {$rowParent['parent_password']}</b><br><br><br>

            {$student_names}
            ";

             //print $message . $count . '----------------------------------------------<br>';
             //print $count . '----------------------------------------------<br>';

            $subject     = 'Edukite Login: Your Username / Password Details';
            $subject     = '​​EDUKITE E-Portfolio Log-in Details.';
            $fromName    = $hostName;
            $fromEmail   = $fromEmailId;
            $toName      = $parent_name;
            $toEmail     = $parent_email;
            //$toEmail     = 'moin@usoftsolutions.com';


            $args = array(
                         'toName'    => $toName
                        ,'toEmail'   => $toEmail
                        ,'subject'   => $subject
                        ,'message'   => $message
                        ,'fromName'  => $fromName
                        ,'fromEmail' => $fromEmail
                    );
            //for ($x = 0; $x <= 1000000; $x++) {
                //echo "The number is: $x <br>";
            //}
            //print date("Y/m/d/i/s")  . "<br>";
            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
            $exp = array('showHeader' => false);
            //if($count >= 0 && $count <= 50){
                $emailMsg->sendEmail($exp);
                print 'Email Sent to: ' . $rowParent['parent_email'] .'-' . $parent_name .'-' . $count . "<br>";
            //}

            if($count == 0){
              return;
            }

            $count++;

         }
      }
    }

    /**
     *
     This function will used for SCBC to remove E at the end of email and add password
     */
    function getPasswordSCBC() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        //http://edukitedev.localhost/index.php?module=edukite_parent&_spAction=parentPassword&showHTML=0
        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];


        $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     ,c.first_name
                     FROM parent c
                     WHERE c.status= 'Active' AND pass_word = 'NOT PAID' AND email != ''
                     ORDER BY c.parent_id";
        $resultParent = $db->sql_query($SQLParent);
        $numParent    = $db->sql_numrows($resultParent);
        $actual_email =  '';
        $count =1;

        while ($rowParent = $db->sql_fetchrow($resultParent)) {
            $password_add = "";
            $last_letter = substr($rowParent['parent_email'], -1,1);
            if($last_letter == 'E'){
                $actual_email = substr($rowParent['parent_email'], 0,-1);
                 //print  $last_letter .  "<br>";
            }

            //if($len > 96){
                $fa = array();
                $fa['pass_word']  =$rowParent['first_name'];
                $whereCondition = "WHERE parent_id = {$rowParent['parent_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'parent', $whereCondition);
                //$db->sql_query($SQL);
                //print 'Email: ' . $rowParent['parent_email'] . '---' . 'Password :' . $rowParent['parent_password'] . ' - '  . $actual_email .  "<br>";
                print  $SQL  . ";<br>";
            //}
                $count++;
                //return;
        }
    }
    /**
     *
     This function will add password to the parents if they are less than 6 characters
     */
    function getParentPassword() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        //http://edukitedev.localhost/index.php?module=edukite_parent&_spAction=parentPassword&showHTML=0
        //exit();

        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];


        $SQLParent   = "SELECT c.parent_id
                     ,c.email as parent_email
                     ,c.pass_word as parent_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS parent_name
                     ,c.first_name
                     FROM parent c
                     WHERE status = 'Active'
                     ORDER BY c.parent_id";
        $resultParent = $db->sql_query($SQLParent);
        $numParent    = $db->sql_numrows($resultParent);
        $count =1;
        while ($rowParent = $db->sql_fetchrow($resultParent)) {
            $password_add = "";
            $len = strlen($rowParent['parent_password']);
            if($len == 0){
                $password_add = $rowParent['first_name'];
                $password_add = substr(rand(),0,6);
            }
            else if($len == 1){
                $password_add = $rowParent['first_name'];
            }
            else if($len == 2){
                $password_add = $rowParent['first_name'];
            }
            else if($len == 3){
                $password_add = substr(rand(),0,3);
            }
            else if($len == 4){
                $password_add = substr(rand(),0,2);
            }
            else if($len == 5){
                $password_add = substr(rand(),0,1);
            }
            if($len < 6){
                $count++;
                $fa = array();
                $fa['pass_word']  = $rowParent['parent_password'] . $password_add;
                $whereCondition = "WHERE parent_id = {$rowParent['parent_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'parent', $whereCondition);
                $db->sql_query($SQL);
                print 'Email: ' . $rowParent['parent_email'] . '---' . 'Password :' . $rowParent['parent_password'] . ' - ' . $len . ' - ' . $password_add .  "<br>";
                print  $SQL .  ";<br>";
            }
                //return;
        }
    }
    /**
     *
    /**
     *
     This function will add password to the parents if they are less than 6 characters
     */
    function getAddFamilyCodes() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        //http://edukitedev.localhost/index.php?module=edukite_parent&_spAction=addFamilyCodes&showHTML=0
        //print 'working';
        //exit();

        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];
        $SQLStudent   = "SELECT s.student_id
                               ,s.first_name
                               ,s.last_name
                               ,s.family_code
                     FROM student s
                     WHERE s.status = 'Active'
                     AND (s.family_code = '' OR s.family_code IS NULL)
                     ORDER BY s.last_name";
        $resultStudent = $db->sql_query($SQLStudent);
        $count = 0;

        while ($rowStudent = $db->sql_fetchrow($resultStudent)) {
            if($rowStudent['last_name'] != ''){
                $familyName = $rowStudent['last_name'];
            } else {
                $familyName = $rowStudent['first_name'];
            }
            $familyName = str_replace("'", "", $familyName);
            $family_code    = $familyName . "UPD2016";
            $SQLUpdateStud  = "UPDATE student SET family_code = '{$family_code}'
                   WHERE student_id = {$rowStudent['student_id']}";
            print $SQLUpdateStud . "<br><br>";
            $resultUpdateStud = $db->sql_query($SQLUpdateStud);

            $SQLParent   = "SELECT p.*
                         FROM parent p
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = p.parent_id)
                         WHERE sp.student_id = {$rowStudent['student_id']}
                         ORDER BY p.parent_id
                         ";
            $resultParent = $db->sql_query($SQLParent);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {
                    print $count . ' - Parent First Name: ' . $rowParent['first_name'] . '---' . 'Last Name :' . $rowParent['last_name']  . '---' . 'Student Name :' . $rowStudent['first_name'] . ' ' . $rowStudent['last_name'] .  ' || Family Code :  ' . $rowStudent['family_code'] . "<br>";
                    $SQLUpdate    = "UPDATE parent SET family_code = '{$family_code}'
                           WHERE parent_id = {$rowParent['parent_id']}";
                    print $SQLUpdate . "<br>";
                    $resultUpdate = $db->sql_query($SQLUpdate);
                //return;
            }
            $count++;
        }
    }
    /**
     *
     This code will find out students who have more than 2 parents.
     */
    function getFindParentsForStudent() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];


        $SQLStudent   = "SELECT s.student_id
                               ,s.first_name
                               ,s.last_name
                     FROM student s
                     ORDER BY s.last_name";
        $resultStudent = $db->sql_query($SQLStudent);

        while ($rowStudent = $db->sql_fetchrow($resultStudent)) {
            $SQLParent   = "SELECT p.* , sp.student_parent_id
                         FROM parent p
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = p.parent_id)
                         WHERE sp.student_id = {$rowStudent['student_id']}
                         ORDER BY p.parent_id
                         ";
            $resultParent = $db->sql_query($SQLParent);
            $numParent    = $db->sql_numrows($resultParent);
            if($numParent > 2){
                while ($rowParent = $db->sql_fetchrow($resultParent)) {
                    print 'Parent Name: ' . $rowParent['first_name'] . ' ' . $rowParent['last_name']  . '---' . 'Student Name :' . $rowStudent['first_name'] . ' ' . $rowStudent['last_name'] . ' || Student Parent ID :' . $rowParent['student_parent_id']. "<br>";

                     //to check if the record is deleted through inner while loop
                     $record = $fn->getRecordRowByID('student_parent', 'student_parent_id', $rowParent['student_parent_id']);
                     if($record['student_parent_id']){
                        $SQLStudParent   = "SELECT sph.*
                                     FROM student_parent sph
                                     WHERE sph.student_id = {$rowStudent['student_id']}
                                     AND sph.parent_id = {$rowParent['parent_id']}
                                     AND sph.student_parent_id != {$rowParent['student_parent_id']}
                                     ";
                        $resultStudParent = $db->sql_query($SQLStudParent);
                        $count = 0;

                        //to delete all duplicate records in student parent.
                        while ($rowStudentParent = $db->sql_fetchrow($resultStudParent)) {
                             $deleteSQL = "DELETE FROM student_parent WHERE student_parent_id = {$rowStudentParent['student_parent_id']}
                            ";
                            $delete = $db->sql_query($deleteSQL);
                            print $SQLStudParent. "<br>";
                            print $deleteSQL . "<br><br>";
                            $count++;
                        }
                    }
                }
                    //return;
            }
            //return;
        }
    }
    /**
     *
     */
    function getFindSiblings() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];


        $SQLStudent   = "SELECT s.student_id
                               ,s.first_name
                               ,s.last_name
                     FROM student s
                     GROUP BY s.last_name
                     ORDER BY s.last_name";
        $resultStudent = $db->sql_query($SQLStudent);

        while ($rowStudent = $db->sql_fetchrow($resultStudent)) {
            $SQLParent   = "SELECT p.*
                         FROM parent p
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = p.parent_id)
                         WHERE sp.student_id = {$rowStudent['student_id']}
                         ORDER BY p.parent_id
                         ";
            $resultParent = $db->sql_query($SQLParent);
            $numParent    = $db->sql_numrows($resultParent);
            if($numParent > 2){
                while ($rowParent = $db->sql_fetchrow($resultParent)) {
                        print 'First Name: ' . $rowParent['first_name'] . '---' . 'Last Name :' . $rowParent['last_name']  . '---' . 'Student Name :' . $rowStudent['first_name'] . ' ' . $rowStudent['last_name'] . "<br>";
                    //return;
                }
            }
        }
    }
    /**
     *
     */
    function getAddStudentCode() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];


        $SQLStudent   = "SELECT s.student_id
                               ,s.first_name
                               ,s.last_name
                     FROM student s
                     GROUP BY s.last_name
                     ORDER BY s.last_name";
        $resultStudent = $db->sql_query($SQLStudent);

        while ($rowStudent = $db->sql_fetchrow($resultStudent)) {
            $SQLParent   = "SELECT p.*
                         FROM parent p
                         LEFT JOIN (student_parent sp) ON (sp.parent_id = p.parent_id)
                         WHERE sp.student_id = {$rowStudent['student_id']}
                         ORDER BY p.parent_id
                         ";
            $resultParent = $db->sql_query($SQLParent);
            $numParent    = $db->sql_numrows($resultParent);
            if($numParent > 2){
                while ($rowParent = $db->sql_fetchrow($resultParent)) {
                        print 'First Name: ' . $rowParent['first_name'] . '---' . 'Last Name :' . $rowParent['last_name']  . '---' . 'Student Name :' . $rowStudent['first_name'] . ' ' . $rowStudent['last_name'] . "<br>";
                    //return;
                }
            }
        }
    }
}