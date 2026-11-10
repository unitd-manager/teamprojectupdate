<?
class CP_Www_Themes_Medical_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $wBanner = getCPWidgetObj('media_banner');

        $topMostRooms = '';

        if ($cpCfg['cp.showTopMostSections']){
            $mainNav = getCPWidgetObj('core_mainNav');
            $topMostRooms = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top Most'
                ,'class'  => 'topMenu'
            ))}
            ";
        }

        $text = "
        {$wBanner->getWidget()}
        <a href='/eng/buy-now/' id='buyNow'><span class='hideme'>Buy Now</span></a>
        <a href='/eng/contact-us/enquiry-form/3/' id='contactUs'><span class='hideme'>contact us</span></a>
        ";

        return $text;
    }

    /*
     *
     */
    function getModuleWebBasicHomeExtendedPanelHook($dataArray) {
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        foreach ($dataArray as $row){
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id']);
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        }

        $wRecordTestimonials1 = getCPWidgetObj('content_record');
        $wRecordTestimonials2 = getCPWidgetObj('content_record');
        $wRecordAboutUs = getCPWidgetObj('content_record');

        $wRecord = getCPWidgetObj('content_record');
        $wRecordSyrub = $wRecord->getWidget(array(
                'contentType' => 'Ivy Syrub'
               ,'showPicInDesc' => false 
        ));
        
        //$wRecordSyrub = getCPWidgetObj('content_record');

        $text = "
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='subcl'>
        	        <div class='content'>
                        <div class='innerDesc'>
                            {$ln->gfv($row, 'description')}
                        </div>
         	        </div>
                </div>
            </div>
            <div class='c33r '>
                <div class='subcr'>               
                	<div class='video'>
            			{$wRecordTestimonials1->getWidget(array(
                             'contentType' => 'Home Video'
            			))}
                	</div>
                	<div class='testimonials'>
                        {$wRecordTestimonials2->getWidget(array(
                             'contentType' => 'Home Testimonials'
            				,'blockQuote'  => true
                        ))}      
                	</div>
                </div>
            </div>
        </div>
        <div class='subcolumns'>
            <div class='coughSyrub'>
                {$wRecordSyrub}      
            </div>
       </div>
        ";
        
        return $text;
    }
}