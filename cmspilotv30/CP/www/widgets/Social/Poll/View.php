<?
class CP_Www_Widgets_Social_Poll_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;
        $cpPollAlreadyTaken = isset($_SESSION['cpPollAlreadyTaken']) ? $_SESSION['cpPollAlreadyTaken']  : false;
        $cls = ($cpPollAlreadyTaken) ? " class='pollTaken'" : '';

        $text = "
        <div{$cls}>
            <h3>{$ln->gd($c->heading)}</h3>
            {$this->getRowsHTML()}
        </div>
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $c = &$this->controller;
        $formAction = $c->formAction;
        
        $data = $this->model->dataArray;
        
        if (count($data) == 0){
            return;
        }

        $cpPollAlreadyTaken = isset($_SESSION['cpPollAlreadyTaken']) ? $_SESSION['cpPollAlreadyTaken']  : false;

        $totalCountSQL = "
        SELECT SUM(answer_count) AS answer_count 
        FROM poll_history 
        WHERE poll_id = {$data['poll_id']}
        ";
        $result = $db->sql_query($totalCountSQL);
        $row = $db->sql_fetchrow($result);
        $answer_total  = $row[0];
                            
        $answers = '';
        foreach($data['answers'] AS $answer){
            $id = $answer['poll_history_id'];
            $ans = $answer['title'];

            if ($cpPollAlreadyTaken){
                if ($answer['answer_count'] == 0) {
                   $ansCountPercentage = 0;
                } else {
                   $ansCountPercentage = ($answer['answer_count']/$answer_total)*100;
                }

                $ansCountPercentage = number_format($ansCountPercentage, 0);
                $ansCountPercentagePx = $ansCountPercentage / 2;

                $answers .= "
                <div class='row'>
                    <div class='answer'>{$ans}</div>
                    <div class='percentageBarOuter'>
                        <div class='percentageBar' style='width:{$ansCountPercentagePx}px;'></div>
                        <div class='percentageText'>{$ansCountPercentage}%</div>
                    </div>
                </div>
                ";
            } else {
                $answers .= "
                <div class='row'>
                    <input name='poll_ans' type='radio' value='{$id}' id='poll_{$id}'><label for='poll_{$id}'>{$ans}</label>
                </div>
                ";
            }
        }

        $submit = '';
        if (!$cpPollAlreadyTaken){
            $submit = "
            <div class='submit'><a href='javascript:void(0);' onClick=\"$('#pollForm').submit();\">{$ln->gd('cp.form.btn.submit')}</a></div>
            ";
        }
        
        $text = "
        <form name='pollForm' id='pollForm' class='' method='post' action='{$c->formAction}'>
            <fieldset>
                <p>{$data['title']}</p>
                {$answers}
            </fieldset>
            {$submit}
        </form>
        ";

        return $text;
    }
}