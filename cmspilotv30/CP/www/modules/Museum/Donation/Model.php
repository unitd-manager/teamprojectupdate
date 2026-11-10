<?
class CP_Www_Modules_Museum_Donation_Model extends CP_Common_Lib_ModuleModelAbstract
{

    //==================================================================//
    function getSQL() {
        $modObj = getCPModuleObj('webBasic_content');
        return $modObj->model->getSQL();
    }

    //==================================================================//
    function setSearchVar($linkRecType) {
        $modObj = getCPModuleObj('webBasic_content');
        $modObj->model->setSearchVar($linkRecType);
    }

    /**
     *
     */
    function getAdd() {
        $hook = getCPModuleHook2('museum_donation', 'add', $this);
        if($hook['status']){
            return $hook['html'];
        }

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

        $fa['cust_first_name']    = $fn->getPostParam('first_name');
        $fa['shipping_first_name']= $fa['cust_first_name'];
        $fa['cust_last_name']     = $fn->getPostParam('last_name');
        $fa['shipping_last_name'] = $fa['cust_last_name'];
        $fa['cust_email']         = $fn->getPostParam('email');
        $fa['shipping_email']     = $fa['cust_email'];
        $fa['cust_company_name']  = $fn->getPostParam('company_name');
        $fa['memo']               = $fn->getPostParam('comments');
        $fa['payment_method']     = 'paypal';
        $fa['record_type']        = 'Donation';
        $fa['module']             = 'museum_donation';
        $fa['order_status']       = 'New';
        $fa['shipping_charge']    = 0;

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
        $result = $db->sql_query($SQL);
        $order_id = $db->sql_nextid();
        $_SESSION['order_id'] = $order_id;

        $product_id = $fn->getPostParam('product_id');
        $SQL = "
        SELECT *
        FROM product
        WHERE product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        //0rder Item
        $fa = array();
        $fa['order_id']   = $order_id;
        $fa['module']     = 'ecommerce_product';
        $fa['record_id']  = $fn->getPostParam('product_id');
        $fa['qty']        = 1;
        $fa['item_title'] = $row['title'];
        $fa['unit_price'] = $row['price'];

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order_item');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
        $db->sql_query($SQL);

        $returnUrl = '/index.php?module=museum_donation&_spAction=proccedToPayment&showHTML=0';
        return $validate->getSuccessMessageXML($returnUrl);
    }

    /**
     *
     */
    function getNewValidate() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('museum_donation', 'newValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name", $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData("last_name", $ln->gd("cp.form.fld.lastName.err"));
        $validate->validateData("company_name", $ln->gd("cp.form.fld.companyName.err"));
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $validate->validateData("product_id", $ln->gd("m.museum.donation.form.fld.donationAmount.err"));

        if (!$cpCfg['m.webBasic.contactUs.hideCaptcha']) {
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

    /**
     *
     */
    function getProccedToPayment() {
        $fn = Zend_Registry::get('fn');

        $order_id = $fn->getSessionParam('order_id');

        if($order_id == ''){
            exit;
        }

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $plObj = getCPPluginObj('paymentMethods_' . $orderRec['payment_method']);
        $plObj->model->proceedToGateway($order_id);
    }
}