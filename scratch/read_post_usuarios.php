<?php
$lines = file('c:\\wamp64\\logs\\access.log');
$recent = array_slice($lines, -500);
$found = [];
foreach($recent as $line) {
    if (strpos($line, 'POST /SBAVECA/api/usuarios') !== false) {
        $found[] = $line;
    }
}
print_r($found);
?>
