<?php

include "../vendor/autoload.php";
include "../src/FormFactory.php";


use \h2lsoft\FormFactory\Form;

$form = new Form();

$form->addHeading("CV SUBMISSION");
$form->addText("Name", "", true);
$form->addText("Firstname", "", true);
$form->addText("Address", "", true);

// two rows
$form->addRowStart();
$form->addNumber("ZipCode", "Zip code", true, ['class' => "text-center"])->setInputSize(3);
$form->addText("City", "", true)->setLabelSize(1);
$form->addRowEnd();

// normal row
$form->addEmail("Email", "", true);
$form->addTel("Phone", "", true)->setInputSize(3);

$options = ['CEO', 'CTO', 'Lead dev', 'Webdesigner'];
$form->addSelect("Position", "Position Applying For", $options, true, true);
$form->addTextArea("AdditionalInformation", "Additional Information", false);

$form->addFile("CV", "Upload your CV", true)->setHelp("accepted File Types : .pdf, .doc[x], .xls[x]");



$form->addFooter("Submit my CV", false);
$form_render = $form->render();







// bootstrap 4 html render************************************************************
echo <<<HTML
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <title>CV SUBMISSION - Form Factory</title>
        
        <link rel="stylesheet" href="https://bootswatch.com/4/yeti/bootstrap.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>
        <style>
        label {white-space: nowrap}
        label[required]:after {content:" * "; color:red; }
        .form-heading {border-bottom:1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;}
        </style>
  </head>
  <body>
  
        <div class="container">
        
			{$form_render}
			
        </div>
        
        
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  
  </body>
</html>
HTML;





