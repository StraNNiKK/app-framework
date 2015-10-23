<?php

/**
 * App Framework
 *
 * @category   App
 * @package    App_Loader
 * @version    $Id:$
 */

/**
 * Класс, содержащий методы для поиска и загрузки файлов
 * с другими классами
 *
 * @category App
 * @package App_Loader
 */
class App_Loader
{

    /**
     * Поиск и загрузка файла с классом
     *
     * @static
     *
     * @param string $class
     *            Имя класса
     * @return boolean
     */
    public static function find($class)
    {
        if (class_exists($class) || interface_exists($class)) {
            return true;
        }
        
        // $c = strtolower($class);
        $parts = explode('_', $class);
        $fname = '';
        
        for ($i = 0, $cnt = sizeof($parts); $i < $cnt - 1; $i ++) {
            $fname .= (ucfirst($parts[$i]) . DIRECTORY_SEPARATOR);
        }
        $fname .= (ucfirst($parts[$i]) . '.php');
        
        $incPaths = explode(PATH_SEPARATOR, get_include_path());
        $incPaths = array_unique($incPaths);
        
        foreach ($incPaths as $incPath) {
            if (file_exists($incPath . DIRECTORY_SEPARATOR . $fname)) {
                require_once ($incPath . DIRECTORY_SEPARATOR . $fname);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Практически псевдоним функции App_Loader::find()
     * за исключением того, что в случае успешного поиска
     * искомого класса возвращает его имя.
     *
     * @static
     *
     * @param string $class
     *            Имя класса
     * @return string|boolean
     */
    public static function load($class)
    {
        if (App_Loader::find($class)) {
            return $class;
        }
        return false;
    }
}