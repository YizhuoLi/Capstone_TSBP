<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

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
                  if ($isTxtFile) {
                      $this->saveFiles_1($file, $ext);
                  } else {
                      echo '<script>alert("Wrong Format!")</script>';

                  }
              }
          }elseif(isset($_POST['submit'])){
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
    public function saveFiles_1($file, $ext){
        //临时绝对路径
        $realPath = $file->getRealPath();
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
                          && ctype_alnum($Associate_Registrant_Number) && is_numeric($Transaction_Date) && ctype_alnum($Strength) && is_numeric($Transaction_Identifier)))
                      {
                          $this->wrongFormat($filename, $realPath);
                          return;
                      }
                      if (!((ctype_alnum($ActionIndicator) || trim($ActionIndicator) == "") && (ctype_alnum($Unit) || trim($Unit) == "")
                          && (ctype_alnum($DEA_Number) || trim($DEA_Number) == "") && (ctype_alnum($Correction_Number) || trim($Correction_Number) == "")))
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

}
