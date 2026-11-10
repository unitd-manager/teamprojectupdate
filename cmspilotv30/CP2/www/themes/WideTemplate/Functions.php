<?
class CP_Www_Themes_WideTemplate_Functions
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
        $wSlideshow = getCPWidgetObj('media_nivoSlider');
        $slideshow = $wSlideshow->getWidget(array(
        ));

        $wRecord = getCPWidgetObj('content_record');
        $welcome = $wRecord->getWidget(array(
             'sectionType'    => 'Home'
            ,'showPicInDesc'  => false
        ));   

        $wRecord = getCPWidgetObj('content_record');
        $features = $wRecord->getWidget(array(
             'sectionType'    => 'Home'
            ,'contentType'    => 'Features'
            ,'showPicInDesc'  => false
        ));   

        $text = "
        {$slideshow}
        <div class='subcolumns'>
            <div class='contentRecord1'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$this->getContentRecord('Our Company')}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr'>
                        {$this->getContentRecord('What We Do')}
                    </div>
                </div>    
            </div>
        </div>
        <div class='subcolumns'>
            <div class='contentRecord2'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$this->getContentRecord('Why Us')}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr'>
                        {$this->getContentRecord('Stellar Ocean Transport')}
                    </div>
                </div>
            </div>
        </div>
       <div class='subcolumns'>
            <div class='homeCallout'>
                 <div class='c33l'>
                     <div class='subcl'>
                         {$welcome}
                     </div>
                 </div>
                 <div class='c33l'>
                     <div class='subc'>
                         {$features}
                     </div>
                 </div>
                 <div class='c33r'>
                     <div class='subcr'>
                         {$this->getLatestNews()}
                     </div>
                 </div>
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
                <h1>{$ln->gfv($row, 'title')}</h1>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 100);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
                <div class='c33l'>
                    <div class='subcl'>
                        {$pic}
                    </div>
                </div>
                <div class='c66r'>
                    <div class='subcr'>
                        <div class='mb5'>{$title}</div>
                        <div>{$desc}</div>
                    </div>
                </div>
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