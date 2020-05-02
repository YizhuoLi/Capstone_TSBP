<?php
error_reporting(0);
require_once "Classes/PHPExcel.php";
$filePath  = array();

//change null to " " in an array
function nulltostr($arr){
    if($arr !== null){
        if(is_array($arr)){
            if(!empty($arr)){
                foreach($arr as $key => $value){
                    if($value === null){
                        $arr[$key] = ' ';
                    }else{
                        $arr[$key] = nulltostr($value);
                    }
                }
            }else{ $arr = ' '; }
        }else{
            if($arr === null){ $arr = ' '; }
        }
    }else{ $arr = ' '; }
    return $arr;
}

//change null to 0 in an array
function nulltoo($arr){
    if($arr !== null){
        if(is_array($arr)){
            if(!empty($arr)){
                foreach($arr as $key => $value){
                    if($value === null){
                        $arr[$key] = '0';
                    }else{
                        $arr[$key] = nulltostr($value);
                    }
                }
            }else{ $arr = '0'; }
        }else{
            if($arr === null){ $arr = '0'; }
        }
    }else{ $arr = '0'; }
    return $arr;
}

//change " " to 0 in an array
function strtoo($arr){
    if($arr !== " "){
        if(is_array($arr)){
            if(!empty($arr)){
                foreach($arr as $key => $value){
                    if($value === " "){
                        $arr[$key] = '0';
                    }else{
                        $arr[$key] = nulltostr($value);
                    }
                }
            }else{ $arr = '0'; }
        }else{
            if($arr === " "){ $arr = '0'; }
        }
    }else{ $arr = '0'; }
    return $arr;
}

//find file path for all files in one dictionary
function traverse($path = '.'){
    global $filePath;
    $current_dir = opendir($path);
    while (($file = readdir($current_dir)) !== false) {
        $sub_dir = $path . DIRECTORY_SEPARATOR . $file;
        if ($file == '.' || $file == '..') {
            continue;
        } else if (is_dir($sub_dir)) {
            traverse($sub_dir);
        } else {
            $filePath[$path . '/' . $file] = $path . '/' . $file;
        }
    }
    return $filePath;
}

// File path may need to change accordingly
$array = traverse("./succeed_files/");

foreach ($array as $key => $fullpath) {
    if (pathinfo($fullpath, PATHINFO_EXTENSION) == "xls" || pathinfo($fullpath, PATHINFO_EXTENSION) == "xlsx") {

        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($fullpath);
        $objWorksheet = $objPHPExcel->getActiveSheet();

// Get the highest row number and column letter referenced in the worksheet
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $br = "<br/>";

        $Reporting_Registrant_Number = array();
        $Reporting_Period = array();

        for ($col = 'A'; $col != 'J'; ++$col) {
            $Reporting_Registrant_Number[] = $objWorksheet->getCell($col . 4)->getValue();
        }

        $Asterisk = $objWorksheet->getCell("K4")->getValue();

        for ($col = 'M'; $col != 'U'; ++$col) {
            $Reporting_Period[] = $objWorksheet->getCell($col . 4)->getValue();
        }

        $Reporting_Frequency = $objWorksheet->getCell("V4")->getValue();

        $firstline = array_merge((array)$Reporting_Registrant_Number, (array)$Asterisk, (array)$Reporting_Period, (array)$Reporting_Frequency);
        $str = implode($firstline) . "\n";


        $fullpath = str_replace('.xls', '.xlsx', $fullpath);
        $fullpath = str_replace('.xlsxx', '.xlsx', $fullpath);
        $fullpath = str_replace('.xlsx', ".txt", $fullpath);

        $myfile = fopen($fullpath, "w") or die("Unable to open file!");
        fwrite($myfile, $str);

        for ($row = 12; $row <= $highestRow; ++$row) {

            $National_Drug_Code = array();
            $Quantity = array();
            $Associate_Registrant_Number = array();
            $DEA_Number = array();
            $Transaction_Date = array();
            $Correction_Number = array();
            $Strength = array();
            $Transaction_Identifier = array();

            $Transaction_Code = $objWorksheet->getCell("A" . $row)->getValue();
            $Transaction_Code = nulltostr($Transaction_Code);

            $ActionIndicator = $objWorksheet->getCell("B" . $row)->getValue();
            $ActionIndicator = nulltostr($ActionIndicator);

            for ($col = 'C'; $col != 'N'; ++$col) {
                $National_Drug_Code[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $National_Drug_Code = nulltostr($National_Drug_Code);

            for ($col = 'N'; $col != 'T'; ++$col) {
                $Quantity[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            array_unshift($Quantity, 0, 0);
            $Quantity = nulltostr($Quantity);
            $Quantity = strtoo($Quantity);

            $Unit = $objWorksheet->getCell("T" . $row)->getValue();
            $Unit = nulltostr($Unit);

            for ($col = 'U'; $col != 'AD'; ++$col) {
                $Associate_Registrant_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Associate_Registrant_Number = nulltostr($Associate_Registrant_Number);

            for ($col = 'AD'; $col != 'AM'; ++$col) {
                $DEA_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $DEA_Number = nulltostr($DEA_Number);

            for ($col = 'AY'; $col != 'BG'; ++$col) {
                $Transaction_Date[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Transaction_Date = nulltostr($Transaction_Date);

            for ($col = 'AM'; $col != 'AU'; ++$col) {
                $Correction_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Correction_Number = nulltostr($Correction_Number);

            for ($col = 'AU'; $col != 'AY'; ++$col) {
                $Strength[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Strength = nulltostr($Strength);

            for ($col = 'BG'; $col != 'BL'; ++$col) {
                $Transaction_Identifier[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            array_unshift($Transaction_Identifier, 0, 0, 0, 0, 0);
            $Transaction_Identifier = nulltostr($Transaction_Identifier);


            $secondline = array_merge((array)$Reporting_Registrant_Number, (array)$Transaction_Code, (array)$ActionIndicator, (array)$National_Drug_Code, (array)$Quantity,
                (array)$Unit, (array)$Associate_Registrant_Number, (array)$DEA_Number, (array)$Transaction_Date, (array)$Correction_Number,
                (array)$Strength, (array)$Transaction_Identifier);
            $sl = implode($secondline) . "\n";

            fwrite($myfile, $sl);
        }
        fclose($myfile);
    }
}
?>