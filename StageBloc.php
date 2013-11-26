<?php
require_once 'StageBloc/Exception.php';
require_once 'StageBloc/Version.php';

/**
 * StageBloc API wrapper with support for authentication using OAuth 2
 *
 * @category  Services
 * @package   Services_StageBloc
 * @author    Josh Holat <bumblebee@stagebloc.com>
 * @copyright 2012 Josh Holat <bumblebee@stagebloc.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/stagebloc/php-sb-connect
 *
 * This code was adapted from the SoundCloud API wrapper by Anton Lindqvist <anton@qvister.se>.
 *
 */
class Services_StageBloc
{

    /**
     * Custom cURL option
     *
     * @var integer
     *
     * @access public
     */
    const CURLOPT_OAUTH_TOKEN = 173;

    /**
     * Access token returned by the service provider after a successful authentication
     *
     * @var string
     *
     * @access private
     */
    private $_accessToken;

    /**
     * Version of the API to use
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_apiVersion = 3.0;

    /**
     * Supported audio MIME types
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_audioMimeTypes = array(
        'aiff' => 'audio/x-aiff',
        'wav' => 'audio/x-wav'
    );

    /**
     * OAuth client id
     *
     * @var string
     *
     * @access private
     */
    private $_clientId;

    /**
     * OAuth client secret
     *
     * @var string
     *
     * @access private
     */
    private $_clientSecret;

    /**
     * Default cURL options
     *
     * @var array
     *
     * @access private
     * @static
     */
     private static $_curlDefaultOptions = array(
         CURLOPT_HEADER => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_USERAGENT => ''
     );

    /**
     * cURL options
     *
     * @var array
     *
     * @access private
     */
    private $_curlOptions;

    /**
     * Development mode
     *
     * @var boolean
     *
     * @access private
     */
     private $_development;

    /**
     * Available API domains
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_domains = array(
        'development' => 'stagebloc.dev', // Note: This doesn't actually exist yet
        'production' => 'stagebloc.com'
    );

    /**
     * HTTP response body from the last request
     *
     * @var string
     *
     * @access private
     */
    private $_lastHttpResponseBody;

    /**
     * HTTP response code from the last request
     *
     * @var integer
     *
     * @access private
     */
    private $_lastHttpResponseCode;

    /**
     * HTTP response headers from last request
     *
     * @var array
     *
     * @access private
     */
    private $_lastHttpResponseHeaders;

    /**
     * OAuth paths
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_paths = array(
        'authorize' => 'connect',
        'access_token' => '2.0/oauth2/token/',
    );

    /**
     * OAuth redirect URI
     *
     * @var string
     *
     * @access private
     */
    private $_redirectUri;

    /**
     * API response format MIME type
     *
     * @var string
     *
     * @access private
     */
    private $_requestFormat;

    /**
     * Available response formats
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_responseFormats = array(
        'json' => 'application/json',
        'xml' => 'application/xml'
    );

    /**
     * HTTP user agent
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_userAgent = 'PHP-StageBloc';

    /**
     * Class constructor
     *
     * @param string  $clientId     OAuth client id
     * @param string  $clientSecret OAuth client secret
     * @param string  $redirectUri  OAuth redirect URI
     * @param boolean $development  Sandbox mode
     *
     * @return void
     * @throws Services_StageBloc_Missing_Client_Id_Exception
     *
     * @access public
     */
    function __construct($clientId, $clientSecret, $redirectUri = null, $development = false)
    {
        if (empty($clientId)) {
            throw new Services_StageBloc_Missing_Client_Id_Exception();
        }

        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_development = $development;
        $this->_responseFormat = self::$_responseFormats['xml'];
        $this->_curlOptions = self::$_curlDefaultOptions;
        $this->_curlOptions[CURLOPT_USERAGENT] .= $this->_getUserAgent();
    }

    /**
     * Get authorization URL
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     *
     * @access public
     * @see StageBloc::_buildUrl()
     */
    function getAuthorizeUrl($params = array())
    {
        $defaultParams = array(
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code'
        );
        $params = array_merge($defaultParams, $params);

        return $this->_buildUrl(self::$_paths['authorize'], $params, false);
    }

    /**
     * Get access token URL
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     *
     * @access public
     * @see StageBloc::_buildUrl()
     */
    function getAccessTokenUrl($params = array())
    {
        return $this->_buildUrl(self::$_paths['access_token'], $params, false);
    }

    /**
     * Retrieve access token
     *
     * @param string $code        Optional OAuth code returned from the service provider
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see StageBloc::_getAccessToken()
     */
    function accessToken($code = null, $postData = array(), $curlOptions = array())
    {
        $defaultPostData = array(
            'code' => $code,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'authorization_code'
        );
        $postData = array_filter(array_merge($defaultPostData, $postData));

        return $this->_getAccessToken($postData, $curlOptions);
    }

    /**
     * Refresh access token
     *
     * @param string $refreshToken The token to refresh
     * @param array  $postData     Optional post data
     * @param array  $curlOptions  Optional cURL options
     *
     * @return mixed
     * @see StageBloc::_getAccessToken()
     *
     * @access public
     */
    function accessTokenRefresh($refreshToken, $postData = array(), $curlOptions = array())
    {
        $defaultPostData = array(
            'refresh_token' => $refreshToken,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'refresh_token'
        );
        $postData = array_merge($defaultPostData, $postData);

        return $this->_getAccessToken($postData, $curlOptions);
    }

    /**
     * Get access token
     *
     * @return mixed
     *
     * @access public
     */
    function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * Get API version
     *
     * @return integer
     *
     * @access public
     */
    function getApiVersion()
    {
        return self::$_apiVersion;
    }

    /**
     * Get the corresponding MIME type for a given file extension
     *
     * @param string $extension Given extension
     *
     * @return string
     * @throws Services_StageBloc_Unsupported_Audio_Format_Exception
     *
     * @access public
     */
    function getAudioMimeType($extension)
    {
        if (array_key_exists($extension, self::$_audioMimeTypes)) {
            return self::$_audioMimeTypes[$extension];
        } else {
            throw new Services_StageBloc_Unsupported_Audio_Format_Exception();
        }
    }

    /**
     * Get cURL options
     *
     * @param string $key Optional options key
     *
     * @return mixed
     *
     * @access public
     */
    function getCurlOptions($key = null)
    {
        if ($key) {
            return (array_key_exists($key, $this->_curlOptions))
                ? $this->_curlOptions[$key]
                : false;
        } else {
            return $this->_curlOptions;
        }
    }

    /**
     * Get development mode
     *
     * @return boolean
     *
     * @access public
     */
    function getDevelopment()
    {
        return $this->_development;
    }

    /**
     * Get HTTP response header
     *
     * @param string $header Name of the header
     *
     * @return mixed
     *
     * @access public
     */
    function getHttpHeader($header)
    {
        if (is_array($this->_lastHttpResponseHeaders)
            && array_key_exists($header, $this->_lastHttpResponseHeaders)
        ) {
            return $this->_lastHttpResponseHeaders[$header];
        } else {
            return false;
        }
    }

    /**
     * Get redirect URI
     *
     * @return string
     *
     * @access public
     */
    function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    /**
     * Get response format
     *
     * @return string
     *
     * @access public
     */
    function getResponseFormat()
    {
        return $this->_responseFormat;
    }

    /**
     * Set access token
     *
     * @param string $accessToken Access token
     *
     * @return object
     *
     * @access public
     */
    function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;

        return $this;
    }

    /**
     * Set cURL options
     *
     * The method accepts arguments in two ways.
     *
     * You could pass two arguments when adding a single option.
     * <code>
     * $stagebloc->setCurlOptions(CURLOPT_SSL_VERIFYHOST, 0);
     * </code>
     *
     * You could also pass an associative array when adding multiple options.
     * <code>
     * $stagebloc->setCurlOptions(array(
     *     CURLOPT_SSL_VERIFYHOST => 0,
     *    CURLOPT_SSL_VERIFYPEER => 0
     * ));
     * </code>
     *
     * @return object
     *
     * @access public
     */
    function setCurlOptions()
    {
        $args = func_get_args();
        $options = (is_array($args[0]))
            ? $args[0]
            : array($args[0] => $args[1]);

        foreach ($options as $key => $val) {
            $this->_curlOptions[$key] = $val;
        }

        return $this;
    }

    /**
     * Set redirect URI
     *
     * @param string $redirectUri Redirect URI
     *
     * @return object
     *
     * @access public
     */
    function setRedirectUri($redirectUri)
    {
        $this->_redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Set response format
     *
     * @param string $format Response format, could either be XML or JSON
     *
     * @return object
     * @throws Services_StageBloc_Unsupported_Response_Format_Exception
     *
     * @access public
     */
    function setResponseFormat($format)
    {
        if (array_key_exists($format, self::$_responseFormats)) {
            $this->_responseFormat = self::$_responseFormats[$format];
        } else {
            throw new Services_StageBloc_Unsupported_Response_Format_Exception();
        }

        return $this;
    }

    /**
     * Set development mode
     *
     * @param boolean $development Development mode
     *
     * @return object
     *
     * @access public
     */
    function setDevelopment($development)
    {
        $this->_development = $development;

        return $this;
    }

    /**
     * Send a GET HTTP request
     *
     * @param string $path        Request path
     * @param array  $params      Optional query string parameters
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see StageBloc::_request()
     */
    function get($path, $params = array(), $curlOptions = array())
    {
        $url = $this->_buildUrl($path, $params);

        return $this->_request($url, $curlOptions);
    }

    /**
     * Send a POST HTTP request
     *
     * @param string $path        Request path
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see StageBloc::_request()
     */
    function post($path, $postData = array(), $curlOptions = array())
    {
        $url = $this->_buildUrl($path);
        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $options += $curlOptions;

        return $this->_request($url, $options);
    }

    /**
     * Send a PUT HTTP request
     *
     * @param string $path        Request path
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see StageBloc::_request()
     */
    function put($path, $postData, $curlOptions = array())
    {
        $url = $this->_buildUrl($path);
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        );
        $options += $curlOptions;

        return $this->_request($url, $options);
    }

    /**
     * Send a DELETE HTTP request
     *
     * @param string $path        Request path
     * @param array  $params      Optional query string parameters
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see StageBloc::_request()
     */
    function delete($path, $params = array(), $curlOptions = array())
    {
        $url = $this->_buildUrl($path, $params);
        $options = array(CURLOPT_CUSTOMREQUEST => 'DELETE');
        $options += $curlOptions;

        return $this->_request($url, $options);
    }

    /**
     * Construct default HTTP request headers
     *
     * @param boolean $includeAccessToken Include access token
     *
     * @return array $headers
     *
     * @access protected
     */
    protected function _buildDefaultHeaders($includeAccessToken = true)
    {
        $headers = array();

        if ($this->_responseFormat) {
            array_push($headers, 'Accept: ' . $this->_responseFormat);
        }

        if ($includeAccessToken && $this->_accessToken) {
            array_push($headers, 'Authorization: OAuth ' . $this->_accessToken);
        }

        return $headers;
    }

    /**
     * Construct a URL
     *
     * @param string  $path           Relative or absolute URI
     * @param array   $params         Optional query string parameters
     * @param boolean $includeVersion Include API version
     *
     * @return string $url
     *
     * @access protected
     */
    protected function _buildUrl($path, $params = array(), $includeVersion = true)
    {
        if (!$this->_accessToken) {
            $params['consumer_key'] = $this->_clientId;
        }

		$responseFormatParts = explode('/', $this->_responseFormat);

        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            $url = 'http' . ( $this->_development ? '' : 's' ) . '://';
            $url .= (!preg_match('/connect/', $path)) ? 'api.' : '';
            $url .= ($this->_development)
                ? self::$_domains['development']
                : self::$_domains['production'];
            $url .= '/';
            $url .= ($includeVersion) ? number_format(self::$_apiVersion, 1) . '/' : '';
            $url .= $path . ($includeVersion && strpos($path, '.') === false ? '.' . end($responseFormatParts) : '' );
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';

        return $url;
    }

    /**
     * Retrieve access token
     *
     * @param array $postData    Post data
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access protected
     */
    protected function _getAccessToken($postData, $curlOptions = array())
    {
        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $options += $curlOptions;
        $response = json_decode(
            $this->_request($this->getAccessTokenUrl(), $options),
            true
        );

        if (array_key_exists('access_token', $response)) {
            $this->_accessToken = $response['access_token'];

            return $response;
        } else {
            return false;
        }
    }

    /**
     * Get HTTP user agent
     *
     * @return string
     *
     * @access protected
     */
    protected function _getUserAgent()
    {
        return self::$_userAgent . '/' . new Services_StageBloc_Version;
    }

    /**
     * Parse HTTP headers
     *
     * @param string $headers HTTP headers
     *
     * @return array $parsedHeaders
     *
     * @access protected
     */
    protected function _parseHttpHeaders($headers)
    {
        $headers = explode("\n", trim($headers));
        $parsedHeaders = array();

        foreach ($headers as $header) {
            if (!preg_match('/\:\s/', $header)) {
                continue;
            }

            list($key, $val) = explode(': ', $header, 2);
            $key = str_replace('-', '_', strtolower($key));
            $val = trim($val);

            $parsedHeaders[$key] = $val;
        }

        return $parsedHeaders;
    }

    /**
     * Validate HTTP response code
     *
     * @param integer $code HTTP code
     *
     * @return boolean
     *
     * @access protected
     */
    protected function _validResponseCode($code)
    {
        return (bool)preg_match('/^20[0-9]{1}$/', $code) || $code == 302;
    }

    /**
     * Performs the actual HTTP request using cURL
     *
     * @param string $url         Absolute URL to request
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws Services_StageBloc_Invalid_Http_Response_Code_Exception
     *
     * @access protected
     */
    protected function _request($url, $curlOptions = array())
    {
        $ch = curl_init($url);
        $options = $this->_curlOptions;
        $options += $curlOptions;

        if (array_key_exists(self::CURLOPT_OAUTH_TOKEN, $options)) {
            $includeAccessToken = $options[self::CURLOPT_OAUTH_TOKEN];
            unset($options[self::CURLOPT_OAUTH_TOKEN]);
        } else {
            $includeAccessToken = true;
        }

        if (array_key_exists(CURLOPT_HTTPHEADER, $options)) {
            $options[CURLOPT_HTTPHEADER] = array_merge(
                $this->_buildDefaultHeaders(),
                $curlOptions[CURLOPT_HTTPHEADER]
            );
        } else {
            $options[CURLOPT_HTTPHEADER] = $this->_buildDefaultHeaders(
                $includeAccessToken
            );
        }

        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        $this->_lastHttpResponseHeaders = $this->_parseHttpHeaders(
            substr($data, 0, $info['header_size'])
        );
        $this->_lastHttpResponseBody = substr($data, $info['header_size']);
        $this->_lastHttpResponseCode = $info['http_code'];

        if ($this->_validResponseCode($this->_lastHttpResponseCode)) {
            return $this->_lastHttpResponseBody;
        } else {
            throw new Services_StageBloc_Invalid_Http_Response_Code_Exception(
                null,
                0,
                $this->_lastHttpResponseBody,
                $this->_lastHttpResponseCode
            );
        }
    }
}
