<?php
namespace Sbe1\Webcore;

/**
 * HTTP Request class. Can be used standalone or inside of a controller.
 * 
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class Request {
    private array $_config;
    private $_referer;
    private string $_remote_addr;
    private int $_remote_port;
    private string $_url;
    private $_url_data = null;
    private array $_path_array;
    private int $_path_array_length = 0;
    private array $_cookies;
    private $_params;
    private string $_post_body;
    private $_files = null;
    private string $_method;
    private bool $_is_head_request;
    private bool $_is_ajax;
    private bool $_is_upload;

    /**
     * Constructor
     * 
     $config = [
         'upload_dir' => '/path/to/where/you/want/to/save/uploads/', # please include ending slash
         'extensions' => ['gif','jpg','mp3'] # array of file extensions that you want to allow
                                             # if set to NULL all file types are allowed
     ];
     * 
     * @param array $config
     * 
     * @return self
     */
    public function __construct(array $config=null){
        $default_config = ['upload_dir'=>null, 'extensions'=>[]];
        $this->_config = empty($config) ? $default_config : $config;
        $this->_referer = (string)empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER'];
        $this->_remote_addr = $_SERVER["REMOTE_ADDR"];
        $this->_remote_port = (int)$_SERVER["REMOTE_PORT"];
        $this->_method = $_SERVER['REQUEST_METHOD'];
        $this->_cookies = $_COOKIE;
        $this->_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . strtolower("://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        $this->_url_data = parse_url($this->_url);
        $this->_path_array = array_values(array_filter(explode('/', $this->_url_data['path'])));
        $this->_path_array_length = count($this->_path_array);
        $this->_is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $this->_is_upload = !empty($_FILES);
        $params = null;
        switch ($this->_method) {
            case 'HEAD':
                $this->_is_head_request = true;
                break;
            case 'GET':
            case 'DELETE':
                if (!empty($this->_url_data['query'])) { parse_str($this->_url_data['query'], $params); }
                break;
            case 'POST':
            case 'PUT':
                if ($this->isUpload()) {
                    $this->handleUpload();
                }
                else {
                    $this->_post_body = file_get_contents('php://input');
                    if ($this->isAjax()) {
                        $params = json_decode($this->_post_body, true);
                        if (empty($params)) {
                            parse_str($this->_post_body, $params);
                        }
                    }
                    else { parse_str($this->_post_body, $params); }
                }
                break;
        }
        $this->_params = $params;
    }

    /**
     * Get request referer.
     * 
     * @return string
     */
    public function getReferer () {
        return $this->_referer;
    }

    /**
     * Get remote address of requesting client.
     * 
     * @return string
     */
    public function getRemoteAddr () {
        return $this->_remote_addr;
    }

    /**
     * Get remote port of requesting client.
     * 
     * @return string
     */
    public function getRemotePort () {
        return $this->_remote_port;
    }

    /**
     * Returns $_SYSTEM['REQUEST_METHOD']
     * 
     * @return string
     */
    public function getMethod () {
        return $this->_method;
    }

    /**
     * Returns $_COOKIE
     * 
     * @return array
     */
    public function getCookies () {
        return $this->_cookies;
    }

    /**
     * Returns full request url
     * 
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Returns data structure produced by parse_url()
     * 
     * @return mixed
     */
    public function getUrlData () {
        return $this->_url_data;
    }

    /**
     * Returns REQUEST_URI path as an exploded array.
     * 
     * @return array
     */
    public function getPathArray () {
        return $this->_path_array;
    }
    
    /**
     * Returns an integer representing the
     * length of the array.
     * @return int
     */
    public function getPathArrayLength () {
        return $this->_path_array_length;
    }

    /**
     * Returns raw post body.
     * 
     * @return string
     */
    public function getPostBody () {
        return $this->_post_body;
    }

    /**
     * Returns the POST form data or GET query string
     * as an associative array.
     * 
     * @return array
     */
    public function getParams () {
        return $this->_params;
    }

    /**
     * Returns boolean test result
     * 
     * @return boolean
     */
    public function isHeadRequest () {
        return $this->_is_head_request;
    }

    /**
     * Returns boolean test result
     * 
     * @return boolean
     */
    public function isAjax () {
        return $this->_is_ajax;
    }

    /**
     * Returns boolean test result
     * 
     * @return boolean
     */
    public function isUpload () {
        return $this->_is_upload;
    }

    /**
     * Processes file uploads.
     * 
     * @return void
     */
    public function handleUpload () {
        $this->_params = [];
        foreach ($_POST as $k=>$v) {
            $this->_params[$k] = $v;
        }
        $this->_files = [];
        $check_ext = isset($this->_config['extensions']) && !empty($this->_config['extensions']);
        $move_file = isset($this->_config['upload_dir']) && !empty($this->_config['upload_dir']);
        foreach ($_FILES as $k => $file) {
            $pi = pathinfo($file);
            $file['pathinfo'] = $pi;
            if ($check_ext) {
                if (in_array($pi['extension'], $this->_config['extensions'])) {
                    $this->_files[] = $file;
                }
            }
            else { $this->_files[$file['name']] = $file; }
            if ($move_file) { move_uploaded_file($file['tmp_name'], $this->_config['upload_dir'].$file['name']); }
        }
    }
}
