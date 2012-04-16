<?php

$lines = exec('ls -1 /var/www/paddata/ | wc -l');
echo $lines;

