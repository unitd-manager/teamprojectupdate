<?
class CP_Www_Modules_Gdj_Jewellery_View extends CP_Common_Modules_Gdj_Jewellery_View
{

    /**
     *
     */
    function __construct(){

    }

    /**
     *
     * @param <type> $result
     * @return <type> 
     */
    function getController($result) {
        $tv = Zend_Registry::get('tv');

        if ($tv['action'] == 'detail'){
            return $this->getDetail($result);
        } else {
            return $this->getList($result);
        }
    }
        
    /**
     *
     * @param <type> $result
     * @return <type> 
     */
    function getListArray($result) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $listArray = array();
                
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $row['url']  = $fn->getSEOUrlByRecord($row, 'product_id');
            $listArray[] = $row;
        }

        return $listArray;
    }

    /**
     *
     * @return <type> 
     */
    function getRows($listArray) {
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $rows = '';
        foreach($listArray AS $row) {
            $description_short = $ln->gfv($row, 'description_short');

            $desc = '';
            if ($description_short != ''){
                $desc = "
                <div class='desc mt10'>
                    {$description_short}
                </div>
                ";
            }
            
            $title = ($row['show_title'] == 1) ? "<h5><a class='colr' href='{$row['url']}'>{$ln->gfv($row, 'title')}</a></h5>" : '';

            $exp = array('folder' => 'thumb', 'url' => $row['url']);
            $rows .= "
            <div class='c25l'>
                <div class='subcl productListWrapInner'>
                    {$title}
                    <div class='productThumb'>
                        {$media->getMediaPicture('gemstone', 'picture', $row['product_id'], $exp)}
                    </div>    
                    <div class='colr'>
                        <strong>{$row['price']}</strong>
                    </div>
                    {$desc}
                    <div class='mt10'>
                        <a class='add' href='#'>{$ln->gd('addToWishlist')}</a>
                    </div>
                    <div>    
                        <a class='add' href='#'>{$ln->gd('addToCompare')}</a>
                    </div>    
                    <div class='mt10'>
                        <a class='btnCart' href='#'>{$ln->gd('addToCart')}</a>
                    </div>    
                </div>
            </div>    
            ";
        }

        return $rows;
    }
    
    /**
     *
     * @param <type> $result
     * @return <type> 
     */
    function getList1($result) {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $tv = Zend_Registry::get('tv');
        
        $rows = '';
        $listArray = $this->getListArray($result);

        $rows = $this->getRows($listArray);

        $text = "
        <div class='subcolumns productListWrap'>
            {$rows}
        </div>
        ";
        return $text;
    }

    /**
     * @param <type> $result
     * @return <type> 
     */
     
    /**
     *
     */
    function getList($dataArray) {
        $hook = getCPModuleHook('gdj_jewellery', 'list', $dataArray);
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
            $pic = $media->getMediaPicture('gdj_jewellery', 'picture', $row['product_id'], $exp);
            $url = $cpUrl->getUrlByRecord($row, 'product_id');

            $title = $ln->gfv($row, 'title');
            if ($row['title'] != '' && $row['carat'] != ''){
                $title = $ln->gfv($row, 'title') . ' ' . $ln->gfv($row, 'carat') . ' ' . $ln->gd('cp.lbl.productSuffix');
            }
            
            $rows .= "
            <li>
                <div class='innerBorder'>
                    <div class='inner'>
                        <div class='pic'><a href='{$url}'>{$pic}</a>&nbsp;</div>
                        <div class='title'><a href='{$url}'>{$title}</a></div>
                        <!--<div class='cart'><a href='{$url}'>{$ln->gd('cp.lbl.addToCart')}</a></div>-->
                        <!--<div class='price'><a href='{$url}'>{$ln->gd('$')}{$ln->gfv($row, 'price')}</a></div>-->
                        {$this->getPriceDisplay($row)}
                    </div>
                </div>
            </li>
            ";
        }
        $theme = getCPThemeObj($cpCfg['cp.theme']);
        
        $text = "
        <div class='productList'>
            <div class='floatbox'>
                {$theme->view->getPagerPanel()}
            </div>

            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }
     
    function getDetail1($result) {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $row = $db->sql_fetchrow($result);

        $title = ($row['show_title'] == 1) ? "<h4 class='heading colr'>{$ln->gfv($row, 'title')}</h4>" : '';
        
        $exp = array('fieldLabelCls' => 'float_left colr', 'rowCls' => 'mb10');

        $wdSlideshow = includeCPClass('Widget', 'media_relatedImagesSlider', 'RelatedImagesSlider');
        $slideshow   = $wdSlideshow->getSlideShow('', 'gemstone', 'relatedPicture', $row['product_id']);

        $wdRelated = includeCPClass('Widget', 'ecommerce_relatedProducts', 'RelatedProducts');
        $related   = $wdRelated->getRelatedProducts('', $row['product_id']);

        $text = "
        {$title}
        <div class='productDetailTop'>
            <div class='subcolumns'>
                <div class='c33l'>
                    <div class='subcl'>
                        <div class='productDetailPicWrap'>
                            {$slideshow}
                        </div>    
                    </div>
                </div>
                <div class='c66r'>
                    <div class='subcr productDetailRight'>
                        {$fn->getFieldLabelWithValue($ln->gd('lotNo'), $row['item_code'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('carat'), $row['carat'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('piecesQty'), $row['pieces_qty'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('shape'), $row['shape'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('color'), $row['color'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('cut'), $row['cut'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('measurement'), $row['measurement'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('luster'), $row['luster'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('origin'), $row['origin'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('type'), $row['type'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('cp.form.fld.comments.lbl'), $row['treatment'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('lab'), $row['lab'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('hardness'), $row['hardness'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('price'), $cpCfg['shopCurrency'] . ' ' . $row['price'], $exp)}
                        {$fn->getFieldLabelWithValue($ln->gd('shortDescription'), $row['description_short'], $exp)}
                    </div>
                </div>
            </div>
            <div class='mt10'>
                <a class='add' href='#'>{$ln->gd('addToWishlist')}</a>
                <a class='add ml10' href='#'>{$ln->gd('addToCompare')}</a>
            </div>    
        </div>    
        <div class='productDetailBottom'>
            <h4 class='heading colr'>
                {$ln->gd('productDescription')}
            </h4>
            <div class='productDesc'>
                <div>
                    {$row['description']}
                </div>
            </div>
        </div>        
        <div class='relatedProductWrap'>
            {$related}
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
        $cpUrl = Zend_Registry::get('cpUrl');

        $hook = getCPModuleHook('gdj_jewellery', 'detail', $row, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $url = $cpUrl->getUrlByCatType('Enquiry Form');
        $url .= "?product_id={$row['product_id']}";

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');
        
        $price = $this->getPriceDisplay($row);

        $buyNow ='';
        if ($cpCfg['m.ecommerce.product.addToCart']== 1) {
            $wAddToCart = getCPWidgetObj('ecommerce_addToCart');
            $buyNow = $wAddToCart->getWidget(array('record' => $row));
        }

        $exp = array('style' => 'mb5');

        $wRelatedProd = getCPWidgetObj('ecommerce_productRecord');
        $title = $ln->gfv($row, 'title');
        if ($row['title'] != '' && $row['carat'] != ''){
            $title = $ln->gfv($row, 'title') . ' ' . $ln->gfv($row, 'carat') . ' ' . $ln->gd('cp.lbl.productSuffix');
        }

        $text = "
        <div class='floatbox'>
            <div class='float_right'>
                <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
            </div>
        </div>

        <div class='subcolumns productDetail'>
            <div class='c50l'>
                <div class='subcl'>
                    {$wImagesSlider->getWidget(array(
                         'module'    => 'gdj_jewellery'
                        ,'record_id' => $row['product_id']
                        ,'height'    => 400
                        ,'width'     => 350
                    ))}
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    <div>
                        <a class='button' href='{$url}'><span>Make offer</span></a>
                    </div>

                    <h1>{$title}</h1>
                    {$this->getPriceDisplay($row)}
                    {$buyNow}
                    <div class='desc'>
                        {$ln->gfv($row, 'description')}
                    </div>

                    <div class='mt10'><h2>{$ln->gd('relatedProducts')}</h2></div>
                    <div class='mt10'>
                    {$wRelatedProd->getWidget(array(
                         'specialFilter' => 'relatedProducts'
                        ,'product_id' => $row['product_id']
                    
                    ))}
                    </div>
                </div>
            </div>
        </div>

        <div class='mt20'>{$this->getProductDetails($row)}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPriceDisplay($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = '';

        if ($row['price'] > 0){
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
    function getProductDetails($row) {
        $ln = Zend_Registry::get('ln');
        
        $category = '';
        $subCategory = '';
        $description = '';
        $material = '';
        $metal = '';
        $color = '';
        $stones = '';

        if ($row['category_title'] != '') {
            $category = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.categoryLbl')}</td>
                <td>{$row['category_title']}</td>
            </tr>
            ";
        }

        if ($row['sub_category_title'] != '') {
            $subCategory = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.subCategoryLbl')}</td>
                <td>{$row['sub_category_title']}</td>
            </tr>
            ";
        }

        if ($row['description'] != '') {
            $description = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.descriptionLbl')}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
        }

        if ($row['material'] != '') {
            $material = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.materialLbl')}</td>
                <td>{$row['material']}</td>
            </tr>
            ";
        }

        if ($row['metal'] != '') {
            $metal = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.metalLbl')}</td>
                <td>{$row['metal']}</td>
            </tr>
            ";
        }

        if ($row['color'] != '') {
            $color = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.colorLbl')}</td>
                <td>{$row['color']}</td>
            </tr>
            ";
        }

        if ($row['stone'] != '') {
            $stones = "<tr>
                <td>{$ln->gd('m.gdj.jewellery.stoneLbl')}</td>
                <td>{$row['stone']}</td>
            </tr>
            ";
        }

        $text = "
        <table class='thinlist productDetails' style='width: 100%;'>
            <thead>
                <td colspan='2' class='txtCenter'><strong>{$ln->gd('m.gdj.diamond.productDescriptionLbl')}</strong></td>
            </thead>

            <tr>
                <td>{$ln->gd('m.gdj.jewellery.itemCodeLbl')}</td>
                <td>{$row['item_code']}</td>
            </tr>

            <tr>
                <td>{$ln->gd('m.gdj.jewellery.titleLbl')}</td>
                <td>{$row['title']}</td>
            </tr>

            {$category}
            {$subCategory}
            {$description}

            <thead>
                <td colspan='2' class='txtCenter'><strong>{$ln->gd('m.gdj.diamond.specificationLbl')}</strong></td>
            </thead>
            
            {$material}
            {$metal}
            {$color}
            {$stones}

        </table>
        ";

        return $text;
    }

}
