<?
class CPL_Admin_Plugins_Common_Login_Model extends CP_Admin_Plugins_Common_Login_Model
{
    /**
     *
     */
    function getLoginSubmit() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        $email     = $fn->getPostParam('email'    , '', true);
        $pass_word = $fn->getPostParam('pass_word', '', true);
        $saveLogin = $fn->getPostParam('saveLogin', '', true);
        $loginBySmartCard = $fn->getPostParam('loginBySmartCard', '', true);

        //-------------------------------------------------------------------------------------//
        $valArr   = $this->getLoginSubmitValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            header('Content-type: application/json');
            return $xmlText;
        }

        if ($loginBySmartCard){
            $smartCardId = $fn->getPostParam('smartCardId', '', true);
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE smart_card_id = '{$smartCardId}'
              AND published = 1
            ";
        } else {
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND published = 1
            ";
        }
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            $userGroupId = $row['user_group_id'];

            //amended by syed for double login issue, not sure about the below condition
            //if($cpCfg['cp.hasMultiUsergroupPerStaff'] && $userGroupId != $cpCfg['cp.superAdminUGId']){
            if($cpCfg['cp.hasMultiUsergroupPerStaff']){
                $returnText = $this->view->getChooseUsergroupForm($row);
                return $validate->getSuccessMessageXML('', $returnText);
            } else {
                if($cpCfg['cp.captureAutoLogin']){
                    
                    if ($row['developer'] != 1 
                     && $row['email'] != 'syed@usoftsolutions.com'
                     && $row['team'] == 'In-house') {
                        $SQLStaff     = "
                        SELECT * FROM staff 
                        WHERE developer = 0
                          AND published = 1
                          AND staff_id  = {$row['staff_id']}
                          AND team = 'In-house'
                          ";
                        $resultStaff  = $db->sql_query($SQLStaff);                            
                        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

                            /* Checking Previous attendance */
                            $SQLAtt = "
                            SELECT * FROM {$cpCfg['cp.attendanceTable']}
                            WHERE staff_id = {$row['staff_id']}
                            ";
                            $resultAtt  = $db->sql_query($SQLAtt);
                            $numRowsAtt = $db->sql_numrows($resultAtt);
                            
                            if ($numRowsAtt > 0) {
                                $this->getMarkPreviousAttendanceRecords($rowStaff);
                            }
                        }
                        
                        $today = date('Y-m-d');                        
                        $SQLStaffAtt = "
                        SELECT *
                        FROM {$cpCfg['cp.attendanceTable']}
                        WHERE record_date = '{$today}'
                          AND staff_id = {$row['staff_id']}
                        ";
                        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
                        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
                        
                        if ($numRowsStaffAtt == 0) {
                            $fa = array();
                            
                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id']      = $row['site_id'];
                            }
                            
                            if ($row['address_country'] == 'SG') {
                                $fa['time_in']          = date('H:i:s');
                                $fa['creation_date']    = date('Y-m-d H:i:s');
                            } else {
                                date_default_timezone_set("Asia/Calcutta");
                                $fa['time_in']          = date('H:i:s');
                                $fa['creation_date']    = date('Y-m-d H:i:s');
                            }
                            $fa['staff_id']         = $row['staff_id'];
                            $fa['record_date']      = $today;
                            $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];
                            
                            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                            $hist_id                = $db->sql_nextid();
                        }
                    }
                }
                
                $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
                /** if there is a hook for homepage in the theme level then use that **/
                $theme = getCPThemeObj($cpCfg['cp.theme']);
                if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                    $theme->fns->setSessionValuesAfterLogin($row);
                }
                return $validate->getSuccessMessageXML($retUrl);
            }
        }
    }

    /**
     *
     */
    function getLogout() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if($cpCfg['cp.captureAutoLogin']){
            $today = date('Y-m-d');
            
            $SQLStaffAtt = "
            SELECT *
            FROM {$cpCfg['cp.attendanceTable']}
            WHERE record_date = '{$today}'
              AND staff_id = '{$_SESSION['staff_id']}'
            ";
            $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
            $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
            
            if ($numRowsStaffAtt > 0) {
                $fa = array();
                
                $staffRec = $fn->getRecordRowById('staff', 'staff_id', $_SESSION['staff_id']);
                if ($staffRec['address_country'] == 'SG') {
                    $fa['leave_time']           = date('H:i:s');
                    $fa['modification_date']    = date('Y-m-d H:i:s');
                } else {
                    date_default_timezone_set("Asia/Calcutta");
                    $fa['leave_time']           = date('H:i:s');
                    $fa['modification_date']    = date('Y-m-d H:i:s');
                }
                $fa['modified_by']          = $_SESSION['userFullName'];

                $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$_SESSION['staff_id']}'";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, $cpCfg['cp.attendanceTable'], $whereCondition);
                $db->sql_query($SQL);
            }
        }

        session_destroy();
        $fn->resetCookie("adminUserNameC");
        $fn->resetCookie("adminPasswordC");
        $fn->sessionRegenerate();
        
        // added the below 2 lines by ahmad due to bug with resetCookie function
        setcookie('adminUserNameC', '',  time()-1209600);
        setcookie('adminPasswordC', '',  time()-1209600);

        $cpUtil->redirect('index.php');
    }    
}