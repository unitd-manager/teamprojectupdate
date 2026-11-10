<?
class CP_Admin_Widgets_AceIms_SummaryDisplay_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $url = '';
        $title = 'Overall Summary';
        
        $text ="
        <h2><a href='{$url}'>$title</a></h2>
        <div class='subcolumns'>
            <div class='subcl float_left'>
                <div class='mt5 mb5 ml20'>
                    <div>
                        <img src='images/icon-task.png'/>
                    </div>
                    <div class='txtCenter'><a href='/admin/index.php?_topRm=main&module=aceIms_course'>Course</a></div>
                </div>
            </div>
            <div class='float_left summary'>
                <div class='mt5 mb5'>
                    <div>Last 3 Months : 5</div> 
                    <div>Last Month : 5</div> 
                    <div>Current Month : 5</div>
                </div>
            </div>
            <div class='subcl float_left'>
                <div class='mt5 mb5 ml20'>
                    <div>
                        <img src='images/icon-interest.png'/>
                    </div>
                    <div class='txtCenter'><a href='/admin/index.php?_topRm=main&module=aceIms_batch'>Batch</a></div>
                </div>
            </div>
            <div class='float_left summary'>
                <div class='mt5 mb5'>
                    <div>Last 3 Months : 5</div> 
                    <div>Last Month : 5</div> 
                    <div>Current Month : 5</div>
                </div>
            </div>
            <div class='subcl float_left'>
                <div class='mt5 mb5 ml20'>
                    <div>
                        <img src='images/icon-contact.png'/>
                    </div>
                    <div class='txtCenter'><a href='/admin/index.php?_topRm=main&module=aceIms_contact'>Trainee</a></div>
                </div>
            </div>
            <div class='float_left summary'>
                <div class='mt5 mb5'>
                    <div>Last 3 Months : 5</div> 
                    <div>Last Month : 5</div> 
                    <div>Current Month : 5</div>
                </div>
            </div>
            <div class='subcl float_left'>
                <div class='mt5 mb5 ml20'>
                    <div>
                        <img src='images/icon-staff.png'/>
                    </div>
                    <div class='txtCenter'><a href='/admin/index.php?_topRm=main&module=aceIms_teacher'>Trainer</a></div>
                </div>
            </div>
            <div class='float_left summary'>
                <div class='mt5 mb5'>
                    <div>Last 3 Months : 5</div> 
                    <div>Last Month : 5</div> 
                    <div>Current Month : 5</div>
                </div>
            </div>
        </div>
        ";
        return $text;
    }
    /**
     *
     */

    function getRowsHTML() {
        $rows = '';

        foreach($this->model->dataArray as $row){
            $rows .= "
            <tr>
                <td>{$row['course_title']}</td>
                <td class='txtRight'>{$row['total']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}