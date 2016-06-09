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

function fail_to_load_silently(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/non_existant.json');
  return $conf->get("test1", "default");
}

Test::identical("Try to load a non existant JSON file and fail silently",
  fail_to_load_silently(),
  "default");

function exception_when_failing_to_load_wrong_json(){
  $conf = new Config();
  try {
    $conf->load(dirname(__FILE__).'/data/malformed.json');
  } catch(Exception $e) {
    return "Exception thrown";
  }
  return "Exception not thrown";
}

Test::identical("Try to load a malformed JSON file and throw exception",
  exception_when_failing_to_load_wrong_json(),
  "Exception thrown");

function get_default_value(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  return $conf->get("non_existant_key", "default_value");
}

Test::identical("Retrieve a nonexistant config value and get default back",
  get_default_value(),
  "default_value");

function global_overrides_global(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->load(dirname(__FILE__).'/data/global2.json');
  return $conf->get("key_that_exists_in_both_global", "default_value");
}

Test::identical("Values loaded from second Global file override values loaded from first Global file",
  global_overrides_global(),
  "loaded_from_second_global");

function local_overrides_global(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->loadLocal(dirname(__FILE__).'/data/local.json');
  return $conf->get("key_that_exists_both_in_global_and_in_local", "default_value");
}

Test::identical("Values loaded from Local file override values loaded from Global file",
  local_overrides_global(),
  "loaded_from_local");

function local_overrides_global_always(){
  $conf = new Config();
  $conf->loadLocal(dirname(__FILE__).'/data/local.json');
  $conf->load(dirname(__FILE__).'/data/global.json');
  return $conf->get("key_that_exists_both_in_global_and_in_local", "default_value");
}

Test::identical("Values loaded from Local file override values loaded from Global file even when loaded in reverse order",
  local_overrides_global_always(),
  "loaded_from_local");

function immutable_overrides_global(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  return $conf->get("key_that_exists_both_in_global_and_in_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values loaded from Global file",
  immutable_overrides_global(),
  "loaded_from_immutable");

function immutable_overrides_global_always(){
  $conf = new Config();
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  $conf->load(dirname(__FILE__).'/data/global.json');
  return $conf->get("key_that_exists_both_in_global_and_in_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values loaded from Global file no matter order of loading",
  immutable_overrides_global_always(),
  "loaded_from_immutable");

function immutable_overrides_local(){
  $conf = new Config();
  $conf->loadLocal(dirname(__FILE__).'/data/local.json');
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  return $conf->get("key_that_exists_both_in_local_and_in_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values loaded from Local file",
  immutable_overrides_local(),
  "loaded_from_immutable");

function immutable_overrides_local_always(){
  $conf = new Config();
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  $conf->loadLocal(dirname(__FILE__).'/data/local.json');
  return $conf->get("key_that_exists_both_in_local_and_in_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values loaded from Local file no matter order of loading",
  immutable_overrides_local_always(),
  "loaded_from_immutable");

function immutable_overrides_immutable(){
  $conf = new Config();
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable2.json');
  return $conf->get("key_that_exists_in_both_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values loaded from previous Immutable file",
  immutable_overrides_immutable(),
  "loaded_from_second_immutable");

function immutable_overrides_set(){
  $conf = new Config();
  $conf->loadImmutable(dirname(__FILE__).'/data/immutable.json');
  $conf->set("key_that_exists_in_immutable", "value_set_in_runtime");
  return $conf->get("key_that_exists_in_immutable", "default_value");
}

Test::identical("Values loaded from Immutable file override values set in runtime with set()",
  immutable_overrides_set(),
  "loaded_from_immutable");

function set_overrides_globals_and_locals(){
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->loadLocal(dirname(__FILE__).'/data/local.json');
  $conf->set("key_that_exists_both_in_global_and_in_local", "value_set_in_runtime");
  return $conf->get("key_that_exists_both_in_global_and_in_local", "default_value");
}

Test::identical("Values set in runtime with set() override all values loaded from Globals",
  set_overrides_globals_and_locals(),
  "value_set_in_runtime");

function save_locals_in_tmp(){
  $tmp = sys_get_temp_dir();
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->loadLocal($tmp.'/local.json');
  $conf->set("key_set_in_runtime", "value_set_in_runtime");
  $conf->saveLocal();
  $created = file_exists($tmp."/local.json");
  unlink($tmp."/local.json");
  return $created;
}

Test::true("Locals get saved in temporary file", save_locals_in_tmp());

function save_locals_in_tmp_and_verify(){
  $tmp = sys_get_temp_dir();

  // Save local values into local file
  $conf = new Config();
  $conf->load(dirname(__FILE__).'/data/global.json');
  $conf->loadLocal($tmp.'/local.json');
  $conf->set("key_set_in_runtime", "value_set_in_previous_run");
  $conf->saveLocal();

  // Load values from local file
  $conf2 = new Config();
  $conf2->load(dirname(__FILE__).'/data/global.json');
  $conf2->loadLocal($tmp.'/local.json');
  $retrieved = $conf2->get("key_set_in_runtime", "default");

  unlink($tmp."/local.json");
  return $retrieved;
}

Test::identical("Locals get saved in temporary file and retrieved later",
  save_locals_in_tmp_and_verify(),
  "value_set_in_previous_run");


Test::totals();



// Example tests:
// Test::true(label, bool);
// Test::is(label, val1, val2);
// Test::not(label, val1, val2);
// Test::identical(label, val1, val2);

