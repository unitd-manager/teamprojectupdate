<?
/**
 *
 */
class CP_Common_Lib_Validate
{
    /**
     *
     * @var <type>
     */
    var $validType   = array('empty', "alphaNumeric", 'email', 'equal', 'number', 'null', 'regEx');

    /**
     *
     * @var <type>
     */
    var $errorArray  = array();

    /**
     *
     * @var type
     */
    var $hasCSRFTokenError  = false;

    var $ignoreEmpty = false;
    /**
     *
     */
    function ValidateLocal() {
    }

    /**
     *
     * @param <type> $fieldName1
     * @param <type> $errorMessage
     * @param <type> $validationType
     * @param <type> $fieldName2
     * @param <type> $minCharLength
     * @param <type> $maxCharLength
     * @return <type>
     */
    function validateData($fieldName1, $errorMessage, $validationType = "empty", $fieldName2 = "",
                          $minCharLength = "", $maxCharLength = "", $exp =  array()){

        $fn = Zend_Registry::get('fn');
        $regEx  = $fn->getIssetParam($exp, 'regEx');
        $this->ignoreEmpty = $fn->getIssetParam($exp, 'ignoreEmpty', false);

        //filed2 is required for comparing two values like pwd & cpwd
        $fieldValue1   = isset($_POST[$fieldName1])  ? $_POST[$fieldName1]  : "";
        $fieldValue2   = isset($_POST[$fieldName2])  ? $_POST[$fieldName2]  : "";
        $hasValidationFailed = false;
        //print $fieldName1 . ": " . $_POST[$fieldName1] . "\n";

        if (is_array($fieldValue1)){

            if (count($fieldValue1) > 0){
                $fieldValue1 = $fieldValue1[0];
            } else {
                $fieldValue1 = "";
            }
        }

        if($minCharLength != "" && !$this->ignoreEmpty){
            if(strlen(trim($fieldValue1)) < $minCharLength){
                $this->updateErrorArray($fieldName1, $errorMessage);
                $hasValidationFailed = true;
            }
        }

        if($maxCharLength != ""  && !$this->ignoreEmpty){
            if(strlen(trim($fieldValue1)) > $maxCharLength){
                $this->updateErrorArray($fieldName1, $errorMessage);
                $hasValidationFailed = true;
            }
        }

        $validationTemp = "is".strtoupper(substr($validationType,0,1)) . substr($validationType,1);
        if($validationType == "regEx"){
            if(!$this->isValidRegEx($fieldValue1, $regEx)){
                $this->updateErrorArray($fieldName1, $errorMessage);
                $hasValidationFailed = true;
            }
        }else if($validationType != "empty"){
            if(!$this->$validationTemp($fieldValue1, $fieldValue2)){
                $this->updateErrorArray($fieldName1, $errorMessage);
                $hasValidationFailed = true;
            }
        }
        else if($validationType == "empty") {
            if($this->$validationTemp($fieldValue1)){
                $this->updateErrorArray($fieldName1, $errorMessage);
                $hasValidationFailed = true;
            }
        }

        return $hasValidationFailed;
    }

    /**
     *
     * @param type $fieldName1
     * @param type $errorMessage
     * @param type $validationType
     * @param type $exp
     */
    function validateData2($fieldName1, $errorMessage, $exp = array()){
        $fn = Zend_Registry::get('fn');
        $validationType = $fn->getIssetParam($exp, 'validationType', 'empty');
        $compareField   = $fn->getIssetParam($exp, 'compareField');
        $minLength   = $fn->getIssetParam($exp, 'minLength');
        $maxLength   = $fn->getIssetParam($exp, 'maxLength');
        $ignoreEmpty = $fn->getIssetParam($exp, 'ignoreEmpty');
        $regEx  = $fn->getIssetParam($exp, 'regEx');

        return  $this->validateData($fieldName1, $errorMessage, $validationType, $compareField,
                                    $minLength, $maxLength, $exp);

    }

    /**
     *
     */
    function resetErrorArray(){
        $this->errorArray = array();
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isEmpty($value){
        if(strlen(trim($value)) == 0 || trim($value) == ""){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isEmail($email){
        $isValid = true;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isValid = false;
        }        
//        if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email)){
//            $isValid = false;
//        }

        return $isValid;
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isAlphaNumeric($value){
        if(!$this->isEmpty($value)){
            if(preg_match("/^[[:alnum:]]+$/", $value)) {
                return true;
            } else {
                return false;
            }
        }
        elseif($this->ignoreEmpty){
            return true;
        }
        elseif($this->isEmpty($value)) {
            return false;
        }
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isNumber($value){
        if(!$this->isEmpty($value)){
            if (preg_match('/^[[:digit:]]+$/', $value)) {
                return true;
            } else {
                return false;
            }
        }
        elseif($this->ignoreEmpty){
            return true;
        }
        elseif($this->isEmpty($value)){
            return false;
        }
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isNull($value){
        if(is_null($value)){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param <type> $value1
     * @param <type> $value2
     * @return <type>
     */
    function isEqual($value1, $value2){
        if(!$this->isEmpty($value1) && !$this->isEmpty($value2)){
            if($value1 == $value2) {
                return true;
            } else {
                return false;
            }
        }
        elseif($this->ignoreEmpty){
            return true;
        }
        elseif($this->isEmpty($value1) || $this->isEmpty($value2)){
            return false;
        }
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function isZero($value){
        if(!$this->isEmpty($value)){
            if($value == 0) {
                return true;
            } else {
                return false;
            }
        }
        elseif($this->ignoreEmpty){
            return true;
        }
        elseif($this->isEmpty($value)){
            return false;
        }
    }

    /**
     *
     * @param type $value
     * @return boolean
     */
    function isValidRegEx($value, $regEx){
        if(!$this->isEmpty($value)){
            if(preg_match($regEx, $value)) {
                return true;
            } else {
                return false;
            }
        }
        elseif($this->ignoreEmpty){
            return true;
        }
        elseif($this->isEmpty($value)){
            return false;
        }
    }

    /**
     *
     * @param type $fieldName
     * @param type $errorMessage
     */
    function validateCSRFToken($fieldName = 'cpCSRFToken', $errorMessage = 'Invalid CSRF Token!'){
        $fn = Zend_Registry::get('fn');
        if(!$fn->isValidCSRFToken()){ //check for valid CSRF token
            $this->updateErrorArray($fieldName, $errorMessage);
            $fn->setCSRFToken();
        }
    }

    /**
     *
     * @param <type> $fieldName
     * @param <type> $errorMessage
     */
    function updateErrorArray($fieldName, $errorMessage){
        $this->errorArray[$fieldName] = array();
        $this->errorArray[$fieldName]['name'] = $fieldName;
        $this->errorArray[$fieldName]['msg']  = $errorMessage;
    }

    /**
     *
     * @return <type>
     */
    function getErrorMessageXML($customError = '') {
        $fn = Zend_Registry::get('fn');
        //use the following header command for debug purpose only. Please do not un-comment it
        //header('Content-type: application/json');
        $json = "";
        $errorCount = count($this->errorArray);
        $arr = array('errorCount' => $errorCount);
        $arr['errors'] = $this->errorArray;
        $arr['customError'] = $customError;
        $arr['hasCSRFTokenError']  = $this->hasCSRFTokenError  ? 1 : 0;

        $json = json_encode($arr);

        return $json;
    }

    /**
     *
     */
    function getSuccessMessageXML($returnUrl = '', $returnText = '', $extraParam = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //use the following header command for debug purpose only. Please do not un-comment it
        //header('Content-type: application/json');
        $json = "";
        $errorCount = count($this->errorArray);
        $arr = $this->getSuccessMessageArray($returnUrl, $returnText, $extraParam);
        $json = json_encode($arr);

        return $json;
   }

    /**
     *
     */
    function getSuccessMessageArray($returnUrl, $returnText, $extraParam) {
        $json = "";
        $errorCount = count($this->errorArray);
        $arr = array('errorCount' => 0
                    ,'returnUrl' => $returnUrl
                    ,'returnText' => $returnText
                    );

        if (count($extraParam) > 0){
            $arr['extraParam'] = $extraParam;
        }

        return $arr;
    }

    function validateCC($type, $cc_num) {
        $verified = false;

    	if($type == "American" || $type == "Amex") {
    	    $pattern = "/^([34|37]{2})([0-9]{13})$/";//American Express
        	if (preg_match($pattern,$cc_num)) {
        	    $verified = true;
        	} else {
        	    $verified = false;
        	}
    	} elseif($type == "Dinners") {
    	    $pattern = "/^([30|36|38]{2})([0-9]{12})$/";//Diner's Club
    	    if (preg_match($pattern,$cc_num)) {
    	        $verified = true;
    	    } else {
    	        $verified = false;
    	    }
   	    } elseif($type == "Discover") {
    	    $pattern = "/^([6011]{4})([0-9]{12})$/";//Discover Card
    	    if (preg_match($pattern,$cc_num)) {
    	        $verified = true;
    	    } else {
    	        $verified = false;
    	    }
    	} elseif($type == "Master" || $type == "Mastercard") {
    	    $pattern = "/^([51|52|53|54|55]{2})([0-9]{14})$/";//Mastercard
    	    if (preg_match($pattern,$cc_num)) {
    	        $verified = true;
    	    } else {
    	        $verified = false;
    	    }
    	        $verified = true;
    	} elseif($type == "Visa") {
    	    $pattern = "/^([4]{1})([0-9]{12,15})$/";//Visa
    	    if (preg_match($pattern,$cc_num)) {
    	        $verified = true;
    	    } else {
    	        $verified = false;
    	    }
    	}
    	return $verified;
    }

}//end class
