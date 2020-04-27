<?php
require_once "Classes/PHPExcel.php";

  if($_POST['submit']){
    $Reporting_Registrant_Number = $_POST['num'];
    $Transaction_Date = $_POST['date'];
    if($_POST['num'] == "" || $_POST['date'] == ""){
      echo '<script>alert("If there is no sale, please input your information!")</script>';
    }
    elseif($_POST['sale'] == 'sale'){
      echo '<script>alert("Uploaded Information Success!")</script>';
    }else{
      echo '<script>alert("If there is no sale, please check the sale box!")</script>';
    }
  }
  if($_POST['upload']){
    $file=$_FILES['file'];
    $fileName="./uploadedfiles/".$file['name'];
    move_uploaded_file($file['tmp_name'],$fileName);

    if(pathinfo($fileName, PATHINFO_EXTENSION) == "txt"){

      $fn = fopen($fileName,"r");
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

          #no sale report
          if($lNum == 2 && $Transaction_Code == "7"){
            $finalfileName="./succeed_files/".$file['name'];
            copy($fileName,$finalfileName);
            echo '<script>alert("Uploaded File Success!")</script>';
          }
          else{
            if($lNum != 1 ){

              if($len != 79 && $len != 0){

                $finalfileName="./failed_files/".$file['name'];

                copy($fileName,$finalfileName);

                //echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
              }
              if($len == 79){
                  if(!(ctype_alnum($Reporting_Registrant_Number) && ctype_alnum($Transaction_Code) && ctype_alnum($National_Drug_Code) && is_numeric($Quantity)
                    && ctype_alnum($Associate_Registrant_Number) && is_numeric($Transaction_Date) && ctype_alnum($Strength) && is_numeric($Transaction_Identifier)))
                    {
                        $finalfileName="./failed_files/".$file['name'];

                        copy($fileName,$finalfileName);

                        //echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
                    }
                  if (!((ctype_alnum($ActionIndicator) || trim($ActionIndicator) == "") && (ctype_alnum($Unit) || trim($Unit) == "")
                            && (ctype_alnum($DEA_Number) || trim($DEA_Number) == "") && (ctype_alnum($Correction_Number) || trim($Correction_Number) == "")))
                    {
                        $finalfileName="./failed_files/".$file['name'];

                        copy($fileName,$finalfileName);

                        //echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
                    }
              }
            }
        }
      }

        if(file_exists("./failed_files/".$file['name'])){
            echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
        }
        elseif(!file_exists("./succeed_files/".$file['name'])){
            $finalfileName="./succeed_files/".$file['name'];
            copy($fileName,$finalfileName);
            echo '<script>alert("Uploaded File Success!")</script>';
        }

        fclose($fn);
  }
  elseif(pathinfo($fileName, PATHINFO_EXTENSION) == "xls"||pathinfo($fileName, PATHINFO_EXTENSION) == "xlsx"){

  //    $tmpfname = "test.xlsx";
      $excelReader = PHPExcel_IOFactory::createReaderForFile($fileName);
      $excelObj = $excelReader->load($fileName);
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
      $finalfileName="./succeed_files/".$file['name'];
      copy($fileName,$finalfileName);
      echo '<script>alert("Uploaded File Success!")</script>';
      }
      else{
          echo "fail";
          $finalfileName="./failed_files/".$file['name'];
          copy($fileName,$finalfileName);
          echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
      }
  }
  else{
      $finalfileName="./failed_files/".$file['name'];

      copy($fileName,$finalfileName);
      echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
  }
}
  //$fileName="./uploadedfiles/".$file['name'];

  //move_uploaded_file($file['tmp_name'],$fileName);

?>
