<?php

if ( ! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
* Extend this class and have your tests written in a consistent way.
*/
class Test_Controller extends CI_Controller {

    var $with_data = false;
    var $queries = array();

    public function __construct() {

        parent::__construct();

        $this->load->dbutil();
        $this->load->dbforge();
        $this->load->database();

        $this->test_db = 'test_' . $this->db->database;

    }

    /**
    * Get db schema and w/ or w/o content
    */
    private function db_query() {

        if( !empty($this->queries) )
            return $this->queries;

        $prefs = array(
            'format'      => 'txt',             // gzip, zip, txt
            'add_drop'    => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert'  => $this->with_data,  // Whether to add INSERT data to backup file
            'newline'     => ""               // Newline character used in backup file
        );
        $query = $this->dbutil->backup($prefs);
        $query = preg_replace("^\#\# TABLE STRUCTURE FOR:(.*?)\;|\n^", "", $query);
        $this->queries = preg_split("^\;(?=(INSERT|CREATE))^", $query);

        return $this->queries;

    }

    public function load_test_db() {

        $db['hostname'] = $this->db->hostname;
        $db['username'] = $this->db->username;
        $db['password'] = $this->db->password;
        $db['database'] = $this->test_db;
        $db['dbdriver'] = $this->db->dbdriver;
        $db['dbprefix'] = $this->db->dbprefix;
        $db['pconnect'] = $this->db->pconnect;
        $db['db_debug'] = $this->db->db_debug;
        $db['cache_on'] = $this->db->cache_on;
        $db['cachedir'] = $this->db->cachedir;
        $db['char_set'] = $this->db->char_set;
        $db['dbcollat'] = $this->db->dbcollat;

        $this->db = $this->load->database( $db, TRUE );

    }

    /**
    * Get tests in unit test controller
    */
    private function get_tests() {

        $tests = array();
        $methods = get_class_methods( get_class($this) );

        foreach( $methods as $method ) {

            if( preg_match("^\Atest_+^", $method) )
                $tests[] = $method;

        }

        return $tests;

    }

    /**
    * Truncate tables after each test.
    */
    private function flush_tables() {

        $query = "SHOW TABLES";
        $results = $this->db->query( $query )->result_array();
        foreach( $results as $result ) {

            $table = array_pop( $result );
            $this->db->empty_table( $table );

        }

        if( $this->with_data )
            $this->tier_up();

    }

    /**
    * Creates test db, import data schema and optionally import data.
    */
    private function tier_up() {

        //clear the db util cache
        $this->dbutil->data_cache = array();
        if( $this->dbutil->database_exists( $this->test_db ) )
            $this->dbforge->drop_database( $this->test_db );

        $this->dbforge->create_database( $this->test_db );

        $queries = $this->db_query();
        $this->load_test_db();

        foreach( $queries as $query ) {

            $this->db->query( $query . ';' );

        }

    }

    /**
    * Destory the test db
    */
    private function tier_down() {

        echo "Dropping test database ...\n";
        $this->dbforge->drop_database( $this->test_db );

    }

    /**
    * The run function accepts test name, without prefix 'test_'
    *
    * This function tier up the db, run and benchmark the tests, and tier down the test database.
    */
    public function run( $test_case = false ) {

        $tests = $this->get_tests();

        echo "Creating test database " . $this->test_db . " ...\n";
        $this->tier_up();
        $this->benchmark->mark('test_start');

        if( !$test_case ) {
            $count = 0;
            foreach ($tests as $test) {
                $this->{ $test }();
                $this->flush_tables();
            }

        } else {

            $this->{ 'test_' . $test_case }();

        }

        $passed_count = 0;
        $test_results = $this->emote_unit_test->result();
        foreach( $test_results as $result ) {

            if( $result['Result'] == "Passed" )
                $passed_count ++;

        }

        echo $this->emote_unit_test->cli_report();
        echo "\n";
        echo count( $test_results ) . " tests ran, " . $passed_count . " passed! \n";

        $this->tier_down();
        $this->benchmark->mark('test_end');
        echo "Total time in " . $this->benchmark->elapsed_time('test_start', 'test_end') . " seconds \n";

    }

}