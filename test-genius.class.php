<?php
require_once '/home/fhou732/classes/tinyHttp.class.php';
require_once 'genius.class.php';

$eol = "\n";

$obj = new genius();

/*
$testcases = [ 'search', 'artist', 'artist-songs', 'song' ];
foreach ($testcases as $testcase)
	do_test ($obj, $testcase);
*/

do_test ($obj, 'search');

function
do_test (genius $obj, string $testcase)
{
	global $eol;

	echo '------------------' . $testcase . '------------------' . $eol;

	$artist_id = 22272;	// Lionel Ritchie
	$song_id = 378195;
	$q = 'hello';
$q = 'brel';

	switch ($testcase)
	{
	case 'search' :
		$result = $obj -> search ($q);
		echo 'Source: ' . $obj->getSource() . $eol;
		if ($obj->getSource() == 'cache')
			echo 'Cache ID: ' . $obj->getCacheID() . $eol;
		display_search_result ($result);
		break;
	case 'artist' :
		$result = $obj -> getArtist ($artist_id);
		echo 'Source: ' . $obj->getSource() . $eol;
		display_artist ($result);
		break;
	case 'artist-songs' :
		$result = $obj -> getArtistSongs ($artist_id);
		echo 'Source: ' . $obj->getSource() . $eol;
		display_artist_songs ($artist_id, $result);
	case 'song' :
		$result = $obj -> getSong ($song_id);
		echo 'Source: ' . $obj->getSource() . $eol;
		display_song ($song_id, $result);
	}
}
function
repeat (string $c, int $n): string
{
	$str = '';
	while ($n--)
		$str .= $c;
	return $str;
}

function
title (string $t): string
{
	global $eol;

	$str = '';
	/*
	$str .= $eol;
	$str .= $t . $eol;
	$str .= repeat ('-', strlen($t)) . $eol;
	*/
	$str .= $t . ': ';
	return $str;
}

function
display_song (int $song_id, array $result): void
{
	$strings = [ ];

	$song = $result['song'];

	$keys = [ 'id', 'apple_music_id', 'lyrics_state', 'release_date', 'title', 'full_title', 'url', 'song_art_image_url' ];

	foreach ($keys as $key)
		$strings[] = title ($key) . $song[$key];

	global $eol;
	foreach ($strings as $str)
		echo $str . $eol;
}

function
display_artist_songs (int $artist_id, array $result): void
{
	$strings = [ ];

	$songs = $result['songs'];
	$strings[] = title ('next page') . $result['next_page'];

	foreach ($songs as $rank => $song)
	{
		$strings[] = '';
		$strings[] = title ('rank') . $rank;

		$keys = [ 'id', 'title' ];
		foreach ($keys as $key)
			$strings[] = title ($key) . $song[$key];

		$primary_artist = $song['primary_artist'];
		if ($primary_artist['id'] != $artist_id)
		{
			$strings[] = title ('primary artist name') . $primary_artist['name'];
			$strings[] = title ('primary artist id') . $primary_artist['id'];
		}
	}

	global $eol;
	foreach ($strings as $str)
		echo $str . $eol;
}

function
display_artist (array $result): void
{
	global $eol;

	$artist = $result['artist'];

	$strings = format_artist_artist ($artist);
	foreach ($strings as $str)
		echo $str . $eol;
}

function
format_artist_artist (array $artist): array
{
	$strings = [ ];

	$keys = [ 'name', 'id', 'facebook_name', 'instagram_name', 'twitter_name' ];
	foreach ($keys as $key)
		if (array_key_exists ($key, $artist) && $artist[$key] != null)
			$strings[] = format_entry ($key, $artist[$key]);

	if (array_key_exists ('alternate_names', $artist))
		$strings[] = format_artist_alternate_names ($artist['alternate_names']);

	if (array_key_exists ('description', $artist))
		$strings[] = format_artist_description ($artist['description']);

	return $strings;
}

function
format_entry (string $name, string $value): string
{
	return title ($name) . $value;
}

function
format_artist_description (array $desc): string
{
	return title ('Description') . $desc['plain'];
}

function
format_artist_alternate_names (array $names): string
{
	global $eol;

	$str = '';
	$str .= title ('Alternate names');
	$str .= implode (', ', $names);
	return $str;
}

function
display_search_result (array $result): void
{
	// print_r ($result);
	// return;
	global $eol;

	$hits = $result['hits'];

	$title = 'Song ID  Song                           Artist ID Artist                        ';
	$sep   = '-------- ------------------------------ --------- ------------------------------';
	echo $title . $eol;
	echo $sep . $eol;
	foreach ($hits as $hit)
		echo display_hit ($hit) . "\n";
}

function
display_hit (array $hit): string
{
	switch ($hit['type'])
	{
	case 'song' :
		return display_hit_song ($hit['result']);
		break;

	default :
		return 'Unknown type [' . $hit['type'] . ']';
	}
}

function
display_hit_song (array $res): string
{
	$str = '';
	$sep = ' ';

	$str .= sprintf ('%8d', $res['id']);
	$str .= $sep;
	$str .= sprintf ('%-30.30s', $res['title']);
	$str .= $sep;
	$str .= sprintf ('%9d', $res['primary_artist']['id']);
	$str .= $sep;
	$str .= sprintf ('%-30.30s', $res['primary_artist']['name']);

	return $str;
}


?>
