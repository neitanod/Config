<?php
/*
Copyright (c) 2008 Sebastián Grignoli
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. Neither the name of copyright holders nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * @author   "Sebastián Grignoli" <grignoli@gmail.com>
 * @package  Config
 * @version  1.0
 * @link     https://github.com/neitanod/Config
 * @example  https://github.com/neitanod/Config
 * @license  Revised BSD
  */

namespace Neitanod;

class Config {
  protected $local               = [];
  protected $combined            = [];
  protected $immutable_combined  = [];
  protected $all_combined        = [];
  protected $local_file          = null;

  public function load( $file = null ){
    $loaded = file_exists($file)?json_decode(file_get_contents($file), true):array();
    $this->combined = static::array_merge_recursive_distinct($this->combined, $loaded);
    $this->refreshCombined();
  }

  public function loadLocal( $file = null ){
    $this->local_file = $file;
    $this->local = file_exists($file)?json_decode(file_get_contents($file), true):array();
    $this->refreshCombined();
  }

  public function loadImmutable( $file = null ){
    $loaded = file_exists($file)?json_decode(file_get_contents($file), true):array();
    $this->immutable_combined = static::array_merge_recursive_distinct($this->immutable_combined, $loaded);
    $this->refreshCombined();
  }

  protected function refreshCombined(){
    $this->all_combined = static::array_merge_recursive_distinct($this->combined, $this->local);
    $this->all_combined = static::array_merge_recursive_distinct($this->all_combined, $this->immutable_combined);
  }

  public function get($path, $default = null){
    $value = static::getByPath($this->all_combined, $path);
    if(!is_null($value)) return $value;

    return $default;
  }

  protected function getByPath(&$arr, $path, $separator='.') {
    $keys = explode($separator, $path);

    foreach ($keys as $key) {
      if(!isset($arr[$key])) return null;
      $arr = &$arr[$key];
    }

    return $arr;
  }

  public function getAll(){
    return $this->all_combined;
  }

  protected function setByPath(&$arr, $path, $value, $separator='.') {
    $keys = explode($separator, $path);
    foreach ($keys as $key) {
      if(!is_array($arr)) $arr = array();
      if(!array_key_exists($key, $arr)) {
        $arr[$key] = array();
      }
      $arr =
        &$arr[$key];
    }
    $arr = $value;
  }

  public function set($path, $value){
    static::setByPath($this->local, $path, $value);
    $this->refreshCombined();
  }

  public function saveLocal(){
    file_put_contents($this->local_file, json_encode($this->local, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }


  protected function array_merge_recursive_distinct ( array &$array1, array &$array2 )
  {
    $merged = $array1;
    foreach ( $array2 as $key => &$value )
    {
      if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
      {
        $merged [$key] = static::array_merge_recursive_distinct ( $merged [$key], $value );
      }
      else
      {
        $merged [$key] = $value;
      }
    }
    return $merged;
  }

}
