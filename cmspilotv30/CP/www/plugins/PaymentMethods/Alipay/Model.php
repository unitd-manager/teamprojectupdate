<?
require_once("lib/alipay_submit.class.php");
require_once("lib/alipay_notify.class.php");
class CP_Www_Plugins_PaymentMethods_Alipay_Model extends CP_Common_Lib_PluginModelAbstract
{

    /**
     *
     */
    function getDataArray($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $basketArray = $cpCfg['cp.basketArray']['ecommerce_product'];
        $successUrl  = $cpUrl->getUrlByCatType('Order Success', $basketArray['basketSecType']);
        $cancelUrl   = $cpUrl->getUrlByCatType('Order Cancel', $basketArray['basketSecType']);
        $siteUrl     = $cpCfg['cp.siteUrl'];

        if (substr($siteUrl, -1, 1) == '/'){
            $siteUrl = substr($siteUrl, 0, strlen($siteUrl)-1);
        }

        $arr = array();
        $arr['title']        = $ln->gd('p.paymentMethods.lbl.alipay');

        //合作身份者id，以2088开头的16位纯数字
        //Cooperation identity by id, 2088 beginning with 16 pure digital
        $arr['partner'] = $cpCfg['alipayPartnerId'];

        //安全检验码，以数字和字母组成的32位字符
        //Safety inspection codes to numbers and letters of 32 characters
        $arr['key'] = $cpCfg['alipaySecurityKey'];

        //卖家支付宝帐户
        //Sellers Alipay account
        $arr['seller_email'] = $cpCfg['alipayEmail'];
        //必填 / Required


        //签名方式 不需修改
        //Signature way without modification
        $arr['sign_type']    = strtoupper('MD5');

        //字符编码格式 目前支持 gbk 或 utf-8
        //Character encoding formats currently supported gbk or utf-8
        $arr['input_charset']= strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //ca certificate path address for curl in ssl check 
        //请保证cacert.pem文件在当前文件夹目录中
        //Please ensure cacert.pem file in the current folder directory
        $arr['cacert']    = getcwd().'\\cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        //Access mode, according to their own server supports ssl access, if supported, select https; Without support, please select http
        $arr['transport']    = 'http';


        //支付类型 / Payment Types
        $arr['payment_type'] = "1"; // 1 = Goods Purchased, 4 = Donations
        //必填，不能修改
        //Required, can not be modified

        $arr['return_url']   = "{$siteUrl}{$successUrl}";
        $arr['notify_url']  = "{$siteUrl}/index.php?plugin=paymentMethods_alipay&_spAction=notify&showHTML=0";

        //商户订单号 / Merchant Order Number
        $arr['out_trade_no'] = $order_id;
        //商户网站订单系统中唯一订单号，必填
        //The only merchant website order system order number, required

        $total = 0;
        if($order_id ){
            $total = getCPModuleObj('ecommerce_basket')->model->getOrderTotal($order_id);
        }

        //付款金额 / Payment Amount
        $arr['total_fee'] = $total;
        //必填 / Required

        //订单名称 / Order Name
        $arr['subject'] = $cpCfg['cp.companyName'];
        //必填 / Required
        
        //订单描述 / Orders description
        $arr['body'] = '';

        //商品展示地址 / Merchandise display address
        $arr['show_url'] = $siteUrl;
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //Need to http: // full path beginning with, for example: http: //www.xxx.com/myorder.html

        //防钓鱼时间戳
        //Anti-phishing timestamp
        $arr['anti_phishing_key'] = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        //客户端的IP地址
        $arr['exter_invoke_ip'] = "";
        //非局域网的外网IP地址，如：221.0.0.1
        //非局域网的外网IP地址，如：221.0.0.1


        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function proceedToGateway($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $_SESSION['cpOrderId'] = $order_id;

        $arr = $this->getDataArray($order_id);

        $item_name = str_replace("'", "\\'", $cpCfg['cp.companyName']);
        if (isset($cpCfg['p.paymentMethods.alipay.paymentItemName']) && 
            $cpCfg['p.paymentMethods.alipay.paymentItemName'] != '') {
            $item_name = str_replace("'", "\\'", $cpCfg['p.paymentMethods.alipay.paymentItemName']);
        }

        $alipay_config = array(
            'partner' => $arr['partner'],
            'key'     => $arr['key'],
            'sign_type' => $arr['sign_type'],
            'input_charset' => $arr['input_charset'],
            'cacert'    => $arr['cacert'],
            'transport' => $arr['transport']
        );

        $parameter = array(
                "service" => "create_direct_pay_by_user",
                "partner" => trim($arr['partner']),
                "payment_type"  => $arr['payment_type'],
                "notify_url"    => $arr['notify_url'],
                "return_url"    => $arr['return_url'],
                "seller_email"  => $arr['seller_email'],
                "out_trade_no"  => $arr['out_trade_no'],
                "subject"   => $arr['subject'],
                "total_fee" => $arr['total_fee'],
                "body"      => $arr['body'],
                "show_url"  => $arr['show_url'],
                "anti_phishing_key" => $arr['anti_phishing_key'],
                "exter_invoke_ip"   => $arr['exter_invoke_ip'],
                "_input_charset"    => trim(strtolower($arr['input_charset']))
        );


        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认 / Confirm");

        $text = "
        {$html_text}

        <div class='proceedToGateway'>
            <h2>Processing to the payment gateway.. please wait</h2>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            Util.showProgressInd();
        "));

        return $text;
    }

    /**
     *
     */
    function getNotify(){
        set_time_limit(120);
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $arr = $this->getDataArray();

        $alipay_config = array(
            'partner' => $arr['partner'],
            'key'     => $arr['key'],
            'sign_type' => $arr['sign_type'],
            'input_charset' => $arr['input_charset'],
            'cacert'    => $arr['cacert'],
            'transport' => $arr['transport']
        );

        //======================================================
        $postData = "";
        foreach ($_POST as $i=>$v) { 
            $postData.= $i . "=" . urlencode($v) . "&"; 
        }
        $fn->logThis("\n ALIPAY RAW POST DATA: \n".$postData);
        //=======================================================

        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        $fn->logThis("\n ALIPAY VERIFY RESULT: {$verify_result} \n");

        if($verify_result){ //Authentication is successful
            $out_trade_no = $_POST['out_trade_no']; //our order reference
            $trade_no = $_POST['trade_no']; //Alipay transaction number
            $trade_status = $_POST['trade_status']; //Transaction status

            $logMsg = "
            \n ALIPAY IPN VERIFIED: {$out_trade_no}
            \n ORDER ID: {$out_trade_no}
            \n PAYMENT STATUS: {$trade_status}
            \n Alipay Transaction No: {$trade_no}
            ";
            $fn->logThis($logMsg);

            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' ){
                $order_id = $out_trade_no;
                $memo = $trade_no;

                $rec = $fn->getRecordRowByID('order', 'order_id', $order_id);

                if (!is_array($rec)) {
                    exit();
                }

                if($rec['order_status'] != 'New'){
                    exit();
                }

                //-------------------------------------------------------------------//
                $fa = array();
                $fa['order_status']      = 'Paid';
                $fa['memo']              = $memo;
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $condn  = "WHERE order_id = {$order_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $condn);
                $result = $db->sql_query($SQL);

                $this->getUpdateStock($order_id);

                $basketObj = getCPModelObj('ecommerce_basket');

                $funcName = "getCallbackAfterPaymentGatewayPostback";
                // write a function with the above name in modules/ecommerce/basket/functions.php to perform necessary actions
                $fnMod = getCPModuleObj('ecommerce_basket')->fns;
                if (method_exists($fnMod, $funcName)) {
                    $fnMod->$funcName($order_id);
                }

                $basketObj->sendOrderConfirmationEmails($order_id);

                print 'success';

            }
            exit();
        }
    }

    /**
     *
     */
    function getUpdateStock($order_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $arr = $this->getDataArray();

        $rec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if (!is_array($rec)) {
            exit();
        }

        $basketArr = $cpCfg['cp.basketArray'][$rec['module']];
        $hasItemsInChildTable = $basketArr['hasItemsInChildTable'];
        $updateStockAfterPayment = $basketArr['updateStockAfterPayment'];

        if (!$updateStockAfterPayment){
            return;
        }

        if ($rec['module'] == 'ecommerce_product'){
            $itemsArr = $fn->getArrBySQL("
                SELECT *
                FROM order_item
                "
                ,array(
                     "order_id = '{$rec['order_id']}'"
                )
            );

            foreach($itemsArr AS $row){
                if ($hasItemsInChildTable){
                    $updateSQL = $fn->getSQL("
                        UPDATE product_item
                        SET stock = (stock - {$row['qty']})
                        "
                        ,array(
                             "product_item_id = '{$row['child_id']}'"
                        )
                    );
                    $result = $db->sql_query($updateSQL);
                }
            }
        }
    }
}