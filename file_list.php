<?php

use App\classes\Upload; 
require_once "App/classes/Upload.php";

$Upload = new Upload;
$files = $Upload->useConnection();
$Upload->Download();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="CSS/main.css">
  <title>List of files</title>
</head> 
<body>

  <table>

  <tr>
    <th>File Name</th>
    <th>Upload Date</th>
  </tr>

  <?php 
    foreach($files as $data){
      $dir = dirname(__FILE__) . '/App/classes/files/';
      if(in_array($data['hash'],scandir($dir))){
  ?>

  <tr>
    <td>
      <a href="?file_id=<?php echo $data['id']?>"><?php echo $data['file_name']?></a>
      <img src="<?php echo 'App/classes/files/' . $data['hash']?>">
    </td>
    <td><?php echo $data['created']?></td>
  </tr>

  <?php
      } else {
        echo "the file not found <br>";
      }
    }
  ?>
  </table>

</body>
</html>