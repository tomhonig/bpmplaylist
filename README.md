BPM PLAYLIST!
===========

Dependencies
  *bpm-tag http://www.pogo.org.uk/~mark/bpm-tools/
  *ffmpeg
  *sox
  *libsox-fmt-mp3
  *flac
  *vlc

Example usage

1. Edit the settings.inc configuration file

2. Build the index
  
  $ ./build-index.php

3. Generate a playlist

  This example will create a playlist with: 5min 100bpm, 1min 60bpm, 3min 110bpm, 30min 90bpm 
  
  $ ./generate-playlist.php -s 300:100,60:60,180:110,1800:90
  
    <duration in seconds>:<bpm>,<duration in seconds>:<bpm>, [...} 
    
