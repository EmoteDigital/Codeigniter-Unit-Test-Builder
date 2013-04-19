Codeigniter-Unit-Test-Builder
=============================

A controller to provider better unit test process for CI apps.

Usage:

Drop the test controller into your CI app and include the file in your unit test controller.
Put the unit test class in your library folder and optionally load make it auto load.

Your unit test controller would looks like something close to this:

    <?php
    
    if ( ! defined('BASEPATH') )
        exit('No direct script access allowed');
    
    include_once( APPPATH . 'libraries/TEST_Controller.php' );
    
    class Test extends Test_Controller {
    
        public function test_first_test() {
        }
    
    }
    
    ?>

Your tests are functions starts with 'test_', and they will be automatically included when you run your test.

To run your test in your CLI, run

    andyiso@local:~$ php index.php test run first_test
    
The last parameter is the test name, if you don't supplie a test name, all tests will be ran.

