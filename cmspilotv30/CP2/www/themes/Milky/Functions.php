<?
class CP_Www_Themes_Milky_Functions
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
        $wSlideshow = getCPWidgetObj('media_s3Slider');
        $slideshow = $wSlideshow->getWidget(array(
            'speed' => $cpCfg['cp.homeSlideshowSpeed']
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
        <div class='subcolumns'>
            <div class='c75l'>
                <div class='subcl'>
                    {$slideshow}
                    {$title}
                    {$ln->gfv($row, 'description')}
                </div>
            </div>
            <div class='c25r'>
                <div class='subcr rightPanel'>
                    {$calloutRight}
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}