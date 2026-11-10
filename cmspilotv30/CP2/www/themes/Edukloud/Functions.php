<?
class CP_Www_Themes_Edukloud_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        //$url = $cpUrl->getUrlByRecord($row, 'content_id', array('secType'=>'News'));

		$wSlideshow= getCPWidgetObj('media_s3Slider');
        $slideshow = $wSlideshow->getWidget(array(
        ));

        $text = "
        {$slideshow}
        <div class='subcolumns homeContent'>
            <div class='c25l '>
                <div class='subcl'>
                    <div class='content1'>
                        {$this->getContentRecord('Callout 1', 'About Eduk')}
                    </div>
                </div>
            </div>
            <div class='c25l '>
                <div class='subcl'>
                    <div class='content2'>
                        {$this->getContentRecord('Callout 2', 'Eduk for Students')}
                    </div>
                </div>
            </div>
            <div class='c25l '>
                <div class='subcl'>
                    <div class='content3'>
                        {$this->getContentRecord('Callout 3', 'Eduk for Teachers')}
                    </div>
                </div>
            </div>
            <div class='c25l'>
                <div class='subcl'>
                    <div class='content4'>
                        {$this->getContentRecord('Callout 4', 'Eduk for Parents')}
                    </div>
                </div>
            </div>
        </div>        
        ";

        return $text;
    }
    
    /*
     *
     */
    function getModuleWebBasicHomeExtendedPanelHook($dataArray) {
    }
    
    /**
     *
     */
    function getModuleWebBasicContentControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($tv['secType'] == 'Site Search') {
            $pSiteSearch = getCPPluginObj('common_siteSearch');
            $text = $pSiteSearch->getView();

        } else if ($tv['secType'] == 'Quick Tour') {
            $text = $contObj->getQuickTour();

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $contObj->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getLatestNews() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,ca.title AS category_title
        FROM content c
        LEFT JOIN category ca     ON (c.category_id     = ca.category_id)
        LEFT JOIN section s     ON (c.section_id     = s.section_id)
        WHERE c.latest = 1 
        AND section_type = 'News'
        AND c.published = 1
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id', array('secType'=>'News'));
            $title ="
            <div class='title'>
                <a href='{$url}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 70);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
            <div class='subcolumns'>
            <div class='latestNews'>
                <div class='c25l'>
                    <div class='subcl'>
                        <img src='/www/images/news.png'/>
                    </div>
                </div>
                <div class='c75r'>
                    <div class='subcr'>
                        <div class='mb5'>{$title}</div>
                        <div>{$desc}</div>
                    </div>
                </div>
            </div>
            </div>
            ";
        }

        $text = "
        <div class='news'>
        <h1>{$ln->gd('latestNews')}</h1>
        <marquee behavior='scroll' direction='up' scrollamount='1' height='170'>
        {$rows}
        </marquee>
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getContentRecord($content_type, $category_type) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil'); 

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,ca.title AS category_title
        FROM content c
        LEFT JOIN category ca     ON (c.category_id     = ca.category_id)
        LEFT JOIN section s     ON (c.section_id     = s.section_id)
        WHERE c.published = 1 
        AND c.content_type = '{$content_type}'
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url    = $cpUrl->getUrlByRecord($row, 'content_id');
            $catUrl = $cpUrl->getUrlByCatType($category_type);
            $title ="
            <div class='title'>
                <h2>{$ln->gfv($row, 'title')}</h2>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 150);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
            <div class='mb5'>{$title}</div>
            <div class='desc'>{$desc}</div>
            <div class='readMore'>
            	<a href='{$catUrl}'>{$ln->gd('cp.lbl.readMore')}</a>
            </div>
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}