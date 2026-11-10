<?
class CP_Admin_Widgets_Project_Task extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT t.title
              ,date_format(t.due_date, '%d %b %Y') AS due_date
              ,t.status
              ,t.task_id
              ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', o.title, p.title) AS project_opp_title
        FROM task t
        JOIN task_staff ts ON (ts.task_id = t.task_id)
        LEFT JOIN (opportunity o) ON (t.opportunity_id = o.opportunity_id)
        LEFT JOIN (project p)     ON (t.project_id     = p.project_id)
        WHERE (t.status =  'Due' || t.status  =  'Late')
          AND ts.staff_id = {$_SESSION['contact_id']}
        ORDER BY
        CASE
        WHEN (t.status = 'Late' ) THEN 1
        WHEN (t.due_date != '' AND t.due_date IS NOT NULL AND t.due_date != '0000-00-00') THEN 2
        ELSE 3
        END, t.due_date
        limit 0, 20
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $url = "index.php?_topRm=project&module=task&record_id={$row['task_id']}&_action=detail";
            
            $rows .= "
            <tr>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['project_opp_title']}</td>
                <td>{$row['due_date']}</td>
                <td>{$row['status']}</td>
            </tr>
            ";
        }

        $url = "index.php?_topRm=project&module=task&staff_id={$_SESSION['staff_id']}";
        $text = "
        <h2><a href='{$url}'>Tasks Due</a></h2>
        <div class='tableOuter'>
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Project</th>
                    <th class='w75'>Due Date</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

}