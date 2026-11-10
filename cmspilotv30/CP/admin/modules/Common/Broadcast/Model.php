<?
class CP_Admin_Modules_Common_Broadcast_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT a.*
              {$fn->getSiteTitleFld()}
        FROM broadcast a
        {$fn->getSiteFldSqlJoin('a')}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'a';

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.broadcast_id  = '{$tv['record_id']}'";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the subject');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $template_id = $fn->getReqParam('template_id');

        if ($template_id != ''){
            $tplRec = $fn->getRecordRowByID('template', 'template_id', $template_id);
            $fa['description'] = $tplRec['description'];
        }

        if($dbUtil->getColumnExists('broadcast', 'random_no')){
            $fa['random_no'] = date('YnjGHis');
        }

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('from_name', 'Please enter the from name');
        $validate->validateData('from_email' , 'Please enter valid from email', 'email');
        $validate->validateData('title', 'Please enter the subject');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detailFromEdit');
    }

    /**
     *
     */
    function getFields() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'from_name');
        $fa = $fn->addToFieldsArray($fa, 'from_no');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'message');
        $fa = $fn->addToFieldsArray($fa, 'from_email');

        $titleLang = $ln->getFieldPrefix() . 'title';
        $descLang  = $ln->getFieldPrefix() . 'description';

        $fa[$titleLang] = $fn->getPostParam('title');
        $fa[$descLang]  = $fn->getPostParam('description');

        return $fa;
    }

    /**
     *
     */
    function getCommonBroadcastCommonTestRecipientLinkSQL($id) {
        $SQL = "
        SELECT DISTINCT a.contact_id
              ,IF(CONCAT_WS(' ', a.first_name, a.last_name ) != '',
                  CONCAT_WS(' ', a.first_name, a.last_name ), a.email
              ) AS contact_name
              ,b.status
        FROM contact a
            ,broadcast_test_recipient b
        WHERE a.contact_id   = b.contact_id
          AND b.broadcast_id = {$id}
        ORDER BY contact_name
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCommonBroadcastCommonContactLinkSQL($id) {
        $SQL = "
        SELECT DISTINCT a.contact_id
              ,IF(CONCAT_WS(' ', a.first_name, a.last_name ) != '',
                  CONCAT_WS(' ', a.first_name, a.last_name ), a.email
              ) AS contact_name
              ,b.status
        FROM contact a
            ,broadcast_contact b
        WHERE a.contact_id   = b.contact_id
          AND b.broadcast_id = $id
        ORDER BY contact_name
        ";

        return $SQL;
    }

    /**
     *
     */
    function getSendBroadcast($broadcast_id = '') {
        $db = Zend_Registry::get('db');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $fn = Zend_Registry::get('fn');
        $mediaArrayObj->setMediaArray('common_broadcast');
        $mediaArray = $mediaArrayObj->mediaArray;

        $text = '';

        //set_time_limit(500000);
        set_time_limit(3600000);

        if ($broadcast_id == ''){
            $broadcast_id = $fn->getReqParam('broadcast_id');
        }

        $SQL = "SELECT a.* FROM broadcast a WHERE a.broadcast_id = {$broadcast_id}";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //-----------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {
            $fromName  = $row['from_name'];
            $fromEmail = $row['from_email'];
            $subject   = $row['title'];
            $message   = $row['description'];
            $broadcastRec = $row;
        }

        $SQL = "
        SELECT a.*
             ,b.*
        FROM broadcast_contact a
            ,contact b
        WHERE a.contact_id   = b.contact_id
          AND a.broadcast_id = {$broadcast_id}
          AND (a.send_tag = 0 OR a.send_tag IS NULL)
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            print "No records to send <a href='javascript:window.close();'> Close </a>";
            return;
        }

        //-----------------------------------------------------------------------------//
        $SQLMedia = "
        SELECT * FROM media
        WHERE record_id = {$broadcast_id}
        AND room_name = 'common_broadcast'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        $attachmentArray = array();

        while ($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $attachmentArray[] = $mediaArray['common_broadcast']['attachment']['normalFolder'] . $rowMedia['file_name'];
        }

        //-----------------------------------------------------------------------------//
        $smtp = includeCPClass('Lib', 'smtp', 'CPSMTP');
        //$smtp->SMTPKeepAlive = true;
        //-----------------------------------------------------------------------------//
        $SERVER = $_SERVER['HTTP_HOST'];

        while ($row = $db->sql_fetchrow($result)) {
            $contact_id    = $row['contact_id'];
            $first_name    = $row['first_name'];
            $last_name     = $row['last_name'];
            $subscribe     = $row['subscribe'];

            $siteUtl       = "{$SERVER}/";
            $name          = $fn->getIssetParam($row, 'name');
            $toName       = ($name != '') ? $row['name'] : $first_name . ' ' . $last_name;
            $toEmail      = $row['email'];

            if ($subscribe == '' || $subscribe == 0 ){

                $SQL1 = "
                UPDATE broadcast_contact a
                SET status = 'Skipped'
                WHERE a.contact_id = {$contact_id}
                AND a.broadcast_id = {$broadcast_id}
                ";

                print "<span style='color:red'>Skipped {$toEmail} because the contact is not subscribed</span><br>";

                $result1 = $db->sql_query($SQL1);

            } else if ($toEmail != '') {

                $messageTemp = $this->getMailMergedMessage($broadcastRec, $row);

                print 'Sending Mail to:' . $toEmail .'<br>';

                $error = '';
                $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $messageTemp, '', '', $attachmentArray);

                flush();
                ob_flush();
                flush();
                ob_flush();
                flush();
                ob_flush();

                if ($error) {
                    $SQL1 = "
                    UPDATE broadcast_contact a
                    SET send_tag = 0
                       ,status = 'Failed'
                    WHERE a.contact_id = {$contact_id}
                      AND a.broadcast_id = {$broadcast_id}
                    ";
                    print 'Error sending the mail<br>';
                } else {
                    $SQL1 = "
                    UPDATE broadcast_contact a
                    SET send_tag = 1
                       ,status = 'Success'
                    WHERE a.contact_id   = {$contact_id}
                    AND a.broadcast_id = {$broadcast_id}
                    ";
                    print 'Successfully Sent<br>';
                }
                $result1 = $db->sql_query($SQL1);
            }
        }
        //-----------------------------------------------------------------------------//

        $SQL    = "UPDATE broadcast a SET broadcast_date = NOW() WHERE a.broadcast_id = {$broadcast_id}";
        $result = $db->sql_query($SQL);

        $text .= "
        The email message is sent to all the recipients.<br><br>
        Please <a href='javascript:window.close();'>close</a> this window to return to the previous page
        ";

        return $text;
    }

    /**
     *
     */
    function getSendTestBroadcast($broadcast_id = '') {
        $db = Zend_Registry::get('db');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $fn = Zend_Registry::get('fn');
        $mediaArrayObj->setMediaArray('common_broadcast');
        $mediaArray = $mediaArrayObj->mediaArray;

        $text = '';

        set_time_limit(5000);

        //-----------------------------------------------------------------------------//
        if ($broadcast_id == ''){
            $broadcast_id = $fn->getReqParam('broadcast_id');
        }

        $SQL = "
        SELECT a.*
        FROM broadcast a
        WHERE a.broadcast_id = {$broadcast_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $SERVER = $_SERVER['HTTP_HOST'];

        //-----------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {
            $fromName  = $row['from_name'];
            $fromEmail = $row['from_email'];
            $subject   = $row['title'];
            $message   = $row['description'];
            $broadcastRec = $row;
        }

        $SQL = "
        SELECT a.*
              ,b.*
        FROM broadcast_test_recipient a
            ,contact b
        WHERE a.contact_id   = b.contact_id
          AND a.broadcast_id = {$broadcast_id}
        ";

        $result      = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            print "No records to send <a href='javascript:window.close();'> Close </a>";
            return;
        }

        //-----------------------------------------------------------------------------//
        $SQLMedia = "
        SELECT * FROM media
        WHERE record_id = {$broadcast_id}
        AND room_name = 'common_broadcast'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        $attachmentArray = array();

        while ($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $attachmentArray[] = $mediaArray['common_broadcast']['attachment']['normalFolder'] . $rowMedia['file_name'];
        }
        //-----------------------------------------------------------------------------//
        $smtp = includeCPClass('Lib', 'smtp', 'CPSMTP');

        while ($row = $db->sql_fetchrow($result)) {

            $contact_id    = $row['contact_id'];
            $first_name    = $row['first_name'];
            $last_name     = $row['last_name'];

            $siteUtl       = "{$SERVER}/";
            $name          = $fn->getIssetParam($row, 'name');
            $toName       = ($name != '') ? $row['name'] : $first_name . ' ' . $last_name;
            $toEmail      = $row['email'];

            if ($toEmail != '') {
                $messageTemp = $this->getMailMergedMessage($broadcastRec, $row);

                print 'Sending Mail to: ' . $toEmail .'<br>';

                flush();
                ob_flush();
                flush();
                ob_flush();
                flush();
                ob_flush();

                $error = '';
                $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $messageTemp, '', '', $attachmentArray);

                if ($error) {
                    $SQL1 = "
                    UPDATE broadcast_test_recipient
                    SET status = 'Failed'
                    WHERE contact_id   = {$contact_id}
                      AND broadcast_id = {$broadcast_id}
                    ";
                } else {
                    $SQL1 = "
                    UPDATE broadcast_test_recipient
                    SET status = 'Success'
                    WHERE contact_id   = {$contact_id}
                      AND broadcast_id = {$broadcast_id}
                    ";
                }
                $result1 = $db->sql_query($SQL1);
            }
        }

        $text .= "
        The email message is sent to all the test recipients.<br><br>
        Please <a href='javascript:window.close();'>close</a> this window to return to the previous page
        ";

        return $text;
    }

    /**
     *
     * @param type $message
     * @param type $row
     */
    protected function getMailMergedMessage($broadcastRec, $row){
        $fn = Zend_Registry::get('fn');

        $SERVER = $_SERVER['HTTP_HOST'];

        $contact_id    = $row['contact_id'];
        $first_name    = $row['first_name'];
        $last_name     = $row['last_name'];

        $salutation    = $fn->getIssetParam($row, 'salutation');
        $name          = $fn->getIssetParam($row, 'name');
        $known_as_name = $fn->getIssetParam($row, 'known_as_name');
        $email         = $fn->getIssetParam($row, 'email');
        $user_name     = $fn->getIssetParam($row, 'user_name');
        $pass_word     = $fn->getIssetParam($row, 'pass_word');
        $random_no     = $fn->getIssetParam($row, 'random_no');
        $siteUtl       = "{$SERVER}/";

        $rid = $fn->getIssetParam($broadcastRec, 'random_no');
        $id = $broadcastRec['broadcast_id'];
        
        $message = $broadcastRec['description'];
        
        $messageTemp  = str_replace('[[first_name]]', $first_name, $message);
        $messageTemp  = str_replace('[[last_name]]', $last_name, $messageTemp);
        $messageTemp  = str_replace('[[known_as_name]]', $known_as_name, $messageTemp);
        $messageTemp  = str_replace('[[salutation]]', $salutation, $messageTemp);
        $messageTemp  = str_replace('[[name]]', $name, $messageTemp);
        $messageTemp  = str_replace('[[email]]', $email, $messageTemp);
        $messageTemp  = str_replace('[[user_name]]', $user_name, $messageTemp);
        $messageTemp  = str_replace('[[pass_word]]', $pass_word, $messageTemp);
        $messageTemp  = str_replace('[[contact_id]]', $contact_id, $messageTemp);
        $messageTemp  = str_replace('[[siteUrl]]', $siteUtl, $messageTemp);
        $messageTemp  = str_replace('[[random_no]]', $random_no, $messageTemp);
        $messageTemp  = str_replace('[[id]]', $id, $messageTemp);
        $messageTemp  = str_replace('[[rid]]', $rid, $messageTemp);

        return $messageTemp;
    }

    
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Title')
             ,'from_name' => $phpExcel->getFldObj('From Name')
             ,'from_email' => $phpExcel->getFldObj('From Email')
             ,'broadcast_date' => $phpExcel->getFldObj('Date')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }       
}
