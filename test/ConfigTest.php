<?php
require_once(dirname(__FILE__)."/Test.class.php");
require_once(dirname(dirname(__FILE__))."/src/Neitanod/Config.php");

use \Neitanod\Config;

// Test the testing class itself.
Test::is("'yes' is true", 'yes', true);
Test::not("1 is not false", 1, false);
Test::identical("true is identical to true", true, true);
Test::true("1 is true", 1);

// Test the Config cass.
function load_from_config_file(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  return $conf->get("test1", "default");
}

Test::identical("Load a JSON file and retrieve a config value",
  load_from_config_file(),
  "test1_value");


Test::totals();



// Example tests:
// Test::true(label, bool);
// Test::is(label, val1, val2);
// Test::not(label, val1, val2);
// Test::identical(label, val1, val2);

