#!/usr/bin/php

<?php

include_once('settings.inc');

$session = array();

if($input = getopt('s:')){
  $parts = explode(',',$input['s']);
  foreach($parts as $part){
    list($time, $tempo) = explode(':', $part);
    $session[] = array('time' => $time, 'tempo' => $tempo);
  }
}
else{
  $session = array(
    array('time' => 3600, 'tempo' => 95),
  );
}

$xml = simplexml_load_file(BPM_INDEX_FILE);

$songs = array();
$matches = array();

$items = (array) $xml->items;

foreach($session as $i => $interval){

  $duration = 0;

  do{
    $item = match_bpm($items['item'], $interval['tempo'], $matches);

    $last_song = ($duration + (int)$item->duration > $interval['time']);

    $songs[] = array(
      'location' => (string) $item->location,
      'duration' => ($last_song) ? $interval['time'] - $duration : (int)$item->duration,
      'item' => (array)$item,
    );

    $duration += (int)$item->duration;

  }while($item && !$last_song);

  $playlist = new SimpleXMLElement('<playlist/>');
  $playlist->addAttribute('xmlns', 'http://xspf.org/ns/0/');
  $playlist->addAttribute('version','1');

  $playlist->title = "Spellista Super duper!";

  $tracklist = $playlist->addChild('trackList');
  $count = 0;

  foreach($songs as $song){

    $track = $tracklist->addChild('track');

    $track->location = 'file://' . $song['location'];

    $min = floor($song['duration']/60);
    $sec = $song['duration'] - ($min*60);
    $track->annotation = "{$song['item']['bpm']} BPM, $min:$sec min ";

    $extension = $track->addChild('extension');
    $extension->addAttribute('application','http://www.videolan.org/vlc/playlist/0');
    $extension->addChild('vlc:option', "stop-time={$song['duration']}.000", 'http://www.videolan.org/vlc/playlist/0');
    $extension->addChild('vlc:id', $count, 'http://www.videolan.org/vlc/playlist/0');

    $count++;
  }

  $dom = new DOMDocument("1.0");
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->loadXML($playlist->asXML());

  $dom->save(PLAYLIST_PATH . '/bpm_playlist.xspf');

}


function match_bpm($items, $bpm, &$matches){

  shuffle($items);

  foreach($items as $item){
    $tempo = (int)$item->bpm;
    $id = (int)$item->id;

    if(in_array($id, $matches)){
      continue;
    }

    if(($bpm + TEMPO_ACCURACY >= $tempo && $bpm - TEMPO_ACCURACY  <= $tempo) ||
       ($bpm + TEMPO_ACCURACY >= $tempo / 2 && $bpm - TEMPO_ACCURACY  <= $tempo / 2)
    ){
      $matches[] = $id;
      return $item;
    }
  }
  return 0;
}
