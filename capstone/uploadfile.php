<?php

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


      if(file_exists("./failed_files/".$file['name'])){
          echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
      }
      else{
          $finalfileName="./succeed_files/".$file['name'];
          copy($fileName,$finalfileName);
          echo '<script>alert("Uploaded File Success!")</script>';
      }

      fclose($fn);
}
else{
    $finalfileName="./failed_files/".$file['name'];

    copy($fileName,$finalfileName);
    echo '<script>alert("Thanks for uploading your report. Workers will check the file later!")</script>';
}
  //$fileName="./uploadedfiles/".$file['name'];

  //move_uploaded_file($file['tmp_name'],$fileName);

?>
