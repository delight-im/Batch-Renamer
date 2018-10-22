# Batch Renamer

Platform-independent utility for renaming batches of files

## Requirements

 * PHP 5.6.0+

## Usage

```
php batch-renamer.php <mode> <format> [<directory>] [<timeOffset>]
```

 * `mode`: either `preview` or `apply`
 * `format`: format string for new filenames with optional placeholders (e.g. `%erf - %fnw.%fel`)
 * `directory` (optional): working directory (e.g. `..` or `subfolder`)
 * `timeOffset` (optional): ISO 8601 duration to be added to any time (e.g. `PT36H` or `-P1DT12H`)

### Placeholders

#### Date and time

##### File creation or metadata change time

 * `%fcf`: full date and time (e.g. “1999-12-31 23.59.59”)
 * `%fcd`: full date (e.g. “1999-12-31”)
 * `%fct`: full time (e.g. “23.59.59”)
 * `%fcy`: year as four decimal digits
 * `%fcm`: month of the year as two decimal digits with leading zeros (“01” through “12”)
 * `%fca`: day of the month as two decimal digits with leading zeros (“01” through “31”)
 * `%fch`: hour of the day as two decimal digits with leading zeros (“00” through “23”)
 * `%fci`: minute of the hour as two decimal digits with leading zeros (“00” through “59”)
 * `%fcs`: second of the minute as two decimal digits with leading zeros (“00” through “59”)
 * `%fce`: week-numbering year as four decimal digits
 * `%fcw`: week of the year as two decimal digits with leading zeros (“01” through “53”)
 * `%fck`: day of the week as a number from “1” (Monday) to “7” (Sunday)
 * `%fcu`: number of seconds since the Unix Epoch

##### File modification time

 * `%fmf`: full date and time (e.g. “1999-12-31 23.59.59”)
 * `%fmd`: full date (e.g. “1999-12-31”)
 * `%fmt`: full time (e.g. “23.59.59”)
 * `%fmy`: year as four decimal digits
 * `%fmm`: month of the year as two decimal digits with leading zeros (“01” through “12”)
 * `%fma`: day of the month as two decimal digits with leading zeros (“01” through “31”)
 * `%fmh`: hour of the day as two decimal digits with leading zeros (“00” through “23”)
 * `%fmi`: minute of the hour as two decimal digits with leading zeros (“00” through “59”)
 * `%fms`: second of the minute as two decimal digits with leading zeros (“00” through “59”)
 * `%fme`: week-numbering year as four decimal digits
 * `%fmw`: week of the year as two decimal digits with leading zeros (“01” through “53”)
 * `%fmk`: day of the week as a number from “1” (Monday) to “7” (Sunday)
 * `%fmu`: number of seconds since the Unix Epoch

##### EXIF recording time

 * `%erf`: full date and time (e.g. “1999-12-31 23.59.59”)
 * `%erd`: full date (e.g. “1999-12-31”)
 * `%ert`: full time (e.g. “23.59.59”)
 * `%ery`: year as four decimal digits
 * `%erm`: month of the year as two decimal digits with leading zeros (“01” through “12”)
 * `%era`: day of the month as two decimal digits with leading zeros (“01” through “31”)
 * `%erh`: hour of the day as two decimal digits with leading zeros (“00” through “23”)
 * `%eri`: minute of the hour as two decimal digits with leading zeros (“00” through “59”)
 * `%ers`: second of the minute as two decimal digits with leading zeros (“00” through “59”)
 * `%ere`: week-numbering year as four decimal digits
 * `%erw`: week of the year as two decimal digits with leading zeros (“01” through “53”)
 * `%erk`: day of the week as a number from “1” (Monday) to “7” (Sunday)
 * `%eru`: number of seconds since the Unix Epoch

#### File properties

 * `%fne`: current filename with extension
 * `%fnw`: current filename without extension
 * `%feo`: current file extension in original form
 * `%fel`: current file extension in lower case
 * `%feu`: current file extension in upper case
 * `%fsb`: file size in bytes

#### Numeration

 * `%ndc`: consecutive number in decimal digits
 * `%nhl`: consecutive number in lowercase hexadecimal digits
 * `%nhu`: consecutive number in uppercase hexadecimal digits

#### EXIF

 * `%eiw`: image width in pixels
 * `%eih`: image height in pixels
 * `%ecb`: camera brand
 * `%ecm`: camera model

#### Hashes and checksums

 * `%hm5`: MD5 hash in lowercase hexadecimal digits
 * `%hs1`: SHA-1 hash in lowercase hexadecimal digits
 * `%hs2`: SHA-256 hash in lowercase hexadecimal digits
 * `%had`: Adler-32 checksum in lowercase hexadecimal digits
 * `%hcr`: CRC32 checksum in lowercase hexadecimal digits

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

This project is licensed under the terms of the [MIT License](https://opensource.org/licenses/MIT).
