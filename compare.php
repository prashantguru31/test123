<?php

$msg = "";
if (($_POST)){
  require 'vendor/autoload.php';

  $bucketName = 'cursorekognition-ioticos';
  $IAM_KEY = 'AKIAJTF72POOCRFNZNXA';
  $IAM_SECRET = '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';

  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
      echo "File is an image - " . $check["mime"] . ".";
      $uploadOk = 1;
    } else {
      echo "File is not an image.";
      $uploadOk = 0;
    }
  }
  // Check if file already exists
  if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
  }
  // Check file size
  if ($_FILES["fileToUpload"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
  }
  // Allow certain file formats
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }
echo "<br>";
  echo $filename = "uploads/".$_FILES["fileToUpload"]["name"];

  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES["fileToUpload2"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload2"]["tmp_name"]);
    if($check !== false) {
      echo "File is an image - " . $check["mime"] . ".";
      $uploadOk = 1;
    } else {
      echo "File is not an image.";
      $uploadOk = 0;
    }
  }
  // Check if file already exists
  if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
  }
  // Check file size
  if ($_FILES["fileToUpload2"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
  }
  // Allow certain file formats
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload2"]["tmp_name"], $target_file)) {
      echo "The file ". basename( $_FILES["fileToUpload2"]["name"]). " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }
echo "<br>";
echo  $filename2 = "uploads/".$_FILES["fileToUpload2"]["name"];
echo "<br>";


  $client = new Aws\Rekognition\RekognitionClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
      'key' => 'AKIAJTF72POOCRFNZNXA',
      'secret' => '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',
    ]
  ]);

  try {
    $results = $client->compareFaces([
      'SimilarityThreshold' => 70,
      'SourceImage' => [
        'Bytes' => file_get_contents($filename),
      ],
      'TargetImage' => [
        'Bytes' => file_get_contents($filename2),
      ],
    ]);
  } catch (\Exception $e) {
    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
  }


  $source_image = array();
  $faces_matches = array();
  $faces_unmatches = array();
  $count_matches=0;
  $count_unmatches=0;

  //echo "<pre>";
  //print_r($results);
  //echo "</pre>";

    if (isset($results['SourceImageFace']['BoundingBox'])){
      $source_image['confidence'] = $results['SourceImageFace']['Confidence'];
      $source_image['box']['width'] = $results['SourceImageFace']['BoundingBox']['Width'];
      $source_image['box']['height'] = $results['SourceImageFace']['BoundingBox']['Height'];
      $source_image['box']['left'] = $results['SourceImageFace']['BoundingBox']['Left'];
      $source_image['box']['top'] = $results['SourceImageFace']['BoundingBox']['Top'];
    }

    if (isset($results['FaceMatches'])){
      foreach ($results['FaceMatches'] as $matches) {
        $faces_matches[$count_matches]['box']['width'] = $matches['Face']['BoundingBox']['Width'];
        $faces_matches[$count_matches]['box']['height'] = $matches['Face']['BoundingBox']['Height'];
        $faces_matches[$count_matches]['box']['left'] = $matches['Face']['BoundingBox']['Left'];
        $faces_matches[$count_matches]['box']['top'] = $matches['Face']['BoundingBox']['Top'];
        $faces_matches[$count_matches]['confidence'] = $matches['Face']['Confidence'];
        $faces_matches[$count_matches]['marks'] = $matches['Face']['Landmarks'];
        $faces_matches[$count_matches]['pose'] = $matches['Face']['Pose'];
        $faces_matches[$count_matches]['quality'] = $matches['Face']['Quality'];

        $count_matches++;
      }
    }

    if (isset($results['UnmatchedFaces'])){
      foreach ($results['UnmatchedFaces'] as $unmatches) {
        $faces_unmatches[$count_unmatches]['box']['width'] = $unmatches['BoundingBox']['Width'];
        $faces_unmatches[$count_unmatches]['box']['height'] = $unmatches['BoundingBox']['Height'];
        $faces_unmatches[$count_unmatches]['box']['left'] = $unmatches['BoundingBox']['Left'];
        $faces_unmatches[$count_unmatches]['box']['top'] = $unmatches['BoundingBox']['Top'];
        $faces_unmatches[$count_unmatches]['confidence'] = $unmatches['Confidence'];
        $faces_unmatches[$count_unmatches]['marks'] = $unmatches['Landmarks'];
        $faces_unmatches[$count_unmatches]['pose'] = $unmatches['Pose'];
        $faces_unmatches[$count_unmatches]['quality'] = $unmatches['Quality'];
        $count_unmatches++;
      }
    }




//    echo "<pre>";
//    print_r($source_image);
//    echo "</pre>-------------------------";

// echo "<pre>";
// print_r($faces_matches);
// echo "</pre>-------------------------";

// echo "<pre>";
 //print_r($faces_unmatches);
// echo "</pre>-------------------------";

 //echo "<pre>";
 //print_r($results);
// echo "</pre>-------------------------";



}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title></title>
</head>
<body>
  <div class="form-style-6">
    <h1>Analizar Imagen</h1>
    <form  action="compare.php" method="post" enctype="multipart/form-data">
      <input type="file" name="fileToUpload" value=""> <br><br>
      <input type="file" name="fileToUpload2" value=""> <br><br>
      <input type="submit" name="submit" value="Procesar">
    </form>

    <div style="color:red;margin-top:10px">
      <?php echo $msg; ?>
    </div>
  </div>
  <br><br>
  <div class="form-style-6">
    <h1>Resultados</h1>
    <?php if (isset($filename)) {?>
      <img id="image" hidden  src="<?php echo $filename; ?>" alt="">
      <img id="image2" hidden  src="<?php echo $filename2; ?>" alt="">
      <canvas id="myCanvas" style="width:100%"></canvas>
      <br><br><br><br>
      <canvas id="myCanvas2" style="width:100%"></canvas>

      <?php } ?>
      <br>
    </div>
    <?php if (isset($result)) { ?>
      <pre>
        <?php //print_r($result); ?>
      </pre>
    <?php } ?>
  </body>
  </html>

  <script>
  window.onload = function() {

    var c=document.getElementById("myCanvas");
    var ctx=c.getContext("2d");
    var img=document.getElementById("image");
    var canvas = document.getElementById('myCanvas');
    var context = canvas.getContext('2d');
    canvas.width=img.width;
    canvas.height=img.height;
    ctx.drawImage(img,0,0,img.width,img.height);

    context.beginPath();


      <?php if (isset($source_image ['confidence'])) {?>
        context.fillStyle = 'rgba(255,255,51,0.4)';
        context.fillRect(img.width * <?php echo $source_image['box']['left'] ?>, img.height * <?php echo $source_image['box']['top'] ?> , <?php echo $source_image['box']['width']*1000 ?>,  <?php echo $source_image['box']['height']*500?>);
        context.font = "20px Arial";
        context.fillStyle = 'yellow';
        context.fillText('Conf: <?php echo round($source_image['confidence'],2) ?>', image.width * <?php echo $source_image['box']['left'] ?>,image.height * <?php echo $source_image['box']['top'] ?> -5);
      <?php }?>



      var c=document.getElementById("myCanvas2");
      var ctx=c.getContext("2d");
      var img=document.getElementById("image2");
      var canvas = document.getElementById('myCanvas2');
      var context = canvas.getContext('2d');
      canvas.width=img.width;
      canvas.height=img.height;
      ctx.drawImage(img,0,0,img.width,img.height);

      context.beginPath();

        <?php if (isset($faces_matches)) {?>
        <?php foreach ($faces_matches as $matches) { ?>
          context.fillStyle = 'rgba(10, 200, 100, 0.39)';
          context.fillRect(img.width * <?php echo $matches['box']['left'] ?>, img.height * <?php echo $matches['box']['top'] ?> , <?php echo $matches['box']['width'] * 1000?>, <?php echo $matches['box']['height'] * 500?>);
          context.font = "20px Arial";
          context.fillStyle = 'green';
          context.fillText('Conf: <?php echo round($matches['confidence'],2) ?>', image.width * <?php echo $matches['box']['left'] ?>,image.height * <?php echo $matches['box']['top'] ?> -5);
        <?php }?>
        <?php }?>



          <?php if (isset($faces_unmatches)) {?>
          <?php foreach ($faces_unmatches as $matches) { ?>
            context.fillStyle = 'rgba(250, 0, 0, 0.39)';
            context.fillRect(img.width * <?php echo $matches['box']['left'] ?>, img.height * <?php echo $matches['box']['top'] ?> , <?php echo $matches['box']['width'] *1000?>, <?php echo $matches['box']['height'] *500?>);
            context.font = "20px Arial";
            context.fillStyle = 'red';
            context.fillText('Conf: <?php echo round($matches['confidence'],2) ?>', image.width * <?php echo $matches['box']['left'] ?>,image.height * <?php echo $matches['box']['top'] ?> -5);
          <?php }?>
          <?php }?>






}
      </script>


      <style type="text/css">
      .form-style-6{
        font: 95% Arial, Helvetica, sans-serif;
        max-width: 600px;
        margin: 10px;
        margin-top: 50px;
        padding: 16px;
        background: #F7F7F7;
      }
      .form-style-6 h1{
        background: #43D1AF;
        padding: 20px 0;
        font-size: 140%;
        font-weight: 300;
        text-align: center;
        color: #fff;
        margin: -16px -16px 16px -16px;
      }
      .form-style-6 input[type="text"],
      .form-style-6 input[type="date"],
      .form-style-6 input[type="datetime"],
      .form-style-6 input[type="email"],
      .form-style-6 input[type="number"],
      .form-style-6 input[type="search"],
      .form-style-6 input[type="time"],
      .form-style-6 input[type="url"],
      .form-style-6 textarea,
      .form-style-6 select
      {
        -webkit-transition: all 0.30s ease-in-out;
        -moz-transition: all 0.30s ease-in-out;
        -ms-transition: all 0.30s ease-in-out;
        -o-transition: all 0.30s ease-in-out;
        outline: none;
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        width: 100%;
        background: #fff;
        margin-bottom: 4%;
        border: 1px solid #ccc;
        padding: 3%;
        color: #555;
        font: 95% Arial, Helvetica, sans-serif;
      }
      .form-style-6 input[type="text"]:focus,
      .form-style-6 input[type="date"]:focus,
      .form-style-6 input[type="datetime"]:focus,
      .form-style-6 input[type="email"]:focus,
      .form-style-6 input[type="number"]:focus,
      .form-style-6 input[type="search"]:focus,
      .form-style-6 input[type="time"]:focus,
      .form-style-6 input[type="url"]:focus,
      .form-style-6 textarea:focus,
      .form-style-6 select:focus
      {
        box-shadow: 0 0 5px #43D1AF;
        padding: 3%;
        border: 1px solid #43D1AF;
      }

      .form-style-6 input[type="submit"],
      .form-style-6 input[type="button"]{
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        width: 100%;
        padding: 3%;
        background: #43D1AF;
        border-bottom: 2px solid #30C29E;
        border-top-style: none;
        border-right-style: none;
        border-left-style: none;
        color: #fff;
      }
      .form-style-6 input[type="submit"]:hover,
      .form-style-6 input[type="button"]:hover{
        background: #2EBC99;
      }
      </style>
