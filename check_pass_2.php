<?php
$hash = '$2y$10$9d/k0WE4UhFebS.rbD0Ji.uscaR58Xxn5EAymYZSrcFfP9UQH7tNK';
echo password_verify('Password123!', $hash) ? "YES Password123!" : "NO";
echo "\n";
echo password_verify('Sbaveca2025!', $hash) ? "YES Sbaveca2025!" : "NO";
?>
