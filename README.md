# File Tools

Several tools I use when working with files and directories, particularly when I have a large amount of files
to work with. I build most of these when exporting years of data for a customer, which ended up being 165,000+
files in 4000+ directories totaling 140GB+. 

## Installation

This package is hosted on packagist installable via [Composer][link-composer].

### Requirements

- PHP version 7.1 or greater (7.2+ recommended)
- Composer (for installation)

### Installing Via Composer

Run the following at the command line in your repo: 
```bash
composer require pendenga/file
```

Or add the following lines to your composer.json file...

```json
"require": {
  "pendenga/file": "0.1.0",
},
```

and run the following command:

```bash
$ composer update
```

This will set the **Pendenga File** as a dependency in your project and install it.

When bootstrapping your application, you will need to require `'vendor/autoload.php'` in order to setup autoloading.

## Tools Available

All logging is done with the Psr\Log\LoggerInterface for compatability with other packages and loggers.
In my examples, I'll just use $logger to mean any LoggerInterface.

### Batch

Batch will take a large number of directories and consolidate them into subdirectories not to exceed a certain size.
The use case was I had 4000+ folders in a single directory, totaling 140GB+ and had uploaded that to Box.com. 
Box.com didn't allow downloads over 15GB so the customer couldn't download the whole set at once. 
This script put sets of hundreds of folders into 15 batch folders of less than 10GB each, so they could be downloaded.

```php
$batch = new Batch(new Scan(), $logger);
$batch->setOption('max_batch_bytes', 10737418240); // 10 gb
$batches = $batch->evaluate($tmp_dir);
// print_r($batches); // review before executing
$batch->execute($batches);

```

### ColumnMap

ColumnMap is used by Manifest to map columns in a spreadsheet to values that need to match up. 
For example if a user-defined manifest spreadsheet has columns (name, size, checksum) or (filename, bytes, md5) those
might both be valid and we need to map the columns in the spreadsheet to be able to work with them.

### Directory

Directory has some tools that are useful when creating directories and moving files around.

```php
$dir = new Directory($logger);

// bool check if directory is empty
if ($dir->checkEmpty($tmp_dir)) { ... }

// array list of files
foreach ($dir->files($tmp_dir) as $file) { ... }

// delete all files in a directory (useful for unit tests)
$dir->deleteFiles($tmp_dir);

// delete all empty subdirectories in a directory (useful for unit tests)
$dir->deleteSubDirs($tmp_dir);
``` 

### Generate

Generate a bunch of random files. You can configure options for a random number of directories, files in each dir, 
lines in each file, characters on each line, etc. Optionally, you can create a manifest file of the new files.

```php
$gen = new Generate($logger);
$gen->setOption('file_base_dir', $tmp_dir)
    ->setManifest(new ManifestWriter($manifest))
    ->files();
```

### Ini

A pretty simple wrapper for the ini file, to help find it in the root directory, even after this package is installed
in a different project, and provide one-line, static functions for ini access.

```php
$tmp_dir = Ini::get('TMP_DIRECTORY');
```

### Manifest

Validate files in a directory using a spreadsheet manifest with relative file name and zero or more of the following: 
 * File size in bytes
 * Number or lines in file
 * md5 checksum
 
```php
$chk = new Manifest(new ColumnMap(), $logger);
$chk->load($path_to_manifest_file);
$results = $chk->validateFiles($path_to_files, new Scan());
```

### ManifestWriter

Used to create a manifest file when files are being created. You can specify which columns get created, but here are
the current options:
 * Relative file name (required)
 * File size in bytes
 * Number or lines in file
 * md5 checksum

### Properties

A simple class to abstract some of the checks that are done in the manifest file. 

```php
$prop = new Properties($path_to_file);
$prop->bytes();
$prop->checksum();
$prop->exists();
$prop->lines();
```

### Scan

Abstraction of some PHP file scanning and GLOB tools. Helps in searching for files, getting lists of files and 
directories.

```php
$scan = new Scan($logger);

// search directory for text files (recursive)
$scan->files($path_to_file_directory, '*.txt'))

// list subdirectories (not recursive)
$scan->directories($path_to_file_directory)
```

[link-composer]: https://getcomposer.org/
