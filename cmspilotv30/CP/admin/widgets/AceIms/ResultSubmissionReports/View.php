<?
class CP_Admin_Widgets_AceIms_ResultSubmissionReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>NRIC Type</th>
                        <th>NRIC Number</th>
                        <th>Name (As in NRIC)</th>
                        <th>Gender</th>
                        <th>Nationality</th>
                        <th>Date of Birth</th>
                        <th>Race</th>
                        <th>Type Of Registration</th>
                        <th>Company Registration Number</th>
                        <th>Company Name</th>
                        <th>Designation</th>
                        <th>Language</th>
                        <th>Education Level</th>
                        <th>Salary Range</th>
                        <th>Assessment Venue</th>
                        <th>Course Reference Number</th>
                        <th>Competency Standard Code</th>
                        <th>Cert Code</th>
                        <th>Submission Type</th>
                        <th>Date Of Assessment</th>
                        <th>Result</th>
                        <th>Trainer NRIC</th>
                        <th>Assessor NRIC</th>
                        <th>Supervisor Assessor NRIC</th>
                        <th>Printing Of SOA(s)</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */

    function getRowsHTML() {
        $rows = '';
        $fn = Zend_Registry::get('fn');

        $serial_no = 0;
        $subject_id = $fn->getReqParam('subject_id');
        foreach($this->model->dataArray as $row){
            $serial_no += 1;

            $rows .= "
            <tr>
                <td>{$row['nric_type']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['trainee_name']}</td>
                <td>{$row['gender']}</td>
                <td>{$row['nationality']}</td>
                <td>{$row['date_of_birth']}</td>
                <td>{$row['race']}</td>
                <td>{$row['member_type']}</td>
                <td>{$row['title']}</td>
                <td></td>
                <td>{$row['language']}</td>
                <td>{$row['qualification']}</td>
                <td>{$row['salary_range']}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{$row['date_training']}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}