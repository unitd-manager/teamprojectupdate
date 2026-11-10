<?
class CP_Admin_Plugins_Member_ChangePassword_Model extends CP_Common_Lib_PluginModelAbstract
{

    /**
     *
     * @return <type>
     */
    function getChangePasswordSubmit() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        //-------------------------------------------------------------------------------------//
        $valArr = $this->getSubmitValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }
        $fa = array();
        $fa['pass_word'] = $fn->getPostParam('pass_word');
        
        if($cpCfg['m.core.staff.hasChangePasswordNextLogin']){
            $fa['change_password_next_login']  = 0;
            $_SESSION['changePasswordOnLogin'] = 0;
        }
        
        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            $staffRow = $fn->getRecordRowByID('staff', 'staff_id', $fn->getSessionParam('staff_id'));
            $email     = $staffRow['email'];
            $pass_word = $fa['pass_word'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } 
        }
        
        $id = $fn->saveRecord($fa, 'staff', 'staff_id', $staffRow['staff_id']);
        
        $retUrl = '';
        if (@$_SESSION['returnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['returnUrlAfterLogin'];
        } else {
            $retUrl = 'index.php';
        }        

        return $validate->getSuccessMessageXML($retUrl);
    }

    /**
     *
     * @return <type>
     */
    function getSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $pLogin = getCPPluginObj('common_login');
        
        $validate->resetErrorArray();

        $isOldPwdInvalidFormat = $validate->validateData("old_pass_word", 'Please enter a valid old password.', "empty", "", "3", "20" );
        $isNewPwdInvalidFormat = $validate->validateData("pass_word", 'Please enter a valid new password. Passwords must be 8 characters.', "empty", "", "8", "20" );
        $validate->validateData("cpass_word",  'New password and confirm passwords are not matching.', "equal", "pass_word", 8, 20);

        if(!$isOldPwdInvalidFormat){
            $staffRow = $fn->getRecordRowByID('staff', 'staff_id', $fn->getSessionParam('staff_id'));
            $email     = $staffRow['email'];
            $pass_word = $fn->getPostParam('old_pass_word', '', true);
            if(!is_array($pLogin->model->checkLogin($email, $pass_word))){ //check old password matches the record
                $validate->errorArray['old_pass_word']['name'] = "old_pass_word";
                $validate->errorArray['old_pass_word']['msg']  = "Old password is invalid.";                
            }
        }   
        
        
        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }
    }
}