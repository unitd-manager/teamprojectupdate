<?
class CP_Www_Widgets_Menu_SuperFish_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $text = '';
        $rowsHtml = $this->getRowsHTML();
        if ($rowsHtml != ''){
            $appendCls = '';
            if ($c->orientation == 'vertical'){
                CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqSuperfish-1.4.8-vertical'));
                $appendCls = ' sf-vertical';
            } else {
                $plugin = 'jqSuperfish-' . $cpCfg['cp.superFishMenuVersion'];
                CP_Common_Lib_Registry::arrayMerge('jssKeys', array($plugin));
            }

            $text = "
            <ul class='sf-menu{$appendCls} clearfix'>
                {$rowsHtml}
            </ul>
            ";

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {}
                cpw.menu.superFish.run(exp);
            "));
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