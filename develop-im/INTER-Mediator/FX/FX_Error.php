<?php
// This portion of the FX.php distribution was modified from PEAR_Error in
// the PEAR distribution and is released under the PHP license.  A big
// "Thank You" to the authors of PEAR.php.  Used with permission.
// 
// The header comment from the original PEAR.php file is included below.
// Note that in the FX.php distribution, the PHP license is included in the
// file PHP_LICENSE.txt not a file called 'LICENSE' as described below.
//
// Thanks also to Steve Lane for providing the the elegant integration of
// FX.php and PEAR_Error object based error handling!
//
// +--------------------------------------------------------------------+
// | PEAR, the PHP Extension and Application Repository                 |
// +--------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                              |
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,     |
// | that is bundled with this package in the file LICENSE, and is      |
// | available through the world-wide-web at the following url:         |
// | http://www.php.net/license/3_0.txt.                                |
// | If you did not receive a copy of the PHP license and are unable to |
// | obtain it through the world-wide-web, please send a note to        |
// | license@php.net so we can mail you a copy immediately.             |
// +--------------------------------------------------------------------+
// | Authors: Sterling Hughes <sterling@php.net>                        |
// |          Stig Bakken <ssb@php.net>                                 |
// |          Tomas V.V.Cox <cox@idecnet.com>                           |
// +--------------------------------------------------------------------+
//
// $Id: PEAR.php,v 1.80 2004/04/03 06:28:13 cellog Exp $
//

define('FX_ERROR_RETURN', 1);
define('FX_ERROR_PRINT', 2);
define('FX_ERROR_TRIGGER', 4);
define('FX_ERROR_DIE', 8);
define('FX_ERROR_CALLBACK', 16);

class FX_Error
{
    // {{{ properties

    var $error_message_prefix = '';
    var $mode = FX_ERROR_RETURN;
    var $level = E_USER_NOTICE;
    var $code = -1;
    var $message = '';
    var $userinfo = '';
    var $backtrace = null;

    // }}}
    // {{{ constructor

    /**
     * FX_Error constructor
     *
     * @param string $message  message
     *
     * @param int $code     (optional) error code
     *
     * @param int $mode     (optional) error mode, one of: FX_ERROR_RETURN,
     * FX_ERROR_PRINT, FX_ERROR_DIE, FX_ERROR_TRIGGER, or FX_ERROR_CALLBACK
     *
     * @param mixed $options   (optional) error level, _OR_ in the case of
     * FX_ERROR_CALLBACK, the callback function or object/method
     * tuple.
     *
     * @param string $userinfo (optional) additional user/debug info
     *
     * @access public
     *
     */
    function FX_Error($message = 'unknown error', $code = 100,
                      $mode = null, $options = null, $userinfo = null)
    {
        if ($mode === null) {
            $mode = FX_ERROR_RETURN;
        }
        $this->message = 'FX: ' . $message;
        $this->code = $code;
        $this->mode = $mode;
        $this->userinfo = $userinfo;
        if (function_exists("debug_backtrace")) {
            $this->backtrace = debug_backtrace();
        }
        if ($mode & FX_ERROR_CALLBACK) {
            $this->level = E_USER_NOTICE;
            $this->callback = $options;
        } else {
            if ($options === null) {
                $options = E_USER_NOTICE;
            }
            $this->level = $options;
            $this->callback = null;
        }
        if ($this->mode & FX_ERROR_PRINT) {
            if (is_null($options) || is_int($options)) {
                $format = "%s";
            } else {
                $format = $options;
            }
            printf($format, $this->getMessage());
        }
        if ($this->mode & FX_ERROR_TRIGGER) {
            trigger_error($this->getMessage(), $this->level);
        }
        if ($this->mode & FX_ERROR_DIE) {
            $msg = $this->getMessage();
            if (is_null($options) || is_int($options)) {
                $format = "%s";
                if (substr($msg, -1) != "\n") {
                    $msg .= "\n";
                }
            } else {
                $format = $options;
            }
            die(sprintf($format, $msg));
        }
        if ($this->mode & FX_ERROR_CALLBACK) {
            if (is_callable($this->callback)) {
                call_user_func($this->callback, $this);
            }
        }
    }

    // }}}
    // {{{ getMode()

    /**
     * Get the error mode from an error object.
     *
     * @return int error mode
     * @access public
     */
    function getMode()
    {
        return $this->mode;
    }

    // }}}
    // {{{ getCallback()

    /**
     * Get the callback function/method from an error object.
     *
     * @return mixed callback function or object/method array
     * @access public
     */
    function getCallback()
    {
        return $this->callback;
    }

    // }}}
    // {{{ getMessage()


    /**
     * Get the error message from an error object.
     *
     * @return  string  full error message
     * @access public
     */
    function getMessage()
    {
        return ($this->error_message_prefix . $this->message);
    }


    // }}}
    // {{{ getCode()

    /**
     * Get error code from an error object
     *
     * @return int error code
     * @access public
     */
    function getCode()
    {
        return $this->code;
    }

    // }}}
    // {{{ getType()

    /**
     * Get the name of this error/exception.
     *
     * @return string error/exception name (type)
     * @access public
     */
    function getType()
    {
        return get_class($this);
    }

    // }}}
    // {{{ getUserInfo()

    /**
     * Get additional user-supplied information.
     *
     * @return string user-supplied information
     * @access public
     */
    function getUserInfo()
    {
        return $this->userinfo;
    }

    // }}}
    // {{{ getDebugInfo()

    /**
     * Get additional debug information supplied by the application.
     *
     * @return string debug information
     * @access public
     */
    function getDebugInfo()
    {
        return $this->getUserInfo();
    }

    // }}}
    // {{{ getBacktrace()

    /**
     * Get the call backtrace from where the error was generated.
     * Supported with PHP 4.3.0 or newer.
     *
     * @param int $frame (optional) what frame to fetch
     * @return array Backtrace, or NULL if not available.
     * @access public
     */
    function getBacktrace($frame = null)
    {
        if ($frame === null) {
            return $this->backtrace;
        }
        return $this->backtrace[$frame];
    }

    // }}}
    // {{{ addUserInfo()

    function addUserInfo($info)
    {
        if (empty($this->userinfo)) {
            $this->userinfo = $info;
        } else {
            $this->userinfo .= " ** $info";
        }
    }

    // }}}
    // {{{ toString()

    /**
     * Make a string representation of this object.
     *
     * @return string a string with an object summary
     * @access public
     */
    function toString()
    {
        $modes = array();
        $levels = array(E_USER_NOTICE => 'notice',
            E_USER_WARNING => 'warning',
            E_USER_ERROR => 'error');
        if ($this->mode & FX_ERROR_CALLBACK) {
            if (is_array($this->callback)) {
                $callback = get_class($this->callback[0]) . '::' .
                    $this->callback[1];
            } else {
                $callback = $this->callback;
            }
            return sprintf('[%s: message="%s" code=%d mode=callback ' .
                    'callback=%s prefix="%s" info="%s"]',
                get_class($this), $this->message, $this->code,
                $callback, $this->error_message_prefix,
                $this->userinfo);
        }
        if ($this->mode & FX_ERROR_PRINT) {
            $modes[] = 'print';
        }
        if ($this->mode & FX_ERROR_TRIGGER) {
            $modes[] = 'trigger';
        }
        if ($this->mode & FX_ERROR_DIE) {
            $modes[] = 'die';
        }
        if ($this->mode & FX_ERROR_RETURN) {
            $modes[] = 'return';
        }
        return sprintf('[%s: message="%s" code=%d mode=%s level=%s ' .
                'prefix="%s" info="%s"]',
            get_class($this), $this->message, $this->code,
            implode("|", $modes), $levels[$this->level],
            $this->error_message_prefix,
            $this->userinfo);
    }

    // }}}
}

?>