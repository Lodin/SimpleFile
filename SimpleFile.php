<?php
/**
 * Class provides the interface to handle php $_FILES array in all cases.
 * Uploaded files data will be incapsulated in the SimpleFile classes.
 *
 * @version		0.1.0
 *
 * @author		Vlad Rindevich (https://github.com/Lodin)
 * @license		This software is licensed under the BSL-1.0 (http://opensource.org/licenses/BSL-1.0)
 * @copyright	Vlad Rindevich
 */
class SimpleFile
{
    /**
     * File name (from $_FILES).
     *
     * @var string
     */
    public $name;

    /**
     * File mime type (from $_FILES).
     *
     * @var string
     */
    public $type;

    /**
     * File size (from $_FILES).
     *
     * @var type
     */
    public $size;

    /**
     * File name in temp folder for uploaded files (from $_FILES).
     *
     * @var string
     */
    public $tmpName;

    /**
     * File errors (from $_FILES).
     *
     * @var string
     */
    public $error;

    /**
     * Field name is the name of `input file` if there is single name, or
     * the array name if `input file` name is like `Array[path][to][filed]`.
     *
     * @var string|null
     */
    public $field = null;

    /**
     * Path to file filed if file field has name like `Array[path][to][filed]`.
     * `0` is the first pocket after form name.
     *
     * @var array|null
     */
    public $path = null;

    /**
     * Disassembles $_FILES array to SimpleFile objects.
     *
     * @param array $files            $_FILES array
     * @param bool  $removeEmptyFiles defines need to remove files with empty
     *                                `name` fields
     *
     * @return array list of SimpleFile objects.
     *
     * @throws FileTransferException if $_FILES is empty
     */
    public static function disassemble(array $files, $removeEmptyFiles = false)
    {
        if (empty($files)) {
            throw new LogicException('File list is empty');
        }

        if (isset(current($files)['name']) && !is_array(current($files)['name'])) {
            $fileList = array();

            foreach ($files as $name => $file) {
                if (!$removeEmptyFiles || ($removeEmptyFiles && $file['name'] !== '')) {
                    $fileList[] = static::disassembleSingle($file, $name);
                }
            }

            return $fileList;
        } else {
            return static::disassembleMultiple(current($files), key($files), $removeEmptyFiles);
        }
    }

    private static function disassembleMultiple($element, $name, $removeEmptyFiles)
    {
        $result = array();

        function createFiles($array, &$filelist, &$path)
        {
            foreach ($array as $pathPart => $element) {
                if (!is_array($element)) {
                    $file = new SimpleFile();
                    $file->path = $path;
                    $file->path[] = $pathPart;

                    $filelist[] = $file;
                }
            }

            foreach ($array as $pathPart => $element) {
                if (is_array($element)) {
                    $pathNew = $path;
                    $pathNew[] = $pathPart;
                    createFiles($element, $filelist, $pathNew);
                }
            }
        }

        function getFileInfo($array, &$file, $field)
        {
            foreach ($array as $pathPart => $element) {
                if (in_array($pathPart, $file->path)) {
                    if (is_array($element)) {
                        getFileInfo($element, $file, $field);
                    } else {
                        $file->$field = $element;
                    }
                }
            }
        }

        $path = array();
        createFiles($element['name'], $result, $path);

        foreach ($element as $field => $data) {
            foreach ($result as &$file) {
                getFileInfo($data, $file, static::camelize($field));
            }
        }

        foreach ($result as $i => &$file) {
            if ($removeEmptyFiles && $file->name === '') {
                unset($result[$i]);
                continue;
            }

            $file->field = $name;
        }

        return $result;
    }

    private static function disassembleSingle($element, $name)
    {
        $file = new static();

        $file->name = $element['name'];
        $file->type = $element['type'];
        $file->size = $element['size'];
        $file->tmpName = $element['tmp_name'];
        $file->error = $element['error'];
        $file->field = $name;

        return $file;
    }

    private static function camelize($word)
    {
        return preg_replace_callback('/_([a-z])/', function ($match) {
            return strtoupper($match[1]);
        }, $word);
    }
}
