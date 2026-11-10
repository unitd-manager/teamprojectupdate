<?
class CP_Admin_Modules_EnggCrm_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');

        //====================== Convert to Project ================================//
        $actObj = $actArray->getActionObj('convertOppToProject');
        $actArray->registerAction($actObj, array(
            'title' => 'Convert to Project'
        ));

        //====================== Raise Invoice ================================//
        $actObj = $actArray->getActionObj('raiseInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Invoice'
           ,'url' => "javascript:Invoice.raiseInvoice('{$tv['topRm']}');"
        ));
        //====================== Duplicate Project ================================//
        $actObj = $actArray->getActionObj('duplicateProject');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate Project'
           ,'url' => "javascript:Project.duplicateProject('{$tv['topRm']}');"
        ));
    }

    /**
     *
     */
    function getAmountFractionFormattedForGst($invoice_amount, $gst_percentage) {

        $gst_amount = (($invoice_amount * $gst_percentage)/100);
        /* Taking two decimal values for gst amount */
        $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
        if ($fraction_length > 2) {
            list($integer, $fraction) = explode(".", (string) $gst_amount);

            /* Checking whether 3rd decimal point is more than or equal to 5
               If Yes, add 1 to 2nd decimal point
             */
            $gstDecimalMore = substr($fraction, 2, 1);
            $fraction = substr($fraction, 0, 2);
            if ($gstDecimalMore >= 5) {
                if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
                    $fraction = '0.00';
                    $integer = $integer + 1;
                } else {
                    $fraction = $fraction + 1;
                }
            }

            $fraction = substr($fraction, 0, 2);
            $gst_amount = $integer . "." . $fraction;
        } else if ($fraction_length == 2) {
            list($integer, $fraction) = explode(".", (string) $gst_amount);
            
            if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
                $fraction = '0.00';
                $integer = $integer + 1;
            }
        }

        $total = $invoice_amount + $gst_amount;
        
        return $total;
    }
}
