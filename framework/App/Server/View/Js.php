<?php

class App_Server_View_Js extends App_Server_View
{

    /**
     * Тип возвращаемых данных (для заголовков)
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/javascript';
    }

    public function out()
    {
        if (array_key_exists('content', $this->_data) && $this->_data['content'] !== false) {
            $expires = 60 * 60 * 24 * 7;
            
            $response = App_Server::getResponse();
            $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
            $response->setHeader('Pragma', 'maxage=' . $expires);
            $response->setHeader('Cache-Control', 'public');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
            
            return $this->_data['content'];
        } else {
            if ($this->checkAssigned()) {
                return '';
            } else {
                $this->generateError();
            }
        }
    }
}