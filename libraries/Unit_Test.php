<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Cli_Unit_Test
 *
 * Pretty print the test results in your CLI
 */
class Unit_Test extends CI_Unit_test {

    public function __construct() {
        parent::__construct();
        $this->CI =& get_instance();
    }

    /**
    * Print out the coloured test result
    */
    public function report($result = array()) {

        if( !$this->input->is_cli_request() )
            return parent::report();

        if( !count($result) ) {
            $result = $this->result();
        }

        $output = "[Test results] : ".$result[0]['File Name']."\n";

        $mask = "%-60.80s \t %-20.20s \t %-20.20s \t %-5.20s \t %-20.80s \n";

        $output .= "\033[01;36m";
        $output .= sprintf($mask, 'Test Name', 'Test Datatype', 'Expected Datatype', 'Result', 'Notes' );
        $output .= "\033[0m";

        foreach ($result as $row) {

            $resultStr = "";
            if( $row['Result'] === 'Passed' )
                $resultStr = "\033[0;32m".$row['Result']."\033[0m";
            else
                $resultStr = "\033[0;31m".$row['Result']."\033[0m";

            $output .= sprintf($mask, $row['Test Name'], $row['Test Datatype'], $row['Expected Datatype'], $resultStr, $row['Notes'] );

        }

        return $output;

    }


}


