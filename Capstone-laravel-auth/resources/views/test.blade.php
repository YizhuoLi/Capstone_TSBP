<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Public Website</title>
</head>
<body>
<h1>Welcome!</h1>
<p>
    You are logged in to the Texas Wholesale Distributor Database Reporting website.  Please upload files in the ARCOS format.
<form action="uploadfile.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" />
    <input type="submit" value="Upload!">
</form>
</body>
</html>
