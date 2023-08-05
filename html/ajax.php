<?php

require_once 'lib.php';
require_once 'logic.php';
require_once 'actions.php';

$action = _get('action');
$action_func='action_'.$action;
$action_func();