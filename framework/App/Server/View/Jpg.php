<?php

class App_Server_View_Jpg extends App_Server_View
{

    /**
     * Тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        return 'image/jpeg';
    }

    public function out()
    {
        if (! array_key_exists('image', $this->_data) || strval($this->_data['image']) == '') {
            $this->generateError();
        } else {
            ob_start();
            imagejpeg($this->_data['image']);
            imagedestroy($this->_data['image']);
            return ob_get_clean();
        }
    }
}