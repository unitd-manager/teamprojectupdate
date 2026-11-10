<?
class CP_Www_Modules_Edukite_Teacher_Model extends CP_Common_Modules_Edukite_Teacher_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
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
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');


        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'teacher_code');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'login_enabled');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_postal_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'role');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     This function will send username and pasword  to the teacher
     */
    function getSendUsernameToTeacher() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        //print 'You are about to send emails to all teacher, to proceed remove the exit from the code';
        //exit();
        //http://edukitedev.localhost/index.php?module=edukite_teacher&_spAction=sendUsernameToTeacher&showHTML=0
        

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
        
        $fromEmailId = "info@edukite.com";
        //print $fromEmailId;
        //exit();
        $today  = date('Y-m-d');

      $SQLParent   = "SELECT c.email as teacher_email
                     ,c.pass_word as teacher_password
                     ,CONCAT_WS(' ', c.first_name, c.last_name) AS teacher_name
                     FROM teacher c
                     WHERE status = 'Active'
                     ORDER BY c.teacher_id";
      $resultParent = $db->sql_query($SQLParent);
      $numParent    = $db->sql_numrows($resultParent);

      $toEmail = "";
      while ($row = $db->sql_fetchrow($resultParent)) {
         $teacher_email = $row['teacher_email'];
         if ($teacher_email != ""){
            $teacher_name = $row['teacher_name'];
            print 'Email Sent to: ' . $row['teacher_email']  .  "<br>";

            $message     = "Dear {$teacher_name},<br><br>
                            <b>Welcome to Edukite.</b><br><br>
                            Please find following your TEACHER login details Edukite.<br><br>
                            Edukite is a secure and private E-Portfolio & communication system for Primary schools.<br><br>
                            Click on the Direct Edukite Link below, click <b>TEACHER,</b> (please be aware that the default is PARENT - so you must click TEACHER, it will turn YELLOW, enter your email address and password as outlined below, then click ENTER to open to your TEACHER LANDING page.<br><br>
                            <b>Your TEACHER Login:</b><br><br>
                            <b>Direct Edukite Link: <a href=\"https://{$hostName}\"><u>https://{$hostName}</u></a><br></b><br>
                            <b>Your Username: {$teacher_email}</b><br><br>
                            <b>Your Password: {$row['teacher_password']}</b><br><br>
                            From your Teachers landing page (your own private web page), you can find instructions of use via the HELP album in the right hand column, & in the notices in the centre panel. To post a notice click the CONTROLLER button in the right hand side of the masthead, this will take you to the HOME page.<br><br>
                            If you have any concerns, or if any of the information or class lists are not correct, please contact us asap on info@edukite.com.<br><br>
                            With best regards,<br><br>
                            The Edukite Team<br><br>                          
                            ";

            $subject     = 'Edukite Login: Your Username / Password Details';
            $fromName    = $hostName;
            $fromEmail   = $fromEmailId;
            $toName      = $teacher_name;
            $toEmail     = $teacher_email;
            //$toEmail     = 'ansari@usoftsolutions.com';


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
            //$count++;
            
            //return;
         }
      }
    }
}
