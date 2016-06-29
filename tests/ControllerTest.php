<?php
require_once('../includes/bootstrap.php');
require_once(CASH_VENUE_ROOT . '/vendor/simpletest/simpletest/autorun.php');
require_once(CASH_VENUE_ROOT . '/vendor/simpletest/simpletest/web_tester.php');

use Cashmusic\Venues;

class ControllerTest extends WebTestCase {

    // heads up that we're using the default IP for our vagrant box for these tests
    function testIndex() {
        $this->get('http://192.168.33.10/');
        $this->assertMime("text/html; charset=utf-8");
    }

    function testSearch() {
        $this->get('http://192.168.33.10/venues/middle.html');
        $this->assertMime("text/html; charset=utf-8");

        $this->get('http://192.168.33.10/venues/middle');
        $this->assertMime("application/json");
    }

    function testDetail() {
        $this->get('http://192.168.33.10/venue/5615586d53dc9.html');
        $this->assertMime("text/html; charset=utf-8");

        $this->get('http://192.168.33.10/venue/5615586d53dc9');
        $this->assertMime("application/json");
    }

}