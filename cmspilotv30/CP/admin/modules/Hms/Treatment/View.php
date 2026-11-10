<?
class CP_Admin_Modules_Hms_Treatment_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            //$email     = $row['email'];
            //$website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['treatment_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['fees'])}
            {$listObj->getListRowEnd($row['treatment_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 't.treatment_code')}
        {$listObj->getListHeaderCell('Title', 't.title')}
        {$listObj->getListHeaderCell('Category', 't.category')}
        {$listObj->getListHeaderCell('Fees', 't.fees' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

       
        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['address_country']);

        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('treatmentCategory', 'value ASC');
        $sqlTitle    = $fn->getValueListSQL('treatmentTitle');

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Treatment Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Code', 'treatment_code', $row['treatment_code'])}</td>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td>{$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Fees', 'fees', $row['fees'])}</td>
                            </tr>
                            <tr>
                                <td class='notesTitle'>{$formObj->getTARow('Description ', 'description', $row['description'])}</td>
                            </tr>
                            <tr>
                                <td class= 'creationModificationText' colspan = '3'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');


        $record_id = $fn->getIssetParam($row, 'treatment_id');
        $treatment_id  = $fn->getReqParam('treatment_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_treatment', 'attachment', $row)}

        ";

        
        $sqlTreatment = "
        SELECT t.*
        FROM treatment t 
        WHERE t.treatment_id = {$row['treatment_id']}
        ";

        $resultTreatment = $db->sql_query($sqlTreatment);
        $rowTreatment = $db->sql_fetchrow($resultTreatment);

        $printText ="";
        if ($rowTreatment['treatment_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddMedicineTemplate($row['treatment_id'])}</div>
            ";
        }
        $text=$text.$printText;
        return $text;
    }
    /**
     *
     */
    function getAddMedicineTemplate($treatment_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($treatment_id == ''){
            $treatment_id = $fn->getReqParam('treatment_id');
        }

        $MedicineTemplate = $this->getAddMedicineTemplateDetail($treatment_id);

        $recCount = $fn->getRecordCount('treatment_medicine_template', "treatment_id = '{$treatment_id}'");

        $header ="
        <thead>
            <tr>
            <th>Title</th>
            <th>Medicine Name</th>
            <th>Instruction</th>
            <th>No of Days</th>
            <th>Qty</th>
            <th class='portalActBtns'></th>
            <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionMedicineTemplate = "index.php?module=hms_treatment&_spAction=MedicineTemplate&treatment_id={$treatment_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddMedicineTemplate' href='{$formActionMedicineTemplate}' treatment_id={$treatment_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_treatment__hms_treatment_medicine_templateLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Medicine Template</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddMedicineTemplatePortal'>
                            {$MedicineTemplate}
                        </tbody>
                    </table>
                    <input type='hidden' name='treatment_id' value='{$treatment_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddMedicineTemplateDetail($treatment_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($treatment_id == ''){
            $treatment_id = $fn->getReqParam('treatment_id');
        }

        $treatment_medicine_template_id = $fn->getReqParam('treatment_medicine_template_id');

        $rows  = "";

        $SQL="
        SELECT tmt.*
              ,p.title AS Medicine_Name
        FROM treatment_medicine_template tmt
        LEFT JOIN (product p) ON (p.product_id = tmt.product_id)
        WHERE treatment_id = '{$treatment_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            //$formActionDeleteMedicineTemplate = "index.php?module=hms_treatment&_spAction=DeleteMedicineTemplate&treatment_medicine_template_id={$row['treatment_medicine_template_id']}&treatment_id={$treatment_id}&showHTML=0";
            $formActionEditMedicineTemplate   = "index.php?module=hms_treatment&_spAction=EditMedicineTemplate&treatment_medicine_template_id={$row['treatment_medicine_template_id']}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteMedicineTemplate' href='#'  treatment_medicine_template_id='{$row['treatment_medicine_template_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditMedicineTemplate' href='{$formActionEditMedicineTemplate}' treatment_medicine_template_id='{$treatment_medicine_template_id}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $sqlproduct = "
            SELECT p.title AS Medicine_Name
                  ,p.product_id 
                  ,tmt.treatment_medicine_template_id
            FROM  product p 
            LEFT JOIN (treatment_medicine_template tmt) ON (tmt.product_id = p.product_id)
            WHERE tmt.treatment_medicine_template_id = {$row['treatment_medicine_template_id']}
            ";

            $resultproduct = $db->sql_query($sqlproduct);
            $rowproduct = $db->sql_fetchrow($resultproduct);


            $rows .= "
                <tr>
                    <td>{$row['title']}</td>
                    <td>{$row['Medicine_Name']}</td>
                    <td>{$row['instruction']}</td>
                    <td>{$row['no_of_days']}</td>
                    <td>{$row['qty']}</td>
                    
                    <td>
                        {$editIcon}
                    </td>
                    <td>
                        {$deleteIcon}
                    </td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */

    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('status');
        $category = $fn->getReqParam('category');

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlCategory = $fn->getValueListSQL('treatmentCategory', 'value ASC');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='category' >
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $category)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }


    /**
     *
     */
    function getMedicineTemplate() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        //echo "Testing";
        $treatment_id  = $fn->getReqParam('treatment_id');

        $formAction = "index.php?_topRm=order&module=hms_treatment&_spAction=MedicineTemplateFormSubmit&showHTML=0";

        $sqlproduct = "
        SELECT product_id
              ,title AS  Medicine_Name
        FROM product
        ";


        //$sqlCategory = '';

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title')}
            {$formObj->getDDRowBySQL('Medicine Name', 'product_id', $sqlproduct,'')}
            {$formObj->getTBRow('Instruction', 'instruction')}
            {$formObj->getTBRow('No of Days', 'no_of_days')}
            {$formObj->getTBRow('Qty', 'qty')}
            <input type='hidden' name='treatment_id' value='{$treatment_id}' />
        </form>
        ";
        return $text;
    }

     /**
     *
     */
    function getEditMedicineTemplate() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');


        $treatment_id  = $fn->getReqParam('treatment_id');

        //echo "Testing";
        $treatment_medicine_template_id  = $fn->getReqParam('treatment_medicine_template_id');

        if($treatment_medicine_template_id == ''){
        $treatment_medicine_template_id  = $fn->getReqParam('treatment_medicine_template_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_treatment&_spAction=EditMedicineTemplateFormSubmit&showHTML=0&treatment_medicine_template_id={$treatment_medicine_template_id}";

        $SQL="
        SELECT tmt.*
              ,p.title AS Medicine_Name
        FROM treatment_medicine_template tmt
        LEFT JOIN (product p) ON (p.product_id = tmt.product_id)
        WHERE treatment_medicine_template_id = '{$treatment_medicine_template_id}'
        ";
        $result   = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlproduct = "
        SELECT product_id
              ,title AS  Medicine_Name
        FROM product
        ";

        //$count = 1;
        //while ($row = $db->sql_fetchrow($result)) {

            $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title', $row['title'])}
            {$formObj->getDDRowBySQL('Medicine Name', 'product_id', $sqlproduct,'')}
            {$formObj->getTBRow('Instruction', 'instruction', $row['instruction'])}
            {$formObj->getTBRow('No of Days', 'no_of_days', $row['no_of_days'])}
            {$formObj->getTBRow('Qty', 'qty', $row['qty'])}
            <input type='hidden' name='treatment_medicine_template_id' value='{$treatment_medicine_template_id}' />
        </form>
        ";        
           // $count++;
       // }

        $text="{$rows}";

        return $text;
    }


    /**
     *
     */
    

}