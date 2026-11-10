<?
class CP_Www_Modules_Gdj_Diamond_View extends CP_Common_Modules_Gdj_Diamond_View
{
    var $jssKeys = array('jqColorbox-1.3.15');
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

        $rows = '';

        foreach ($dataArray as $row){
            $shape = strtolower($row['shape']);
            $price = '';

            if ($row['price'] > 0){
                $price = number_format($row['price'], 0);
            }

            $certArr = $media->getMediaFilesArray('gdj_diamond', 'certificate', $row['product_id']);
            $certText = '';

            if (count($certArr) > 0){
                $caption = $certArr[0]['caption'] != '' ? $certArr[0]['caption'] : 'certificate';
                $certText = "
                <a href='{$certArr[0]['file_large']}' class='cpZoom'>
                    {$caption}
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td><span class='{$shape}'></span></td>
                <td>{$row['carat']}</td>
                <td>{$row['color']}</td>
                <td>{$row['clarity']}</td>
                <td>{$row['cut']}</td>
                <td>{$row['polish']}</td>
                <td>{$row['symmetry']}</td>
                <td>{$row['fluorescence']}</td>
                <td class='txtRight'>{$price}</td>
                <td>{$row['lab']}</td>
                <td>{$certText}</td>
            </tr>
            ";
        }

        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $text = "
        <div class='diamondList'>
            {$this->getSearch()}
            {$theme->view->getPagerPanel()}
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th class='shape'>Shape</th>
                        <th class='carat'>Carat</th>
                        <th class='color'>Color</th>
                        <th class='clarity'>Clarity</th>
                        <th class='cutGrade'>Cut Grade</th>
                        <th class='polish'>Polish</th>
                        <th class='symmetry'>Symmetry</th>
                        <th class='fluoresence'>Fluoresence</th>
                        <th class='price'>Price ({$cpCfg['cp.basketArray']['ecommerce_product']['currencyDisplay']})</th>
                        <th class='lab'>Lab</th>
                        <th class='certificate'>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
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

        $hook = getCPModuleHook('gdj_diamond', 'detail', $row, $this);
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
                         'module'    => 'gdj_diamond'
                        ,'record_id' => $row['category_id']
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
    function getSearch() {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl  = Zend_Registry::get('cpUrl');

        $color           = $fn->getReqParam('color');
        $clarity         = $fn->getReqParam('clarity');
        $lab             = $fn->getReqParam('lab');
        $fluorescence    = $fn->getReqParam('fluorescence');
        $shape           = $fn->getReqParam('shape');
        $polish          = $fn->getReqParam('polish');
        $symmetry        = $fn->getReqParam('symmetry');
        $carat           = $fn->getReqParam('carat');
        $price           = $fn->getReqParam('price');
        $cut             = $fn->getReqParam('cut');

        $sqlShape        = $fn->getValueListSQL('diamondShape');
        $sqlColor        = $fn->getValueListSQL('diamondColor');
        $sqlClarity      = $fn->getValueListSQL('diamondClarity');
        $sqlPolish       = $fn->getValueListSQL('diamondPolish');
        $sqlSymmetry     = $fn->getValueListSQL('diamondSymmetry');
        $sqlLab          = $fn->getValueListSQL('diamondLab');
        $sqlFluorescence = $fn->getValueListSQL('diamondFluorescence');
        $sqlCut          = $fn->getValueListSQL('diamondCut');
        $sqlCarat        = $fn->getValueListSQL('diamondCarat');
        $sqlPrice        = $fn->getValueListSQL('stonePrice');

        $actionUrl = $cpUrl->getUrlBySecType('Diamond');

        $text = "
        <form name='search' action='{$actionUrl}' method='get' id='topSearch' autoSubmitOnChange='1'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th class='shape'>
                            <select name='shape'>
                                <option value=''>Shape</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlShape, $shape)}
                            </select>
                        </th>
                        <th class='carat'>
                            <select name='carat'>
                                <option value=''>Carat</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCarat, $carat)}
                            </select>
                        </th>
                        <th class='color'>
                            <select name='color'>
                                <option value=''>Color</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
                            </select>
                        </th>
                        <th class='clarity'>
                            <select name='clarity'>
                                <option value=''>Clarity</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlClarity, $clarity)}
                            </select>
                        </th>
                        <th class='cutGrade'>
                            <select name='cut'>
                                <option value=''>Cut Grade</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCut, $cut)}
                            </select>
                        </th>
                        <th class='polish'>
                            <select name='polish'>
                                <option value=''>Polish</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPolish, $polish)}
                            </select>
                        </th>
                        <th class='symmetry'>
                            <select name='symmetry' class='symmetry'>
                                <option value=''>Symmetry</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlSymmetry, $symmetry)}
                            </select>
                        </th>
                        <th class='fluoresence'>
                            <select name='fluorescence'>
                                <option value=''>Fluorescence</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlFluorescence, $fluorescence)}
                            </select>
                        </th>
                        <th class='price'>
                            <select name='price'>
                                <option value=''>Price</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPrice, $price)}
                            </select>
                        </th>
                        <th class='lab'>
                            <select name='lab'>
                                <option value=''>Lab</option>
                                {$dbUtil->getDropDownFromSQLCols1($db, $sqlLab, $lab)}
                            </select>
                        </th>
                        <th class='certificate'>
                            <!-- <input type='submit' class='button btnSearch' value='{$ln->gd('p.common.siteSearch.btn.search')}'/> -->
                        </th>
                    </tr>
                </thead>
            </table>
            <input type='submit' name='x_submit' class='submithidden' />
            <input type='hidden' name='search_done' value='1' />
        </form>
        ";

        return $text;
    }

}
