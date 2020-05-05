<?php

include "../src/FormFactory.php";
require_once "../vendor/autoload.php";

use \h2lsoft\FormFactory\Form;

$form = new Form();
$form->addText('Name', "", true, '', ['minlength'=>3, 'maxlength'=>20]);
$form->addEmail('Email', "", true);
$form->addTel('Phone', '', true, '', ['data-mask'=>'99.99.99.99']);
$form->addDate('Date', '', true);
$form->addTime('Time', '', true);
$form->addDateTime('DateTime', 'Date time', true);
$form->addNumber('Age', '', true, ['min'=>5, 'max'=>20, 'step'=>1, 'class'=>'text-center'])->setInputSize(2);
$form->addSelect('Fruit', '', ['Apple', 'Banana', 'Pear'], true, true);
$form->addRadio('Rating', '', ['Bad', 'Medium', 'Good'], true);
$form->addFile('CV', '', true, ['accept' => '.doc,.docx,.pdf,application/msword,application/pdf']);
$form->addTextArea('Comments', '', false, '', ['minlength'=>5, 'maxlength'=>255]);
$form->addColorPicker('Color', '', false);
$form->addUrl("Website", '', false);
$form->addSwitch('Conditions', '', "I accept conditions", 'yes', true);
$form->addFooter("Submit", false);

// add special rules
$form->validator->input('Name')->alpha();

// auto compile validator
if($form->isValid())
{
	// your treatment here
	die("your form is valid, treatment here");
}

$form_render = $form->render();


// bootstrap 4 html render************************************************************
echo <<<HTML
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <title>Validator - Form Factory</title>
        
        <link rel="stylesheet" href="https://bootswatch.com/4/yeti/bootstrap.css">
        
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>
		<script defer src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
		<script defer src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
        
        <style type="text/css">
        label {white-space: nowrap}
        label[required]:after {content:" * "; color:red}
        .form-heading {border-bottom:1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;}
        </style>
        
  </head>
  <body>
  
        <div class="container">
            <h1 class="form-heading">Form validator</h1>
			{$form_render}
        </div>

  
  </body>
</html>
HTML;



