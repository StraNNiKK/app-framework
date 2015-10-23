<?php

interface App_Resource_Min_Interface
{

    /**
     * Минифицировать группу файлов и сохранить минифицированный контент
     * в некоторый файл
     *
     * @param array $files
     *            массив вида array(i => array('file' => '/path/to/file/', 'min' => true|false))
     * @param string $outFile
     *            файл, куда сохраняется минифицированный контент
     * @return string
     */
    public function minFiles($files, $outFile);

    /**
     * Минифицировать код и минифицированный код
     * в некоторый файл
     *
     * @param string $str
     *            код для минификации
     * @param string $outFile
     *            файл, куда сохраняется минифицированный код
     */
    public function minContent($str, $outFile);
}