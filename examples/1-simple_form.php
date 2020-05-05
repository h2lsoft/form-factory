<?php

include "../vendor/autoload.php";
include "../src/FormFactory.php";

use \h2lsoft\FormFactory\Form;


$form = new Form();

$form->addHeading("Simple form");

$form->addText("Name", "", true);
$form->addText("Language", "Language (autocomplete)", false)->addDatalist(['English', 'French', 'Spanish', 'German', 'Italian']); # combo
$form->addSelect("Fruit", "", ['Ananas', 'Meloon', 'Apple', 'Pear']);
$form->addEmail("Email", "", true);
$form->addPassword("Password", "", true);
$form->addTel("Phone", "", true);
$form->addUrl("Website");
$form->addTextArea("Comments", "", false);
$form->addSwitch("Subscribe", "Subscribe to Newsletter");
$form->addHidden('field_hidden', "hidden content");
$form->addFooter();
$form_render = $form->render();







// bootstrap 4 html render************************************************************
echo <<<HTML
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <title>Simple form - Form Factory</title>
        
        <link rel="stylesheet" href="https://bootswatch.com/4/yeti/bootstrap.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>
        <style>
        label[required]:after {content:" * "; color:red}
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





