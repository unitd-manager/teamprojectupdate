<?
class CP_Www_Themes_Herbal2_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        foreach ($dataArray as $row){
        }

        $wRecord = getCPWidgetObj('content_record');
        $url = $cpUrl->getUrlBySecType('Product', '');

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
            ,'showGroupReadMore' => true
            ,'groupReadMoreUrl' => $url
            ,'groupReadMoreLbl' => 'more products...'
        ));

        $wRecord = getCPWidgetObj('content_record');

        $text = "
        {$slideshow}
        <div class='homeBottom'>
        <div class='subcolumns'>
            <div class='c40l col1'>
                <div class='subcl'>
                    {$title}
                    <div class='desc'>
                        {$ln->gfv($row, 'description')}
                    </div>
                </div>
            </div>
            <div class='c40l col2'>
                <div class='subcl'>
                    <h1>{$ln->gd('w.ecommerce.productRecord.latest.heading')}</h1>
                    {$latestProducts}
                </div>
            </div>
            <div class='c20r col3'>
                <div class='subcr'>
                    <h1>{$ln->gd('cp.lbl.testimonials')}</h1>
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