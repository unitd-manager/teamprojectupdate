<?
class CP_Www_Themes_Rugby_Functions
{

    function getModuleWebBasicHomeContentHook() {
    }
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        foreach ($dataArray as $row){
            $exp = array('folder' => 'large');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        }

        $wCarousel = getCPWidgetObj('media_carousel');

        $wRecord1 = getCPWidgetObj('content_record');
        $wRecord2 = getCPWidgetObj('content_record');
        $wRecord3 = getCPWidgetObj('content_record');
        $wRecord4 = getCPWidgetObj('content_record');
        $wRecord5 = getCPWidgetObj('content_record');
       
        $text = "
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='subcl'>
                    <div class='inner'>
                        {$title}
                        {$pic}
                        {$ln->gfv($row, 'description')}
                    </div>
                	<div class='events'>
                	    <h4>{$ln->gd('cp.home.heading.upcomingEvents')}</h4>
            			{$wRecord1->getWidget(array(
                             'contentType'  => 'Events'
                            ,'showReadMore' => 'true'
            			))}
                	</div>
                	<div class='announcements'>
                	    <h4>{$ln->gd('cp.home.heading.announcements')}</h4>
                        {$wRecord2->getWidget(array(
                             'contentType' => 'Announcements'
                            ,'showReadMore' => 'true'
                        ))}
                	</div>
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr rightPanel'>
                    {$wRecord3->getWidget(array(
                         'sectionType'    => 'Home'
                        ,'contentType'    => 'Callout Right'
                        ,'showDesc'       => TRUE
                        ,'showPicInDesc'  => FALSE
                        ,'showShortDesc'  => FALSE
                        ,'showPic'        => TRUE
                        ,'displayLimit'   => 3

                    ))}
                </div>
            </div>
        </div>
        <div class='calloutBottom floatbox'>
            {$wRecord4->getWidget(array(
                'contentType' => 'Callout Bottom'
                ,'showShortDesc'  => FALSE
            ))}
        </div>
        <div class='ourSponsors floatbox'>
            {$wRecord5->getWidget(array(
                'contentType' => 'Sponsors'
                ,'showDesc'  => FALSE

            ))}
        </div>
        ";

        return $text;
    }

}