<!DOCTYPE html>
<html>
<div class="panel panel-default">
    <div class="panel-heading">Welcome</div>
    <div class="panel-body">

        <form class="form-horizontal" role="form" method="POST" action=""
              οnsubmit="return startUploading();" enctype="multipart/form-data">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="file" class="col-md-4 control-label">
                    You are logged in to the Texas Wholesale Distributor Database Reporting website.
                    Please upload files in the ARCOS format.
                </label>

                <div class="col-md-6">
                    <div><label for="choose_file">Please Choose a file: </label></div>
                    <div><input id="file" type="file" class="form-control" name="source" οnchange="fileChange(this);"></div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
<!--                    <button type="submit" class="btn btn-primary">-->
<!--                        <i class="fa fa-btn fa-sign-in"></i> upload-->
<!--                    </button>-->
                    <input type="submit" name = "upload" value="Upload" />
                </div>
            </div>

            <div class="form-group">
                <label for="file" class="col-md-4 control-label">
                    If you do not have sale, please input the following information.
                </label>

                <div class="col-md-6">
                  Registrant Number: <input type="text" name="num" /><br />
                  Transaction Date: <input type="text" name="date" /><br />
                  No Sale? <input id = "sale" type="checkbox" name="sale" value = "sale"/>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <input type="submit" name = "submit" value="Submit" />
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    // not in use, but will put here for future use.
    function startUploading(){
        var isEmpty = document.getElementById('file');
        if(isEmpty.value.length == 0){
            alert("Please choose file");
            return false;
        }else{
            return true;
        }
    }

    function fileChange(target) {
        var fileSize = 0;
        fileSize = target.files[0].size;
        var size = fileSize / 1024;
        if (size > 1000) {
            alert("should smaller than 1M");
            target.value = "";
            return false;   //block submit
        }
        var name = target.value;
        var fileName = name.substring(name.lastIndexOf(".") + 1).toLowerCase();
        if (fileName != "jpg" && fileName != "jpeg" && fileName != "pdf" && fileName != "png" && fileName != "dwg" && fileName != "gif" && fileName != "xls" && fileName != "xlsx" && fileName != "word" && fileName != "doc" && fileName != "docx" && fileName != "txt") {
            alert("choose picture please(jpg,png,gif,dwg,pdf,gif等)！");
            target.value = "";
            return false;   //block submit
        }
    }
</script>
</html>
