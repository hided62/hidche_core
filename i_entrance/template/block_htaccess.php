Deny from  all
SetEnvIf X-Forwarded-For ^192\.168\.0\.1 env_allow_1
Allow from env=env_allow_1
Allow from 192.168.0.1