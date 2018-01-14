Deny from  all
<?php if(isset($allow_ip) && $allow_ip != ''): ?>
SetEnvIf X-Forwarded-For ^192\.168\.0\.1 env_allow_1
Allow from env=env_allow_1
Allow from 192.168.0.1
<?php endif; ?>