<?php

include 'Engine.php';
include 'Extension/ExtensionInterface.php';
include 'Template/Data.php';
include 'Template/Directory.php';
include 'Template/FileExtension.php';
include 'Template/Folders.php';
include 'Template/Func.php';
include 'Template/Functions.php';
include 'Template/Name.php';
include 'Template/Template.php';
// Create new Plates instance
$templates = new League\Plates\Engine('templates');

// Preassign data to the layout
$templates->addData(['company' => 'The Company Name'], 'layout');

// Render a template
echo $templates->render('profile', ['name' => 'Jonathan']);
