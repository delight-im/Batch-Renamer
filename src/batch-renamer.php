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
				return $fileObj->getBasename(\FILENAME_EXTENSION_SEPARATOR . $fileObj->getExtension());
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
			elseif ($matches[1] === 'ndc') {
				return $total;
			}
			elseif ($matches[1] === 'nhl') {
				return \strtolower(\dechex($total));
			}
			elseif ($matches[1] === 'nhu') {
				return \strtoupper(\dechex($total));
			}
			elseif (\substr($matches[1], 0, 1) === 'e') {
				$exif = @\exif_read_data($fileObj->getRealPath(), null, true, false);

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
