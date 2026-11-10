<?
class CP_Www_Modules_Gdj_Gemstone_View extends CP_Common_Modules_Gdj_Gemstone_View
{

    /**
     *
     */
    function getList($dataArray) {
        $hook = getCPModuleHook('gdj_gemstone', 'list', $dataArray);
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
            $pic = $media->getMediaPicture('gdj_gemstone', 'picture', $row['product_id'], $exp);
            $url = $cpUrl->getUrlByRecord($row, 'product_id');

            $title = $ln->gfv($row, 'title');

            if ($pic == ''){
                $pic = "<img src='/www/images/no-image.gif'>";
            }

            $carat = ($row['carat'] != '') ? "<div class='carat'>{$row['carat']} {$ln->gd('cp.lbl.productSuffix')}</div>" : '';

            $rows .= "
            <li>
                <div class='innerBorder'>
                    <div class='inner'>
                        <div class='pic'><a href='{$url}'>{$pic}</a>&nbsp;</div>
                        <div class='title'><a href='{$url}'>{$title}</a></div>
                        {$carat}
                        {$this->getPriceDisplay($row)}
                    </div>
                </div>
            </li>
            ";
        }

        $theme = getCPThemeObj($cpCfg['cp.theme']);


        if (count($dataArray) > 0){
            $text = "
            {$theme->view->getPagerPanel()}
            <div class='productList'>
                <ul class='noDefault'>
                    {$rows}
                </ul>
            </div>
            ";
        } else {
            $text = "
            <div class='productList'>
                Sorry! No items found for your search criteria! Please contact us for your requirements..
            </div>
            ";
        }

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

        $hook = getCPModuleHook('gdj_gemstone', 'detail', $row, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $url = $cpUrl->getUrlByCatType('Enquiry Form');
        $url .= "?product_id={$row['product_id']}";

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');

        $buyNow ='';
        if ($cpCfg['m.ecommerce.product.addToCart']== 1) {
            $wAddToCart = getCPWidgetObj('ecommerce_addToCart');
            $buyNow = $wAddToCart->getWidget(array('record' => $row));
        }

        $exp = array('style' => 'mb5');

        $wRelatedProd = getCPWidgetObj('ecommerce_productRecord');
        $carat = $ln->gfv($row, 'title');
        if ($row['carat'] != ''){
            $carat = $ln->gfv($row, 'carat') . ' ' . $ln->gd('cp.lbl.productSuffix');
        }

        $relProducts = "
        {$wRelatedProd->getWidget(array(
             'specialFilter' => 'relatedProducts'
            ,'product_id'    => $row['product_id']
            ,'prodSecType'   => 'Gemstone'
            ,'heading'       => $ln->gd('relatedProducts')
        ))}
        ";

        if (trim($relProducts) != ''){
            $relProducts = "
            <div class='relatedProducts'>
                {$relProducts}
            </div>
            ";
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
                         'module'    => 'gdj_gemstone'
                        ,'record_id' => $row['product_id']
                        ,'height'    => 400
                        ,'width'     => 358
                    ))}
                    {$this->getProductDetails($row)}
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    <div>
                        <a class='button makeOffer' href='{$url}'><span>Make offer</span></a>
                    </div>

                    <h1>{$ln->gfv($row, 'title')}</h1>
                    <h2>{$row['pieces_qty']} / {$carat}</h2>
                    {$this->getPriceDisplay($row)}
                    {$buyNow}
                    <div class='desc'>
                        {$ln->gfv($row, 'description')}
                    </div>
                    {$relProducts}
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
        $shape = '';
        $color = '';
        $carat = '';
        $measurement = '';
        $cut = '';
        $origin = '';
        $type = '';
        $hardness = '';
        $luster = '';
        $treatment = '';
        $lab = '';

        if ($row['category_title'] != '') {
            $category = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.categoryLbl')}</td>
                <td>{$row['category_title']}</td>
            </tr>
            ";
        }

        if ($row['sub_category_title'] != '') {
            $subCategory = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.subCategoryLbl')}</td>
                <td>{$row['sub_category_title']}</td>
            </tr>
            ";
        }

        if ($row['description'] != '') {
            $description = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.descriptionLbl')}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
        }

        if ($row['shape'] != '') {
            $shape = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.shapeLbl')}</td>
                <td>{$row['shape']}</td>
            </tr>
            ";
        }

        $qty = "
        <tr>
            <td>{$ln->gd('m.gdj.gemstone.qtyLbl')}</td>
            <td>{$row['pieces_qty']}</td>
        </tr>
        ";

        if ($row['color'] != '') {
            $color = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.colorLbl')}</td>
                <td>{$row['color']}</td>
            </tr>
            ";
        }

        if ($row['carat'] != '') {
            $carat = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.caratLbl')}</td>
                <td>{$row['carat']}</td>
            </tr>
            ";
        }

        if ($row['measurement'] != '') {
            $measurement = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.measurementLbl')}</td>
                <td>{$row['measurement']}</td>
            </tr>
            ";
        }

        if ($row['cut'] != '') {
            $cut = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.cutLbl')}</td>
                <td>{$row['cut']}</td>
            </tr>
            ";
        }

        if ($row['origin'] != '') {
            $origin = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.originLbl')}</td>
                <td>{$row['origin']}</td>
            </tr>
            ";
        }

        if ($row['type'] != '') {
            $type = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.typeLbl')}</td>
                <td>{$row['type']}</td>
            </tr>
            ";
        }

        if ($row['hardness'] != '' && $row['hardness'] != 0) {
            $hardness = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.hardnessLbl')}</td>
                <td>{$row['hardness']}</td>
            </tr>
            ";
        }

        if ($row['luster'] != '') {
            $luster = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.lusterLbl')}</td>
                <td>{$row['luster']}</td>
            </tr>
            ";
        }

        if ($row['treatment'] != '') {
            $treatment = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.treatmentLbl')}</td>
                <td>{$row['treatment']}</td>
            </tr>
            ";
        }

        if ($row['lab'] != '') {
            $lab = "<tr>
                <td>{$ln->gd('m.gdj.gemstone.labLbl')}</td>
                <td>{$row['lab']}</td>
            </tr>
            ";
        }

        $text = "
        <table class='thinlist productDetails'>
            <!--
            <thead>
                <td colspan='2' class='txtCenter'><strong>{$ln->gd('m.gdj.diamond.productDescriptionLbl')}</strong></td>
            </thead>

            <tr>
                <td>{$ln->gd('m.gdj.gemstone.itemCodeLbl')}</td>
                <td>{$row['item_code']}</td>
            </tr>

            <tr>
                <td>{$ln->gd('m.gdj.gemstone.titleLbl')}</td>
                <td>{$row['title']}</td>
            </tr>

            {$category}
            {$subCategory}
            {$description}
            -->

            <thead>
                <td colspan='2' class='txtCenter'><strong>Specifications</strong></td>
            </thead>

            {$shape}
            {$qty}
            {$color}
            {$carat}
            {$measurement}
            {$cut}
            {$origin}
            {$type}
            {$hardness}
            {$luster}
            {$treatment}
            {$lab}

        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $subNav = Zend_Registry::get('subNav');

        $lab            = $fn->getReqParam('lab');
        $shape          = $fn->getReqParam('shape');
        $carat          = $fn->getReqParam('carat');
        $price          = $fn->getReqParam('price');
        $cut            = $fn->getReqParam('cut');
        $title          = $fn->getReqParam('title');

        $sqlShape       = $fn->getValueListSQL('gemstoneShape');
        $sqlLab         = $fn->getValueListSQL('gemstoneLab');
        $sqlCut         = $fn->getValueListSQL('gemstoneCut');

        $sqlTitle = "
        SELECT DISTINCT title
        FROM product
        WHERE record_type = 'Gemstone'
        ORDER BY title
        ";

        $sqlCarat = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'gemstoneCarat'
        ORDER BY valuelist_id
        ";

        $sqlPrice = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'stonePrice'
        ORDER BY valuelist_id
        ";

        $actionUrl = $cpUrl->getUrlBySecType('Gemstone');
        $actionUrlKeyword = $cpUrl->getUrlBySecType('Site Search');
        $catSQL = getCPModuleObj('webBasic_category')->model->getCategorySQLBySection($tv['room']);
        $subCatSQL = '';

        if ($tv['subRoom'] != ''){
            $subCatSQL = "
            SELECT DISTINCT sc.sub_category_id
                  ,sc.title AS title
            FROM sub_category sc
            JOIN category c ON (sc.category_id = c.category_id)
             AND c.category_id = '{$tv['subRoom']}'
             AND c.published = 1
             AND sc.published = 1
            ORDER BY sc.title
            ";
        }

        $text = "
        <h6 class='vlist'>Gemstone Search</h6>
        <form name='search' action='{$actionUrl}' method='get' id='srchLeftPanel'>
            <div>
                {$formObj->getDropDownBySQL('Category', '_subRoom', $catSQL, $tv['subRoom'])}
            </div>
            <div>
                {$formObj->getDropDownBySQL('Sub Category', '_subCat', $subCatSQL, $tv['subCat'])}
            </div>

            <div>
                <select name='shape'>
                    <option value=''>Shape</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlShape, $shape)}
                </select>
            </div>

            <div>
                <select name='carat'>
                    <option value=''>Carat</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCarat, $carat)}
                </select>
            </div>

            <div>
                <select name='cut'>
                    <option value=''>Cut Grade</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCut, $cut)}
                </select>
            </div>

            <div>
                <select name='lab'>
                    <option value=''>Lab</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlLab, $lab)}
                </select>
            </div>

            <div>
                <select name='price'>
                    <option value=''>Price</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlPrice, $price)}
                </select>
            </div>
            <div class='keyword'>
                <input type='text' class='keyword gemstone' name='keyword' id='keyword' rel='' value=''>
            </div>
            <div>
                <input type='submit' class='button btnSearch' value='{$ln->gd('p.common.siteSearch.btn.search')}'/>
                <input type='submit' name='x_submit' class='submithidden' />
            </div>
        </form>
        ";

        return $text;
    }
}
