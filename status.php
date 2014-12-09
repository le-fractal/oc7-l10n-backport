#!/usr/bin/php
<?php

// path to checked out stable7 code
define('OC7_CORE_DIR', '/home/zinks/src/core7c');

// path to master code
define('OC8_CORE_DIR', '/home/zinks/src/core8');

// language code as it appears in translation file names
define('Z_LANGUAGE', 'de'); 


//--------------------------------------------

function findPhpFiles($directory, $language) {

	$files = array();
	$locateCmd = "find ".OC7_CORE_DIR." -name ".strtolower($language).".php";
	exec($locateCmd, $files);

	return $files;
}

function readJsonFile($jsonFile) {

	$data = array();
	if (file_exists($jsonFile)) {
		$json = file_get_contents($jsonFile);
		$data = json_decode($json);
	} else {
		throw new Exception("Cannot find file $jsonFile");
	}
	return $data;
}

function buildJsonFilePath($phpFile, $language) {

	$parts = explode('/', $phpFile);
	$resourceName = $parts[count($parts)-3];
	// Being lazy here, could probably figure out from $phpFile
	// if we need to go down into apps/ or not
	return array(
		OC8_CORE_DIR.'/'.$resourceName.'/l10n/'.strtolower($language).'.json',
		OC8_CORE_DIR.'/apps/'.$resourceName.'/l10n/'.strtolower($language).'.json',
	);
}

// Escape strings
// This is a bit flimsy as some apps have different escaping requirements
function esc_($string) {
	return addcslashes(str_replace("\n", '\\n', $string), '$');
}


/* Now that we have our functions, here is our script */


$oc7Files = findPhpFiles(OC7_CORE_DIR, Z_LANGUAGE);

// Here we will put the strings and their translations that are ...
$result = array(
	"OC7"      => array(), // ... only in oc7
	"OC8"      => array(), // ... only in oc8
	"OC7==OC8" => array(), // ... in both with same translation
	"OC7!=OC8" => array(), // ... in both with different translations
);

// In this script we'll only do something with the strings that have been updated
// in OC8, that is the strings in $results["OC7!=OC8"]

// Here we include each PHP file containing the translations (OC7)
// Then get the OC8 translations from the corresponding json file
foreach ($oc7Files as $file) {

	include $file; // php translations (oc7) are in $TRANSLATIONS
	
	$jsonFile = buildJsonFilePath($file, Z_LANGUAGE);
	foreach ($jsonFile as $jsonFileItem) {
		try {

			$jsonData = readJsonFile($jsonFileItem);
			$JSON_TRANSLATIONS = get_object_vars($jsonData->translations); // oc8 translations
			foreach ($TRANSLATIONS as $en => $fr) {
				if (array_key_exists($en, $JSON_TRANSLATIONS)) {
					// String is found in both OC7 and OC8
					if ($TRANSLATIONS[$en] === $JSON_TRANSLATIONS[$en]) {
						// translation has NOT changed from oc7 to oc8
						$result['OC7==OC8'][$file][$en] = $TRANSLATIONS[$en];
					} else {
						// translation has changed from oc7 to oc8
						$result['OC7!=OC8'][$file][$en]['OC7'] = $TRANSLATIONS[$en];
						$result['OC7!=OC8'][$file][$en]['OC8'] = $JSON_TRANSLATIONS[$en];
					}
				} else {
					// String is found only in OC7
					$result['OC7'][$file][$en] = $TRANSLATIONS[$en];
				}
			}
		} catch (Exception $e) {
			; // see the code of buildJsonFilePath() to know why I need this
		}
	}	
}


$patchCount = 0;

foreach ($result['OC7!=OC8'] as $file => $trans) {

	$fileData = @file($file);
	if ($fileData === false) {
		print "! Error opening file $file\n";
		continue;
	}

	foreach ($fileData as $i => $line) {
		// $part[0] contains the key (english string),
		// $parts[1] the (old) translation
		$parts = explode(' => ', $line);
		if ($parts[0] === $line) {
			continue;
		}
		// If the original string on this line is one we want to patch
		foreach (array_keys($trans) as $en) {
			if ($parts[0] === esc_('"'.$en.'"')) {
				print "> File ".$file." line ".$i."\n";
				$fileData[$i] = implode(' => ', array($parts[0], '"'.esc_($trans[$en]['OC8']).'",'))."\n";
				$patchCount++;
				print "-- ".$line;
				print "++ ".$fileData[$i];
				break;
			}
		}
	}

	if ($argc > 1 && $argv[1] === "write") {
		$fileWritten = file_put_contents($file, implode($fileData));
		if ($fileWritten !== false) {
			print "# File $file overwritten successfully\n";
		} else {
			print "! Error while overwriting file $file\n";
		}
	}

}

print "\n===\n";
print $patchCount." strings have been found and patched\n";


