<?
class CP_Www_Themes_Korban_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        foreach ($dataArray as $row){
        }

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_anythingSlider');
        $slideshow = $wSlideshow->getWidget(array(
             'height' => 330
        ));

        $wRecord = getCPWidgetObj('content_record');
        $calloutRight = $wRecord->getWidget(array(
             'sectionType'    => 'Home'
            ,'contentType'    => 'Callout Right'
            ,'showDesc'       => FALSE
            ,'showPicInDesc'  => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => TRUE
            ,'addSearchCond'  => " AND c.latest = 1"
            ,'displayLimit'   => 3
        ));

        $wRecord = getCPWidgetObj('content_record');

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        $text = "
        {$slideshow}
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='subcl'>
                    {$title}
                    {$ln->gfv($row, 'description')}
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr latestNews'>
                    {$wRecord->getWidget(array(
                             'contentType'    => 'Record'
                            ,'showDate'       => false
                            ,'specialFilter'  => 'Latest'
                            ,'showDesc'       => false
                            ,'showPic'        => false
                            ,'heading'        => $ln->gd('w.content.record.whatsnew.heading')
                            ,'showReadMore'   => true
                            ,'displayLimit'   => 4
                    ))}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    //==================================================================//
    function getModuleEcommerceBasketControllerHook1($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['secType'] == 'Basket'){
            $text = '';

            $modName = $fn->getReqParam('modName', 'ecommerce_product');

            if ($tv['catType'] == 'Shipping Details' || $tv['catType'] == ''){
                if ($cpCfg['cp.basketArray'][$modName]['loginRequired']){
                    $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                    checkLoggedIn();
                }
                $wShip = getCPWidgetObj('ecommerce_shippingDetails');

                $text = $wShip->getWidget(array(
                     'showOrgId' => true
                    ,'showNotesPerItem' => true
                    ,'defaultCountryCode' => 'SG'
                    ,'showProductItems' => true
                    ,'showCaptcha' => true
                ));

            } else if ($tv['catType'] == 'Confirm Order'){
                if ($cpCfg['cp.basketArray'][$modName]['loginRequired']){
                    checkLoggedIn();
                }
                $wConfirm = getCPWidgetObj('ecommerce_confirmOrder');
                $text = $wConfirm->getWidget();

            } else if ($tv['catType'] == 'Order Success'){
                $text = $contObj->view->getOrderSuccess();

            } else if ($tv['catType'] == 'Order Fail'){
                $text = $contObj->view->getOrderFail();

            } else {
                $wBasket = getCPWidgetObj('ecommerce_basket');
                $text = $wBasket->getWidget();
            }

            return $text;

        } else if ($tv['secType'] == 'Order Form' || $tv['catType'] == 'Order Form'){
            $wShip = getCPWidgetObj('ecommerce_shippingDetails');

            return $wShip->getWidget(array(
                 'showOrgId' => true
                ,'showNotesPerItem' => true
                ,'defaultCountryCode' => 'SG'
                ,'showProductItems' => true
                ,'showCaptcha' => true
            ));
        } else {
            checkLoggedIn();

            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
            return $text;
        }
    }

    /**
     *
     */
    function getModuleEcommerceBasketOrderSuccessHook($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($order_id == ''){
            $order_id = $fn->getSessionParam('cpOrderId');
        }

        $text = $ln->gd('m.ecommerce.basket.order.message.success');

        if ($order_id > 0){
            $currentDate  = date("d-M-Y l h:i:s A");

            $row = $fn->getRecordRowByID('`order`', 'order_id', $order_id);

            $wBasket = getCPWidgetObj('ecommerce_basket');
            $basket  = $wBasket->getWidget(array(
                 'modName'     => $row['module']
                ,'mode'        => 'detail'
                ,'orderId'     => $order_id
            ));

            /*** empty the basket ***/
            $wBasket->model->getEmptyBasket($row['module']);

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
            $text = str_replace("[[order_id]]"       , $row["order_id"]                     , $text);

            if($dbUtil->getColumnExists('order', 'organization_id')){
                $orgRow = $fn->getRecordRowByID('organization', 'organization_id', $row['organization_id']);
                $orgCountry = $fn->getRecordByCondition('geo_country', "country_code = '{$orgRow['address_country_code']}'");

                $text = str_replace("[[org_name]]", $orgRow['name'], $text);
                $text = str_replace("[[org_address1]]", $orgRow['address1'], $text);
                $text = str_replace("[[org_address2]]", $orgRow['address2'], $text);
                $text = str_replace("[[org_city]]", $orgRow['address_city'], $text);
                $text = str_replace("[[org_country]]", $orgCountry['name'], $text);
                $text = str_replace("[[postal_code]]", $orgRow['address_po_code'], $text);
                $text = str_replace("[[org_phone]]", $orgRow['phone'], $text);
                $text = str_replace("[[org_mobile]]", $orgRow['mobile'], $text);
                $text = str_replace("[[org_fax]]", $orgRow['fax'], $text);
                $text = str_replace("[[org_email]]", $orgRow['email'], $text);
                $text = str_replace("[[org_hours]]", $orgRow['operating_hours'], $text);
                $text = str_replace("[[org_remarks]]", $orgRow['remarks'], $text);
            }

        }

        $text = "
        <div class='floatbox'>
            <div class='button float_right'>
                <a class='cpPrint'>{$ln->gd('print')}</a>
            </div>
        </div>
        {$text}
        ";

        return $text;
    }
}