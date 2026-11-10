<?
class CP_Www_Modules_Ecommerce_Stockist_Model extends CP_Common_Modules_Ecommerce_Stockist_Model
{
    /**
     *
     */
    function getAdd() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        $gcRec = $fn->getRecordByCondition('geo_country', "country_code='{$fa['address_country_code']}'");

        $message = $ln->gd("m.ecommerce.stockist.form.new.email.notifyBody");
        $message = str_replace('[[first_name]]'      , $fa['first_name']      , $message );
        $message = str_replace('[[last_name]]'       , $fa['last_name']       , $message );
        $message = str_replace('[[email]]'           , $fa['email']           , $message );
        $message = str_replace('[[company_name]]'    , $fa['company_name']    , $message );
        $message = str_replace('[[address1]]'        , $fa['address1']        , $message );
        $message = str_replace('[[address2]]'        , $fa['address2']        , $message );
        $message = str_replace('[[address_area]]'    , $fa['address_area']    , $message );
        $message = str_replace('[[address_city]]'    , $fa['address_city']    , $message );
        $message = str_replace('[[address_state]]'   , $fa['address_state']   , $message );
        $message = str_replace('[[address_po_code]]' , $fa['address_po_code'] , $message );
        $message = str_replace('[[address_country]]' , $gcRec['name']         , $message );
        $message = str_replace('[[currentDate]]'     , $currentDate           , $message );

        $subject     = $ln->gd('m.ecommerce.stockist.form.new.email.notifySubject');
        $fromName    = $fa['first_name'] . " " . $fa['last_name'];
        $fromEmail   = $fa['email'];
        $toName      = $cpCfg['cp.companyName'];
        $toEmail     = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $cpCfg['cp.companyName']
            ,'toEmail'   => $cpCfg['cp.adminEmail']
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err")   );
        $validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");

        $validate->validateData('phone' , $ln->gd('cp.form.fld.phone.err'));
        $validate->validateData('address1' , $ln->gd('cp.form.fld.address1.err'));
        $validate->validateData('address2' , $ln->gd('cp.form.fld.address2.err'));
        $validate->validateData('address_area' , $ln->gd('cp.form.fld.addressArea.err'));
        $validate->validateData('address_city' , $ln->gd('cp.form.fld.addressCity.err'));
        $validate->validateData('address_country_code' , $ln->gd('cp.form.fld.country.err'));

       	$captcha_code = $fn->getPostParam('captcha_code');
        require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        $img = new Securimage;
        if ($img->check($captcha_code) == false) {
            $validate->errorArray['captcha_code']['name'] = "captcha_code";
            $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }
}