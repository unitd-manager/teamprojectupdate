<?
class CP_Www_Themes_Organization_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $wSlideshow = getCPWidgetObj('media_simpleFadeSlideshow');
        $slideshow = $wSlideshow->getWidget(array(
             'width' => '990'
            ,'height' => '300'
        ));
       
        $text = "
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='c66topCurvePanel'></div>
                <div class='c66middleCurvePanel'>
	               	<div class='inner'>
		                {$this->getFavouriteContent()}
                   	</div>
                </div>
                <div class='c66bottomCurvePanel'></div>
            </div>

            <div class='c33r'>
                <div class='c33topCurvePanel'></div>
                <div class='c33middleCurvePanel'>
                	<div class='subcr'>
               			{$this->getLatestNews()}
                	</div>
                </div>
                <div class='c33bottomCurvePanel'></div>
            </div>
        </div>
        ";

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
        ORDER BY c.content_date DESC
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

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 170);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );
            $content_date = $fn->getCPDate($row['content_date']);

            $rows .= "
            <div class='subcolumns'>
				<div class='latestNews'>
                	<div class='mb5'>{$title}</div>
                    <div>{$desc}</div>
		            <div class='mb5'>{$content_date}</div>
   		        </div>
            </div>
            ";
        }

        $text = "
        <div class='news'>
        <h1 class=ml10>{$ln->gd('latestNews')}</h1>
        {$rows}
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getFavouriteContent() {
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
        WHERE c.favourite = 1 
        AND section_type = 'News'
        AND c.published = 1
        ORDER BY c.sort_order ASC
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id', array('secType'=>'News'));
            $title ="
            <div class='title'>
                <h4>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 570);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );
            $content_date = $fn->getCPDate($row['content_date']);

            $rows .= "
            <div class='homeContent'>
            	<div class='mb5'>{$title}</div>
            	<div>{$desc}</div>
            	<div class='mt10'><a class='readMore' href='{$url}'>{$ln->gd('readMore')}</a></div>
		    </div>
            ";
        }

        $text = "
        <div class='floatbox'>
        {$rows}
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getContentRecord($content_type) {
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
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $title ="
            <div class='title'>
                <h4>{$ln->gfv($row, 'title')}</h4>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 400);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
            <div class='mb5'>{$title}</div>
            <div>{$desc}</div>
            <div class='mt10'><a class='readMore' href='{$url}'>{$ln->gd('readMore')}</a></div>
            ";
        }

        $text = "
        <div class='homeContent'>
        {$rows}
        </div>
        ";

        return $text;
    }

}