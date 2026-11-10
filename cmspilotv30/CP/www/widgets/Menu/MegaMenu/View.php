<?
class CP_Www_Widgets_Menu_MegaMenu_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqMegaDropDownMenu-1.3.3');
    /**
     *
     */
    function getWidget() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        $text = '';
        if ($this->getRowsHTML() != ''){

            $text = "
            ";

            $text = "
            <ul class='mega-menu clearfix'>
                {$this->getRowsHTML()}
            </ul>
            ";

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {
                     classParent: '{$c->classParent}'
                    ,classContainer: '{$c->classContainer}'
                    ,classSubLink: '{$c->classSubLink}'
                    ,rowItems: '{$c->rowItems}'
                    ,speed: '{$c->speed}'
                    ,effect: '{$c->effect}'
                    ,event: '{$c->event}'
                    ,fullWidth: {$cpUtil->getBoolStr($c->fullWidth)}
                }
                cpw.menu.megaMenu.run(exp);
            "));
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $mainNav = Zend_Registry::get('mainNav');
        $c = &$this->controller;

        $exp = array(
             'noOfSecToDisplay' => $c->noOfSecToDisplay
            ,'noOfCatToDisplay' => $c->noOfCatToDisplay
            ,'noOfSubCatToDisplay' => $c->noOfSubCatToDisplay
        );

        $rows = $mainNav->view->getMenuDataRowsHTML($this->controller->btnPos, $exp);

        return $rows;
    }
}