<?
class CP_Www_Themes_MegaNav_Functions
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

        $title = ($row['show_title'] == 1) ? "<h2>{$ln->gfv($row, 'title')}</h2>" : '';

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_simpleFadeSlideshow');
        $slideshow = $wSlideshow->getWidget(array(
             'width'  => 990
            ,'height' => 600
        ));

        $wNewsletterSignup = getCPWidgetObj('member_newsletterSignup');

        $text = "
        {$slideshow}
        <div class='megaNav'>
            <div class='about row'>
                <div class='wrap'>
                    <h2>ABOUT US</h2>
                    <div class='desc'>
                        <p>
                            Pollen is a new retail concept focused on bringing modern design to the contemporary Chinese home.
                            Pollen's carefully selected collection of high-quality international labels introduces the Chinese market
                            to well-designed products that are innovative, practical and pleasurable.
                        </p>
                        <p>
                            The first flagship store opened in Guangzhou in 2011. The 1500 m<sup>2</sup> space offers a lively, 
                            inviting and engaging experience. Detailed attention goes into creating the displays, 
                            providing a narrative for the product concepts and bringing them to life for customers.
                        </p>
                    </div>
                </div>
            </div>

            <div class='store row'>
                <div class='wrap'>
                    <h2>STORE</h2>
                    <div class='desc'>
                        <p>
                           The store is organized around seven product categories: Home D&eacute;cor, Trends & Leisure, 
                           Housewares, Boys & Girls, Home Fragrances, Stationery, Gifts. 
                           Established brands are at the core of each category, sharing rich history and expertise 
                           from their respective fields. Emerging labels add fresh personality to the character of each category.
                        </p>
                        <p>
                            Pollen's vision is to inspire enjoyment in personal living spaces and create appreciation 
                            of an enjoyable daily life. The large-scale space entices the Chinese market with a unique 
                            and distinctive product collection that makes Pollen a memorable shopping destination.
                        </p>
                    </div>
                </div>
            </div>

            <div class='products row'>
                <div class='wrap'>
                    <h2>PRODUCTS</h2>
                    <div class='desc'>
                        <img src='/www/images/products.jpg' />
                    </div>
                </div>
            </div>

            <div class='contact row'>
                <div class='wrap'>
                    <h2>CONTACT US</h2>
                    <div class='desc'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                MF 01-10, One Link Mail,<br>
                                No.230-232 Tianhe Road,<br>
                                Guangzhou, China<br><br>
                                Tel: +86 (0)20 38992413<br>
                                Fax: +86 (0)20 38992420<br><br>
                                <a href='mailto:info@pollen-shop.com'>info@pollen-shop.com</a>
                            </div>
                            <div class='float_right'>
                                <a class='cpZoom' href='/www/images/map-large.jpg'>
                                    <img src='/www/images/map.jpg' />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='signup row'>
                <div class='wrap'>
                    <h2>SIGN UP FOR OUR NEWSLETTER</h2>
                    <div class='desc'>
                        {$wNewsletterSignup->getWidget(array(
                            'showCaptcha' => false
                        ))}
                    </div>
                </div>
            </div>

            <div class='footer'>
                <div class='floatbox tagLine'>
                    <div class='float_right'>
                        <h2>DESIGN FOR HOME LIFE.</h2>
                    </div>
                </div>

                <div class='floatbox'>
                    <div class='float_left'>
                        <a id='toggleMenu' href='#' text1='- GALLERY' text2='+ MENU'>- GALLERY</a>
                    </div>
                    <div class='float_right'>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}