<?php

require_once('SplClassLoader.php');

// Private (non-committed) definitions (SQL DSNs, logins, etc)
require_once('.private.php');

$classLoader = new SplClassLoader('Loris', dirname(__FILE__));
$classLoader->register();
