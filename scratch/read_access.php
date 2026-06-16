<?php
$lines = file('c:\\wamp64\\logs\\access.log');
$recent = array_slice($lines, -50);
foreach($recent as $line) {
    if (strpos($line, '/api/usuarios') !== false || strpos($line, '/api/auth') !== false) {
        echo $line;
    }
}
?>
