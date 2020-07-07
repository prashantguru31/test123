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
      $msg .= "El archivo es una imagen tipo: " . $check["mime"] . ".";
      $uploadOk = 1;
    } else {
      $msg .= "El archivo no es una imagen.";
      $uploadOk = 0;
    }
  }
  // Check if file already exists
  if (file_exists($target_file)) {
    $msg .= "La imagen ya existe...";
    $uploadOk = 0;
  }
  // Check file size
  if ($_FILES["fileToUpload"]["size"] > 50000000) {
    $msg .= "El archivo de imagen es muy grande.";
    $uploadOk = 0;
  }
  // Allow certain file formats
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
    $msg .="Solo JPG, JPEG, PNG & GIF son permitidas. ";
    $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    $msg .= " La imagen no fue subida.";
    // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      $msg .= "El archivo ". basename( $_FILES["fileToUpload"]["name"]). " ha sido subido.";
    } else {
      $msg .=  "Lo siento no se subió el archivo.";
    }
  }

  $filename = "uploads/".$_FILES["fileToUpload"]["name"];



  $client = new Aws\Rekognition\RekognitionClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
      'key' => 'AKIAJTF72POOCRFNZNXA',
      'secret' => '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',
    ]
  ]);

  try {
    $result = $client->detectLabels([
      'Image' => [
        'Bytes' => file_get_contents($filename),
      ],
      'MaxLabels' => 20,
      'MinConfidence' => 20,
    ]);
  } catch (\Exception $e) {
    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
  }



  $labels = array();
  $count=0;

  //echo "<pre>";
  //print_r($result);
  //echo "</pre>";

  foreach ($result['Labels'] as $label){

    if (isset($label['Instances'][0])){
      foreach ($label['Instances'] as $instances) {
        $labels[$count]['name'] = $label['Name'];
        $labels[$count]['confidence'] = $label['Confidence'];
        $labels[$count]['width'] = $instances['BoundingBox']['Width'];
        $labels[$count]['height'] = $instances['BoundingBox']['Height'];
        $labels[$count]['left'] = $instances['BoundingBox']['Left'];
        $labels[$count]['top'] = $instances['BoundingBox']['Top'];
        $count++;
      }
    }
  }

//  echo "<pre>";
//  print_r($labels);
//  echo "</pre>";

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
    <form  action="labels.php" method="post" enctype="multipart/form-data">
      <input type="file" name="fileToUpload" value=""> <br><br>
      <input type="submit" name="submit" value="Procesar">
    </form>

    <div style="color:red;margin-top:10px">
      <?php echo $msg; ?>
    </div>
  </div>
  <br><br>

  <div class="canvas-style">
    <h1>Resultados</h1>

    <?php if (isset($filename)) {?>
      <img id="image" hidden  src="<?php echo $filename; ?>" alt="">
      <canvas id="myCanvas" style="width:100%;max-width: 100%;" > </canvas>
      <?php } ?>

      <br>
    </div>

    <?php if (isset($result)) { ?>
      <pre>
        <?php // print_r($result); ?>
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

    <?php if (isset($labels)) {?>
      <?php foreach ($labels as $label){ ?>
        context.rect(img.width * <?php echo $label['left'] ?>, img.height * <?php echo $label['top'] ?> , <?php echo $label['width']*1000 ?>,  <?php echo $label['height']*1000 ?>);
        context.font = "20px Arial";
        context.fillStyle = 'red';
        context.fillText('<?php echo $label['name'] ?>: <?php echo round($label['confidence'],2) ?>', img.width * <?php echo $label['left'] ?>,img.height * <?php echo $label['top'] ?> -5);
        <?php }?>
      <?php  } ?>

        context.fillStyle = 'rgba(200, 50, 100, 0.29)';
        context.fill();
        context.lineWidth = 3;
        context.strokeStyle = 'red';
        context.stroke();

      }
      </script>


      <style type="text/css">
      .form-style-6{
        font: 95% Arial, Helvetica, sans-serif;
        max-width: 100%;
        margin: 10px;
        margin-top: 50px;
        padding: 16px;
        background: #F7F7F7;
      }

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
