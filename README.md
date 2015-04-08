# SimpleFile
Transforms `$_FILES` array to a list of objects incapsulating file information to simplify it using.

### Usage
```php
// Simply disassemble $_FILES array
$files = SimpleFile::disassemble($_FILES);

// And now you can use list of SimpleFile objects
echo $files[0]->name;
```
Except `$_FILES` fields, SimpleFile object contains two extra fields: `path`
and `field`.
* `field` contains `$_FILES` root element name. E.g. in `<input type="file" name="first">` `field` will contain `first`, and in `<input type="file" name="FormName[first][second]">` it will be `FormName`.
* `path` contains `$_FILES` nested keys that defines path to data. E.g. in `<input type="file" name="FormName[first][second]">` `path` will be the next array: `['first', 'second']`
