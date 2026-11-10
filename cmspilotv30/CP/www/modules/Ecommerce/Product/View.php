<?
class CP_Www_Modules_Ecommerce_Product_View extends CP_Common_Modules_Ecommerce_Product_View
{
    /**
     *
     */
    function getList($dataArray) {
        $hook = getCPModuleHook('ecommerce_product', 'list', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($cpCfg['m.ecommerce.product.list.noDetailPage']){
            return $this->getDetailPageInList($dataArray);
        }

        $rows = '';
        foreach ($dataArray as $row){
            $exp = array('zoomImage' => false, 'folder' => $cpCfg['m.ecommerce.product.list.picSize']);
            $pic = $media->getMediaPicture('ecommerce_product', 'picture', $row['product_id'], $exp);
            $url = $cpUrl->getUrlByRecord($row, 'product_id');

            $price = $this->getPriceDisplay($row);
            $spIcon = $this->getSpecialIcon($row);

            $rows .= "
            <li>
                <div class='inner'>
                    {$spIcon}
                    <div class='pic'><a href='{$url}'>{$pic}</a>&nbsp;</div>
                    <div class='title'>{$ln->gfv($row, 'title')}</div>
                    <div class='cart'>{$ln->gfv($row, 'title')}</div>
                    {$price}
                </div>
            </li>
            ";
        }

        $text = "
        <div class='productList'>
            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetailPageInList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <li>
                {$this->getDetail($row)}
            </li>
            ";
        }

        $text = "
        <div class='detailInList'>
            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $hook = getCPModuleHook('ecommerce_product', 'detail', $row, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');

        $buyNow ='';
        if ($cpCfg['m.ecommerce.product.addToCart']== 1) {
            $wAddToCart = getCPWidgetObj('ecommerce_addToCart');
            $buyNow = $wAddToCart->getWidget(array('record' => $row));
        }

        $exp = array('style' => 'mb5');

        $text = "
        <div>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>

        <div class='subcolumns productDetail'>
            <div class='c50l'>
                <div class='subcl'>
                    <h1>{$ln->gfv($row, 'title')}</h1>
                    {$this->getPriceDisplay($row)}
                    {$buyNow}
                    <div class='desc'>
                        {$ln->gfv($row, 'description')}
                    </div>
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    {$wImagesSlider->getWidget(array(
                         'module'    => 'ecommerce_product'
                        ,'record_id' => $row['product_id']
                        ,'height'    => 400
                        ,'width'     => 350
                    ))}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPriceDisplay($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $text = '';

        if ($cpCfg['m.ecommerce.product.showPrice'] && $row['price'] > 0){
            $text = "
            <div class='price'>
                {$cpCfg['cp.basketArray']['ecommerce_product']['currencyDisplay']} " . number_format($row['price'], 0) . "
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getSpecialIcon($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $url = $cpUrl->getUrlByRecord($row, 'product_id');

        $text = '';
        if ($cpCfg['m.ecommerce.product.list.spIconClass'] != '' && $row['price'] > 0){
            $text = "
            <div class='{$cpCfg['m.ecommerce.product.list.spIconClass']}'>
                <a href='{$url}'><span>{$ln->gd('listSpIconText')}</span></a>
            </div>
            ";
        }

        return $text;
    }
}