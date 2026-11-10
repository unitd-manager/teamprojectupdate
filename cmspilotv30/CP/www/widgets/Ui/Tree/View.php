<?
class CP_Www_Widgets_Ui_Tree_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('dyntree-1.2.0');
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;

        $text = '';
        if ($this->getRowsHTML() != ''){
            $text = "
            <div id='{$c->handle}'>
            <ul>
                {$this->getRowsHTML()}
            </ul>
            </div>
            ";

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {
                    handle: '{$c->handle}'
                }
                cpw.ui.tree.run(exp);
            "));
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $c = &$this->controller;

        $rows = '';
        if ($c->treeType == 'navigation'){
            $mainNav = Zend_Registry::get('mainNav');
            $rows = $mainNav->view->getMenuDataRowsHTML($this->controller->btnPos);
        } else {
            // add more tree type here //
        }
        
        return $rows;
    }
}