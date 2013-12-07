#!/usr/bin/php

<?php

include_once('settings.inc');

$allowedExtensions = array('mp3', 'flac');

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(MUSIC_PATH),
  RecursiveIteratorIterator::SELF_FIRST);

print_r("Building BPM index...\n");

$xml = new SimpleXMLElement('<root/>');
$items = $xml->addChild("items");

$id = 0;
foreach($objects as $object){

  if(in_array(strtolower($object->getExtension()), $allowedExtensions)){

    $name = $object->getFilename();
    $subpath = $path . '/' . $objects->getSubPathname();
    $sh_path = escapeshellarg($subpath);

    $result = exec("bpm-tag -nf $sh_path 2>&1");

    if(preg_match('|:\s([0-9\.]{1,8}?)\sBPM|', $result, $matches)){

      $time = exec("ffmpeg -i $sh_path 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
      list($hms, $milli) = explode('.', $time);
      list($hours, $minutes, $seconds) = explode(':', $hms);
      $duration = ($hours * 3600) + ($minutes * 60) + $seconds;

      $bpm = floor($matches[1]);

      $item = $items->addChild("item");

      $item->location = implode("/", array_map("rawurlencode", explode("/", $subpath)));
      $item->id = $id;
      $item->bpm = $bpm;
      $item->duration = $duration;

      $id++;
      print_r("$name | $bpm BPM | Duration: $total_seconds\n");
    }
  }
}

$xml->asXML(BPM_INDEX_FILE);

