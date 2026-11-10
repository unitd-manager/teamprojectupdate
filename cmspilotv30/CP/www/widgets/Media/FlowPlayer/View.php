<?
class CP_Www_Widgets_Media_FlowPlayer_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('flowPlayer-3.2.7');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rows = $this->getRowsHTML();

        $text = "
        {$rows}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;

        $rows = "
        <div class='jqToolsOverlay' id='{$c->handle}'> 
        	<a class='cpFlowPlayer' href='{$c->videoFile}'> 
        		&nbsp;
        	</a> 
        </div> 
        ";

        return $rows;
    }
}
