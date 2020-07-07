<?php

error_reporting(0);
require "vendor/autoload.php";
use Aws\S3\S3Client;
$bucketName = 'cursorekognition-ioticos';
$IAM_KEY = 'AKIAJTF72POOCRFNZNXA';
$IAM_SECRET = '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';

try {

  $s3 = S3Client::factory(
    array(
      'credentials' => array(
        'key' => $IAM_KEY,
        'secret' => $IAM_SECRET
      ),
      'version' => 'latest',
      'region'  => 'us-west-2'
    )
  );

} catch (Exception $e) {

  die("Error: " . $e->getMessage());

}

$FileName = basename($_FILES["fileToUpload"]["name"]);
$PathInS3 = 'https://s3-us-west-2.amazonaws.com/' . $bucketName . '/' . $FileName;

try {
  // Uploaded:
  $file = $_FILES["fileToUpload"]['tmp_name'];
  $s3->putObject(
    array(
      'Bucket'=>$bucketName,
      'Key' =>  $FileName,
      'SourceFile' => $file,
      'StorageClass' => 'REDUCED_REDUNDANCY',
      'ACL'  => 'public-read'
    )
  );
} catch (S3Exception $e) {
  die('Error:' . $e->getMessage());
} catch (Exception $e) {
  die('Error:' . $e->getMessage());
}

if (basename($_FILES["fileToUpload"]['name'])!=""){

  $valor = basename($_FILES["fileToUpload"]['name']);

  $client = new Aws\Rekognition\RekognitionClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
      'key' => 'AKIAJTF72POOCRFNZNXA',
      'secret' => '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',
    ]
  ]);

  $result = $client->DetectText([
    'Image' => [
      'S3Object' => [
        'Bucket' => 'cursorekognition-ioticos',
        'Name' => $valor,
      ],
    ],
    'MaxLabels' => 10,
    'MinConfidence' => 20,
  ]);
}
//echo "<pre>";
//print_r($result);
//echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <img src="https://s3-us-west-2.amazonaws.com/cursorekognition-ioticos/<?php echo $valor; ?>" alt="">
    <?php foreach ($result['TextDetections'] as $label): ?>
    <h1><?php print_r($label['DetectedText']); ?></h1>
  <?php endforeach; ?>

</body>
</html>
