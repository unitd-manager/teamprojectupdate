<?
class CP_Common_Plugins_Common_Comment_Model extends  CP_Common_Lib_PluginModelAbstract
{
    //==================================================================//
    function getSQL() {
        $c = &$this->controller;
        $modulesArr = Zend_Registry::get('modulesArr');
        if($c->contactModule != ''){
            $tableName = $modulesArr[$c->contactModule]["tableName"];
            $keyField  = $modulesArr[$c->contactModule]["keyField"];

            $SQL = "
            SELECT c.*
                  ,CONCAT_WS(' ', b.first_name, b.last_name) AS contact_name
            FROM `comment` c
            LEFT JOIN ({$tableName} b) ON (c.contact_id = b.{$keyField})
            ";
        } else {
            $SQL = "
            SELECT c.*
            FROM `comment` c
            ";
        }
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';
        $c = &$this->controller;

        $searchVar->sqlSearchVar[] = "c.room_name = '{$c->roomName}'";
        if ($c->contactId != ''){
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$c->contactId}'";
        } else {
            $searchVar->sqlSearchVar[] = "c.record_id = '{$c->recordId}'";
        }
        $searchVar->sortOrder = "comment_date DESC";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->setPluginDataArray($this->controller, 'common_comment');
        $this->dataArray = $dataArray;
        return $dataArray;
    }

    /*
     *
     */
    function getDataArrayTwig($modName, $record_id, $parent_id = 0) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $media = Zend_Registry::get('media');

        $extraSQL = '';
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        if ($userType == 'directory_contact'){
            $cpContactId = $fn->getSessionParam('cpContactId');
            $extraSQL = "
            ,(SELECT COUNT(*)
              FROM contact_friend 
              WHERE friend_id = c.contact_id
              AND contact_id = '{$cpContactId}'
             ) AS me_following_count

            ,(SELECT COUNT(*)
              FROM contact_friend 
              WHERE contact_id = c.contact_id
              AND friend_id = '{$cpContactId}'
             ) AS following_me_count
            ,(SELECT COUNT(*)
              FROM liked_comments 
              WHERE comment_id = c.comment_id
              AND contact_id = '{$cpContactId}'
             ) AS liked_by_me
            ,(SELECT COUNT(*)
              FROM liked_comments 
              WHERE comment_id = c.comment_id
             ) AS liked_count
            ";
        }

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              ,(SELECT COUNT(*)
                FROM contact_friend
                WHERE friend_id = c.contact_id
               ) AS user_no_of_followers

              ,(SELECT COUNT(*)
                FROM comment
                WHERE contact_id = c.contact_id
               ) AS user_comment_count
               {$extraSQL}
        FROM `comment` c
        JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        WHERE c.room_name = '{$modName}'
        AND c.record_id = '{$record_id}'
        AND c.parent_id = {$parent_id}
        ORDER BY c.creation_date DESC
        ";

        $dataArray = $dbUtil->getSQLResultAsArray($SQL);
        $rows = '';

        $arr = array();
        foreach($dataArray AS $row){
            $exp = array('folder' => 'thumb');
            $userPicArray = $media->getFirstMediaRecord('directory_contact', 'picture', $row['contact_id']);

            if (count($userPicArray) == 0){
                $userPicArray = array(
                    'file_thumb'  => '//placehold.it/65x65'
                );
            }

            $urlUsr = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
            $row['file_thumb_user'] = $userPicArray['file_thumb'];
            $row['urlUsr'] = $urlUsr;
            $row['rating'] = $fn->getRatingValue($row['rating']);
            $row['user_no_of_followers'] = $row['user_no_of_followers'] > 0 ? $row['user_no_of_followers'] : 0;
            $row['user_comment_count'] = $row['user_comment_count'] > 0 ? $row['user_comment_count'] : 0;

            $SQL = "
            SELECT *
            FROM media
            WHERE room_name = 'common_comment'
              AND record_id = {$row['comment_id']}
              ORDER BY creation_date DESC
            ";

            $picArray = $dbUtil->getSQLResultAsArray($SQL);

            $picArrayNew = array();
            foreach($picArray AS $picRow){
                $file_thumb = '/media/thumb/' . $picRow['file_name'];
                $file_large = '/media/large/' . $picRow['file_name'];
                $picArrayNew[] = array(
                    'file_thumb' => $file_thumb,
                    'file_large' => $file_large,
                );
            }
            $row['picArray'] = $picArrayNew;
            $row['childReviews'] = array();

            if ($parent_id == 0 && $row['comment_id'] > 0){
                $row['childReviews'] = $this->getChildArray($modName, $record_id, $row['comment_id']);
            }

            $arr[] = $row;
        }

        return $arr;
    }

    /*
     *
     */
    function getChildArray($modName, $record_id, $parent_id) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $media = Zend_Registry::get('media');

        $extraSQL = '';
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        if ($userType == 'directory_contact'){
            $cpContactId = $fn->getSessionParam('cpContactId');
            $extraSQL = "
            ,(SELECT COUNT(*)
              FROM contact_friend 
              WHERE friend_id = c.contact_id
              AND contact_id = '{$cpContactId}'
             ) AS me_following_count

            ,(SELECT COUNT(*)
              FROM contact_friend 
              WHERE contact_id = c.contact_id
              AND friend_id = '{$cpContactId}'
             ) AS following_me_count
            ,(SELECT COUNT(*)
              FROM liked_comments 
              WHERE comment_id = c.comment_id
              AND contact_id = '{$cpContactId}'
             ) AS liked_by_me
            ,(SELECT COUNT(*)
              FROM liked_comments 
              WHERE comment_id = c.comment_id
             ) AS liked_count
            ";
        }

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              {$extraSQL}
        FROM `comment` c
        LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        WHERE c.room_name = '{$modName}'
        AND c.record_id = '{$record_id}'
        AND c.parent_id = {$parent_id}
        ORDER BY c.creation_date ASC
        ";

        $dataArray = $dbUtil->getSQLResultAsArray($SQL);
        $rows = '';

        $arr = array();
        foreach($dataArray AS $row){
            $exp = array('folder' => 'thumb');
            $userPicArray = $media->getFirstMediaRecord('directory_contact', 'picture', $row['contact_id']);

            if (count($userPicArray) == 0){
                $userPicArray = array(
                    'file_thumb'  => '//placehold.it/65x65'
                );
            }

            if ($row['business_owner'] == 1){
                $row['file_thumb_user'] = '/www/images/business-owner.png';
                $row['urlUsr'] = '#';
                $row['contact_name'] = 'Business Owner';
                $row['divClass'] = 'businessOwner';
                
            } else {
                $urlUsr = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
                $row['file_thumb_user'] = $userPicArray['file_thumb'];
                $row['urlUsr'] = $urlUsr;
                $row['divClass'] = '';
            }


            $arr[] = $row;
        }

        return $arr;
    }

    //==================================================================//
    function getAdd() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $media = Zend_Registry::get('media');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $publishByDefault = $fn->getPostParam('p-common-comment_publishByDefault');
        $hasFileUpload    = $fn->getPostParam('p-common-comment_hasFileUpload');
        $showNotifyCbox   = $fn->getPostParam('p-common-comment_showNotifyCbox');
        $notifyAdmin      = $fn->getPostParam('p-common-comment_notifyAdmin');

        $fa = $fn->addToFieldsArray($fa, 'room_name');
        $fa = $fn->addToFieldsArray($fa, 'record_id');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'rating');
        $fa = $fn->addToFieldsArray($fa, 'subject');
        $fa = $fn->addToFieldsArray($fa, 'notify');
        $fa = $fn->addToFieldsArray($fa, 'rating_food');
        $fa = $fn->addToFieldsArray($fa, 'rating_value');
        $fa = $fn->addToFieldsArray($fa, 'rating_atmosphere');
        $fa = $fn->addToFieldsArray($fa, 'rating_service');
        $fa = $fn->addToFieldsArray($fa, 'parent_id');

        $fa['published'] = 0;
        if ($publishByDefault) {
            $fa['published'] = 1;
        }
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['comment_date']  = date("Y-m-d H:i:s");

        if (CP_SCOPE == 'admin'){
            $fa['contact_id'] = $_SESSION['staff_id'];
        } else {
            if (isLoggedInWWW()){
                $fa['contact_id']     = $_SESSION['cpContactId'];
                $fa['contact_module'] = $fn->getPostParam('p-common-comment_contactModule');
            }
        }

        $comment_id = $fn->addRecord($fa, 'comment');

        if ($hasFileUpload){
            $media->getAddMedia('common_comment', 'picture', $comment_id);
        }

        if ($showNotifyCbox){
            $this->sendNotificationToFollowers($comment_id);
        }

        if ($notifyAdmin){
            $this->sendNotificationToAdmin($comment_id);
        }

        $exp = array('comment_id' => $comment_id);
        return $validate->getSuccessMessageXML('', '', $exp);
    }

    function sendNotificationToFollowers($comment_id) {
        $fn = Zend_Registry::get('fn');
        $rec = $fn->getRecordRowByID('comment', 'comment_id', $comment_id);
        $record_id = $rec['record_id'];
        $room_name = $rec['room_name'];

        $SQL = "
        SELECT contact_id
        FROM comment
        WHERE record_id = '{$record_id}'
          AND room_name = '{$room_name}'
          AND notify = 1
        ";
    }

    function sendNotificationToAdmin($comment_id = '') {
        set_time_limit(1000);

        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rowC = $fn->getRecordRowByID('comment', 'comment_id', $comment_id);

        $url = $cpUrl->getURLPrefix(). '/admin/index.php?_topRm=main&module=webBasic_comment'
             . '&_action=edit&record_id=' . $comment_id;

        $message = $ln->gd('p.common.comment.new.notifyAdmin.body');
        $message = str_replace('[url_comment]', $url, $message);
        $message = str_replace('[date_posted]', $rowC['creation_date'], $message);
        $message = str_replace('[comments]', $rowC['comments'], $message);

        $subject = $ln->gd('p.common.comment.new.notifyAdmin.subject');
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = '';
        $toEmail   = $cpCfg['cp.adminEmail'];
        $replyTo   = $toEmail;

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
            ,'replyTo' => $replyTo
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $error = $emailMsg->sendEmail();

    }

    /**
     *
     */
    function getNewValidate() {
        $hook = getCPPluginHook('common_comment', 'newValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $showCaptcha = $fn->getReqParam('p-common-comment_showCaptcha');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("comments", $ln->gd("cp.form.fld.comments.err"));

        if ($showCaptcha){
       	    $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
    function getSave() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $record_id = $fn->getReqParam('record_id');
        $rec = $fn->getRecordRowByID('comment', 'comment_id', $record_id);

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE comment_id = {$record_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "comment", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

    }

    //==================================================================//
    function getSaveSpecialRating() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $comment_id = $fn->getPostParam('comment_id');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'rating_food');
        $fa = $fn->addToFieldsArray($fa, 'rating_value');
        $fa = $fn->addToFieldsArray($fa, 'rating_atmosphere');
        $fa = $fn->addToFieldsArray($fa, 'rating_service');

        $fn->saveRecord($fa, 'comment', 'comment_id', $comment_id);

        return $validate->getSuccessMessageXML();
    }

    //==================================================================//
    function getDelete() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getReqParam('record_id');
        $contact_module = $fn->getReqParam('contact_module');

        if (!is_numeric ($record_id)) {
            return;
        }

        $rec = $fn->getRecordRowByID('comment', 'comment_id', $record_id);

        $SQL = "
        DELETE FROM comment
        WHERE comment_id = {$record_id}
        ";
        $result = $db->sql_query($SQL);
    }

    //==================================================================//
    function getReportAbuse($comment_id, $contact_id){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rec = $fn->getRecordRowByID('comment', 'comment_id', $comment_id);

        if ($rec['verified'] == 1){
            return $ln->gd('cp.form.comment.reportAbuse.message.alreadyVerified');
        } else {
            $fa = array();
            $fa['abusive'] = 1;
            $fn->saveRecord($fa, 'comment', 'comment_id', $comment_id);
            
            //-----------------------------------------------------------------//
            if ($rec['room_name'] == 'directory_guide'){
                $SQL = "
                SELECT c.*
                      ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                      ,cont.email AS contact_email
                	  ,g.title AS record_title
                FROM comment c
                JOIN (guide g) ON (c.record_id = g.guide_id)
                JOIN (contact cont) ON (c.contact_id = cont.contact_id)
                WHERE comment_id = '{$comment_id}'
                ";
                
                $recType = 'Guide';

            } else {
                $SQL = "
                SELECT c.*
                      ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                      ,cont.email AS contact_email
                	  ,b.business_name AS record_title
                FROM comment c
                JOIN (business b) ON (c.record_id = b.business_id)
                JOIN (contact cont) ON (c.contact_id = cont.contact_id)
                WHERE comment_id = '{$comment_id}'
                ";

                $recType = 'Business';
            }

            $dataArray = $dbUtil->getSQLResultAsArray($SQL);
            $row = $dataArray[0];

            //-----------------------------------------------------------------//
            $currentDate  = date("d-M-Y l h:i:s A");

            $cms_url = "{$cpCfg['cp.siteUrl']}/admin/index.php?_topRm=directory&module=common_comment&_action=edit&record_id={$row['comment_id']}";
            $cms_url = "<a href='{$cms_url}'>{$cms_url}</a>";
    
            $message = $ln->gd('p.common.comment.reportAbuse.email.notifyBody');
            $message = str_replace("[[comment_id]]" , $row['comment_id'], $message );
            $message = str_replace("[[comment_by]]" , $row["contact_name"] . ' / ' . $row["contact_email"] , $message );
            $message = str_replace("[[flagged_by]]" , $_SESSION['cpUserFullNameWWW'] . ' / ' . $_SESSION['cpEmail'], $message );
            $message = str_replace("[[record_title]]" , $recType . ' / ' . $row['record_title'], $message);
            $message = str_replace("[[comments]]" , $row['comments'], $message );
            $message = str_replace("[[cms_url]]" , $cms_url, $message );
            $message = str_replace("[[currentDate]]", $currentDate, $message );
            
            $subject   = $ln->gd('p.common.comment.reportAbuse.email.notifySubject');
            $fromName  = '';
            $fromEmail = $cpCfg['cp.adminEmail'];
            $toName    = $cpCfg['cp.companyName'];
            $toEmail   = $cpCfg['cp.adminEmail'];
    
            $args = array(
                 'toName'    => $toName
                ,'toEmail'   => $toEmail
                ,'subject'   => $subject
                ,'message'   => $message
                ,'fromName'  => $fromName
                ,'fromEmail' => $fromEmail
            );
    
            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
            $emailMsg->sendEmail();

            return $ln->gd('cp.form.comment.reportAbuse.message.success');
        }
    }

    //==================================================================//
    function getLikeComment(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpContactId = $fn->getSessionParam('cpContactId');
        $comment_id = $fn->getReqParam('id');

        if (isLoggedInWww()){
            $count = $fn->getRecordCount('liked_comments', "comment_id = '{$comment_id}' AND contact_id = '{$cpContactId}'");
    
            if ($count == 0){
                $fa = array();
                $fa['comment_id'] = $comment_id;
                $fa['contact_id'] = $cpContactId;
                $fn->addRecord($fa, 'liked_comments');
            }

            $arr['count'] = $fn->getRecordCount('liked_comments', "comment_id = '{$comment_id}'");
            //$arr['html'] = $ln->gd('p.common.comment.likeComment.message.success');
            return json_encode($arr);
        }
    }

}