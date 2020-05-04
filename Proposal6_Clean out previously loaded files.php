<?php
  #Feel free to change the path of directory and delete files.
  $dir = "./uploadedfiles/";
  function deldir($dir) {
   $dh=opendir($dir);
   while ($file=readdir($dh)) {
      if($file!="." && $file!="..") {
         $fullpath=$dir."/".$file;
         if(!is_dir($fullpath)) {
            unlink($fullpath);
         } else {
            deldir($fullpath);
         }
      }
   }
}
deldir($dir);
echo '<script>alert("The uploaded files have been deleted!")</script>';

?>
