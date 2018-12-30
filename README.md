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

#### Segments of the current filename without extension

##### Separated by whitespace

###### From the start of the filename

 * `%w11`, `%w12`, `%w13`, …, `%w19`, … or `%w1n`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%w22`, `%w23`, `%w24`, …, `%w29`, … or `%w2n`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%w33`, `%w34`, `%w35`, …, `%w39`, … or `%w3n`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

###### From the end of the filename

 * `%wi1`, `%w21`, `%w31`, …, `%w91`, … or `%wn1`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%wi2`, `%w32`, `%w42`, …, `%w92`, … or `%wn2`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%wi3`, `%w43`, `%w53`, …, `%w93`, … or `%wn3`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

##### Separated by dashes and hyphens

###### From the start of the filename

 * `%d11`, `%d12`, `%d13`, …, `%d19`, … or `%d1n`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%d22`, `%d23`, `%d24`, …, `%d29`, … or `%d2n`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%d33`, `%d34`, `%d35`, …, `%d39`, … or `%d3n`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

###### From the end of the filename

 * `%di1`, `%d21`, `%d31`, …, `%d91`, … or `%dn1`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%di2`, `%d32`, `%d42`, …, `%d92`, … or `%dn2`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%di3`, `%d43`, `%d53`, …, `%d93`, … or `%dn3`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

##### Separated by parentheses and brackets

###### From the start of the filename

 * `%p11`, `%p12`, `%p13`, …, `%p19`, … or `%p1n`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%p22`, `%p23`, `%p24`, …, `%p29`, … or `%p2n`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%p33`, `%p34`, `%p35`, …, `%p39`, … or `%p3n`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

###### From the end of the filename

 * `%pi1`, `%p21`, `%p31`, …, `%p91`, … or `%pn1`: part from the first segment to the first, second, third, …, ninth, … or last segment
 * `%pi2`, `%p32`, `%p42`, …, `%p92`, … or `%pn2`: part from the second segment to the second, third, fourth, …, ninth, … or last segment
 * `%pi3`, `%p43`, `%p53`, …, `%p93`, … or `%pn3`: part from the third segment to the third, fourth, fifth, …, ninth, … or last segment
 * …

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
