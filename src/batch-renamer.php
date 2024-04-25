<?php

/*
 * Batch Renamer (https://github.com/delight-im/Batch-Renamer)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

\set_time_limit(0);

\header('Content-Type: text/plain; charset=utf-8');

\define('TIME_OFFSET_REGEX', '/^([+-]?)(P(?:[0-9YMWD]+)?(?:T[0-9HMS]+)?)$/i');
\define('FORMAT_PLACEHOLDERS_REGEX', '/%([a-z0-9]{3})/i');
\define('WHITESPACE_REGEX', '/\\s+/');
\define('DASHES_AND_HYPHENS_REGEX', '/[' . "\u{002D}\u{005F}\u{2010}\u{2011}\u{2012}\u{2013}\u{2014}" . ']+/');
\define('PARENTHESES_AND_BRACKETS_REGEX', '/[' . "\u{0028}\u{0029}\u{003C}\u{003E}\u{005B}\\\u{005D}\u{007B}\u{007D}" . ']+/');
\define('EXIF_DATE_AND_TIME_LENGTH', 19);
\define('EXIF_DATE_AND_TIME_FORMAT', 'Y:m:d H:i:s');
\define('DATETIME_FORMAT_IDENTIFIERS', 'fdtymahisewku');
\define('UNKNOWN_INFORMATION_MARKER', \chr(24));
\define('FILENAME_EXTENSION_SEPARATOR', '.');

$mode = !empty($argv[1]) ? \trim((string) $argv[1]) : null;

if ($mode !== 'preview' && $mode !== 'apply') {
	echo 'Please specify the mode as either \'preview\' or \'apply\' in the first argument';
	echo \PHP_EOL;
	exit(1);
}

$format = !empty($argv[2]) ? \trim((string) $argv[2]) : null;

if (empty($format)) {
	echo 'Please specify the format as a string with optional placeholders in the second argument';
	echo \PHP_EOL;
	exit(2);
}

$directory = !empty($argv[3]) ? \trim((string) $argv[3]) : '.';
$directoryObj = new \SplFileInfo($directory);

if ($directoryObj->isDir()) {
	$files = @\scandir($directoryObj->getRealPath(), \SCANDIR_SORT_ASCENDING);
}
else {
	$files = null;
}

if (empty($files)) {
	echo 'Please specify a valid directory in the third argument';
	echo \PHP_EOL;
	exit(3);
}

$timeOffset = !empty($argv[4]) ? \trim((string) $argv[4]) : null;
$timeOffsetObj = null;

if (!empty($timeOffset) && \preg_match(\TIME_OFFSET_REGEX, $timeOffset, $matches)) {
	try {
		$timeOffsetObj = new \DateInterval($matches[2]);

		if (!empty($matches[1]) && $matches[1] === '-') {
			$timeOffsetObj->invert = 1;
		}
	}
	catch (\Exception $e) {
		$timeOffsetObj = null;
	}
}

echo 'Directory: ' . $directoryObj->getRealPath();
echo \PHP_EOL;

$total = 0;
$renamedFrom = [];
$renamedTo = [];

foreach ($files as $file) {
	$fileObj = new \SplFileInfo($directoryObj->getRealPath() . \DIRECTORY_SEPARATOR . $file);

	if ($fileObj->isFile()) {
		echo \PHP_EOL;
		echo '    ' . $fileObj->getBasename();
		echo \PHP_EOL;

		$total++;

		$newFile = \preg_replace_callback(\FORMAT_PLACEHOLDERS_REGEX, function ($matches) use ($fileObj, $timeOffsetObj, $total) {
			if ($matches[1] === 'fne') {
				return $fileObj->getBasename();
			}
			elseif ($matches[1] === 'fnw') {
				return \makeFilenameWithoutExtension($fileObj);
			}
			elseif ($matches[1] === 'feo') {
				return $fileObj->getExtension();
			}
			elseif ($matches[1] === 'fel') {
				return \strtolower($fileObj->getExtension());
			}
			elseif ($matches[1] === 'feu') {
				return \strtoupper($fileObj->getExtension());
			}
			elseif ($matches[1] === 'fsb') {
				return $fileObj->getSize();
			}
			// TODO: drop support for `ndc` format string in next major version
			elseif (\substr($matches[1], 0, 2) === 'nd' || $matches[1] === 'ndc') {
				$number = $total;
				$digits = (int) \substr($matches[1], 2);

				return \str_pad($number, $digits, '0', \STR_PAD_LEFT);
			}
			// TODO: drop support for `nhl` format string in next major version
			elseif (\substr($matches[1], 0, 2) === 'nx' || $matches[1] === 'nhl') {
				$number = \strtolower(\dechex($total));
				$digits = (int) \substr($matches[1], 2);

				return \str_pad($number, $digits, '0', \STR_PAD_LEFT);
			}
			// TODO: drop support for `nhu` format string in next major version
			elseif (\substr($matches[1], 0, 2) === 'nh' || $matches[1] === 'nhu') {
				$number = \strtoupper(\dechex($total));
				$digits = (int) \substr($matches[1], 2);

				return \str_pad($number, $digits, '0', \STR_PAD_LEFT);
			}
			elseif (\substr($matches[1], 0, 1) === 'e') {
				$exif = \readExifDataFromFile($fileObj->getRealPath());

				if (\substr($matches[1], 1) === 'iw') {
					if (!empty($exif) && !empty($exif['EXIF']) && isset($exif['EXIF']['ExifImageWidth']) && isset($exif['EXIF']['ExifImageLength'])) {
						return (int) $exif['EXIF']['ExifImageWidth'];
					}
					elseif (!empty($exif) && !empty($exif['COMPUTED']) && isset($exif['COMPUTED']['Width']) && isset($exif['COMPUTED']['Height'])) {
						return (int) $exif['COMPUTED']['Width'];
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
				elseif (\substr($matches[1], 1) === 'ih') {
					if (!empty($exif) && !empty($exif['EXIF']) && isset($exif['EXIF']['ExifImageLength']) && isset($exif['EXIF']['ExifImageWidth'])) {
						return (int) $exif['EXIF']['ExifImageLength'];
					}
					elseif (!empty($exif) && !empty($exif['COMPUTED']) && isset($exif['COMPUTED']['Height']) && isset($exif['COMPUTED']['Width'])) {
						return (int) $exif['COMPUTED']['Height'];
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
				elseif (\substr($matches[1], 1) === 'cb') {
					if (!empty($exif) && !empty($exif['IFD0']) && !empty($exif['IFD0']['Make'])) {
						return (string) $exif['IFD0']['Make'];
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
				elseif (\substr($matches[1], 1) === 'cm') {
					if (!empty($exif) && !empty($exif['IFD0']) && !empty($exif['IFD0']['Model'])) {
						return (string) $exif['IFD0']['Model'];
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
				elseif (\substr($matches[1], 1, 1) === 'r') {
					if (\strpos(\DATETIME_FORMAT_IDENTIFIERS, \substr($matches[1], 2)) !== false) {
						$fields = [
							[ 'EXIF', 'DateTimeOriginal' ],
							[ 'EXIF', 'DateTimeDigitized' ],
							[ 'IFD0', 'DateTime' ]
						];

						foreach ($fields as $field) {
							if (!empty($exif) && !empty($exif[$field[0]]) && !empty($exif[$field[0]][$field[1]])) {
								if (\strlen($exif[$field[0]][$field[1]]) === \EXIF_DATE_AND_TIME_LENGTH) {
									$dateTimeObj = \DateTime::createFromFormat(
										\EXIF_DATE_AND_TIME_FORMAT,
										(string) $exif[$field[0]][$field[1]]
									);

									if ($timeOffsetObj !== null) {
										$dateTimeObj->add($timeOffsetObj);
									}

									return \formatDateTimeByIdentifier($dateTimeObj, \substr($matches[1], 2));
								}
							}
						}

						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
			}
			elseif (\preg_match('/([wdp])([1-9ni])([1-9n])/', $matches[1], $segmentation)) {
				switch ($segmentation[1]) {
					case 'w':
						$patternRegex = \WHITESPACE_REGEX;
						break;
					case 'd':
						$patternRegex = \DASHES_AND_HYPHENS_REGEX;
						break;
					case 'p':
						$patternRegex = \PARENTHESES_AND_BRACKETS_REGEX;
						break;
					default:
						$patternRegex = null;
						break;
				}

				if (!empty($patternRegex)) {
					$filenameWithoutExtension = \makeFilenameWithoutExtension($fileObj);

					$numberOfSeparators = \preg_match_all(
						$patternRegex,
						$filenameWithoutExtension,
						$separators,
						\PREG_SET_ORDER | \PREG_OFFSET_CAPTURE
					);

					if (!empty($separators)) {
						if ($segmentation[3] === 'n') {
							$segmentation[3] = $numberOfSeparators + 1;
						}
						else {
							$segmentation[3] = (int) $segmentation[3];
						}

						if ($segmentation[2] === 'n') {
							$segmentation[2] = $numberOfSeparators + 1;
						}
						elseif ($segmentation[2] === 'i') {
							$segmentation[3] = $numberOfSeparators - $segmentation[3] + 2;
							$segmentation[2] = $segmentation[3];
						}
						else {
							$segmentation[2] = (int) $segmentation[2];
						}

						if ($segmentation[2] > $segmentation[3]) {
							$segmentation[2] = $numberOfSeparators - $segmentation[2] + 2;
							$segmentation[3] = $numberOfSeparators - $segmentation[3] + 2;
						}

						$indexBefore = ((int) $segmentation[2]) - 2;
						$indexAfter = ((int) $segmentation[3]) - 1;

						if ($indexBefore === -1) {
							$offsetStart = 0;
						}
						elseif (isset($separators[$indexBefore])) {
							$offsetStart = $separators[$indexBefore][0][1] + \strlen($separators[$indexBefore][0][0]);
						}
						else {
							return \UNKNOWN_INFORMATION_MARKER;
						}

						if ($indexAfter === $numberOfSeparators) {
							$offsetEnd = \strlen($filenameWithoutExtension);
						}
						elseif (isset($separators[$indexAfter])) {
							$offsetEnd = $separators[$indexAfter][0][1];
						}
						else {
							return \UNKNOWN_INFORMATION_MARKER;
						}

						$segmented = \substr(
							$filenameWithoutExtension,
							$offsetStart,
							$offsetEnd - $offsetStart
						);

						return \trim($segmented);
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
			}
			elseif (\substr($matches[1], 0, 1) === 'h') {
				switch (\substr($matches[1], 1)) {
					case 'm5': $algorithm = 'md5'; break;
					case 's1': $algorithm = 'sha1'; break;
					case 's2': $algorithm = 'sha256'; break;
					case 'ad': $algorithm = 'adler32'; break;
					case 'cr': $algorithm = 'crc32'; break;
					default: $algorithm = null; break;
				}

				if (!empty($algorithm)) {
					$hash = @\hash_file($algorithm, $fileObj->getRealPath(), false);

					if (!empty($hash)) {
						return $hash;
					}
					else {
						return \UNKNOWN_INFORMATION_MARKER;
					}
				}
			}
			elseif (\substr($matches[1], 0, 2) === 'fc' || \substr($matches[1], 0, 2) === 'fm') {
				if (\strpos(\DATETIME_FORMAT_IDENTIFIERS, \substr($matches[1], 2)) !== false) {
					if (\substr($matches[1], 0, 2) === 'fc') {
						$dateTimeObj = \DateTime::createFromFormat('U', $fileObj->getCTime());
					}
					elseif (\substr($matches[1], 0, 2) === 'fm') {
						$dateTimeObj = \DateTime::createFromFormat('U', $fileObj->getMTime());
					}
					else {
						throw new \Exception('Unexpected placeholder: ' . $matches[1]);
					}

					$dateTimeObj->setTimeZone(new DateTimeZone(\date_default_timezone_get()));

					if ($timeOffsetObj !== null) {
						$dateTimeObj->add($timeOffsetObj);
					}

					return \formatDateTimeByIdentifier($dateTimeObj, \substr($matches[1], 2));
				}
			}

			return $matches[0];
		}, $format, -1);

		if (\strpos($newFile, \UNKNOWN_INFORMATION_MARKER) === false) {
			$newPath = $directoryObj->getRealPath() . \DIRECTORY_SEPARATOR . $newFile;

			$nameAvailable = !\file_exists($newPath);

			if ($mode === 'preview') {
				if (\in_array($newFile, $renamedFrom, true)) {
					$nameAvailable = true;
				}

				if (\in_array($newFile, $renamedTo, true)) {
					$nameAvailable = false;
				}
			}

			if ($nameAvailable) {
				if ($mode === 'preview' || @\rename($fileObj->getRealPath(), $newPath)) {
					echo '  > ' . $newFile;
					echo \PHP_EOL;

					$renamedFrom[] = $file;
					$renamedTo[] = $newFile;
				}
				else {
					echo '  ! ' . $newFile;
					echo \PHP_EOL;
					echo '  ! File could not be renamed';
					echo \PHP_EOL;
				}
			}
			else {
				echo '  ! ' . $newFile;
				echo \PHP_EOL;
				echo '  ! Filename does already exist';
				echo \PHP_EOL;
			}
		}
		else {
			echo '  ! Unknown information for file';
			echo \PHP_EOL;
		}
	}
}

echo \PHP_EOL;
echo \count($renamedFrom) . ' of ' . $total . ' files';

if ($mode === 'apply') {
	echo ' have been renamed';
}
elseif ($mode === 'preview') {
	echo ' would be renamed';
}
else {
	throw new \Exception('Unknown mode: ' . $mode);
}

echo \PHP_EOL;

exit(0);

function formatDateTimeByIdentifier(\DateTime $dateTime, $identifier) {
	switch ($identifier) {
		case 'f': return $dateTime->format('Y-m-d H.i.s');
		case 'd': return $dateTime->format('Y-m-d');
		case 't': return $dateTime->format('H.i.s');
		case 'y': return $dateTime->format('Y');
		case 'm': return $dateTime->format('m');
		case 'a': return $dateTime->format('d');
		case 'h': return $dateTime->format('H');
		case 'i': return $dateTime->format('i');
		case 's': return $dateTime->format('s');
		case 'e': return $dateTime->format('o');
		case 'w': return $dateTime->format('W');
		case 'k': return $dateTime->format('N');
		case 'u': return $dateTime->format('U');
		default: throw new \Exception('Unknown identifier: ' . $identifier);
	}
}

function makeFilenameWithoutExtension(\SplFileInfo $file) {
	return $file->getBasename(\FILENAME_EXTENSION_SEPARATOR . $file->getExtension());
}

function readExifDataFromFile($filePath) {
	if (empty($filePath)) {
		return null;
	}

	$exifExtensionResultArray = @\exif_read_data($filePath, null, true, false);

	if ($exifExtensionResultArray !== false) {
		return $exifExtensionResultArray;
	}

	$exifToolCommandStr = 'env -i exiftool ' . \escapeshellarg($filePath) . ' 2>/dev/null';
	$exifToolResultStr = @\shell_exec($exifToolCommandStr);

	if ($exifToolResultStr === false || $exifToolResultStr === null) {
		return null;
	}

	$output = [];
	$output['FILE'] = [];
	$output['FILE']['FileName'] = \readExifValueFromString($exifToolResultStr, 'File Name');
	$output['FILE']['FileDateTime'] = @\filemtime($filePath);
	$output['FILE']['FileSize'] = (int) @\filesize($filePath);
	$output['FILE']['MimeType'] = \readExifValueFromString($exifToolResultStr, 'MIME Type');
	$output['COMPUTED'] = [];
	$output['COMPUTED']['Width'] = (int) \readExifValueFromString($exifToolResultStr, 'Image Width');
	$output['COMPUTED']['Height'] = (int) \readExifValueFromString($exifToolResultStr, 'Image Height');
	$output['COMPUTED']['html'] = 'width="' . $output['COMPUTED']['Width'] . '" height="' . $output['COMPUTED']['Height'] . '"';
	$output['IFD0'] = [];
	$output['IFD0']['ImageWidth'] = (int) \readExifValueFromString($exifToolResultStr, 'Image Width');
	$output['IFD0']['ImageLength'] = (int) \readExifValueFromString($exifToolResultStr, 'Image Height');
	$output['IFD0']['Make'] = \readExifValueFromString($exifToolResultStr, 'Make');
	$output['IFD0']['Model'] = \readFirstExifValueFromStrings($exifToolResultStr, [ 'Camera Model Name'/* e.g. Apple HEIC */, 'Model'/* e.g. Apple MOV */ ]);
	$output['IFD0']['Orientation'] = readExifOrientationFromString($exifToolResultStr);
	$output['IFD0']['XResolution'] = (int) \readExifValueFromString($exifToolResultStr, 'X Resolution') . '/1';
	$output['IFD0']['YResolution'] = (int) \readExifValueFromString($exifToolResultStr, 'Y Resolution') . '/1';
	$output['IFD0']['ResolutionUnit'] = readExifResolutionUnitFromString($exifToolResultStr);
	$output['IFD0']['Software'] = \readExifValueFromString($exifToolResultStr, 'Software');
	$output['IFD0']['DateTime'] = \readExifValueFromString($exifToolResultStr, 'Modify Date');
	$output['EXIF'] = [];
	$output['EXIF']['ISOSpeedRatings'] = (int) \readExifValueFromString($exifToolResultStr, 'ISO');
	$output['EXIF']['ExifVersion'] = \readExifValueFromString($exifToolResultStr, 'Exif Version');
	$output['EXIF']['DateTimeOriginal'] = \readExifValueFromString($exifToolResultStr, 'Date/Time Original');
	$output['EXIF']['DateTimeDigitized'] = \readExifValueFromString($exifToolResultStr, 'Create Date');
	$output['EXIF']['UndefinedTag:0x9010'] = \readExifValueFromString($exifToolResultStr, 'Offset Time');
	$output['EXIF']['UndefinedTag:0x9011'] = \readExifValueFromString($exifToolResultStr, 'Offset Time Original');
	$output['EXIF']['UndefinedTag:0x9012'] = \readExifValueFromString($exifToolResultStr, 'Offset Time Digitized');
	$output['EXIF']['SubSecTime'] = \readExifValueFromString($exifToolResultStr, 'Sub Sec Time');
	$output['EXIF']['SubSecTimeOriginal'] = \readExifValueFromString($exifToolResultStr, 'Sub Sec Time Original');
	$output['EXIF']['SubSecTimeDigitized'] = \readExifValueFromString($exifToolResultStr, 'Sub Sec Time Digitized');
	$output['EXIF']['ExifImageWidth'] = (int) \readExifValueFromString($exifToolResultStr, 'Exif Image Width');
	$output['EXIF']['ExifImageLength'] = (int) \readExifValueFromString($exifToolResultStr, 'Exif Image Height');
	$output['EXIF']['ExposureMode'] = readExifExposureModeFromString($exifToolResultStr);
	$output['EXIF']['WhiteBalance'] = readExifWhiteBalanceFromString($exifToolResultStr);
	$output['EXIF']['FocalLengthIn35mmFilm'] = (int) \readExifValueFromString($exifToolResultStr, 'Focal Length In 35mm Format');
	$output['EXIF']['Contrast'] = readExifContrastFromString($exifToolResultStr);
	$output['EXIF']['Saturation'] = readExifSaturationFromString($exifToolResultStr);
	$output['EXIF']['Sharpness'] = readExifSharpnessFromString($exifToolResultStr);
	$output['EXIF']['SubjectDistanceRange'] = readExifSubjectDistanceRangeFromString($exifToolResultStr);
	$output['EXIF']['UndefinedTag:0xA433'] = \readExifValueFromString($exifToolResultStr, 'Lens Make');
	$output['EXIF']['UndefinedTag:0xA434'] = \readExifValueFromString($exifToolResultStr, 'Lens Model');
	$output['GPS'] = [];
	$output['GPS']['GPSLatitudeRef'] = \substr(\readExifValueFromString($exifToolResultStr, 'GPS Latitude Ref'), 0, 1);
	$output['GPS']['GPSLatitude'] = \readExifGpsCoordinatesFromString($exifToolResultStr, 'GPS Latitude');
	$output['GPS']['GPSLongitudeRef'] = \substr(\readExifValueFromString($exifToolResultStr, 'GPS Longitude Ref'), 0, 1);
	$output['GPS']['GPSLongitude'] = \readExifGpsCoordinatesFromString($exifToolResultStr, 'GPS Longitude');
	$output['GPS']['GPSTimeStamp'] = \readExifGpsTimeStampFromString($exifToolResultStr);
	$output['GPS']['GPSImgDirectionRef'] = readExifGpsDirectionRefFromString($exifToolResultStr, 'GPS Img Direction Ref');
	$output['GPS']['GPSImgDirection'] = (int) \readExifValueFromString($exifToolResultStr, 'GPS Img Direction') . '/1';
	$output['GPS']['GPSDateStamp'] = \readExifValueFromString($exifToolResultStr, 'GPS Date Stamp');

	return $output;
}

function readExifValueFromString($exifLines, $exifKey) {
	$delimiter = '/';
	$pattern = $delimiter . '^' . \preg_quote($exifKey, $delimiter) . '[\t ]*:[\t ]*([^\r\n]+)$' . $delimiter . 'm';
	$matched = \preg_match($pattern, $exifLines, $matches);

	if ($matched === 1) {
		return $matches[1];
	}
	else {
		return null;
	}
}

function readFirstExifValueFromStrings($exifLines, $exifKeys) {
	foreach ($exifKeys as $exifKey) {
		$exifValue = \readExifValueFromString($exifLines, $exifKey);

		if (!empty($exifValue)) {
			return $exifValue;
		}
	}

	return null;
}

function readExifOrientationFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Orientation');

	switch ($value) {
		case 'Horizontal (normal)':
			return 1;
		case 'Mirror horizontal':
			return 2;
		case 'Rotate 180':
			return 3;
		case 'Mirror vertical':
			return 4;
		case 'Mirror horizontal and rotate 270 CW':
			return 5;
		case 'Rotate 90 CW':
			return 6;
		case 'Mirror horizontal and rotate 90 CW':
			return 7;
		case 'Rotate 270 CW':
			return 8;
		default:
			return null;
	}
}

function readExifResolutionUnitFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Resolution Unit');

	switch ($value) {
		case 'None':
			return 1;
		case 'inches':
			return 2;
		case 'cm':
			return 3;
		default:
			return null;
	}
}

function readExifExposureModeFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Exposure Mode');

	switch ($value) {
		case 'Auto':
			return 0;
		case 'Manual':
			return 1;
		case 'Auto bracket':
			return 2;
		default:
			return null;
	}
}

function readExifWhiteBalanceFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'White Balance');

	switch ($value) {
		case 'Auto':
			return 0;
		case 'Manual':
			return 1;
		default:
			return null;
	}
}

function readExifContrastFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Contrast');

	switch ($value) {
		case 'Normal':
			return 0;
		case 'Low':
			return 1;
		case 'High':
			return 2;
		default:
			return null;
	}
}

function readExifSaturationFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Saturation');

	switch ($value) {
		case 'Normal':
			return 0;
		case 'Low':
			return 1;
		case 'High':
			return 2;
		default:
			return null;
	}
}

function readExifSharpnessFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Sharpness');

	switch ($value) {
		case 'Normal':
			return 0;
		case 'Soft':
			return 1;
		case 'Hard':
			return 2;
		default:
			return null;
	}
}

function readExifSubjectDistanceRangeFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'Subject Distance Range');

	switch ($value) {
		case 'Unknown':
			return 0;
		case 'Macro':
			return 1;
		case 'Close':
			return 2;
		case 'Distant':
			return 3;
		default:
			return null;
	}
}

function readExifGpsCoordinatesFromString($exifLines, $exifKey) {
	$value = \readExifValueFromString($exifLines, $exifKey);
	$matched = \preg_match('/^([0-9]+) deg ([0-9]+)\' ([0-9.]+)" [NSWE]$/', $value, $matches);

	if ($matched === 1) {
		return [
			((int) $matches[1]) . '/1',
			((int) $matches[2]) . '/1',
			((float) $matches[3] * 100) . '/100',
		];
	}
	else {
		return null;
	}
}

function readExifGpsTimeStampFromString($exifLines) {
	$value = \readExifValueFromString($exifLines, 'GPS Time Stamp');
	$matched = \preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $value, $matches);

	if ($matched === 1) {
		return [
			((int) $matches[1]) . '/1',
			((int) $matches[2]) . '/1',
			((int) $matches[3]) . '/1',
		];
	}
	else {
		return null;
	}
}

function readExifGpsDirectionRefFromString($exifLines, $exifKey) {
	$value = \readExifValueFromString($exifLines, $exifKey);

	switch ($value) {
		case 'Magnetic North':
			return 'M';
		case 'True North':
			return 'T';
		default:
			return null;
	}
}
