<?php   
    use App\classes\Upload; 
    use App\classes\Session;
    use App\classes\CsrfMiddleware;

    require_once "vendor/autoload.php";

    session_start();
    
    if(!empty($_FILES['file'])){
        $files = $_FILES['file'];
        $upload_obj = new Upload;    
        $upload_obj->upload_test($files);
    }   
    
    Session::add("username" , "Ahmed Abdel-Fattah");
    echo Session::get("username");

    echo (CsrfMiddleware::check("username")) ? "<br> Ahmed Yes" : "<br> Ahmed No";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/main.css">
    <title>Upload files by php oop</title>
</head>
<body>
    
    <form action="<?= htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES , 'UTF-8');?>" method="POST" enctype="multipart/form-data">
        <label for="">Upload your files: </label>
        <input type="file" name="file[]" multiple=''>
        <input type="submit" value="Upload">
    </form>

</body>
</html>