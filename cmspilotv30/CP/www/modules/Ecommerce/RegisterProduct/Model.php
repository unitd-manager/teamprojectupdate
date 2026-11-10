<?
class CP_Www_Modules_Ecommerce_RegisterProduct_Model extends CP_Common_Lib_ModuleModelAbstract
{

    //==================================================================//
    function getSQL() {
        $modObj = getCPModuleObj('ecommerce_product');
        return $modObj->model->getSQL();
    }

    //==================================================================//
    function setSearchVar($linkRecType) {
        $modObj = getCPModuleObj('ecommerce_product');
        $modObj->model->setSearchVar($linkRecType);
    }

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

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['last_name']     = $fn->getPostParam('last_name');
        $fa['email']         = $fn->getPostParam('email');
        $fa['product_id']    = $fn->getPostParam('product_id');
        $fa['product_serial']= $fn->getPostParam('product_serial');
        $fa['country']       = $fn->getPostParam('country_code');
        $fa['creation_date'] = date('Y-m-d H:i:s');

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, 'register_product');
        $result      = $db->sql_query($SQL);
        $product_id  = $db->sql_nextid();

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact',  "email = '{$email}'");
        
        if (isset($rec['email'])){
            $fa1 = array();
            $fa1['subscribe'] = 1;
            $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'contact', "WHERE email = '{$rec['email']}'");
            $result = $db->sql_query($updateSQL);
        } else {
            $fa2 = array();
            $fa2['first_name']    = $fn->getPostParam('first_name');
            $fa2['last_name']     = $fn->getPostParam('last_name');
            $fa2['email']         = $fn->getPostParam('email');
            $fa2['creation_date'] = date('Y-m-d H:i:s');
            $fa2['subscribe']     = 1;
            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa2, 'contact');
            $result = $db->sql_query($SQL);
            $id     = $db->sql_nextid();
        }
        
        //-----------------------------------------------------------------//
        $product_serial = $fn->getPostParam('product_serial', '', true);
        $product_id = $fn->getPostParam('product_id', '', true);

        $fa3 = array();
        $fa3['registered'] = 1;
        $fn->saveRecord($fa3, 'serial_nos', '', '', 
        array('customWhereCondn' => "product_serial = '{$product_serial}' AND product_id = '{$product_id}'"));

        $currentDate  = date('d-M-Y l h:i:s A');
        $gcRec = $fn->getRecordByCondition('geo_country', "country_code='{$fa['country']}'");
        $prodRec = $fn->getRecordRowByID('product', 'product_id', $fa['product_id']);

        $message = $ln->gd('m.ecommerce.registerProduct.form.registerProduct.email.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);
        $message = str_replace('[[product_name]]', $prodRec['title'], $message);
        $message = str_replace('[[product_serial]]', $fa['product_serial'], $message);
        $message = str_replace('[[country]]', $gcRec['name'], $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('m.ecommerce.registerProduct.form.registerProduct.email.notifySubject');
        $fromName  = $fa['first_name'] . ' ' . $fa['last_name'];
        $fromEmail = $fa['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];                

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name", $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData("last_name", $ln->gd("cp.form.fld.lastName.err"));
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $validate->validateData("product_id", $ln->gd("cp.form.fld.productName.err"));
        $validate->validateData("product_serial", $ln->gd("cp.form.fld.productSerial.err"));
        $validate->validateData("accept_terms", $ln->gd("cp.form.fld.acceptTerms.err"));

        $product_serial = $fn->getPostParam('product_serial', '', true);
        $product_id = $fn->getPostParam('product_id', '', true);
        
        if ($product_serial != '' && $product_id != ''){
            $rec = $fn->getRecordByCondition('serial_nos', "
                product_serial = '{$product_serial}'
                AND product_id = '{$product_id}'
                "
            );
    
            if (is_array($rec)){
                if ($rec['registered'] == 1){
                    $validate->errorArray['product_serial']['name'] = "product_serial";
                    $validate->errorArray['product_serial']['msg']  = $ln->gd("cp.form.fld.productSerial.err.alreadyRegistered");
                }
            } else {
                $validate->errorArray['product_serial']['name'] = "product_serial";
                $validate->errorArray['product_serial']['msg']  = $ln->gd("cp.form.fld.productSerial.err.notExists");
            }
        }

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
}