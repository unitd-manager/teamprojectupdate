<?
class CP_Www_Themes_Herbal5_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        foreach ($dataArray as $row){
        }

        $wRecord = getCPWidgetObj('content_record');

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_s3Slider');
        $slideshow = $wSlideshow->getWidget(array(
        ));

        /** create an instance of the widget **/
        $wLatestProd = getCPWidgetObj('ecommerce_productRecord');
        $latestProducts = $wLatestProd->getWidget(array(
             'displayLimit' => 4
            ,'showTitle' => false
        ));

        $wRecord = getCPWidgetObj('content_record');

        $text = "
        <div class='bannerWrapper'>
            <div class='subcolumns'>
                <div class='c40l'>
                    <div class='subcl'>
                        <h1>{$ln->gd('w.ecommerce.productRecord.latest.heading')}</h1>
                        {$latestProducts}
                    </div>
                </div>
                <div class='c60r'>
                    <div class='subcr'>
                        {$slideshow}
                    </div>
                </div>
            </div>
        </div>

        <div class='homeBottom'>
        <div class='subcolumns'>
            <div class='c80l col1'>
                <div class='subcl'>
                    {$title}
                    {$ln->gfv($row, 'description')}
                </div>
            </div>
            <div class='c20r col3'>
                <div class='subcr'>
                    <h1>{$ln->gd('testimonials')}</h1>
                    {$wRecord->getWidget(array(
                         'contentType' => 'Testimonial'
                    ))}
                </div>
            </div>
        </div>
        </div>
        ";

        return $text;
    }
}