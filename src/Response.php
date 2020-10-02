<?php
namespace Sbe1\Webcore;

/**
 * Response class. Can be used standalone or inside of a controller.
 * 
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class Response {
    private array $_cookies;
    private int $_status;
    private string $_redirect_url;
    private string $_content_type_header;
    private string $_content;
    private array $_status_headers = array(
        100=>"HTTP/1.1 100 Continue",
        101=>"HTTP/1.1 101 Switching Protocols",
        103=>"HTTP/1.1 103 Early Hints",
        200=>"HTTP/1.1 200 OK",
        201=>"HTTP/1.1 201 Created",
        202=>"HTTP/1.1 202 Accepted",
        204=>"HTTP/1.1 204 No Content",
        205=>"HTTP/1.1 205 Reset Content",
        206=>"HTTP/1.1 206 Partial Content",
        301=>"HTTP/1.1 301 Moved Permanently",
        302=>"HTTP/1.1 302 Found",
        303=>"HTTP/1.1 303 See Other",
        307=>"HTTP/1.1 303 Permanent Redirect",
        400=>"HTTP/1.1 400 Bad Request",
        401=>"HTTP/1.1 401 Unauthorized",
        402=>"HTTP/1.1 402 Payment Required",
        403=>"HTTP/1.1 403 Forbidden",
        404=>"HTTP/1.1 404 Not Found",
        405=>"HTTP/1.1 405 Method Not Allowed",
        406=>"HTTP/1.1 406 Not Acceptable",
        408=>"HTTP/1.1 408 Request Timeout",
        409=>"HTTP/1.1 409 Conflict",
        410=>"HTTP/1.1 410 Gone",
        412=>"HTTP/1.1 412 Precondition Failed",
        413=>"HTTP/1.1 413 Payload Too Large",
        414=>"HTTP/1.1 414 URI Too Long",
        415=>"HTTP/1.1 415 Unsupported Media Type",
        417=>"HTTP/1.1 417 Expectation Failed",
        426=>"HTTP/1.1 426 Upgrade Required",
        428=>"HTTP/1.1 428 Precondition Required",
        429=>"HTTP/1.1 429 Too Many Requests",
        431=>"HTTP/1.1 431 Request Header Fields Too Large",
        451=>"HTTP/1.1 451 Unavailable For Legal Reasons",
        500=>"HTTP/1.1 500 Internal Server Error",
        501=>"HTTP/1.1 501 Not Implemented");

        /**
         * Set and HTTP cookie.
         * 
         * @param string $name
         * @param string $value
         * @param string $path
         * @param string $domain
         * @param boolean $httponly
         * @param boolean $secure
         * @param string $samesite
         * 
         * @return void
         */
    public function setCookie (string $name, string $value, string $path=null,
        string $domain=null, $httponly=true, $secure=true, string $samesite='Lax') {
        $p = empty($path) ? '/' : $path;
        $d = empty($domain) ? '' : ' domain=;'.$domain;
        $h = $httponly ? ' HttpOnly;' : '';
        $s = $secure ? ' Secure;' : '';
        header("Set-Cookie: $name=$value; path=$p;$d$h$s SameSite=$samesite");
    }

    /**
     * Sets HTTP cookies from arguments in a multidimensional array.
     * 
     * @param array $cookies
     * 
     * @return void
     */
    public function setCookies (array $cookies) {
        foreach ($cookies as $cookie) {
            $this->setCookie($cookie['name'], $cookie['value'], $cookie['path'],
                $cookie['domain'], $cookie['httponly'], $cookie['secure'],
                $cookie['samesite']);
        }
    }
    


    /**
     * Determine content type header.
     * 
     * @param boolean $isAjax
     * @param string $charset
     * 
     * @return string
     */
    public function determineContentTypeHeader ($isAjax, $charset=null) {
        $cs = empty($charset) ? 'utf-8' : $charset;
        return $isAjax ? 'Content-Type: application/json; charset='.$cs
            : 'Content-Type: text/html; charset='.$cs;
    }

    /**
     * Set content type header.
     * 
     * @param string $customheader
     * 
     * @return void
     */
    public function setContentTypeHeader (string $customheader=null) {
        $this->_content_type_header = empty($customheader) ?
            $this->determineContentTypeHeader(false, 'utf-8') : $customheader;
    }

    /**
     * Gets the content type header.
     * 
     * @return string
     */
    public function getContentTypeHeader () {
        return $this->_content_type_header;
    }

    /**
     * Sends the content type header to the client.
     * 
     * @return void
     */
    public function sendContentTypeHeader () {
        header($this->_content_type_header);
    }

    /**
     * Get HTTP status header.
     * 
     * @return string
     */
    public function getStatusHeader () {
        return $this->_status_headers[$this->_status];
    }

    /**
     * Sends HTTP status header to the client.
     * 
     * @return void
     */
    public function sendStatusHeader () {
        header($this->getStatusHeader());
    }

    /**
     * Sets the HTTP status code.
     * 
     * @return void
     */
    public function setStatus (int $status) {
        $this->_status = $status;
    }

    /**
     * Return HTTP status code.
     * 
     * @return int
     */
    public function getStatus () {
        return $this->_status;
    }

    /**
     * Set the response body content.
     * 
     * @param int $content
     * 
     * @return void
     */
    public function setContent (string $content) {
        $this->_content = $content;
    }

    /**
     * Get response body content.
     * 
     * @return string
     */
    public function getContent () {
        return $this->_content;
    }

    /**
     * Set redirect URL.
     * 
     * @param string $url
     * 
     * @return void
     */
    public function setRedirectUrl (string $url) {
        $this->_redirect_url = $url;
    }

    /**
     * Get redirect URL.
     * 
     * @return string
     */
    public function getRedirectUrl () {
        return $this->_redirect_url;
    }

    /**
     * Set temporary redirect (code 302) URL and status.
     * 
     * @return string $url
     */
    public function setTemporaryRedirect (string $url) {
        $this->_status = 302;
        $this->_redirect_url = $url;
    }

    /**
     * Set permanent redirect (code 301) URL and status.
     * 
     * @return string $url
     */
    public function setPermanentRedirect (string $url) {
        $this->_status = 301;
        $this->_redirect_url = $url;
    }

    /**
     * Test if current status matches a redirect status code.
     * 
     * @return boolean
     */
    public function isRedirect () {
        return in_array($this->_status, [301,302,307,308]);
    }

    /**
     * Send the complete response.
     * NOTE: The $nobody argument set to boolean TRUE is mainly useful for responding to HEAD requests.
     * HEAD request do not expect a body in the response.
     * 
     * @param boolean $nobody
     * @param array $headers
     * 
     * @return void
     */
    public function sendResponse (array $headers=null, $nobody=false) {
        if ($this->isRedirect()) {
            header($this->getStatusHeader(), true);
            header('Location: '.$this->getRedirectUrl());
        }
        else {
            header($this->getStatusHeader(), true);
            header($this->getContentTypeHeader());
            if (!empty($headers)) {
                foreach ($headers as $header) {
                    header($header);
                }
            }
            if (!$nobody) { echo $this->getContent(); }
        }
    }
}
