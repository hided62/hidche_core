<IfModule mod_headers.c>
Header set Cache-Control "max-age=60, must-revalidate"
</IfModule>

Deny from  all
<?php
$targetPath = realpath(__DIR__.'/../../../403.html');
$docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$targetPath = str_replace($docRoot, '', $targetPath);
$targetPath = str_replace('\\', '/', $targetPath);
?>
ErrorDocument 403 <?=$targetPath?>


<?php if (isset($allow_ip) && $allow_ip != ''): ?>
SetEnvIf X-Forwarded-For ^<?=str_replace('.', '\\.', $allow_ip)?> env_allow_1
Allow from env=env_allow_1
Allow from <?=$allow_ip?>
<?php endif; ?>

<Files j_autoreset.php>
    Allow from 127.0.0.1 ::1 localhost
</Files>

<Files j_install.php>
    Allow from all
</Files>
<Files install.php>
    Allow from all
</Files>
<Files j_server_basic_info.php>
    Allow from all
</Files>
<Files ~ "\.(xml|css|jpe?g|png|gif|js|pdf)$">
    Allow from all
</Files>
<Files j_load_scenarios.php>
    Allow from all
</Files>