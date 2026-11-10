<?
class CP_Www_Widgets_Edukite_CalendarDisplay_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('fullcalendar-1.6.4');

    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        //$rowsHTML = $this->getRowsHTML();

        $text = "
        <div id='{$c->handle}' class='{$c->cssClass}'>
        </div>
        ";

        $headerObj = "
        {
             left: '{$c->headerLeft}'
            ,center: '{$c->headerCenter}'
            ,right: '{$c->headerRight}'
        }
        ";

        $timeFormatObj = "{
             {$c->monthTimeFormat}
            ,{$c->genTimeFormat}
            }
        ";

        $minTime = $c->minTime;

        $script = "
        exp = {
             handle: '{$c->handle}'
            ,eventAction: '{$c->eventAction}'
            ,headerObj: $headerObj
            ,timeFormatObj: $timeFormatObj
            ,minTime: $minTime
        }
        cpw.edukite.calendarDisplay.run(exp);
        ";

        if($c->executeScript){
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                {$script}
            "));
        }

        $text = "
        <div id='{$c->handle}' class='calendarDisplay'></div>
        ";

        return $text;
    }
    /**
     *
     */

    function getRowsHTML() {
    }
}