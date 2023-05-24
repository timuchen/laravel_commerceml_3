<?php
/**
 * Filename.php
 * Date: 19.05.2017
 * Time: 11:03
 * Author: Maksim Klimenko
 * Email: mavsan@gmail.com
 */

namespace Timuchen\LaravelCommerceml3\Model;

class FileName
{
    /** недопустимые символы в имени файла */
    const invalidChars = "\\/:*?\"'<>|~#&;";
    /** @var string Переданное имя файла */
    protected $originalFileName;
    /** @var array расширения файлов - скриптов */
    protected $script_ext = [
        'php',
        'php3',
        'php4',
        'php5',
        'php6',
        'phtml',
        'pl',
        'asp',
        'aspx',
        'cgi',
        'dll',
        'exe',
        'ico',
        'shtm',
        'shtml',
        'fcg',
        'fcgi',
        'fpl',
        'asmx',
        'pht',
        'py',
        'psp',
        'var',
    ];
    /** @var array не допустимые имена файлов */
    protected $unsafeFiles = [
        '.htaccess',
        '.htpasswd',
        'web.config',
        'global.asax',
    ];

    /**
     * Filename constructor.
     *
     * @param string $fileName имя файла
     */
    public function __construct($fileName)
    {
        $this->originalFileName = $fileName;
    }

    /**
     * Проверка того, что обрабатываемый файл не в списке запрещенных
     *
     * @return bool
     */
    public function isFileUnsafe()
    {
        $name = $this->getProcessFileName();

        return in_array(mb_strtolower($this->TrimUnsafe($name)),
            $this->unsafeFiles);
    }

    /**
     * @return string
     */
    protected function getProcessFileName()
    {
        $fileName = $this->getFileName();
        $fileName = $this->TrimUnsafe($fileName);
        $fileName = str_replace("\\", "/", $fileName);
        $fileName = rtrim($fileName, "/");

        $p = mb_strpos($fileName, "/");
        if ($p !== false) {
            return mb_substr($fileName, $p + 1);
        }

        return $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "",
            $this->originalFileName);
        $filename = trim(str_replace("\\", "/", trim($filename)), "/");

        return $filename;
    }

    /**
     * Удаление не безопасных символов из имени файла
     *
     * @param $path
     *
     * @return string
     */
    protected function trimUnsafe($path)
    {
        return rtrim($path, "\0.\\/+ ");
    }

    /**
     * Проверка валидности пути
     *
     * @param $path
     *
     * @return bool
     */
    public function validatePathString($path)
    {
        if (mb_strlen($path) > 4096) {
            return false;
        }

        $p = trim($path);
        if ($p == '') {
            return false;
        }

        if (mb_strpos($path, "\0") !== false) {
            return false;
        }

        return (preg_match("#^([a-z]:)?/([^\x01-\x1F"
                .preg_quote(self::invalidChars, "#")."]+/?)*$#isD",
                $path) > 0);
    }

    /**
     * Проверка - имеет ли переданное имя файла от 1С не верное расширение файла
     *
     * @param $filename
     *
     * @return bool
     */
    public function hasScriptExtension()
    {
        $ext = $this->script_ext;

        $filename = $this->getProcessFileName();

        $arParts = explode(".", $filename);
        foreach ($arParts as $i => $part) {
            if ($i > 0
                && in_array(mb_strtolower($this->trimUnsafe($part)), $ext)
            ) {
                return true;
            }
        }

        return false;
    }
}
