<IfModule mod_headers.c>
Header set Cache-Control "max-age=60, must-revalidate"
</IfModule>

Deny from  all
<?php if (isset($allow_ip) && $allow_ip != ''): ?>
SetEnvIf X-Forwarded-For ^<?=str_replace('.', '\\.', $allow_ip)?> env_allow_1
Allow from env=env_allow_1
Allow from <?=$allow_ip?>
<?php endif; ?>