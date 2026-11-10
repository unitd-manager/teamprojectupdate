<?
class CP_Www_Widgets_Common_Sitemap_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;

        $text = '';
        if ($this->getRowsHTML() != ''){
            $appendCls = '';

            $text = "
            <ul class='{$appendCls}'>
                {$this->getRowsHTML()}
            </ul>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $mainNav = Zend_Registry::get('mainNav');
        $rows = $mainNav->view->getMenuDataRowsHTML($this->controller->btnPos);
    
        return $rows;
    }
}