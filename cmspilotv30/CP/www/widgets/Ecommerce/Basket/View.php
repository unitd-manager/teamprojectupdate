<?
class CP_Www_Widgets_Ecommerce_Basket_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getWidget($exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];

        $c->currency = $basketArray['currency'];
        $c->currencyDisplay = $basketArray['currencyDisplay'];
        $decimals = $basketArray['decimals'];

        if ($rowsHTML != ""){
            $heading = ($c->mode == 'edit') ? "<h1>{$ln->gd($c->heading)}</h1>" : '';
            $buttons = ($c->mode == 'edit') ? $this->getButtons() : '';

            $totColSpan = $c->totalTableCols - 1;
            if (!$c->showPicture){
                $totColSpan = $totColSpan - 1;
            }

            $removeCell = '';
            if ($c->mode != 'detail') {
                $removeCell = "<td></td>";
            }

            $shipping = '';
            if ($c->hasShippingCharge){
               $shipping = $this->getShippingDisplay($totColSpan);
            }

            $discount = '';
            if ($c->hasDiscount){
               $discount = $this->getDiscountDisplay($totColSpan);
            }

            $grossVal = number_format($this->model->getBasketGrossTotal(), $decimals);
            $netVal = number_format($this->model->getBasketNetTotal(), $decimals);

            $text = "
            {$heading}
            <div class='cartTblOuter'>
                <table class='thinlist'>
                    <thead>
                        <tr>{$this->getTHeadRow()}</tr>
                    </thead>
                    <tbody>
                        {$rowsHTML}
                        <tr>
                            <td colspan='{$totColSpan}' class='txtRight grossTotalLbl'>{$ln->gd('w.ecommerce.basket.lbl.grossTotal')}</td>
                            <td class='txtRight grossTotalValue'>{$this->getPriceDisplay($grossVal, 'gross')}</td>
                            {$removeCell}
                        </tr>
                        {$discount}
                        {$shipping}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='{$totColSpan}' class='txtRight netTotalLbl'>{$ln->gd('w.ecommerce.basket.lbl.netTotal')}</td>
                            <td class='txtRight netTotalValue'>{$this->getPriceDisplay($netVal, 'net')}</td>
                            {$removeCell}
                        </tr>
                    </tfoot>
                </table>
                {$buttons}
            </div>
            ";
        } else {
            $url = $cpUrl->getUrlBySecType($basketArray['sectionType']);

            $text = "
            <div class='emptyCartInfo'>
                <div>{$ln->gd($c->emptyCartInfo)}</div>
            </div>
            <div class='button btnContinueShopping'>
                <a href='{$url}'>
                    {$ln->gd($c->continueShopping)}
                </a>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getTHeadRow() {
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $picture = '';
        if ($c->showPicture){
            $picture = "
            <th class='picture'><span>{$ln->gd('w.ecommerce.basket.lbl.picture')}</span></th>
            ";
        }

        $removeCell = '';
        if ($c->mode != 'detail') {
            $removeCell = "<th></th>";
        }

        $basket = $cpCfg['cp.basketArray'][$c->modName];
        $showCurrencyOnAllValues = $basket['showCurrencyOnAllValues'];

        $currency = '';
        if (!$showCurrencyOnAllValues){
            $currency = "({$c->currencyDisplay})";
        }

        $text = "
        {$picture}
        <th class='title'>{$ln->gd('w.ecommerce.basket.lbl.title')}</th>
        <th class='unit txtRight'>{$ln->gd('w.ecommerce.basket.lbl.unitPrices')} {$currency}</th>
        <th class='qty txtCenter'>{$ln->gd('w.ecommerce.basket.lbl.quantity')}</th>
        <th class='total txtRight'>{$ln->gd('w.ecommerce.basket.lbl.total')} {$currency}</th>
        {$removeCell}
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $decimals = $basketArray['decimals'];

        $rows = '';
        foreach ($this->model->dataArray as $key => $row) {
            $picture = '';
            if ($c->showPicture){
                $exp = array('folder' => 'thumb', 'appendSiteUrl' => true, 'limit' => 1);

                if ($c->hasItemsInChildTable){
                    $exp['childId'] = $row['child_id'];
                    $pic = $media->getMediaPicture($row['module'], 'picture', $row['record_id'], $exp);
                } else {
                    $pic = $media->getMediaPicture($row['module'], 'picture', $row['record_id'], $exp);
                }

                $picture = "
                <td class='picture'>{$pic}</td>
                ";
            }

            $notes = '';
            if($dbUtil->getColumnExists('basket', 'notes')){
                $notes = "<div class='notes'>{$row['notes']}</div>";
            }

            $removeCell = '';
            if ($c->mode != 'detail') {
                $removeCell = "<td class='remove'><div class='button2 removeItem' basket_id='{$row['basket_id']}'>{$ln->gd('w.ecommerce.basket.lbl.remove')}</div></td>";
            }

            $unit_price = number_format($row['unit_price'], $decimals);
            $total_price = number_format($row['total_price'], $decimals);

            $skuNo = '';
            if($basketArray['showSKUNoInEmail'] && $c->orderId > 0){
                $skuNo = "
                <br>
                SKU: {$row['sku_no']}
                ";
            }

            $rows .= "
            <tr>
                {$picture}
                <td class='title'>{$row['title']}{$skuNo}{$notes}</td>
                <td class='unit txtRight'>{$this->getPriceDisplay($unit_price, 'unit')}</td>
                <td class='qty txtCenter'>{$this->getQtyText($row)}</td>
                <td class='total txtRight'>{$this->getPriceDisplay($total_price, 'subTotal')}</td>
                {$removeCell}
            </tr>
            ";
        }

        return $rows;
    }

    /**
     *
     */
    function getButtons() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $shopUrl = $cpUrl->getUrlBySecType($basketArray['sectionType']);
        $shipUrl = $cpUrl->getUrlByCatType('Shipping Details', $basketArray['basketSecType']);

        $text = "
        <div class='shopBtns' modName='{$c->modName}'>
        <div class='floatbox'>
            <div class='float_left button btnContinueShopping'>
                <a href='{$shopUrl}'>
                    {$ln->gd($c->continueShopping)}
                </a>
            </div>
            <div class='float_left button btnEmptyCart'>{$ln->gd($c->emptyCart)}</div>
            <div class='float_right button btnCheckout'>
                <a href='{$shipUrl}'>
                    {$ln->gd($c->checkout)}
                </a>
            </div>
        </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getQtyText($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        if ($c->mode == 'detail') {
            return $row['qty'];
        }

        $basket = $cpCfg['cp.basketArray'][$row['module']];
        $tableName  = $basket['tableName'];
        $keyField   = $basket['keyField'];

        $rec = $fn->getRecordRowByID($tableName, $keyField, $row['record_id']);
        $stock = ($rec['qty_in_stock'] > 100) ? 100 : $rec['qty_in_stock'];

        $arr = array();
        for($i = 0; $i <= $stock; $i++){
            $arr[] = $i;
        }

        $text = "
        <select name='qty' basket_id='{$row['basket_id']}'>
            {$cpUtil->getDropDown1($arr, $row['qty'], false)}
        </select>
        ";

        return $text;
    }

    /**
     *
     */
    function getCurrencyDisplay() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $basket = $cpCfg['cp.basketArray'][$c->modName];
        $showCurrencyOnAllValues = $basket['showCurrencyOnAllValues'];

        if ($showCurrencyOnAllValues){
            return $c->currencyDisplay . " ";
        }
    }

    /**
     *
     */
    function getPriceDisplay($price, $type = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $negSign = ($type == 'discount' && $price > 0) ? '-' : '';
        $value = "
        <span class='sign'>{$negSign}</span>
        <span class='currencyDisplay'>{$this->getCurrencyDisplay()}</span>
        <span class='priceDisplay'>{$price}</span>
        ";

        return $value;
    }

    /**
     *
     */
    function getShippingDisplay($totColSpan) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $showCurrencyOnAllValues = $basketArray['showCurrencyOnAllValues'];
        $decimals = $basketArray['decimals'];
        $countryDd = '';

        if ($c->showCtryDdInBktForShipCalc && $c->mode != 'detail'){
            $shippingCountryCode = $fn->getSessionParam('cpShippingCountryCode');
            $countryDd = $formObj->getDropDownBySQL($ln->gd('w.ecommerce.basket.lbl.chooseCountry'),
                         'shippingCountryCode', $fn->getGeoCountrySQL(), $shippingCountryCode);
        }

        $shippngVal = number_format($this->model->getShippingCharge(), $decimals);

        $removeCell = '';
        if ($c->mode != 'detail') {
            $removeCell = "<td></td>";
        }

        $text = "
        <tr>
            <td colspan='{$totColSpan}' class='txtRight shippingChargeLbl'>
                {$ln->gd('w.ecommerce.basket.lbl.shippingCharge')} {$countryDd}
            </td>
            <td class='txtRight shippingChargeValue'>{$this->getPriceDisplay($shippngVal, 'shipping')}</td>
            {$removeCell}
        </tr>
        ";

        return $text;
    }


    /**
     *
     */
    function getDiscountDisplay($totColSpan) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $showCurrencyOnAllValues = $basketArray['showCurrencyOnAllValues'];
        $decimals = $basketArray['decimals'];
        $promoCodeSess = $fn->getSessionParam('cpPromoCode');

        $enterPromoTxt = '';
        if ($c->hasPromoCode && $c->mode != 'detail'){

            $cls1 = ($promoCodeSess != '') ? 'hideme' : '';
            $cls2 = ($promoCodeSess != '') ? '' : 'hideme';

            $enterPromoTxt = "
            <a href='#' id='enterPromoCode' class='{$cls1}'>
                 {$ln->gd('w.ecommerce.basket.lbl.enterPromoCode')}
            </a>
            <span id='promoCodeAppliedWrapper' class='{$cls2}'>
                {$ln->gd('w.ecommerce.basket.lbl.promoCodeApplied')}
                <code>{$promoCodeSess}</code>
                <a href='#' class='remove'>
                     {$ln->gd('w.ecommerce.basket.lbl.removePromoCode')}
                </a>
            </span>
            ";
        }

        $discountVal = number_format($this->model->getDiscount(), $decimals);

        $removeCell = '';
        if ($c->mode != 'detail') {
            $removeCell = "<td></td>";
        }

        $text = "
        <tr>
            <td colspan='{$totColSpan}' class='txtRight discountLbl'>
                {$ln->gd('w.ecommerce.basket.lbl.discount')}
                {$enterPromoTxt}
            </td>
            <td class='txtRight discountValue'>{$this->getPriceDisplay($discountVal, 'discount')}</td>
            {$removeCell}
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getEnterPromoCode() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;

        $url = "{$cpCfg['cp.scopeRootAlias']}index.php?widget=ecommerce_basket&_spAction=addPromoCode&showHTML=0";
        $text = "
        <form id='frmPromoCode' class='yform columnar' method='post' action='{$url}'>
            {$formObj->getTBRow($ln->gd('w.ecommerce.basket.lbl.enterPromoCode'), 'promo_code')}
        </form>
        ";

        return $text;
    }
}