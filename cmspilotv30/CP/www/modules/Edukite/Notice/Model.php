<?
class CP_Www_Modules_Edukite_Notice_Model extends CP_Common_Modules_Edukite_Notice_Model
{
    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $expiry_date = $fn->getPostParam('expiry_date', '', true);
        $date = date("d-m-Y");

        /*if ($expiry_date < $date) {
            $validate->errorArray['expiry_date']['name'] = "expiry_date";
            $validate->errorArray['expiry_date']['msg'] = "Past dates are invalid, please place a future expiry date.";
        }*/

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

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the notice title');

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

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'links');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'launch_now');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'youtube_links');
        $fa = $fn->addToFieldsArray($fa, 'vimeo_links');
        $fa = $fn->addToFieldsArray($fa, 'parent_email_sent');
        $fa = $fn->addToFieldsArray($fa, 'kite_chat');
        $fa = $fn->addToFieldsArray($fa, 'template');
        $fa = $fn->addToFieldsArray($fa, 'notice_type');
        $fa = $fn->addToFieldsArray($fa, 'subject_id');
        $fa = $fn->addToFieldsArray($fa, 'notice_type_id');

        if(isset($_POST['activity_date'])){
            $actDateArr = explode('-', $_POST['activity_date']);

            if (count($actDateArr) == 3){
                $fa['activity_date'] = $actDateArr[2] . '-' . $actDateArr[1] . '-' . $actDateArr[0];
            } else {
                $fa['activity_date'] = '';
            }
        }

        if(isset($_POST['expiry_date'])){
            $actDateArr = explode('-', $_POST['expiry_date']);

            if (count($actDateArr) == 3){
                $fa['expiry_date'] = $actDateArr[2] . '-' . $actDateArr[1] . '-' . $actDateArr[0];
            } else {
                $fa['expiry_date'] = '';
            }
        }

        if(isset($_POST['launch_date'])){
            $actDateArr = explode('-', $_POST['launch_date']);

            if (count($actDateArr) == 3){
                $fa['launch_date'] = $actDateArr[2] . '-' . $actDateArr[1] . '-' . $actDateArr[0];
            } else {
                $fa['launch_date'] = '';
            }
        }

        return $fa;
    }
    /**
     *
     */
    function getDeleteLinkedClasses() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $class_id  = $fn->getReqParam('class_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE class_id_hook = {$class_id}
            AND notice_id = {$notice_id}
            AND class_id_hook > 0
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $deleteSQL1     = "
        DELETE FROM notice_parent
        WHERE notice_id = {$notice_id}
          AND notice_student_id NOT IN
          (SELECT notice_student_id FROM notice_student WHERE notice_id = {$notice_id} AND notice_student_id > 0)
        ";
        $deleteResult1  = $db->sql_query($deleteSQL1);

        if ($cpCfg['showAcheivement'] == 1){
            $deleteSQL2     = "
            DELETE FROM achievement_student
            WHERE notice_id = {$notice_id}
                AND class_id = {$class_id}
            ";
            $deleteResult2  = $db->sql_query($deleteSQL2);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedClassList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedStudentsFromClass() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $class_id = $fn->getReqParam('class_id');
        $notice_student_id  = $fn->getReqParam('notice_student_id');
        $student_id = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE notice_student_id = {$notice_student_id}
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id}
                                                     ");
        if(is_array($noticeStudentChk)){
        } else{
            $deleteSQL1     = "
            DELETE FROM notice_parent
            WHERE notice_id = {$notice_id}
              AND notice_student_id = {$notice_student_id}
            ";
            $deleteResult1  = $db->sql_query($deleteSQL1);
        }

        if ($cpCfg['showAcheivement'] == 1){
            $deleteSQL2     = "
            DELETE FROM achievement_student
            WHERE notice_id = {$notice_id}
                AND class_id = {$class_id}
                AND student_id = {$student_id}
            ";
            $deleteResult2  = $db->sql_query($deleteSQL2);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedClassList($notice_id);

        return $text;
    }
    /**
     *
     */
    function getDeleteAllLinkedClasses() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE class_id_hook > 0
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $deleteSQL1     = "
        DELETE FROM notice_parent
        WHERE notice_id = {$notice_id}
          AND notice_student_id NOT IN
          (SELECT notice_student_id FROM notice_student WHERE notice_id = {$notice_id} AND notice_student_id > 0)
        ";
        $deleteResult1  = $db->sql_query($deleteSQL1);

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedClassList($notice_id);

        return $text;
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

        $notice_id   = $fn->getReqParam('notice_id');
        $student_id  = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE student_id = {$student_id}
            AND notice_id = {$notice_id}
            AND (class_id_hook = '' OR class_id_hook IS NULL)
            AND (year_group_id_hook = '' OR  year_group_id_hook IS NULL)
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $deleteSQL1     = "
        DELETE FROM notice_parent
        WHERE notice_id = {$notice_id}
          AND student_id = {$student_id}
          AND notice_student_id NOT IN
          (SELECT notice_student_id FROM notice_student
            WHERE notice_id = {$notice_id} AND student_id = {$student_id} AND notice_student_id > 0)
        ";
        $deleteResult1  = $db->sql_query($deleteSQL1);

        if ($cpCfg['showAcheivement'] == 1){
            $deleteSQL2     = "
            DELETE FROM achievement_student
            WHERE notice_id = {$notice_id}
                AND student_id = {$student_id}
                AND (class_id = '' OR class_id IS NULL)
                AND (year_group_id = '' OR  year_group_id IS NULL)
            ";
            $deleteResult2  = $db->sql_query($deleteSQL2);
        }


        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedStudentList($notice_id);

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

        $notice_id   = $fn->getReqParam('notice_id');

        $sqlMain = "
        SELECT linkTble.notice_student_id
        FROM notice_student linkTble
        WHERE linkTble.notice_id = {$notice_id}
        AND (linkTble.class_id_hook = '' OR linkTble.class_id_hook IS NULL)
        AND (linkTble.year_group_id_hook = '' OR  linkTble.year_group_id_hook IS NULL)
        ";
        $result = $db->sql_query($sqlMain);
        while ($row = $db->sql_fetchrow($result)) {
            $deleteSQL     = "
            DELETE FROM notice_student
            WHERE notice_student_id = {$row['notice_student_id']}
            ";
            $deleteResult  = $db->sql_query($deleteSQL);

            $deleteSQL1     = "
            DELETE FROM notice_parent
            WHERE notice_student_id = {$row['notice_student_id']}
            ";
            $deleteResult1  = $db->sql_query($deleteSQL1);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedStudentList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedStaff() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id   = $fn->getReqParam('notice_id');
        $teacher_id  = $fn->getReqParam('teacher_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE teacher_id = {$teacher_id}
            AND notice_id = {$notice_id}
            AND (class_id_hook = '' OR class_id_hook IS NULL)
            AND (year_group_id_hook = '' OR  year_group_id_hook IS NULL)
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedStaffList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteAllLinkedStaff() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id   = $fn->getReqParam('notice_id');

        $sqlMain = "
        SELECT linkTble.notice_student_id
        FROM notice_student linkTble
        WHERE linkTble.notice_id = {$notice_id}
        AND (linkTble.class_id_hook = '' OR linkTble.class_id_hook IS NULL)
        AND (linkTble.year_group_id_hook = '' OR  linkTble.year_group_id_hook IS NULL)
        ";
        $result = $db->sql_query($sqlMain);
        while ($row = $db->sql_fetchrow($result)) {
            $deleteSQL     = "
            DELETE FROM notice_student
            WHERE notice_student_id = {$row['notice_student_id']}
            ";
            $deleteResult  = $db->sql_query($deleteSQL);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedStaffList($notice_id);

        return $text;
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
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getDeleteLinkedCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id      = $fn->getReqParam('notice_id');
        $year_group_id  = $fn->getReqParam('year_group_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE year_group_id_hook = {$year_group_id}
            AND notice_id = {$notice_id}
            AND year_group_id_hook > 0
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $deleteSQL1     = "
        DELETE FROM notice_parent
        WHERE notice_id = {$notice_id}
          AND notice_student_id NOT IN
          (SELECT notice_student_id FROM notice_student WHERE notice_id = {$notice_id} AND notice_student_id > 0)
        ";
        $deleteResult1  = $db->sql_query($deleteSQL1);

        if ($cpCfg['showAcheivement'] == 1){
            $deleteSQL2     = "
            DELETE FROM achievement_student
            WHERE notice_id = {$notice_id}
                AND year_group_id = {$year_group_id}
                AND (year_group_id = '' OR year_group_id IS NULL)
            ";
            $deleteResult2  = $db->sql_query($deleteSQL2);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedCohortList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteAllLinkedCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE year_group_id_hook > 0
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $deleteSQL1     = "
        DELETE FROM notice_parent
        WHERE notice_id = {$notice_id}
          AND notice_student_id NOT IN
          (SELECT notice_student_id FROM notice_student WHERE notice_id = {$notice_id} AND notice_student_id > 0)
        ";
        $deleteResult1  = $db->sql_query($deleteSQL1);

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedCohortList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedStudentsFromCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $notice_student_id  = $fn->getReqParam('notice_student_id');
        $student_id = $fn->getReqParam('student_id');
        $year_group_id = $fn->getReqParam('year_group_id');

        $deleteSQL     = "
        DELETE FROM notice_student
        WHERE notice_student_id = {$notice_student_id}
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id}
                                                     ");
        if(is_array($noticeStudentChk)){
        } else{
            $deleteSQL1     = "
            DELETE FROM notice_parent
            WHERE notice_id = {$notice_id}
              AND notice_student_id = {$notice_student_id}
            ";
            $deleteResult1  = $db->sql_query($deleteSQL1);
        }

        if ($cpCfg['showAcheivement'] == 1){
            $deleteSQL2     = "
            DELETE FROM achievement_student
            WHERE notice_id = {$notice_id}
                AND year_group_id = {$year_group_id}
                AND student_id = {$student_id}
            ";
            $deleteResult2  = $db->sql_query($deleteSQL2);
        }

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getLinkedCohortList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getUpdateCaptionInMedia() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $caption   = $fn->getReqParam('caption');
        $media_id   = $fn->getReqParam('media_id');

        $updateSQL = "
        UPDATE media
        SET caption = '{$caption}'
        WHERE media_id = {$media_id}
        ";
        $result = $db->sql_query($updateSQL);
    }

    /**
     *
     */
    function getRotateMediaRecord() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $media_id   = $fn->getReqParam('media_id');
        $mediaRec    = $fn->getRecordRowByID('media', 'media_id', $media_id);

        $spArray = array(
             'normal'
            ,'thumb'
            ,'large'
        );

        $count = 0;
        foreach ($spArray as $row){

            $imgPath = $cpCfg['cp.mediaFolder'] .'/'. $spArray[$count];
            $img = $mediaRec['file_name'];
            $suffix = '';
            $quality = 100;
            $degrees = -90;

            $fileType = strtolower(substr($mediaRec['file_name'], strrpos($mediaRec['file_name'], '.') + 1));

            if($fileType == 'png' || $fileType == 'PNG'){
                // Open the original image.
                $original = imagecreatefrompng("$imgPath/$img") or die("Error Opening original");
                list($width, $height, $type, $attr) = getimagesize("$imgPath/$img");

                // Resample the image.
                $tempImg = imagecreatetruecolor($width, $height) or die("Cant create temp image");
                imagecopyresized($tempImg, $original, 0, 0, 0, 0, $width, $height, $width, $height) or die("Cant resize copy");

                $bgColor = imagecolorallocatealpha($original, 255, 255, 255, 127);
                // Rotate
                $rotate = imagerotate($original, $degrees, $bgColor);
                imagesavealpha($rotate, true);
                imagepng($rotate, "$imgPath/$img");
            } elseif ($fileType == 'gif' || $fileType == 'GIF') {
                // Open the original image.
                $original = imagecreatefromgif("$imgPath/$img") or die("Error Opening original");
                list($width, $height, $type, $attr) = getimagesize("$imgPath/$img");

                // Resample the image.
                $tempImg = imagecreatetruecolor($width, $height) or die("Cant create temp image");
                imagecopyresized($tempImg, $original, 0, 0, 0, 0, $width, $height, $width, $height) or die("Cant resize copy");

                $bgColor = imagecolorallocatealpha($original, 255, 255, 255, 127);
                // Rotate
                $rotate = imagerotate($original, $degrees, $bgColor);
                imagesavealpha($rotate, true);
                imagegif($rotate, "$imgPath/$img");
            } else {
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
            }

            // Clean up.
            imagedestroy($original);
            imagedestroy($tempImg);

            $count++;
        }
        return true;
    }

    /**
     *
     */
    function getAutoUpdateFields() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $notice_id      = $fn->getReqParam('notice_id');
        $title          = $fn->getReqParam('title');
        $description    = $fn->getReqParam('description');
        $web_links      = $fn->getReqParam('web_links');
        $youtube_links  = $fn->getReqParam('youtube_links');
        $vimeo_links  = $fn->getReqParam('vimeo_links');
        $subject_id     = $fn->getReqParam('subject_id');
        $expiry_date    = $fn->getReqParam('expiry_date');
        $activity_date  = $fn->getReqParam('activity_date');
        $launch_date    = $fn->getReqParam('launch_date');
        $field_name     = $fn->getReqParam('field_name');
        $hostName       = $_SERVER['HTTP_HOST'];

        $dateArr = explode('-', $expiry_date);
        if (count($dateArr) == 3){
            $expiry_date = $dateArr[2] . '-' . $dateArr[1] . '-' . $dateArr[0];
        } else {
            $expiry_date = '';
        }

        $dateArr = explode('-', $activity_date);
        if (count($dateArr) == 3){
            $activity_date = $dateArr[2] . '-' . $dateArr[1] . '-' . $dateArr[0];
        } else {
            $activity_date = '';
        }

        $dateArr = explode('-', $launch_date);
        if (count($dateArr) == 3){
            $launch_date = $dateArr[2] . '-' . $dateArr[1] . '-' . $dateArr[0];
        } else {
            $launch_date = '';
        }
//print $hostName;
        /*
        if(strpos($hostName, 'marys') !== false
        || strpos($hostName, 'allsaint') !== false
        || strpos($hostName, 'primary') !== false
        || strpos($hostName, 'ece') !== false
        || strpos($hostName, 'silverseeds') !== false
        || strpos($hostName, 'miracles') !== false
        || strpos($hostName, 'scbcchildcare') !== false){
            $description = '';
        }
        */

        $append = '';
        if($title) {
            $append = "SET title = '{$title}'";
        } else if ($description) {
            $append = "SET description = '{$description}'";
        } else if ($field_name == 'web links') {
            $append = "SET links = '{$web_links}'";
        } else if ($field_name == 'youtube links') {
            $append = "SET youtube_links = '{$youtube_links}'";
        } else if ($field_name == 'vimeo links') {
            $append = "SET vimeo_links = '{$vimeo_links}'";
        } else if ($field_name == 'subject id') {
            $append = "SET subject_id = '{$subject_id}'";
        } else if ($expiry_date) {
            $append = "SET expiry_date = '{$expiry_date}'";
        } else if ($activity_date) {
            $append = "SET activity_date = '{$activity_date}'";
        } else if ($launch_date) {
            $append = "SET launch_date = '{$launch_date}'";
        }

        if($append){
            $SQLUpdate    = "
            UPDATE notice
            {$append}
            WHERE notice_id = {$notice_id}
            ";
            $result = $db->sql_query($SQLUpdate);

        }

    }

    /**
     *
     */
    function getCreateAchievementHistoryRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id      = $fn->getReqParam('notice_id');
        $achievement_id = $fn->getReqParam('achievement_id');

        $fa = array();
        $fa['notice_id']      = $notice_id;
        $fa['achievement_id'] = $achievement_id;

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
        $db->sql_query($SQL);

        /*
        $SQL = "
        SELECT ns.*
        FROM notice_student ns
        WHERE ns.notice_id = {$notice_id}
        AND (ns.teacher_id = '' OR ns.teacher_id IS NULL)
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['notice_id']      = $notice_id;
            $fa['achievement_id'] = $achievement_id;
            $fa['student_id']     = $row['student_id'];
            $fa['class_id']       = $row['class_id_hook'];
            $fa['year_group_id']  = $row['year_group_id_hook'];

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
            $db->sql_query($SQL);
        }
        */
        $viewObj = getCPViewObj('edukite_notice');
        $text = $viewObj->getAchievementPanel($notice_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteAchievementHistoryRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id      = $fn->getReqParam('notice_id');
        $achievement_id = $fn->getReqParam('achievement_id');

        /*
        $SQL = "
        SELECT ns.*
        FROM notice_student ns
        WHERE ns.notice_id = {$notice_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $deleteSQL     = "
            DELETE FROM achievement_student
            WHERE achievement_id = {$achievement_id}
                AND notice_id = {$notice_id}
                AND student_id = {$row['student_id']}
            ";
            $deleteResult  = $db->sql_query($deleteSQL);
        }
        */
        $deleteSQL     = "
        DELETE FROM achievement_student
        WHERE achievement_id = {$achievement_id}
            AND notice_id = {$notice_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_notice');
        $text    = $viewObj->getAchievementPanel($notice_id);

        return $text;
    }
}

