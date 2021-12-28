<?php
var_dump(dolphin()->set('hello', 'world'));
var_dump(dolphin()->get('hello'));

\Bnomei\MySQLCache::singleton()->benchmark(1000);
\Bnomei\MySQLCache::singleton()->flush();
