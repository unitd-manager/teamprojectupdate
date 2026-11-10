<?
class CP_Www_Modules_Ecommerce_Basket_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getOrderSuccess($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if ($order_id == ''){
            $order_id = $fn->getSessionParam('cpOrderId');
        }

        $hook = getCPModuleHook('ecommerce_basket', 'orderSuccess', $order_id);
        if($hook['status']){
            return $hook['html'];
        }

        if ($order_id > 0){
            $row = $fn->getRecordRowByID('order', 'order_id', $order_id);
            $_SESSION['cpNonMemberContactID'] = $row['contact_id'];

            if(!is_array($row)){
                return;
            }

            $basketArr = $cpCfg['cp.basketArray'][$row['module']];
            $text = $ln->gd($basketArr['successMsg']);

            $wBasket = getCPWidgetObj('ecommerce_basket');
            $basket  = $wBasket->getWidget(array(
                 'modName' => $row['module']
                ,'mode'    => 'detail'
                ,'orderId' => $order_id
            ));

            /*** empty the basket ***/
            $wBasket->model->getEmptyBasket($row['module']);

            $currentDate  = date("d-M-Y l h:i:s A");

            $text = str_replace("[[first_name]]"     , $row["shipping_first_name"]          , $text);
            $text = str_replace("[[last_name]]"      , $row["shipping_last_name"]           , $text);
            $text = str_replace("[[email]]"          , $row["shipping_email"]               , $text);
            $text = str_replace("[[phone]]"          , $row["shipping_phone"]               , $text);
            $text = str_replace("[[address1]]"       , $row["shipping_address1"]            , $text);
            $text = str_replace("[[address2]]"       , $row["shipping_address2"]            , $text);
            $text = str_replace("[[address_area]]"   , $row["shipping_address_area"]        , $text);
            $text = str_replace("[[address_city]]"   , $row["shipping_address_city"]        , $text);
            $text = str_replace("[[address_state]]"  , $row["shipping_address_state"]       , $text);
            $text = str_replace("[[address_country]]", $row["shipping_address_country_code"], $text);
            $text = str_replace("[[payment_method]]" , $row["payment_method"]               , $text);
            $text = str_replace("[[currentDate]]"    , $currentDate                         , $text);
            $text = str_replace("[[basket]]"         , $basket                              , $text);

            if($dbUtil->getColumnExists('order', 'organization_id')){
                $orgRow = $fn->getRecordRowByID('organization', 'organization_id', $row['organization_id']);
                $orgName = $orgRow['name'];
                $text = str_replace("[[org_name]]", $orgName, $text);
            }

        }

        $heading = '';
        $key = 'm.ecommerce.basket.order.message.success.heading';

        if ($ln->gd2($key)){
            $heading = "
            <h1>{$ln->gd2($key)}</h1>
            ";
        }

        $nonMemberMsg = '';
        $cpNonMemberContactID = $fn->getSessionParam('cpNonMemberContactID');

        if (!isLoggedInWww() && $ln->gd2($key) != '' && $cpNonMemberContactID > 0){
            $key = 'm.ecommerce.basket.order.message.success.nonMember';
            $expPass['password'] = 1;

            $formAction = '/index.php?module=membership_contact&_spAction=addPasswordForNonMember&showHTML=0';
            $memberType  = 'membership_contact'; // as: to generailize this later

            CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-2.69'));

            $nonMemberMsg = "
            <div class='nonMemberMsg'>
                {$ln->gd2($key)}
                <form name='registerForm' id='registerForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
                    {$formObj->getTBRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expPass)}
                    <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('m.ecommerce.basket.nonMember.addPassword.sucecss')) . "' />
                    <div class='type-button'>
                        <div class='floatbox'>
                            <div class='btnSubmit'>
                                <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            </div>
                        </div>
                    </div>
                    <input type='hidden' name='memberType' value='{$memberType}' />
                    <input type='submit' name='x_submit' class='submithidden' />
                </form>
            </div>
            ";
        }

        $text = "
        <div class='orderThanks'>
            {$heading}
            <div class='floatbox'>
                <div class='button float_right printOrder'>
                    <a class='cpPrint'>{$ln->gd('cp.lbl.print')}</a>
                </div>
            </div>
            <div class='thxMessage'>
                {$text}
                {$nonMemberMsg}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderFail($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($order_id == ''){
            $order_id = $fn->getSessionParam('cpOrderId');
        }

        $hook = getCPModuleHook('ecommerce_basket', 'orderFail', $order_id);
        if($hook['status']){
            return $hook['html'];
        }

        if ($order_id > 0){
            $row = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if(!is_array($row)){
                return;
            }

            $basketArr = $cpCfg['cp.basketArray'][$row['module']];
            $text = $ln->gd($basketArr['failMsg']);

            return $text;
        }
    }
}
