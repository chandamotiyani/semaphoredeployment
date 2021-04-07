# probably need to run this on install.
GRANT ALL PRIVILEGES ON yalumba.* TO `yalumba`@`%`;
ALTER USER 'yalumba'@'%' IDENTIFIED WITH mysql_native_password BY 'password';