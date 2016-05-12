<?php
session_start();
require '../vendor/autoload.php';

use Cashmusic\Venues;

define("CASH_VENUE_ROOT", realpath(__DIR__ . '/..'));

$venues = new Venues\Controller();

?><pre></pre>