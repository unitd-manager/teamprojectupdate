<?
/**
 *
 */
class CP_Common_Lib_Util
{

    /**
     *
     */
    function redirect($dest,$ssl=false){
        $toUrl = ($ssl ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
        if (strpos($dest,0) == "/"){
            // root-relative
            $toUrl .= $dest;
        } else if (strpos('http',0) == 0){
            // absolute
            $toUrl = $dest;
        } else {
            // document-relative
            // traverse up the directory tree as needed
            $parent = dirname($dest);
            while(strpos($dest, "../") == 0){
                $parent = dirname($parent);
                $dest = substr($dest,3);
            }
            $toUrl .= $dest;
        }

        header("Location: " . $toUrl);
        exit();
    }

    /**
     *
     */
    function smartCopy($source, $dest, $folderPermission=0755,$filePermission=0644){
        # source=file & dest=dir => copy file from source-dir to dest-dir
        # source=file & dest=file / not there yet => copy file from source-dir to dest and overwrite a file there, if present

        # source=dir & dest=dir => copy all content from source to dir
        # source=dir & dest not there yet => copy all content from source to a, yet to be created, dest-dir
        $result=false;

        if (is_file($source)) { # $source is file
            if(is_dir($dest)) { # $dest is folder
                if ($dest[strlen($dest)-1]!='/') # add '/' if necessary
                    $__dest=$dest."/";
                $__dest .= basename($source);
                }
            else { # $dest is (new) filename
                $__dest=$dest;
                }
            $result=copy($source, $__dest);
            chmod($__dest,$filePermission);
            }
        elseif(is_dir($source)) { # $source is dir
            if(!is_dir($dest)) { # dest-dir not there yet, create it
                @mkdir($dest,$folderPermission);
                chmod($dest,$folderPermission);
                }
            if ($source[strlen($source)-1]!='/') # add '/' if necessary
                $source=$source."/";
            if ($dest[strlen($dest)-1]!='/') # add '/' if necessary
                $dest=$dest."/";

            # find all elements in $source
            $result = true; # in case this dir is empty it would otherwise return false
            $dirHandle=opendir($source);
            while($file=readdir($dirHandle)) { # note that $file can also be a folder
                if($file!="." && $file!="..") { # filter starting elements and pass the rest to this function again
                    #echo "$source$file ||| $dest$file<br />\n";
                    $result=smartCopy($source.$file, $dest.$file, $folderPermission, $filePermission);
                    }
                }
            closedir($dirHandle);
            }
        else {
            $result=false;
       }
       return $result;
    }

    /**
     *
     */
    function getRandomNumber() {
        $today = getdate();
        $rnd = rand (100000, 1000000).
        $today["seconds"] . $today["minutes"] . $today["hours"] .
        $today["mday"] . $today["year"] . $today["mon"];

        return $rnd;
    }

    /**
     *
     */
    function getRandomChar($string)  {
        $length = strlen($string);
        $position = mt_rand(0, $length - 1);
        return($string[$position]);
    }

    /**
     *
     */
    function getRandomPassword($charset_string, $length)  {
       $return_string = $this->getRandomChar($charset_string);
       for ($x = 1; $x < $length; $x++)
       $return_string .= $this->getRandomChar($charset_string);
       return($return_string);
    }

    function getRandomString($min = 6, $max = 10) {
        $charset = "abcdefghijklmnopqrstuvwxyz";
        $length = mt_rand ($min, $max);
        $key = '';
        for ($i=0; $i<$length; $i++) {
            $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        }
        return $key;
    }

    function getRandomInteger($min = 6, $max = 10) {
        $charset = "0123456789";
        $length = mt_rand ($min, $max);
        $key = '';
        for ($i=0; $i<$length; $i++) {
            $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        }
        return $key;
    }

    /**
     *
     */
    function getRandomPasswordCS1($length)  {
       mt_srand((double)microtime() * 100000);
       $charset = "abcdefghijk9";
       $password = $this->getRandomPassword($charset, $length);
       return $password;
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     * @return array
     */
    function getSaltAndPasswordArray($email, $pass_word){
        $salt = uniqid(mt_rand(), true);
        $combine = $email . $pass_word . $salt;
        $newPassword = hash('sha256', $combine);

        $arr = array('salt' => $salt, 'pass_word' => $newPassword);
        return $arr;
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     * @return array
     */
    function getResetPasswordHash($email, $unGuessableHash = 'p!l@*!)11!)H<'){
        $salt = $unGuessableHash.uniqid(mt_rand(), true);
        $hash = hash('sha256', $email.$salt);
        return $hash;
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     * @param type $salt
     * @return type
     */
    function getSaltedPassword($email, $pass_word, $salt){
        $combine = $email . $pass_word . $salt;
        $saltedPassword = hash('sha256', $combine);

        return $saltedPassword;
    }

    /**
     *
     */
    function urlise($text, $wrap=100000, $split="<br>") {
        $pat="/([f|ht]+tp:\/\/[a-zA-Z0-9@%_.~#\/\-\?&=]+.)|(www.[a-zA-Z0-9@%_.~#\-\?&]+.)|([a-zA-Z0-9@%_.~#\-\?&]+.\@[a-zA-Z0-9@%_.~#\-\?&]+.)/";
        preg_match_all($pat, $text, $out, PREG_PATTERN_ORDER);
        for ($i=0; $i < sizeof($out[0]); $i++) {
           $outvar = trim($out[0][$i]);
           $text = preg_replace('~' . preg_quote($outvar, '~') . '~', 'LINK' . $i, $text);
        }

        $text  = wordwrap($text, $wrap, $split, 1);
        $textn = $text;

        for ($j=0; $j < sizeof($out[0]); $j++)
        {
           $outvar = trim($out[0][$j]);
           if (strpos($text, 'LINK' . $j) !== false) {
              $outvar = stripslashes($outvar);
              $patt1  = "(((f|ht){1}tp://)[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/])";
              $patt2  = "(www.[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/])";
              $patt3  = "([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})";
              if (preg_match('!' . $patt2 . '!', $outvar) || preg_match('!' . $patt1 . '!', $outvar)) {
                 $outvar = str_replace("http://", "", $outvar);
                 $text = str_replace('LINK' . $j, '<a href="http://' . $outvar . '" target="_blank">' . $outvar . '</a>', $text);
              }
              else if (preg_match('!' . $patt3 . '!', $outvar)) {
                 $text = str_replace('LINK' . $j, '<a href="mailto:' . $outvar . '">' . $outvar . '</a>', $text);
              }
           }
        }
        return $text;
    }

    /**
     *
     */
    function urlise2($text,$wrap=100000, $split="<br>", $linkDispLength = 0) {
       if ($wrap == 0) {
          $wrap = 100000;
       }
       $outvarDisp = $text;
       if ($linkDispLength > 0 && strlen($outvarDisp) > $linkDispLength) {
          $outvarDisp = substr($outvarDisp, 0, $linkDispLength) . "...";
       }

       $pat="/([f|ht]+tp:\/\/[a-zA-Z0-9@%_.~#\/\-\?&=]+.)|(www.[a-zA-Z0-9@%_.~#\-\?&]+.)|([a-zA-Z0-9@%_.~#\-\?&]+.\@[a-zA-Z0-9@%_.~#\-\?&]+.)/";
       preg_match_all($pat, $text, $out, PREG_PATTERN_ORDER);
       // print "<pre>";
       // print_r ($out);
       // print "</pre>";
       for ($i=0; $i < sizeof($out[0]); $i++) {
          $outvar = trim($out[0][$i]);
          $text = preg_replace('~' . preg_quote($outvar, '~') . '~', 'LINK' . $i, $text);
       }
       $text  = wordwrap($text, $wrap, $split, 1);



       for ($j=0; $j < sizeof($out[0]); $j++) {
          $outvar = trim($out[0][$j]);
          if (strpos($text, 'LINK' . $j) !== false) {
             $outvar = stripslashes($outvar);
             $patt1  = "(((f|ht){1}tp://)[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/])";
             $patt2  = "(www.[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/])";
             $patt3  = "([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})";
             if (preg_match('!' . $patt2 . '!', $outvar) || preg_match('!' . $patt1 . '!', $outvar)) {
             //print $outvarDisp . "---" ."<br>";

                $outvar = str_replace("http://", "", $outvar);
                $text = str_replace('LINK' . $j, '<a href="http://' . $outvar . '" target="_blank">' . $outvarDisp . '</a>', $text);
             }
             else if (preg_match('!' . $patt3 . '!', $outvar)) {
                $text = str_replace('LINK' . $j, '<a href="mailto:' . $outvar . '">' . $outvar . '</a>', $text);
             }
          }
       }
       return $text;
    }

    /**
     *
     */
    function getDropDown1($array1, $selectedValue = "", $useKey = 0) {
        $resultText = "";

        foreach ($array1 as $key => $value) {
            if ($useKey == 1) {
                $keyText   = $key;
                $valueText = $value;
            } else {
                $keyText   = $value;
                $valueText = $value;
            }

            if (strval($keyText) === strval($selectedValue)) {
                $resultText .= "<option selected='selected' value=\"" . $keyText . "\">" . $valueText . "</option>\n";
            } else {
                $resultText .= "<option value=\"" . $keyText . "\">" . $valueText . "</option>\n";
            }
        }
        return $resultText;
    }

    /**
     *
     */
    function getDropDown2($array1, $selectedValue = "") {
       $resultText = "";
       $countArr   = count($array1[0]);
       for ($i = 0; $i < $countArr; $i++) {
          if ($array1[0][$i] == $selectedValue) {
            $resultText .= "<option selected value=\"" . $array1[0][$i] . "\">" . $array1[1][$i] . "</option>\n";
          } else {
            $resultText .= "<option value=\"" . $array1[0][$i] . "\">" . $array1[1][$i] . "</option>\n";
          }
       }
       return $resultText;
    }

    /**
     *
     */
    function getDropDownFromArr($array1, $selectedValue = "") {
       $resultText = "";

       foreach ($array1 as $key => $display) {
          if ($key == $selectedValue) {
            $resultText .= "<option selected value=\"{$key}\">{$display}</option>\n";
          } else {
            $resultText .= "<option value=\"{$key}\">{$display}</option>\n";
          }

       }
       return $resultText;
    }

    /**
     *
     */
    function getCheckboxFromArray1($array1, $fieldName, $selectedValue = "", $orientation = "horizontal") {
       $resultText = "";
       foreach ($array1 as $key => $value) {
          if ($value == $selectedValue) {
            $resultText .= "<input name=\"{$fieldName}\" type=\"checkbox\" checked value=\"" . $value . "\"> " . $value;
          } else {
            $resultText .= "<input name=\"{$fieldName}\" type=\"checkbox\" value=\"" . $value . "\"> " . $value;
          }
          if($orientation == "vertical"){
             $resultText .= "<br/>";
          }
       }
       return $resultText;
    }

    /**
     *
     */
    function getCheckboxFromArray($array1, $fieldName, $selectedValuesArr = array(), $extraParam = array()) {
        $text    = "";
        $counter = 1;

        $exp = $extraParam;

        $selectedValuesArr = is_array($selectedValuesArr) ? $selectedValuesArr : array();

        $rowCls            = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls          = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $isEditable        = isset($exp['isEditable'])    ? $exp['isEditable']                 : 1;
        $useKey            = isset($exp['useKey'])        ? $exp['useKey']                     : 0;
        $singleValue       = isset($exp['singleValue'])   ? $exp['singleValue']                : 0;
        $disabled          = isset($exp['disabled'])      ? $exp['disabled']                   : false;
        $disabled          = ($disabled == true)          ? " disabled='{$disabled}'"          : "";

        foreach ($array1 as $key => $value) {
            $key       = ($useKey == 1) ? $key : $value;
            $checked = '';
            if ($useKey == 1 && array_key_exists($key, $selectedValuesArr)){
                $checked = " checked='checked'";
            } else if (in_array($key, $selectedValuesArr) === true){
                $checked = " checked='checked'";
            }

            $id        = ($singleValue == 1) ? "fld_{$fieldName}" : "{$fieldName}_{$counter}";
            $fldName   = ($singleValue == 1) ? $fieldName : "{$fieldName}[]";

            $text .= "
            <div class='type-check ym-fbox-check'>
                <input type='checkbox' name='{$fldName}' value='{$key}' id='{$id}' {$checked} {$disabled} />
                <label for='{$id}'>{$value}</label>
            </div>
            ";

            $counter++;
        }
        return $text;
    }

    /**
     *
     */
    function getRadioFromArray($array1, $fieldName, $selectedValue = "", $extraParam = array()) {
        $text    = "";
        $counter = 1;
        $exp = &$extraParam;
        $rowCls            = isset($exp['rowCls'])        ? " {$exp['rowCls']}"       : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls']) ? "{$exp['fieldLabelCls']}" : "type-check ym-fbox-check";
        $fieldCls          = isset($exp['fieldCls'])      ? "{$exp['fieldCls']}"      : "";
        $jsFunction        = isset($exp['jsFunction'])    ? $exp['jsFunction']        : "";
        $isEditable        = isset($exp['isEditable'])    ? $exp['isEditable']        : 1;
        $useKey            = isset($exp['useKey'])        ? $exp['useKey']            : 0;
        $useCode           = isset($exp['useCode'])       ? $exp['useCode']           : 0;
        $disabled          = isset($exp['disabled'])      ? $exp['disabled']          : false;
        $strictKeyValCheck = isset($exp['strictKeyValCheck']) ? $exp['strictKeyValCheck'] : false;

        $jsText            = ($jsFunction != "") ? " onchange='{$jsFunction}'"  : "";
        $disabled          = ($disabled == true) ? " disabled='{$disabled}"     : "";


        foreach ($array1 as $key => $value) {
            $key     = ($useKey == 1) ? $key : $value;
            if ($strictKeyValCheck) {
                $checked = ($key === $selectedValue) ? " checked='checked'" : "";
            } else {
                $checked = ($key == $selectedValue) ? " checked='checked'" : "";
            }

            $id = "{$fieldName}_{$counter}";
            $text .= "
            <div class='{$fieldLabelCls}'>
                <input type='radio' name='{$fieldName}' value='{$key}' id='{$id}' {$checked}{$disabled}{$jsText} />
                <label for='{$id}'>{$value}</label>
            </div>
            ";

            $counter++;
        }
        return $text;
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function replaceForDB($value) {
       $value = str_replace("'", "\'", $value);
       return $value;
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function replaceForFormField($value) {
       $value = htmlspecialchars($value);
       return $value;
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function formatPara($value) {
       $value = str_replace("\n", "<br>", $value);
       return $value;
    }

    /**
     *
     */
    function sendFile($path, $actualFileName = "") {
       session_write_close();
       ob_end_clean();
       if (!is_file($path) || connection_status()!=0)
           return(FALSE);

       //to prevent long file from getting cut off from    //max_execution_time

       @set_time_limit(0);

       if ($actualFileName != ""){
          $name = $actualFileName;
       } else {
          $name = basename($path);
       }

       //filenames in IE containing dots will screw up the
       //filename unless we add this

       if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
           $name = preg_replace('/\./', '%2e', $name, substr_count($name, '.') - 1);

       //required, or it might try to send the serving    //document instead of the file

       header("Cache-Control: ");
       header("Pragma: ");
       //header("Content-Type: application/octet-stream");
       //header("Content-Type: application/jpg");
       header("Content-type: application/force-download");
       header("Content-Length: " .(string)(filesize($path)) );
       header('Content-Disposition: attachment; filename="'.$name.'"');
       header("Content-Transfer-Encoding: binary\n");

       if($file = fopen($path, 'rb')){
           while( (!feof($file)) && (connection_status()==0) ){
               print(fread($file, 1024*8));
               flush();
           }
           fclose($file);
       }
       return((connection_status()==0) and !connection_aborted());
    }



    /**
     *
     * @param <type> $phrase
     * @param <type> $max_words
     * @return <type>
     */
    function truncateByWords($phrase, $max_words) {
         $phrase_array = explode(' ',$phrase);
         if(count($phrase_array) > $max_words && $max_words > 0) {
             $phrase = implode(' ', array_slice($phrase_array, 0, $max_words)) . '...';
         }
         return $phrase;
    }

    /**
     * Subtract a string for ex: if string = Y and if you subtract by 1 the string will be X.
     * This function currently work for single character for now. Needs upgrading
     * Copied From Old functions.php
     */
    function getSubtractString($string, $value = 1) {
        $char = substr($string, -1);
        $string2 = substr($string, 0, -1);
        $ascii = ord($char);
        $ascii = $ascii - $value;
        $string = $string2 . chr($ascii);

        return $string;
    }

    /**
     *
     */
    function getSubString($strValue, $strMaxLength) {
      //include_once 'lib_php/flourish/fUTF8.php';

      $strValue = strip_tags($strValue);
      $strValue = str_replace("&nbsp;", "" , $strValue);
      $strArr   = explode(" ", $strValue);

      $strNewValue  = "";
      $strLength = 0;

      //----------------------------------------------------------//
      foreach ($strArr as $key => $value){
         $strNewValue .= $value . " ";
         $strLength += strlen($value) + 1;

         if ($strLength > $strMaxLength){
            return $strNewValue;
         }
      }

      return $strNewValue;
      //----------------------------------------------------------//
    }

    /**
     *
     */
    function getStringAsCommaSeperated($str, $seperator) {

        if(substr($str, 0, 1) == "#") { // if the first char is # then remove it
            $str = substr($str, 1);
        }

        $str = str_replace($seperator, ",", $str);
        $str = trim($str);

        if(substr($str, -1, 1) == ",") { // if the last char is "," then remove it
            $str = substr($str, 0, strlen($str) - 1);
        }

        if(substr($str, -1, 1) == ",") { // if the last char is "," then remove it
            $str = substr($str, 0, strlen($str) - 1);
        }

        return $str;
    }

    /**
     *
     * @param <type> $status
     * @param <type> $html
     * @param <type> $errorMsg
     * @return <type>
     */
    function getJsonText($status, $html = '', $errorMsg = '') {

        $arr = array(
           'status'   => $status //success/error
          ,'html'     => $html
          ,'errorMsg' => $errorMsg
        );

        header('Content-type: application/json');
        return json_encode($arr);
    }

    /**
     * Returns an array as a json string and sets the header
     * @param <type> $arr
     * @return <type>
     */
    function getJsonFromArray($arr) {
        header('Content-type: application/json');
        return json_encode($arr);
    }

    /**
     *
     * @param <type> $stringSource
     * @param <type> $findString
     * @return <type>
     */
    function stringPosition($stringSource, $findString) {
       $pos = strpos($stringSource, $findString);
       if ($pos === false) {
           return -1;
       } else {
           return $pos;
       }
    }

    /**
     *
     * @param <type> $fileName
     * @return <type>
     */
    function fixFileName($fileName) {
       $allowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-_.";
       $fileNameLength = strlen($fileName);
       $counter = 0;
       $fileNameTemp = "";

       while ($counter < $fileNameLength):
          $currChar = substr($fileName, $counter, 1);
          if ( strpos($allowedChars, $currChar) === false) {
             $fileNameTemp = $fileNameTemp . "_";
          }
          else {
             $fileNameTemp = $fileNameTemp . $currChar;
          }

          $counter++;

       endwhile;

       return $fileNameTemp;

    }
    /**
     *
     * @param <type> $timeString
     * @return <type>
     */
    function convertServerTimeToLocalTime($timeString, $localTimeZone = "Asia/Hong_Kong") {
		 //date_default_timezone_set($serverTimeZone);
         $d = new DateTime($timeString);
         if ($d) {
              $d->setTimeZone(new DateTimeZone($localTimeZone));
              return $d->format("Y-m-d H:i:s");
         }
         return null;
    }

    /**
     *
     * @Find position of Nth occurance of search string
     * @param string $search The search string
     * @param string $string The string to seach
     * @param int $offset The Nth occurance of string
     * @return int or false if not found
     */
    function strposOffset($search, $string, $offset)
    {
        /*** explode the string ***/
        $arr = explode($search, $string);
        /*** check the search is not out of bounds ***/
        switch( $offset )
        {
            case $offset == 0:
            return false;
            break;

            case $offset > max(array_keys($arr)):
            return false;
            break;

            default:
            return strlen(implode($search, array_slice($arr, 0, $offset)));
        }
    }

    /**
     *
     * @param type $str
     * @param type $width
     * @param type $break
     * @return type
     */
    function utf8_wordwrap($str, $width = 75, $break = "\n")
    {
        $return = '';
        $br_width = mb_strlen($break, 'UTF-8');
        for($i = 0, $count = 0; $i < mb_strlen($str, 'UTF-8'); $i++, $count++)
        {
            if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
            {
                $count = 0;
                $return .= mb_substr($str, $i, $br_width, 'UTF-8');
                $i += $br_width - 1;
            }

            if ($count > $width)
            {
                $return .= $break;
                $count = 0;
            }

            $return .= mb_substr($str, $i, 1, 'UTF-8');
        }

        return $return;
    }

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string  $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                    // if tag is a closing tag (f.e. </b>)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    // if tag is an opening tag (f.e. <b>)
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }

    /**
     * checks the path in the current include path
     */
    function getFilePathInIncludePath($filename) {
        // Check for absolute path
        if (realpath($filename) == $filename) {
            return $filename;
        }

        // Otherwise, treat as relative path
        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($paths as $path) {
            if (substr($path, -1) == DIRECTORY_SEPARATOR) {
                $fullpath = $path.$filename;
            } else {
                $fullpath = $path.DIRECTORY_SEPARATOR.$filename;
            }

            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }

        return false;
    }

    /**
     * get http protocol
     */
    function getHttpProtocol() {
        $https = isset($_SERVER["HTTPS"]) ? $_SERVER["HTTPS"] : '';
        $protocol = strtolower($https) == 'on' ? 'https' : 'http';
        return $protocol;
    }

    /**
     *
     */
    function getUrlPrefix($addTrailingSlash = true) {
        $trailingSlash = '';
        if ($addTrailingSlash) {
            $trailingSlash = '/';
        }
        $url = "{$this->getHttpProtocol()}://{$_SERVER['HTTP_HOST']}{$trailingSlash}";
        return $url;
    }

    /**
     * Regular expression to find words in a phrase and highlight them
     * The first is the phrase and the second is an array of words to be highlighted in the phrase.
     */
    function highlight($sString, $aWords) {
        if (!is_array ($aWords) || empty ($aWords) || !is_string ($sString)) {
            return false;
        }

        $sWords = implode ('|', $aWords);
        return preg_replace ('@\b('.$sWords.')\b@si', '<strong style="background-color:yellow" class="highlight">$1</strong>', $sString);
    }

    /**
     * Identifies the request is ajax
     */
    function isAjaxRequest() {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
        }

        return $isAjax;
    }

    /**
     * Detect for IE browsers
     */
    function isIEBrowser() {
        if (isset($_SERVER['HTTP_USER_AGENT']) &&
        (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds nocache headers to for IE browsers
     */
    function addNoCacheHeaderForIEAjaxRequest() {
        if ($this->isAjaxRequest() & $this->isIEBrowser()) {
            header('Cache-control: private, no-cache');
            header('Expires: Mon, 26 Jun 1997 05:00:00 GMT');
            header('Pragma: no-cache');
        }
    }


    /**
     * this function will return all the combinations of an array
     * array('abc', 'xyz', 'kkk') will return the combinations
     * as abc xyz kkk, abc, kkk xyz, xyz abc kkk and so on
     */
    function getArrayPermutations($items, $perms = array()) {
        static $permutedArray;
        if (empty($items)) {
            $permutedArray[] = $perms;
        } else {
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $this->getArrayPermutations($newitems, $newperms);
            }
            return $permutedArray;
        }
    }

    /**
     *
     */
    function getUrl($urlArr) {
        $tv = Zend_Registry::get('tv');

        $url = '';

        $urlArr['_topRm']  = isset($urlArr['_topRm']) ? $urlArr['_topRm'] : $tv['topRm'];
        $urlArr['module'] = isset($urlArr['module']) ? $urlArr['module'] : $tv['module'];
        $urlExtra = '';
        foreach ($urlArr as $qpar => $value) {
            $urlExtra .= '&' . $qpar . '=' . $value;
        }

        $urlExtra = substr($urlExtra, 1); //remove the first &
        $url = 'index.php?' . $urlExtra;

        return $url;
    }

    /**
     *
     */
    function getUrlParamsAsHiddenFields($url) {
        $tv = Zend_Registry::get('tv');

        $text = '';

        $urlArr = explode('?', $url);
        $url = $urlArr[1];
        $urlArr = explode('&', $url);
        $text = '';
        foreach ($urlArr as $val) {
            list($name, $value) = explode('=', $val);
            $text .= "
            <input type='hidden' name='{$name}' value='{$value}' />
            ";
        }

        return $text;
    }

    /**
     *
     */
    function is_date($value, $format = 'yyyy-mm-dd'){
        if(strlen($value) == 10 && strlen($format) == 10){

            // find separator. Remove all other characters from $format
            $separator_only = str_replace(array('m','d','y'),'', $format);
            $separator = $separator_only[0]; // separator is first character

            if($separator && strlen($separator_only) == 2){
                // make regex
                $regexp = str_replace('mm', '[0-1][0-9]', $value);
                $regexp = str_replace('dd', '[0-3][0-9]', $value);
                $regexp = str_replace('yyyy', '[0-9]{4}', $value);
                $regexp = str_replace($separator, "\\" . $separator, $value);

                if($regexp != $value && preg_match('/'.$regexp.'/', $value)){

                    // check date
                    $day   = substr($value,strpos($format, 'd'),2);
                    $month = substr($value,strpos($format, 'm'),2);
                    $year  = substr($value,strpos($format, 'y'),4);

                    if(@checkdate($month, $day, $year))
                        return true;
                }
            }
        }
        return false;
    }

    /**
     *
     */
    function fsockPost($url,$data, $urlencode = 1) {
    	$postData = '';

    	// return value
    	$info = '';

    	//Parse url
    	$web = parse_url($url);

    	//print_r ($web);
    	//build post string
    	foreach ($data as $i=>$v) {

    	   if ($urlencode == 1){
    		   $postData.= $i . "=" . urlencode($v) . "&";
    		} else {
    		   $postData.= $i . "=" . $v . "&";
          }
    	}


    	// we must append cmd=_notify-validate to the POST string
    	// so paypal know that this is a confirmation post
    	$postData .= "cmd=_notify-validate";

    	//Set the port number
    	if ($web['scheme'] == "https") {
    		$web['port'] = "443";
    		$ssl       = "ssl://";
    	} else {
    		$web['port'] = "80";
    		$ssl       = "";
    	}

    	//print $postData;
    	//Create paypal connection

    	$fp = @fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr,30);

    	//Error checking
    	if(!$fp) {
    		return "$errnum: $errstr -- {$ssl} {$web['host']} {$web['port']}";
    	} else {

          //Post Data
          fputs($fp, "POST {$web['path']} HTTP/1.1\r\n");
          fputs($fp, "Host: {$web['host']}\r\n");
          fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
          fputs($fp, "Content-length: ".strlen($postData)."\r\n");
          fputs($fp, "Connection: close\r\n\r\n");
          fputs($fp, $postData . "\r\n\r\n");

          // loop through the response from the server
          $info = array();
          while (!feof($fp)) {
          	$info[] = @fgets($fp, 1024);
          }

          //close fp - we are done with it
          fclose($fp);

          // join the results into a string separated by comma
          $info = implode("", $info);
    	}
       //print $info;
    	return $info;
    }

    /**
     *
     */
    function getSplitValuesFromArray($arr, $splitChar = '-') {
        $arr1 = array();
        $arr2 = array();
        $arr3 = array();
        foreach ($arr as $val) {
            $val1 = '';
            $val2 = '';
            $val3 = '';

            $arrTemp = explode('-', $val);
            if (count($arrTemp) > 2) {
                list($val1, $val2, $val3) = $arrTemp;
            } else {
                list($val1, $val2) = $arrTemp;
            }
            $arr1[] = $val1;
            $arr2[] = $val2;
            $arr3[] = $val3;
        }

        return array($arr1, $arr2, $arr3);
    }

    /**
     * Get Array as Ul tag
     */
    function getArrayAsUl($arr) {
        $text = '';
        foreach ($arr as $value) {
            $text .= "<li>{$value}</li>\n";
        }

        $text = "<ul>{$text}</ul>\n";
        return $text;
    }

    /**
     *
     * if in the value is not empty then set it to the array
     */
    function getAddToArrayIfSet($arr, $value) {

        if ($value != '') {
            $arr[] = $value;
        }
        return $arr;
    }

    /**
     *
     * @param type $arr
     * @return type
     */
    function getArrayValueAsCommaSeperatedString($arr){
        foreach ($arr as $key => $value) {
            $arr[$key] = "'{$value}'";
        }
        $arrAsStr = implode(',', $arr);
        return $arrAsStr;
    }

    /**
     *
     * get sql where condition from array
     */
    function getWhereConditionFromArr($arr, $dontAddWhere = false) {

        $whereCond = join(' AND ', $arr);
        if ($whereCond != '') {
            $whereCond = 'WHERE ' . $whereCond;
        }

        return $whereCond;
    }

    /**
     *
     */
    function getBoolStr($boolVal) {
        $returnVal = $boolVal ? 'true' : 'false';
        return $returnVal;
    }

    function getISODateStr() {
        return date('Y-m-d');
    }

    function getISODateTimeStr() {
        return date('Y-m-d H:i:s');
    }

    /**
     * Fox ex: if fldName = entry_date then it will return Entry Date
     */
    function getLabelFromFieldName($fldName) {
        $arr = explode('_', $fldName);
        $text = ucwords(join(' ', $arr));
        return $text;
    }

    /**
     * Display no records found message
     */
    function getDisplayListRows($rows, $message = '') {
        $ln = Zend_Registry::get('ln');

        if ($message == '') {
            $message = $ln->gd('cp.message.noRecords');
        }

        $rowsText = $rows;
        if (trim($rows) == '') {
            $rowsText = "
            <div class='list-no-records'>
            <p class='message'>{$message}</p>
            </div>
            ";

        }

        return $rowsText;
    }

    /**
     */
    function hasAnyHtmlTags($text) {
        preg_match_all("/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", $text, $matches, PREG_SET_ORDER);

        if (count($matches) > 0){
            return true;
        } else {
            return false;
        }
    }

    function compressCSS($buffer) {
        /* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        /* remove @charset "UTF-8"; it must be used only once at the top of the file (as the site is breaking in Safari 4 ) */
        $buffer = str_replace('@charset "UTF-8";', '', $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        return $buffer;
    }

    function getGoogleWeatherData($place) {
        //Initialize CURL
        $ch = curl_init();

        $timeout = 0;

        $place = urlencode($place);

        //Set CURL options
        curl_setopt ($ch, CURLOPT_URL, "http://www.google.com/ig/api?weather={$place}");
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $xml_str=curl_exec($ch);
        //close CURL cause we dont need it anymore
        curl_close($ch);

        // Parse the XML response
        $xml = new SimplexmlElement($xml_str);

        $data = array();
        foreach($xml->weather as $item) {
            foreach($item->current_conditions as $new) {
                //For temperature in fahrenheit replace temp_c by temp_f
                $data['current_temperature'] = $new->temp_c['data'];
                $data['current_humidity']    = $new->humidity['data'];
            }

            $data['current_condition'] = $item->forecast_conditions[0]->condition['data'];
            $next_temperature          = $item->forecast_conditions[1]->high['data'];

            //to convert Fahrenheit into Celcius
            $data['next_temperature'] = round(($next_temperature-32)*(5/9));
            $data['next_condition']   = $item->forecast_conditions[1]->condition['data'];
        }

        return $data;
    }

    function getStringType($str) {
        $type = 'text';

        $str = str_replace(',', '', $str);
        if (is_numeric($str)) {
            $type = 'numeric';
        }

        return $type;
    }

    function getClientIPAddress() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

    //=============================================================//
    function getDayArray(){
        $dayArray =
        array(
             "1"
            ,"2"
            ,"3"
            ,"4"
            ,"5"
            ,"6"
            ,"7"
            ,"8"
            ,"9"
            ,"10"
            ,"11"
            ,"12"
            ,"13"
            ,"14"
            ,"15"
            ,"16"
            ,"17"
            ,"18"
            ,"19"
            ,"20"
            ,"21"
            ,"22"
            ,"23"
            ,"24"
            ,"25"
            ,"26"
            ,"27"
            ,"28"
            ,"29"
            ,"30"
            ,"31"
        );

        return $dayArray;
    }

    //=============================================================//
    function getMonthArray(){
        $monthArray =
        array(
             "1"  => "Jan"
            ,"2"  => "Feb"
            ,"3"  => "Mar"
            ,"4"  => "Apr"
            ,"5"  => "May"
            ,"6"  => "Jun"
            ,"7"  => "Jul"
            ,"8"  => "Aug"
            ,"9"  => "Sep"
            ,"10" => "Oct"
            ,"11" => "Nov"
            ,"12" => "Dec"
        );

        return $monthArray;
    }


    /**
     *
     */
    function getWeekDaysArr() {
        $daysArr = array(
             1 => 'Monday'
            ,2 => 'Tuesday'
            ,3 => 'Wednesday'
            ,4 => 'Thursday'
            ,5 => 'Friday'
            ,6 => 'Saturday'
            ,7 => 'Sunday'
        );

        return $daysArr;
    }

    /**
     *
     * @param <type> $selectedValue
     * @param <type> $fromYear
     * @param <type> $toYear
     * @param <type> $sortOrder
     * @return <type>
     */
    function getYearListArray($fromYear = 1930, $toYear ="" , $sortOrder = "asc") {
       if ($toYear == ""){
          $toYear  = date("Y");
       }


       $yearArray = array();
       for($i=0 ; $i < ($toYear - $fromYear)+1 ; $i++){
         $yearArray[$i] =  $fromYear + $i;
       }

       if($sortOrder != "asc"){
          rsort($yearArray);
       }

       return $yearArray;
    }

    /**
     *
     * @param SimpleXMLElement $xml
     * @param string $attributesKey
     * @param string $childrenKey
     * @param string $valueKey
     * @return type
     */
    function simpleXMLToArray(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null){

        if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';}
        if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';}
        if($valueKey && !is_string($valueKey)){$valueKey = '@values';}

        $return = array();
        $name = $xml->getName();
        $_value = trim((string)$xml);
        if(!strlen($_value)){$_value = null;};

        if($_value!==null){
            if($valueKey){$return[$valueKey] = $_value;}
            else{$return = $_value;}
        }

        $children = array();
        $first = true;
        foreach($xml->children() as $elementName => $child){
            $value = $this->simpleXMLToArray($child,$attributesKey, $childrenKey,$valueKey);
            if(isset($children[$elementName])){
                if(is_array($children[$elementName])){
                    if($first){
                        $temp = $children[$elementName];
                        unset($children[$elementName]);
                        $children[$elementName][] = $temp;
                        $first=false;
                    }
                    $children[$elementName][] = $value;
                }else{
                    $children[$elementName] = array($children[$elementName],$value);
                }
            }
            else{
                $children[$elementName] = $value;
            }
        }
        if($children){
            if($childrenKey){$return[$childrenKey] = $children;}
            else{$return = array_merge($return,$children);}
        }

        $attributes = array();
        foreach($xml->attributes() as $name=>$value){
            $attributes[$name] = trim($value);
        }
        if($attributes){
            if($attributesKey){$return[$attributesKey] = $attributes;}
            else{$return = array_merge($return, $attributes);}
        }

        return $return;
    }

    /*
    * James Earlywine - July 20th 2011
    *
    * Translates a jagged associative array
    * to XML
    *
    * @param : $theArray - The jagged Associative Array
    * @param : $tabCount - for persisting tab count across recursive function calls
    */
    function associativeArrayToXML ($theArray, $tabCount=2) {
        //echo "The Array: ";
        //var_dump($theArray);
        // variables for making the XML output easier to read
        // with human eyes, with tabs delineating nested relationships, etc.

        $tabCount++;
        $tabSpace = "";
        $extraTabSpace = "";
        for ($i = 0; $i<$tabCount; $i++) {
            $tabSpace .= "\t";
        }

        for ($i = 0; $i<$tabCount+1; $i++) {
            $extraTabSpace .= "\t";
        }

        $theXML = '';
        // parse the array for data and output xml
        foreach($theArray as $tag => $val) {
            if (!is_array($val)) {
                $theXML .= PHP_EOL.$tabSpace.'<'.$tag.'>'.htmlentities($val).'</'.$tag.'>';
            } else {
                $tabCount++;
                $theXML .= PHP_EOL.$extraTabSpace.'<'.$tag.'>'.$this->associativeArrayToXML($val, $tabCount);
                $theXML .= PHP_EOL.$extraTabSpace.'</'.$tag.'>';
            }
        }

        return $theXML;
    }

    function parseEmailListToArray($list) {
        $t = str_getcsv($list);

        foreach($t as $k => $v) {
            if (strpos($v,',') !== false) {
                $t[$k] = '"'.str_replace(' <','" <',$v);
            }
        }

        foreach ($t as $addr) {
            if (strpos($addr, '<')) {
                preg_match('!(.*?)\s?<\s*(.*?)\s*>!', $addr, $matches);
                $emails[] = array(
                    'email' => $matches[2],
                    'name' => $matches[1]
                );
            } else {
                $emails[] = array(
                    'email' => $addr,
                    'name' => ''
                );
            }
        }

        return $emails;
    }

    function isWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        } else {
            return false;
        }
    }

    function callScriptInBackground($phpScript, $params, $phpPath = '') {
        if ($this->isWindows()) {
            $WshShell = new COM('WScript.Shell');

            $cmdline = "C:/xampp/php/php.exe " . $phpScript . ' ' . $params;
            $runInFG = false;
            $WshShell->Run($cmdline, 0, $runInFG);
        } else {
            if ($phpPath == '') {
                $phpPath = '/usr/bin/php';
            }
            $cmdline = $phpPath . ' ' . $phpScript . ' "' . $params . '"';

             $output = '';
            exec($cmdline . ' > /dev/null 2>&1 &', $output);

            //exec($cmdline . ' > /dev/null 2>&1 &');
        }
        //print $cmdline;
    }

    function objectToArray($data) {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->objectToArray($value);
            }
            return $result;
        }
        return $data;
    }

    function getMemoryUsage() {
        $mem = (int)(memory_get_usage()/1024) . 'KB';

        return $mem;
    }


    function ceilWithSignificance($number, $significance = 1){
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }


    function getPlural($amount, $singular = '', $plural = 's') {
        if ($amount == 0 || $amount == 1){
            return $singular;
        } else {
            return $plural;
        }
    }

    /**
     *   echo convertNumberToWords(123456789);
     *   one hundred and twenty-three million, four hundred and fifty-six thousand, seven hundred and eighty-nine
     *
     *   echo convertNumberToWords(123456789.123);
     *   one hundred and twenty-three million, four hundred and fifty-six thousand, seven hundred and eighty-nine point one two three
     *
     *   echo convertNumberToWords(-1922685.477);
     *   negative one million, nine hundred and twenty-two thousand, six hundred and eighty-five point four seven seven
     *
     * @param type $number
     * @return string
     */
    function convertNumberToWords($number) {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }

}