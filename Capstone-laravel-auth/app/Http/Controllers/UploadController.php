<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;
//use PhpOffice\PhpSpreadsheet\Spreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UploadController extends Controller
{
    const PUBLIC_UPLOADS = './public/uploads/';
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    //上传文件 功能实现方法
    public function upload(Request $request)
    {
        if ($request->isMethod('POST')) {
            if(isset($_POST['upload'])){
              $file = $request->file('source');
              //判断文件是否上传成功
              if ($file->isValid()) {
                  //原文件名
                  //$originalName = $file->getClientOriginalName();
                  //文件后缀
                  $txtfile = array('txt');
                  $excelfile = array('xls', 'xlsx');
                  //扩展名
                  $ext = $file->getClientOriginalExtension();
                  //MimeType
                  //$type = $file->getClientMimeType();
                  //是否是要求的文件
                  $isTxtFile = in_array($ext, $txtfile);
                  $isExcelFile = in_array($ext, $excelfile);
                  $realPath = $file->getRealPath();
                  if ($isTxtFile) {
                      $this->saveFiles_1($realPath, $ext);
                  } elseif ($isExcelFile){
                      $this->whetherRightExcelFile($realPath, $ext);
                  } else {
                      echo '<script>alert("Wrong Format!")</script>';
                  }
              }
            }
            elseif(isset($_POST['submit'])){
                $Reporting_Registrant_Number = $_POST['num'];
                $Transaction_Date = $_POST['date'];
                if($_POST['num'] == "" || $_POST['date'] == ""){
                    echo '<script>alert("If there is no sale, please input your information!")</script>';
                }
                elseif(isset($_POST['sale'])== 'sale'){
                    DB::table('arcos')->insert([
                        'Reporting_Registrant_Number' => $Reporting_Registrant_Number,
                        'Transaction_Date' => $Transaction_Date,]);
                    echo '<script>alert("Uploaded Information Success!")</script>';
                }else{
                    echo '<script>alert("If there is no sale, please check the sale box!")</script>';
                }
          }
      }
        return view('upload');
    }

    public function whetherRightExcelFile($realPath, $ext){
        //$realPath = $file->getRealPath();
        $filename = date('Y-m-d-h-i-s') . '-' . uniqid() . '.' . $ext;
        $this->originalUploads($filename, $realPath);
        if($ext == 'xlsx'){
            $ext = 'Xlsx';
        }else{
            $ext = 'Xls';
        }

        //$excelReader = PHPExcel_IOFactory::createReaderForFile($realPath);
        // Create a new Reader of the type that has been identified
        $excelReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($ext);
        $excelObj = $excelReader->load($realPath);
        $worksheet = $excelObj->getSheet(0);
        $lastRow = $worksheet->getHighestRow();
        $br="<br/>";

        $array = array('A2'=>"ReportingRegistrantNumber", 'M2'=>"LastDayofReportingPeriod", 'X2'=>"CentralReporter'sRegistrationNumber(ifapplicable)",
            'Q4'=>"2",'A8'=>"TRNSCODE",'C8'=>"NATIONALDRUGCODE", 'U8'=>"ASSOCIATEREGISTRATIONNUMBER",'AD8'=>"DEAORDERFORMNUMBER",
            'AY8'=>"TRANSACTIONDATE", 'BG8'=>"TRANSACTIONIDENTIFIER");

        foreach ($array as $x => $x_value) {
            $cellvalue = $worksheet->getCell($x)->getValue();
            $cellvalue = preg_replace('/\s(?=)/', '', $cellvalue);
            $array[$x] = $cellvalue == $x_value;;
        }
        //    print_r($array);

        if($array["A2"] && $array["M2"] && $array["X2"] && $array["Q4"] && $array["A8"] && $array["C8"] && $array["U8"] &&
            $array["AD8"] && $array["AY8"] && $array["BG8"]) {
            //        echo "success";
            $fullpath = $this->excelToTxt($excelReader, $excelObj, $realPath);
            $this->saveFiles_1($fullpath, 'txt');
            //echo '<script>alert("Uploaded File Success!")</script>';
        }
        else{
            $this->wrongFormat($filename, $realPath);
            //echo '<script>alert("Uploaded File Failed!")</script>';
        }
    }

    public function saveFiles_1($realPath, $ext){
        //临时绝对路径
        //$realPath = $file->getRealPath();
        $filename = date('Y-m-d-h-i-s') . '-' . uniqid() . '.' . $ext;
        $this->originalUploads($filename, $realPath);
        $fn = fopen(public_path('uploads/'.date('Ymd').'/'.$filename), "r");

        $lNum = 0;
        while(! feof($fn))  {
            $lNum ++;
            $result = fgets($fn);
            $result = trim($result);
            $len = strlen($result);

            $Reporting_Registrant_Number = substr($result, 0, 9);
            $Transaction_Code = substr($result, 9, 1);
            $ActionIndicator = substr($result, 10, 1);
            $National_Drug_Code = substr($result, 11, 11);
            $Quantity = substr($result, 22, 8);
            $Unit = substr($result, 30, 1);
            $Associate_Registrant_Number = substr($result, 31, 9);
            $DEA_Number = substr($result, 40, 9);
            $Transaction_Date = substr($result, 49, 8);
            $Correction_Number = substr($result, 57, 8);
            $Strength = substr($result, 65, 4);
            $Transaction_Identifier = substr($result, 69, 10);

            if($lNum == 2 && $Transaction_Code == "7"){
              $this->rightFormat($filename, $realPath);
              return;
            }else{
              if($lNum != 1 ){
                  if($len != 79 && $len != 0){
                      $this->wrongFormat($filename, $realPath);
                      return;
                  }
                  if($len == 79){
                      if(!(ctype_alnum($Reporting_Registrant_Number) && ctype_alnum($Transaction_Code) && ctype_alnum($National_Drug_Code) && is_numeric($Quantity)
                          && ctype_alnum($Associate_Registrant_Number) && is_numeric($Transaction_Date) && is_numeric($Transaction_Identifier)))
                      {
                          $this->wrongFormat($filename, $realPath);
                          return;
                      }
                      if (!((ctype_alnum($ActionIndicator) || trim($ActionIndicator) == "") && (ctype_alnum($Unit) || trim($Unit) == "")
                          && (ctype_alnum($DEA_Number) || trim($DEA_Number) == "") && (ctype_alnum($Correction_Number) || trim($Correction_Number) == "") && (ctype_alnum($Strength) || trim($Strength) == "")))
                      {
                          $this->wrongFormat($filename, $realPath);
                          return;
                      }
                  }
              }
          }
        }

        $bool = Storage::disk('uploadsRightFromat')->put($filename, file_get_contents($realPath));
        //判断是否上传成功
        if ($bool) {
            $insertNumber = $this->insertAll($filename);
            echo '<script> alert("success upload the file and '.$insertNumber.' records has been insert")</script>';
        } else {
            echo '<script>alert("Uploaded Failed!")</script>';
        }

    }

    public function rightFormat($filename, $realPath){
        $bool = Storage::disk('uploadsRightFromat')->put($filename, file_get_contents($realPath));
        //判断是否上传成功
        if ($bool) {
            $fn = fopen(public_path('succeed_files/'.date('Ymd').'/'.$filename), "r");
            $lNum = 0;
            while(! feof($fn)) {
                $lNum++;
                $trueResult = fgets($fn);
                $trueResult = trim($trueResult);

                if ($lNum != 1) {
                    $Reporting_Registrant_Number = substr($trueResult, 0, 9);
                    $Transaction_Code = substr($trueResult, 9, 1);

                    DB::table('arcos')->insert([
                        'Reporting_Registrant_Number' => $Reporting_Registrant_Number,
                        'Transaction_Code' => $Transaction_Code,]);
                }
            }
            echo '<script>alert("Uploaded File Success!")</script>';
        } else {
            echo '<script>alert("Uploaded Failed!")</script>';
        }
    }

    public function wrongFormat($filename, $realPath){
        $bool = Storage::disk('uploadsWrongFromat')->put($filename, file_get_contents($realPath));
        //判断是否上传成功
        if ($bool) {
            echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
        } else {
            echo 'fail';
        }
    }

    public function originalUploads($filename, $realPath){
        $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
        //判断是否上传成功
        if (!$bool) {
            echo 'fail';
        }
    }

    public function insertAll($filename){
        $fn = fopen(public_path('succeed_files/'.date('Ymd').'/'.$filename), "r");
        $lNum = 0;
        $insertNumber = 0;
        while(! feof($fn)) {
            $lNum++;
            $trueResult = fgets($fn);
            $trueResult = trim($trueResult);
//                $len = strlen($trueResult);

            if ($lNum != 1) {
                $Reporting_Registrant_Number = substr($trueResult, 0, 9);
                $Transaction_Code = substr($trueResult, 9, 1);
                $ActionIndicator = substr($trueResult, 10, 1);
                $National_Drug_Code = substr($trueResult, 11, 11);
                $Quantity = substr($trueResult, 22, 8);
                $Unit = substr($trueResult, 30, 1);
                $Associate_Registrant_Number = substr($trueResult, 31, 9);
                $DEA_Number = substr($trueResult, 40, 9);
                $Transaction_Date = substr($trueResult, 49, 8);
                $Correction_Number = substr($trueResult, 57, 8);
                $Strength = substr($trueResult, 65, 4);
                $Transaction_Identifier = substr($trueResult, 69, 10);


                $insert_bool = DB::table('arcos')->insert([
                    'Reporting_Registrant_Number' => $Reporting_Registrant_Number,
                    'Transaction_Code' => $Transaction_Code,
                    'ActionIndicator' => $ActionIndicator,
                    'National_Drug_Code' => $National_Drug_Code,
                    'Quantity' => $Quantity,
                    'Unit' => $Unit,
                    'Associate_Registrant_Number' => $Associate_Registrant_Number,
                    'DEA_Number' => $DEA_Number,
                    'Transaction_Date' => $Transaction_Date,
                    'Correction_Number' => $Correction_Number,
                    'Strength' => $Strength,
                    'Transaction_Identifier' => $Transaction_Identifier,]);
                if ($insert_bool) {
                    $insertNumber++;
                }
            }
        }
        return $insertNumber;
    }

    //change null to " " in an array
    function nulltostr($arr){
        if($arr !== null){
            if(is_array($arr)){
                if(!empty($arr)){
                    foreach($arr as $key => $value){
                        if($value === null){
                            $arr[$key] = ' ';
                        }else{
                            $arr[$key] = $this->nulltostr($value);
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
                            $arr[$key] = $this->nulltostr($value);
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
                            $arr[$key] = $this->nulltostr($value);
                        }
                    }
                }else{ $arr = '0'; }
            }else{
                if($arr === " "){ $arr = '0'; }
            }
        }else{ $arr = '0'; }
        return $arr;
    }

    function excelToTxt($objReader, $objPHPExcel, $fullpath){

        $objReader->setReadDataOnly(TRUE);
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
            $Transaction_Code = $this->nulltostr($Transaction_Code);

            $ActionIndicator = $objWorksheet->getCell("B" . $row)->getValue();
            $ActionIndicator = $this->nulltostr($ActionIndicator);

            for ($col = 'C'; $col != 'N'; ++$col) {
                $National_Drug_Code[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $National_Drug_Code = $this->nulltostr($National_Drug_Code);

            for ($col = 'N'; $col != 'T'; ++$col) {
                $Quantity[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            array_unshift($Quantity, 0, 0);
            $Quantity = $this->nulltostr($Quantity);
            $Quantity = $this->strtoo($Quantity);

            $Unit = $objWorksheet->getCell("T" . $row)->getValue();
            $Unit = $this->nulltostr($Unit);

            for ($col = 'U'; $col != 'AD'; ++$col) {
                $Associate_Registrant_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Associate_Registrant_Number = $this->nulltostr($Associate_Registrant_Number);

            for ($col = 'AD'; $col != 'AM'; ++$col) {
                $DEA_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $DEA_Number = $this->nulltostr($DEA_Number);

            for ($col = 'AY'; $col != 'BG'; ++$col) {
                $Transaction_Date[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Transaction_Date = $this->nulltostr($Transaction_Date);

            for ($col = 'AM'; $col != 'AU'; ++$col) {
                $Correction_Number[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Correction_Number = $this->nulltostr($Correction_Number);

            for ($col = 'AU'; $col != 'AY'; ++$col) {
                $Strength[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $Strength = $this->nulltostr($Strength);

            for ($col = 'BG'; $col != 'BL'; ++$col) {
                $Transaction_Identifier[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            array_unshift($Transaction_Identifier, 0, 0, 0, 0, 0);
            $Transaction_Identifier = $this->nulltostr($Transaction_Identifier);


            $secondline = array_merge((array)$Reporting_Registrant_Number, (array)$Transaction_Code, (array)$ActionIndicator, (array)$National_Drug_Code, (array)$Quantity,
                (array)$Unit, (array)$Associate_Registrant_Number, (array)$DEA_Number, (array)$Transaction_Date, (array)$Correction_Number,
                (array)$Strength, (array)$Transaction_Identifier);
            $sl = implode($secondline) . "\n";

            fwrite($myfile, $sl);
        }
        fclose($myfile);
        return $fullpath;
    }


}
