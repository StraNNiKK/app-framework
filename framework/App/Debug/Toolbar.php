<?php

defined('APP_FRAMEWORK_MAIN_DIR') || define('APP_FRAMEWORK_MAIN_DIR', dirname(__FILE__) . '/../');
require_once APP_FRAMEWORK_MAIN_DIR . 'Debug/Toolbar/Interface.php';

class App_Debug_Toolbar
{

    protected static $_enabled = true;

    protected $_modules = array();

    protected $_panelHtml = '';

    public function __construct($params)
    {
        self::$_enabled = false;
        
        foreach ($params as $val) {
            $pluginClass = 'App_Debug_Toolbar_' . ucfirst($val);
            
            $res = App_Loader::load($pluginClass);
            if ($res) {
                $pluginObj = new $pluginClass();
                $icon = $pluginObj->getIcon();
                $this->_modules[$val] = array();
                $this->_modules[$val]['content'] = $pluginObj->getHtml();
                $this->_modules[$val]['short'] = $pluginObj->getShortName();
            }
        }
        
        $this->_createPanelHtml();
    }

    public static function isEnabled()
    {
        return (bool) self::$_enabled;
    }

    public function decorate($content)
    {
        $head = "<style type='text/css'>" . $this->_createPanelCss() . "</style>";
        $head .= '<script type="text/javascript">' . $this->_createPanelJs() . '</script>';
        
        $content = str_ireplace('</head>', $head . '</head>', $content);
        $content = str_ireplace('</body>', $this->_panelHtml . '</body>', $content);
        
        return $content;
    }

    protected function _createPanelHtml()
    {
        $html = '<div id="AppDebug_debug">';
        
        foreach ($this->_modules as $key => $val) {
            $html .= '<div id="AppDebug_' . $key . '" class="AppDebug_panel">';
            $html .= $val['content'];
            $html .= '</div>';
        }
        $html .= '<div id="AppDebug_info">';
        foreach ($this->_modules as $key => $val) {
            $html .= '<span class="AppDebug_span clickable" onclick="AppDebugPanel(\'AppDebug_' . $key . '\');">' . $val['short'] . '</span>';
        }
        $html .= '<span onclick="AppDebugSlideBar()" id="AppDebug_toggler" class="AppDebug_span AppDebug_last clickable">«</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $this->_panelHtml = $html;
    }
    
    protected function _createPanelJs()
    {
        $js = <<<EOD
function AppSetCookie(name, value) {
      var valueEscaped = escape(value);
      var expiresDate = new Date();
      expiresDate.setTime(expiresDate.getTime() + 365 * 24 * 60 * 60 * 1000);
      var expires = expiresDate.toGMTString();
      var newCookie = name + "=" + valueEscaped + "; path=/; expires=" + expires;
      if (valueEscaped.length <= 4000) document.cookie = newCookie + ";";
}

function AppGetCookie(name) {
      var prefix = name + "=";
      var cookieStartIndex = document.cookie.indexOf(prefix);
      if (cookieStartIndex == -1) return null;
      var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length);
      if (cookieEndIndex == -1) cookieEndIndex = document.cookie.length;
      return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex));
}

function AppDebugCollapsed() {
	if (parseInt(AppGetCookie('AppDebugCollapsed')) == 1) {
		AppDebugPanel();
		jQuery("#AppDebug_toggler").html("&#187;");
		return jQuery("#AppDebug_debug").css("left", "-"+parseInt(jQuery("#AppDebug_debug").outerWidth()-jQuery("#AppDebug_toggler").outerWidth()+1)+"px");
	}
}

function AppDebugPanel(name) {
	jQuery(".AppDebug_panel").each(function(i){
		if(jQuery(this).css("display") == "block") {
			jQuery(this).slideUp();
		} else {
			if (jQuery(this).attr("id") == name)
				jQuery(this).slideDown();
			else
				jQuery(this).slideUp();
		}
	});
}

function AppDebugSlideBar() {
	if (jQuery("#AppDebug_debug").position().left > 0) {
		AppSetCookie('AppDebugCollapsed', 1);
		AppDebugPanel();
		jQuery("#AppDebug_toggler").html("&#187;");
		return jQuery("#AppDebug_debug").animate({left:"-"+parseInt(jQuery("#AppDebug_debug").outerWidth()-jQuery("#AppDebug_toggler").outerWidth()+1)+"px"}, "normal", "swing");
	} else {
		AppSetCookie('AppDebugCollapsed', 0);
		jQuery("#AppDebug_toggler").html("&#171;");
		return jQuery("#AppDebug_debug").animate({left:"5px"}, "normal", "swing");
	}
}

function AppDebugToggleElement(name, whenHidden, whenVisible) {
	if(jQuery(name).css("display")=="none"){
		jQuery(whenVisible).show();
		jQuery(whenHidden).hide();
	} else {
		jQuery(whenVisible).hide();
		jQuery(whenHidden).show();
	}
	jQuery(name).slideToggle();
}

function AppDebugAddLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			oldonload();
			func();
		}
	}
}

if (typeof jQuery == "undefined") {
	var scriptObj = document.createElement("script");
	scriptObj.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	scriptObj.type = "text/javascript";
	var head=document.getElementsByTagName("head")[0];
	head.insertBefore(scriptObj,head.firstChild);
	AppDebugAddLoadEvent(AppDebugCollapsed)
} else {
	$(document).ready(function() {
		AppDebugCollapsed();
	});
}
EOD;
        return $js;
    }
    
    protected function _createPanelCss()
    {
        $css = <<<EOD
#AppDebug_debug {
	font: 11px/1.4em Lucida Grande, Lucida Sans Unicode, sans-serif;
	position: fixed;
	bottom: 5px;
	left: 5px;
	color: #000;
	z-index: 255;
}

#AppDebug_debug ol {
	margin: 10px 0px;
	padding: 0 25px
}

#AppDebug_debug li {
	margin: 0 0 10px 0;
}

#AppDebug_debug .clickable {
	cursor: pointer
}

#AppDebug_toggler {
	font-weight: bold;
	background: #BFBFBF;
}

.AppDebug_span {
	border: 1px solid #999;
	border-right: 0px;
	background: #DFDFDF;
	padding: 5px 5px;
}

.AppDebug_last {
	border: 1px solid #999;
}

.AppDebug_panel {
	text-align: left;
	position: absolute;
	bottom: 21px;
	width: 600px;
	max-height: 400px;
	overflow: auto;
	display: none;
	background: #E8E8E8;
	padding: 5px;
	border: 1px solid #999;
}

.AppDebug_panel .pre {
	font: 11px/1.4em Monaco, Lucida Console, monospace;
	margin: 0 0 0 22px
}

#AppDebug_exception {
	border: 1px solid #CD0A0A;
	display: block;
}
EOD;
        return $css;
    }
}