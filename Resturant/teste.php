<?php
$passw  = 'admin123';
$hash = password_hash($passw, PASSWORD_DEFAULT);
echo "Password: $passw\nHash: $hash\n";