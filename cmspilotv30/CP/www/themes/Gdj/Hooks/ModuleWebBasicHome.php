<?
class CP_Www_Themes_Gdj_Hooks_ModuleWebBasicHome
{
    /*
     *
     */
    function getList($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $homeText = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
            $homeText = "
            <div class='homeContent'>
                {$title}
                {$ln->gfv($row, 'description')}
            </div>
            ";
        }
        $wRecord = getCPWidgetObj('content_record');

        $text = "
        {$homeText}
        <div>
            <h1>{$ln->gd('w.ecommerce.productRecord.latest.heading')}</h1>
            {$this->getLatestProduct()}
        </div>
        ";

        return $text;

    }

    /*
     *
     */
    function getExtendedPanel($dataArray) {
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $text = '';
        return $text;
    }

    /**
     *
     */
    function getLatestProduct() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQL = "
        SELECT p.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
        FROM product p
        LEFT JOIN category ca ON (p.category_id = ca.category_id)
        LEFT JOIN sub_category sc ON (p.sub_category_id = sc.sub_category_id)
        WHERE p.latest = 1
        AND p.published = 1
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        $cart = '';
        foreach ($dataArray as $row){

            $exp = array('style' => '', 'folder' => 'thumb');
            if ($row['record_type'] == 'Gemstones'){
                $secType = 'Gemstone';
                $module = 'gdj_gemstone';
            } else {
                $secType = 'Jewellery';
                $module = 'gdj_jewellery';
            }

            $secRec = getCPModelObj('webBasic_section')->getRecordByType($secType);
            $pic = $media->getMediaPicture($module, 'picture', $row['product_id'], $exp );

            $urlArray = array();
            $urlArray['lang'] = $tv['lang'];
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['category_id']    = $row['category_id'];
            $urlArray['category_title'] = $row['category_title'];
            $urlArray['sub_category_id']    = $row['sub_category_id'] ;
            $urlArray['sub_category_title'] = $row['sub_category_title'];
            $urlArray['record_id']          = $row['product_id'] ;
            $urlArray['record_title']       = $row['title'];
            $url = $cpUrl->make_seo_url($urlArray);

            if ($cpCfg['m.ecommerce.product.addToCart']== 1) {
                $cart = "<div class='cart'><a href='{$url}'>{$ln->gd('cp.lbl.addToCart')}</a></div>";
            }

            $price = ($row['price'] != '') ? "<div class='title'>{$row['price']}</div>" : '';
            $carat = ($row['carat'] != '') ? "<div class='title'>{$row['carat']} {$ln->gd('cp.lbl.productSuffix')}</div>" : '';

            $rows .= "
            <li>
                <div class='innerBorder'>
                    <div class='inner'>
                        <div class='pic'><a href='{$url}'>{$pic}</a>&nbsp;</div>
                        <div class='title'><a href='{$url}'>{$ln->gfv($row, 'title')}</a></div>
                        {$price}
                        {$carat}
                    </div>
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
}