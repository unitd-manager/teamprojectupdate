<?
class CP_Www_Widgets_Social_AddThis_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        $text = "
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;

        $text = "
        <!-- AddThis Button BEGIN -->
        <div class='addthis_toolbox addthis_default_style addthis_32x32_style'>
        <a class='addthis_button_preferred_1'></a>
        <a class='addthis_button_preferred_2'></a>
        <a class='addthis_button_preferred_3'></a>
        <a class='addthis_button_preferred_4'></a>
        <a class='addthis_button_compact'></a>
        <a class='addthis_counter addthis_bubble_style'></a>
        </div>
        <script type='text/javascript' src='http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e7da2fb11a5f666'></script>
        <!-- AddThis Button END -->
        ";

        return $text;
    }
}