<?php
if (!defined('e107_INIT')) { exit; }

// Multi indice array sort by sweetland@whoadammit.com
if (!function_exists('asortbyindex')) {
	function asortbyindex($sortarray, $index) {
		$lastindex = count ($sortarray) - 1;
		for ($subindex = 0; $subindex < $lastindex; $subindex++) {
			$lastiteration = $lastindex - $subindex;
			for ($iteration = 0; $iteration < $lastiteration; $iteration++) {
				$nextchar = 0;
				if (comesafter ($sortarray[$iteration][$index], $sortarray[$iteration + 1][$index])) {
					$temp = $sortarray[$iteration];
					$sortarray[$iteration] = $sortarray[$iteration + 1];
					$sortarray[$iteration + 1] = $temp;
				}
			}
		}
		return ($sortarray);
	}
}

if (!function_exists('comesafter')) {
	function comesafter($s1, $s2) {
		$order = 1;
		if (strlen ($s1) > strlen ($s2)) {
			$temp = $s1;
			$s1 = $s2;
			$s2 = $temp;
			$order = 0;
		}
		for ($index = 0; $index < strlen ($s1); $index++) {
			if ($s1[$index] > $s2[$index]) return ($order);
				if ($s1[$index] < $s2[$index]) return (1 - $order);
			}
		return ($order);
	}
}

if (!function_exists('multiarray_sort')) {
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = $arr[$key];
        }

        if(!$natsort) 
        {
        	($order=='asc')? asort($sort_values) : arsort($sort_values);
        }
        else
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}



/**
 * TODO - core request handler (non-admin), core response
 */
class e_admin_request
{
	/**
	 * Current GET request array
	 * @var array
	 */
	protected $_request_qry;
	
	/**
	 * Current POST array
	 * @var array
	 */
	protected $_posted_qry;
	
	/**
	 * Current Mode
	 * @var string
	 */
	protected $_mode = 'main';
	
	/**
	 * Key name for mode search
	 * @var string
	 */
	protected $_mode_key = 'mode';
	
	/**
	 * Current action
	 * @var string
	 */
	protected $_action = 'default';
	
	/**
	 * Key name for action search
	 * @var string
	 */
	protected $_action_key = 'action';
	
	/**
	 * Current ID
	 * @var integer
	 */
	protected $_id = 0;
	
	/**
	 * Key name for ID search
	 * @var string
	 */
	protected $_id_key = 'id';
	
	/**
	 * Constructor
	 * 
	 * @param string|array $qry [optional]
	 * @return 
	 */
	public function __construct($request_string = null, $parse = true)
	{
		if(null === $request_string)
		{
			$request_string = str_replace('&amp;', '&', e_QUERY);
		}
		if($parse)
		{
			$this->parseRequest($request_string);
		}
	}
	
	/**
	 * Parse request data
	 * @param string|array $request_data
	 * @return e_admin_request
	 */
	protected function parseRequest($request_data)
	{
		if(is_string($request_data))
		{
			parse_str($request_data, $request_data);
		}
		$this->_request_qry = (array) $request_data;
		
		// Set current mode
		if(isset($this->_request_qry[$this->_mode_key]))
		{
			$this->_mode = preg_replace('/[^\w]/', '', $this->_request_qry[$this->_mode_key]);
		}
		
		// Set current action
		if(isset($this->_request_qry[$this->_action_key]))
		{
			$this->_action = preg_replace('/[^\w]/', '', $this->_request_qry[$this->_action_key]);
		}
		
		// Set current id
		if(isset($this->_request_qry[$this->_id_key]))
		{
			$this->_id = intval($this->_request_qry[$this->_id_key]);
		}
		
		$this->_posted_qry = $_POST; //raw?
		
		return $this;
	}
	
	/**
	 * Retrieve variable from GET scope
	 * If $key is null, all GET data will be returned
	 * 
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getQuery($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_request_qry;
		}
		return (isset($this->_request_qry[$key]) ? $this->_request_qry[$key] : $default);
	}
	
	/**
	 * Set/Unset GET variable
	 * If $key is array, $value is not used.
	 * If $value is null, (string) $key is unset 
	 * 
	 * @param string|array $key
	 * @param mixed $value [optional]
	 * @return e_admin_request
	 */
	public function setQuery($key, $value = null)
	{
		if(is_array($key))
		{
			foreach ($key as $k=>$v)
			{
				$this->setQuery($k, $v);
			}
			return $this;
		}
		
		if(null === $value)
		{
			unset($this->_request_qry[$key]);
			return $this;
		}
		
		$this->_request_qry[$key] = $value;
		return $this;
	}
	
	/**
	 * Retrieve variable from POST scope
	 * If $key is null, all POST data will be returned
	 * 
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getPosted($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_posted_qry;
		}
		return (isset($this->_posted_qry[$key]) ? $this->_posted_qry[$key] : $default);
	}
	
	/**
	 * Set/Unset POST variable
	 * If $key is array, $value is not used.
	 * If $value is null, (string) $key is unset 
	 * 
	 * @param object $key
	 * @param object $value [optional]
	 * @return e_admin_request
	 */
	public function setPosted($key, $value = null)
	{
		if(is_array($key))
		{
			foreach ($key as $k=>$v)
			{
				$this->setPosted($k, $v);
			}
			return $this;
		}
		
		if(null === $value)
		{
			unset($this->_posted_qry[$key]);
			return $this;
		}
		
		$tp = e107::getParser();
		$this->_posted_qry[$tp->post_toForm($key)] = $tp->post_toForm($value);
		return $this;
	}
	
	/**
	 * Get current mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->_mode;
	}
	
	/**
	 * Get current mode name
	 * 
	 * @return string
	 */
	public function getModeName()
	{
		return strtolower(str_replace('-', '_', $this->_mode));
	}
	
	/**
	 * Reset current mode
	 * @param string $mode
	 * @return e_admin_request
	 */
	public function setMode($mode)
	{
		$this->_mode = preg_replace('/[^\w]/', '', $mode);
		$this->setQuery($this->_mode_key, $this->_mode);
		return $this;
	}
	
	/**
	 * Set mode key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setModeKey($key)
	{
		$this->_mode_key = $key;
		return $this;
	}
	
	/**
	 * Get current action
	 * @return 
	 */
	public function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Get current action name
	 * @return string camelized action
	 */
	public function getActionName()
	{
		return $this->camelize($this->_action);
	}
	
	/**
	 * Reset current action
	 * 
	 * @param string $action
	 * @return e_admin_request
	 */
	public function setAction($action)
	{
		$this->_action = preg_replace('/[^\w]/', '', $action);
		$this->setQuery($this->_action_key, $this->_action);
		return $this;
	}
	
	/**
	 * Set action key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setActionKey($key)
	{
		$this->_action_key = $key;
		return $this;
	}
	
	/**
	 * Get current ID
	 * @return integer
	 */
	public function getId()
	{
		return $this->_id;
	}
	
	/**
	 * Reset current ID
	 * @param string $id
	 * @return e_admin_request
	 */
	public function setId($id)
	{
		$id = intval($id);
		$this->_id = $id;
		$this->setQuery($this->_id_key, $id);
		return $this;
	}
	
	/**
	 * Set id key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setIdKey($key)
	{
		$this->_id_key = $key;
		return $this;
	}
	
	/**
	 * Build query string from current request array
	 * @param string|array $merge_with [optional] override request values
	 * @return string url encoded query string
	 */
	public function buildQueryString($merge_with = array())
	{
		$ret = $this->getQuery();
		if(is_string($merge_with))
		{
			parse_str($merge_with, $merge_with);
		}
		return http_build_query(array_merge($ret, (array) $merge_with));
	}
	
	/**
	 * Convert string to camelCase
	 * 
	 * @param string $str
	 * @return string
	 */
	public function camelize($str)
	{
		return implode('', array_map('ucfirst', explode('-', str_replace('_', '-', $str))));
	}
}

/**
 * TODO - front response parent, should do all the header.php work
 */
class e_admin_response
{
	/**
	 * Body segments
	 *
	 * @var array
	 */
	protected $_body = array();
	
	/**
	 * Title segments
	 *
	 * @var unknown_type
	 */
	protected $_title = array();
	
	/**
	 * e107 meta title
	 *
	 * @var array
	 */
	protected $_e_PAGETITLE = array();
	
	/**
	 * e107 meta description
	 *
	 * @var array
	 */
	protected $_META_DESCRIPTION = array();
	
	/**
	 * e107 meta keywords
	 *
	 * @var array
	 */
	protected $_META_KEYWORDS = array();
	
	/**
	 * Render mods
	 *
	 * @var array
	 */
	protected $_render_mod = array();
	
	/**
	 * Meta title segment description
	 *
	 * @var string
	 */
	protected $_meta_title_separator = ' - ';
	
	/**
	 * Title segment separator
	 *
	 * @var string
	 */
	protected $_title_separator = ' &raquo; ';
	
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		$this->__render_mod['default'] = 'admin_page';
	}

	/**
	 * Set body segments for a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function setBody($content, $namespace = 'default')
	{
		$this->_body[$namespace] = $content;
		return $this;
	}

	/**
	 * Append body segment to a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function appendBody($content, $namespace = 'default')
	{
		if(!isset($this->_body[$namespace]))
		{
			$this->_body[$namespace] = array();
		}
		$this->_body[$namespace][] = $content;
		return $this;
	}

	/**
	 * Prepend body segment to a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namespace
	 * @return e_admin_response
	 */
	function prependBody($content, $namespace = 'default')
	{
		if(!isset($this->_body[$namespace]))
		{
			$this->_body[$namespace] = array();
		}
		$this->_body[$namespace] = array_merge(array($content), $this->_body[$namespace]);
		return $this;
	}
	
	/**
	 * Get body segments from a namespace
	 *
	 * @param string $namespace segment namesapce
	 * @param boolean $reset reset segment namespace
	 * @param string|boolean $glue if false return array, else return string
	 * @return string|array
	 */
	function getBody($namespace = 'default', $reset = false, $glue = '')
	{
		$content = vartrue($this->_body[$namespace], array());
		if($reset)
		{
			$this->_body[$namespace] = array();
		}
		if(is_bool($glue))
		{
			return ($glue ? $content : implode('', $content));
		}
		return implode($glue, $content);
	}

	/**
	 * Set title segments for a namespace
	 *
	 * @param string $title
	 * @param string $namespace
	 * @return e_admin_response
	 */
	function setTitle($title, $namespace = 'default')
	{
		$this->_title[$namespace] = array($title);
		return $this;
	}

	/**
	 * Append title segment to a namespace
	 *
	 * @param string $title
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function appendTitle($title, $namespace = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!isset($this->_title[$namespace]))
		{
			$this->_title[$namespace] = array();
		}
		$this->_title[$namespace][] = $title;
		return $this;
	}

	/**
	 * Prepend title segment to a namespace
	 *
	 * @param string $title
	 * @param string $namespace segment namespace
	 * @return e_admin_response
	 */
	function prependTitle($title, $namespace = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!isset($this->_title[$namespace]))
		{
			$this->_title[$namespace] = array();
		}
		$this->_title[$namespace] = array_merge(array($title), $this->_title[$namespace]);
		return $this;
	}

	/**
	 * Get title segments from namespace
	 *
	 * @param string $namespace
	 * @param boolean $reset
	 * @param boolean|string $glue
	 * @return unknown
	 */
	function getTitle($namespace = 'default', $reset = false, $glue = ' - ')
	{
		$content = array();
		if(!isset($this->_title[$namespace]) && is_array($this->_title[$namespace]))
		{
			$content = $this->_title[$namespace];
			
		}
		if($reset)
		{
			unset($this->_title[$namespace]);
		}
		if(is_bool($glue) || empty($glue))
		{
			return ($glue ? $content : implode($this->_title_separator, $content));
		}

		return implode($glue, $content);
	}

	/**
	 * Set render mode for a namespace
	 *
	 * @param string $render_mod
	 * @param string $namespace
	 * @return e_admin_response
	 */
	function setRenderMod($render_mod, $namespace = 'default')
	{
		$this->_render_mod[$namespace] = $render_mod;
		return $this;
	}

	/**
	 * Set render mode for namespace
	 *
	 * @param string $namespace
	 * @return string
	 */
	function getRenderMod($namespace = 'default')
	{
		return varset($this->_render_mod[$namespace], null);
	}

	/**
	 * Add meta title, description and keywords segments
	 *
	 * @param string $meta property name
	 * @param string $content meta content
	 * @return e_admin_response
	 */
	function addMetaData($meta, $content)
	{
		$tp = e107::getParser();
		$meta = '_' . $meta;
		if(isset($this->{$meta}) && !empty($content))
		{
			$this->{$meta}[] = $tp->toAttribute(strip_tags($content));
		}
		return $this;
	}
	
	/**
	 * Add meta title segment
	 *
	 * @param string $title
	 * @return e_admin_response
	 */
	function addMetaTitle($title)
	{
		$this->addMetaData('e_PAGETITLE', $title);
		return $this;
	}
	
	/**
	 * Add meta description segment
	 *
	 * @param string $description
	 * @return e_admin_response
	 */
	function addMetaDescription($description)
	{
		$this->addMetaData('META_DESCRIPTION', $description);
		return $this;
	}
	
	/**
	 * Add meta keywords segment
	 *
	 * @param string $keyword
	 * @return e_admin_response
	 */
	function addMetaKeywords($keyword)
	{
		$this->addMetaData('META_KEYWORDS', $keyword);
		return $this;
	}

	/**
	 * Send e107 meta-data
	 *
	 * @return e_admin_response
	 */
	function sendMeta()
	{
		//HEADERF already included or meta content already sent
		if(e_AJAX_REQUEST || defined('HEADER_INIT') || defined('e_PAGETITLE'))
			return $this;
			
		if(!defined('e_PAGETITLE') && !empty($this->_e_PAGETITLE))
		{
			define('e_PAGETITLE', implode($this->_meta_title_separator, $this->_e_PAGETITLE));
		}
		
		if(!defined('META_DESCRIPTION') && !empty($this->_META_DESCRIPTION))
		{
			define('META_DESCRIPTION', implode(' ', $this->_META_DESCRIPTION));
		}
		if(!defined('META_KEYWORDS') && !empty($this->_META_KEYWORDS))
		{
			define('META_KEYWORDS', implode(', ', $this->_META_KEYWORDS));
		}
		return $this;
	}
	
	/**
	 * Add content segment to the header namespace
	 *
	 * @param string $content
	 * @return e_admin_response
	 */
	function addHeaderContent($content)
	{
		$this->appendBody($content, 'header_content');
		return $this;
	}
	
	/**
	 * Get page header namespace content segments
	 *
	 * @param boolean $reset
	 * @param boolean $glue
	 * @return string
	 */
	function getHeaderContent($reset = true, $glue = "\n\n")
	{
		return $this->getBody('header_content', $reset, $glue);
	}
	
	/**
	 * Switch to iframe mod
	 * FIXME - implement e_IFRAME to frontend - header_default.php
	 *
	 * @return e_admin_response
	 */
	function setIframeMod()
	{
		global $HEADER, $FOOTER, $CUSTOMHEADER, $CUSTOMFOOTER;
		$HEADER = $FOOTER = ''; 
		$CUSTOMHEADER = $CUSTOMFOOTER = array();
		
		// New
		if(!defined('e_IFRAME'))
		{
			define('e_IFRAME', true);
		}
		return $this;
	}

	/**
	 * Send Response Output
	 *
	 * @param string $name segment
	 * @param array $options valid keys are: messages|render|meta|return|raw|ajax
	 * @return mixed
	 */
	function send($name = 'default', $options = array())
	{
		if(is_string($options))
		{
			parse_str($options, $options);
		}
		
		// Merge with all available default options
		$options = array_merge(array(
			'messages' => true, 
			'render' => true, 
			'meta' => false, 
			'return' => false, 
			'raw' => false,
			'ajax' => false
		), $options);
		
		$content = $this->getBody($name, true);
		$title = $this->getTitle($name, true);
		$return = $options['return'];
		
		if($options['ajax'] || e_AJAX_REQUEST)
		{
			$type = $options['ajax'] && is_string($options['ajax']) ? $options['ajax'] : '';
			$this->getJsHelper()->sendResponse($type);
		}
		
		if($options['messages'])
		{
			$content = e107::getMessage()->render().$content;
		}
		
		if($options['meta'])
		{
			$this->sendMeta();
		}
		
		// raw output expected - force return array
		if($options['raw'])
		{
			return array($title, $content, $this->getRenderMod($name));
		}
		
		//render disabled by the controller
		if(!$this->getRenderMod($name))
		{
			$options['render'] = false;
		}

		if($options['render'])
		{
			return e107::getRender()->tablerender($title, $content, $this->getRenderMod($name), $return);
		}
		
		if($return)
		{
			return $content;
		}
		
		print($content);
		return '';
	}
	
	/**
	 * Get JS Helper instance
	 *
	 * @return e_jshelper
	 */
	public function getJsHelper()
	{
		return e107::getSingleton('e_jshelper', true, 'admin_response');
	}
}

/**
 * TODO - request related code should be moved to core
 * request handler
 */
class e_admin_dispatcher
{
	/**
	 * @var e_admin_request
	 */
	protected $_request = null;
	
	/**
	 * @var e_admin_response
	 */
	protected $_response = null;
	
	/** 
	 * @var e_admin_controller
	 */
	protected $_current_controller = null;
	
	/**
	 * Required (set by child class).
	 * Controller map array in format 
	 * 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'path' => 'CONTROLLER SCRIPT PATH']);
	 * 
	 * @var array
	 */
	protected $controllerList;
	
	/**
	 * Optional (set by child class).
	 * Required for admin menu render
	 * Format: 'mode/action' => array('caption' => 'Link title'[, 'perm' => '0', 'url' => '{e_PLUGIN}plugname/admin_config.php'], ...);
	 * All valid key-value pair (see e_admin_menu function) are accepted.
	 * @var array
	 */
	protected $adminMenu = array();
	
	/**
	 * Optional (set by child class).
	 * @var string
	 */
	protected $menuTitle = 'Menu';

	/**
	 * Constructor 
	 * 
	 * @param string|array|e_admin_request $request [optional]
	 * @param e_admin_response $response
	 */
	public function __construct($request = null, $response = null)
	{
		if(null === $request || !is_object($request))
		{
			$request = new e_admin_request($request);
		}
		
		if(null === $response)
		{
			$response = new e_admin_response();
		}
		
		$this->setRequest($request)->setResponse($response)->init();
		//$this->_initController();
		
	}
	
	/**
	 * User defined constructor - called before _initController() method
	 * @return e_admin_dispatcher
	 */
	public function init()
	{
	}
	
	/**
	 * Get request object
	 * @return e_admin_request
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Set request object
	 * @param e_admin_request $request
	 * @return e_admin_dispatcher
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
	}
	
	/**
	 * Get response object
	 * @return e_admin_response
	 */
	public function getResponse()
	{
		return $this->_response;
	}
	
	/**
	 * Set response object
	 * @param e_admin_response $response
	 * @return e_admin_dispatcher
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}
	
	/**
	 * Dispatch & render all
	 * 
	 * @param boolean $return if true, array(title, body, render_mod) will be returned 
	 * @return string|array current admin page body
	 */
	public function run($return = false)
	{
		return $this->runObserver()->renderPage($return);
	}
	
	/**
	 * Run observers/headers only, should be called before header.php call
	 * 
	 * @return e_admin_dispatcher
	 */
	public function runObservers($run_header = true)
	{
		//search for $actionName.'Observer' method. Additional $actionName.$triggerName.'Trigger' methods will be called as well
		$this->getController()->dispatchObserver();
		
		//search for $actionName.'Header' method, js manager should be used inside for sending JS to the page,
		// meta information should be created there as well
		if($run_header)
		{
			$this->getController()->dispatchHeader();
			
		}
		return $this;
	}
	
	/**
	 * Run page action.
	 * If return type is array, it should contain allowed response options (see e_admin_response::send())
	 * Available return type string values:
	 * - render_return: return rendered content ( see e107::getRender()->tablerender()), add system messages, send meta information
	 * - render: outputs rendered content ( see e107::getRender()->tablerender()), add system messages
	 * - response: return response object
	 * - raw: return array(title, content, render mode)
	 * - ajax: force ajax output (and exit)
	 * 
	 * @param string|array $return_type expected string values: render|render_out|response|raw|ajax[_text|_json|_xml]
	 * @return mixed
	 */
	public function runPage($return_type = 'render')
	{
		$response = $this->getController()->dispatchPage();
		if(is_array($return_type))
		{
			return $response->send('default', $return_type);
		}
		switch($return_type)
		{
			case 'render_return':
				$options = array(
					'messages' => true, 
					'render' => true, 
					'meta' => true, 
					'return' => true, 
					'raw' => false
				);
			break;

			case 'raw':
				$options = array(
					'messages' => false, 
					'render' => false, 
					'meta' => false, 
					'return' => true, 
					'raw' => true
				);
			break;

			case 'ajax':
			case 'ajax_text':
			case 'ajax_xml';
			case 'ajax_json';
				$options = array(
					'messages' => false, 
					'render' => false, 
					'meta' => false, 
					'return' => false, 
					'raw' => false,
					'ajax' => str_replace(array('ajax_', 'ajax'), array('', 'text'), $return_type)
				);
			break;
		
			case 'response':
				return $response;
			break;
			
			case 'render':
			default:
				$options = array(
					'messages' => true, 
					'render' => true, 
					'meta' => false, 
					'return' => false, 
					'raw' => false
				);
			break;
		}
		return $response->send('default', $options);
	}
	
	/**
	 * Get current controller object
	 * @return e_admin_controller
	 */
	public function getController()
	{
		if(null === $this->_current_controller)
		{
			$this->_initController();
		}
		return $this->_current_controller;
	}
	
	/**
	 * Try to init Controller from request using current controller map
	 * 
	 * @return e_admin_dispatcher
	 */
	protected function _initController()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		if(isset($this->controllerList[$request->getModeName()]) && isset($this->controllerList[$request->getModeName()]['controller']))
		{
			$class_name = $this->controllerList[$request->getModeName()]['controller'];
			$class_path = vartrue($this->controllerList[$request->getModeName()]['path']);
			
			if($class_path)
			{
				require_once(e107::getParser()->replaceConstants($class_path));
			}
			if($class_name && class_exists($class_name))//NOTE: autoload in the play
			{
				$this->_current_controller = new  $class_name($request, $response);
				//give access to current request object, user defined init
				$this->_current_controller->setRequest($this->getRequest())->init(); 
			}
			else
			{
				//TODO - get default controller (core or user defined), set Action for 
				//'Controller not found' page, add message(?), break
				// get default controller 
				$this->_current_controller = $this->getDefaultController();
				// add messages
				e107::getMessage()->add('Can\'t find class '.($class_name ? $class_name : 'n/a'), E_MESSAGE_ERROR)
					->add('Requested: '.e_SELF.'?'.$request->buildQueryString(), E_MESSAGE_DEBUG);
				// 
				$request->setMode($this->getDefaultControllerName())->setAction('e404');
				$this->_current_controller->setRequest($request)->init(); 
			}
		}
		
		return $this;
	}
	
	/**
	 * Default controller object - needed if controller not found
	 * @return e_admin_controller
	 */
	public function getDefaultController()
	{
		$class_name = $this->getDefaultControllerName();
		return new $class_name($this->getRequest(), $this->getResponse());
	}
	
	/**
	 *  Default controller name - needed if controller not found
	 * @return 
	 */
	public function getDefaultControllerName()
	{
		return 'e_admin_controller';
	}
	
	/**
	 * Generic Admin Menu Generator
	 * @return string
	 */
	function renderMenu()
	{
		$tp = e107::getParser();
		$var = array();
		
		foreach($this->adminMenu as $key => $val)
		{
			$tmp = explode('/', trim($key, '/'), 2);
			
			foreach ($val as $k=>$v)
			{
				switch($k)
				{
					case 'caption':
						$k2 = 'text';
					break;
					
					case 'url':
						$k2 = 'link';
						$v = $tp->replaceConstants($v, 'abs').'?mode='.$tmp[0].'&action='.$tmp[1];
					break;
				
					default:
						$k2 = $k;
					break;
				}
				$var[$key][$k2] = $v;
			}
			if(vartrue($var[$key]['link']))
			{
				$var[$key]['link'] = e_SELF.'?mode='.$tmp[0].'&action='.$tmp[1];
			}
			
			/*$var[$key]['text'] = $val['caption'];
			$var[$key]['link'] = (vartrue($val['url']) ? $tp->replaceConstants($val['url'], 'abs') : e_SELF).'?mode='.$tmp[0].'&action='.$tmp[1];
			$var[$key]['perm'] = $val['perm'];	*/
		}
		$request = $this->getRequest();
		e_admin_menu($this->menuTitle, $request->getMode().'/'.$request->getAction(), $var);
	}
}

class e_admin_controller
{
	/**
	 * @var e_admin_request
	 */
	protected $_request;
	
	/**
	 * @var e_admin_response
	 */
	protected $_response;
	
	/**
	 * @var array User defined parameters
	 */
	protected $_params = array();
	
	/**
	 * Constructor 
	 * @param e_admin_request $request [optional]
	 */
	public function __construct($request, $response, $params = array())
	{
		$this->_params = array('enable_triggers');
		$this->setRequest($request)
			->setResponse($response)
			->setParams($params);
	}
	
	/**
	 * User defined init
	 * Called before dispatch routine
	 */
	public function init()
	{
	}
	
	/**
	 * Get controller parameter
	 * Currently used core parameters:
	 * - enable_triggers: don't use it direct, see {@link setTriggersEnabled()}
	 * - TODO - more parameters
	 * 
	 * @param string $key [optional] if null - get whole array 
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getParam($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_params;
		}
		return (isset($this->_params[$key]) ? $this->_params[$key] : $default);
	}
	
	/**
	 * Set parameter
	 * @param string $key
	 * @param mixed $value
	 * @return e_admin_controller
	 */
	public function setParam($key, $value)
	{
		if(null === $value)
		{
			unset($this->_params[$key]);
			return $this;
		}
		$this->_params[$key] = $value;
		return $this;
	}
	
	/**
	 * Merge passed parameter array with current parameters
	 * @param array $params
	 * @return e_admin_controller
	 */
	public function setParams($params)
	{
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}
	
	/**
	 * Reset parameter array
	 * @param array $params
	 * @return e_admin_controller
	 */
	public function resetParams($params)
	{
		$this->_params = $params;
		return $this;
	}
	
	/**
	 * Get current request object
	 * @return e_admin_request 
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Set current request object
	 * @param e_admin_request $request
	 * @return e_admin_controller
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
	}

	/**
	 * Get current response object
	 * @return e_admin_response 
	 */
	public function getResponse()
	{
		return $this->_response;
	}
	
	/**
	 * Set current response object
	 * @param e_admin_response $response
	 * @return e_admin_controller
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}
	
	/**
	 * Request proxy method 
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getQuery($key = null, $default = null)
	{
		return $this->getRequest()->getQuery($key, $default);
	}
	
	/**
	 * Request proxy method 
	 * @param string|array $key
	 * @param mixed $value [optional]
	 * @return e_admin_controller
	 */
	public function setQuery($key, $value = null)
	{
		$this->getRequest()->setQuery($key, $value);
		return $this;
	}
	
	/**
	 * Request proxy method 
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getPosted($key = null, $default = null)
	{
		return $this->getRequest()->getPosted($key, $default);
	}
	
	/**
	 * Request proxy method 
	 * @param string $key
	 * @param mixed $value [optional]
	 * @return e_admin_controller
	 */
	public function setPosted($key, $value = null)
	{
		$this->getRequest()->setPosted($key, $value);
		return $this;
	}
	
	/**
	 * Add page title, response proxy method
	 *
	 * @param string $title
	 * @return e_admin_controller
	 */
	public function addTitle($title)
	{
		$this->getResponse()->appendTitle($title);
		return $this;
	}
	
	/**
	 * Add page meta title, response proxy method.
	 * Should be called before header.php
	 *
	 * @param string $title
	 * @return e_admin_controller
	 */
	public function addMetaTitle($title)
	{
		$this->getResponse()->addMetaTitle($title);
		return $this;
	}
	
	/**
	 * Add header content, response proxy method
	 * Should be called before header.php
	 * 
	 * @param string $content
	 * @return e_admin_controller
	 */
	public function addHeader($content)
	{
		$this->getResponse()->addHeaderContent($content);
		return $this;
	}
	
	/**
	 * Get header content, response proxy method
	 *
	 * @return string
	 */
	public function getHeader()
	{
		return $this->getResponse()->getHeaderContent();
	}
	
	public function getMode()
	{
		return $this->getRequest()->getMode();
	}
	
	public function getAction()
	{
		return $this->getRequest()->getAction();
	}
	
	public function getId()
	{
		return $this->getRequest()->getId();
	}
	
	/**
	 * Get response owned JS Helper instance, response proxy method
	 *
	 * @return e_jshelper
	 */
	public function getJsHelper()
	{
		return $this->getResponse()->getJsHelper();
	}
	
	/**
	 * Dispatch observer, check for triggers
	 * 
	 * @param string $action [optional]
	 * @return e_admin_controller
	 */
	public function dispatchObserver($action = null)
	{
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionObserverName = $action.(e_AJAX_REQUEST ? 'Ajax' : '').'Observer';
		if(method_exists($this, $actionObserverName))
		{
			$this->$actionObserverName();
		}
		
		// check for triggers, not available in Ajax mode
		if(!e_AJAX_REQUEST && $this->triggersEnabled())
		{
			$posted = $request->getPosted();
			foreach ($posted as $key => $value)
			{
				if(strpos($key, 'etrigger_') === 0)
				{
					$actionTriggerName = $action.$request->camelize(substr($key, 9)).'Trigger';
					if(method_exists($this, $actionTriggerName))
					{
						$this->$actionTriggerName();
					}
					//Check if triggers are still enabled
					if(!$this->triggersEnabled())
					{
						break;
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Dispatch header, not allowed in Ajax mode
	 * @param string $action [optional]
	 * @return e_admin_controller
	 */
	public function dispatchHeader($action = null)
	{
		// not available in Ajax mode
		if(e_AJAX_REQUEST)
		{
			return $this;
		}
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionHeaderName = $action.'Header';
		if(method_exists($this, $actionHeaderName))
		{
			$this->$actionHeaderName();
		}
		
		//send meta data
		$this->getResponse()->sendMeta();
		return $this;
	}
	
	/**
	 * Dispatch controller action
	 * 
	 * @param string $action [optional]
	 * @return e_admin_response
	 */
	public function dispatchPage($action = null)
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionName = $action.(e_AJAX_REQUEST ? 'Ajax' : '').'Page';
		$ret = '';
		if(!method_exists($this, $actionName))
		{
			e107::getMessage()->add('Action '.$actionName.' no found!', E_MESSAGE_ERROR);
		}
		
		ob_start(); //catch any output
		$ret = $this->$actionName();
		
		//Ajax XML/JSON communictaion
		if(e_AJAX_REQUEST && is_array($ret))
		{
			$response_type = $this->getParam('ajax_response', 'xml');
			ob_clean();
			$js_helper = $response->getJsHelper();
			foreach ($ret as $act => $data) 
			{
				$js_helper->addResponseAction($act, $data);
			}
			$js_helper->sendResponse($response_type);
		}
		
		$ret .= ob_get_clean();
		
		// Ajax text response
		if(e_AJAX_REQUEST)
		{
			$response_type = $this->getParam('ajax_response', 'text');
			$response->getJsHelper()->addTextResponse($ret)->sendResponse($response_type);
		}
		else
		{
			$response->appendBody($ret);
		}
		
		return $response;
	}
	
	/**
	 * @return boolean
	 */
	public function triggersEnabled()
	{
		return $this->getParam('enable_triggers');
	}
	
	/**
	 * @param boolean $flag
	 * @return e_admin_controller
	 */
	public function setTriggersEnabled($flag)
	{
		$this->setParam('enable_triggers', $flag);
		return $this;
	}
}

//FIXME - move everything from e_admin_controller_main except model auto-create related code
class e_admin_controller_base extends e_admin_controller
{
	
}

class e_admin_controller_main extends e_admin_controller_base
{
	protected $fields = array();
	protected $fieldpref = array();
	protected $fieldTypes = array();
	protected $dataFields = array();
	protected $validationRules = array();
	protected $prefs = array();
	protected $pluginName;
	protected $listQry;
	protected $editQry;
	protected $table;
	protected $pid;
	protected $pluginTitle;
	protected $perPage = 20;
	
	
	/**
	 * @var e_admin_model
	 */
	protected $_model = null;
	
	/**
	 * @var e_admin_tree_model
	 */
	protected $_tree_model = null;
	
	/**
	 * @var e_admin_tree_model
	 */
	protected $_ui = null;
	
	/**
	 * @var e_plugin_pref|e_core_pref
	 */
	protected $_pref = null;
	
	/**
	 * Constructor 
	 * @param e_admin_request $request
	 * @param e_admin_response $response
	 * @param array $params [optional]
	 */
	public function __construct($request, $response, $params = array())
	{
		parent::__construct($request, $response, $params);
		if(!$this->pluginName)
		{
			$this->pluginName = 'core';
		}
		$this->_pref = $this->pluginName == 'core' ? e107::getConfig() : e107::getPlugConfig($this->pluginName);
		
		$ufieldpref = $this->getUserPref();
		if($ufieldpref)
		{
			$this->fieldpref = $ufieldpref;
		}
		
		$this->addTitle($this->pluginTitle);
	}
	
	/**
	 * Default is List action page
	 * @return string
	 */
	public function DefaultPage()
	{
		return $this->getUI()->getList();
	}
	
	/**
	 * List action observer
	 * @return void
	 */
	public function ListObserver()
	{
		$this->getTreeModel()->load();
	}
	
	/**
	 * List action header
	 * @return void
	 */
	public function ListHead()
	{
		e107::getJs()->headerCore('core/tabs.js')
			->headerCore('core/admin.js');
	}
	
	/**
	 * List action page
	 * @return string
	 */
	public function ListPage()
	{
		return $this->getUI()->getList();
	}
	
	public function getPerPage()
	{
		return $this->perPage;
	}
	
	public function getPrimaryName()
	{
		return $this->pid;
	}
	
	public function getPluginName()
	{
		return $this->pluginName;
	}
	
	public function getPluginTitle()
	{
		return $this->pluginTitle;
	}
	
	public function getTableName()
	{
		return $this->table;
	}
	
	public function getFields()
	{
		return $this->fields;
	}
	
	public function getFieldPref()
	{
		return $this->fieldpref;
	}
	
	/**
	 * Get Config object 
	 * @return e_plugin_pref|e_core_pref
	 */
	public function getConfig()
	{
		return $this->_pref;
	}
	
	/**
	 * Get column preference array
	 * @return array
	 */
	public function getUserPref()
	{
		global $user_pref;
		return vartrue($user_pref['admin_cols_'.$this->getTableName()], array());
	}
	
	public function getModel()
	{
		if(null === $this->_model)
		{
			// default model
			$this->_model = new e_admin_model();
			$this->_model->setModelTable($this->table)
				->setFieldIdName($this->pid)
				->setValidationRules($this->validationRules)
				->setFieldTypes($this->fieldTypes)
				->setDataFields($this->dataFields)
				->load($this->getId());
		}
		return $this->_model;
	}
	
	public function setModel($model)
	{
		$this->_model = $model;
	}
	
	public function getTreeModel()
	{
		if(null === $this->_tree_model)
		{
			// default tree model
			$this->_tree_model = new e_admin_tree_model();
			$this->_tree_model->setModelTable($this->table)
				->setParams(array('model_class' => 'e_admin_model', 'db_query' => $this->listQry));
		}
		return $this->_tree_model;
	}
	
	public function setTreeModel($tree_model)
	{
		$this->_tree_model = $tree_model;
	}
	
	
	public function getUI()
	{
		if(null === $this->_ui)
		{
			// default ui
			$this->_ui = new e_admin_ui($this);
		}
		return $this->_ui;
	}
	
	public function setUI($ui)
	{
		$this->_ui = $ui;
	}
}

class e_admin_ui extends e_form
{	
	/**
	 * @var e_admin_controller_main
	 */
	protected $_controller = null;
	
	/**
	 * Constructor
	 * @param e_admin_controller_main $controller
	 * @return 
	 */
	function __construct($controller, $tabindex = false)
	{
		$this->_controller = $controller;
		parent::__construct($tabindex);
	}
	
	/**
	 * User defined init
	 */
	public function init()
	{
	}
	
	/**
	 * Create list view
	 * Search for the following GET variables:
	 * - from: integer, current page
	 * 
	 * @return string
	 */
	public function getList()
	{
		$controller = $this->getController();
		$request = $controller->getRequest(); 
		$tree = $controller->getTreeModel()->getTree();
		$total = $controller->getTreeModel()->getTotal();
		
		$amount = $controller->getPerPage();
		$from = $controller->getQuery('from', 0);
		$field = $controller->getQuery('field', $controller->getPrimaryName());
		$asc = strtoupper($controller->getQuery('asc', 'desc'));
		$elid = ($controller->getPluginName() == 'core' ? 'core-' : '').str_replace('_', '-', $controller->getTableName());

		$text = $tree ? $this->renderFilter() : '';
        $text .= "
			<form method='post' action='".e_SELF."?' id='{$elid}-list-form'>
				<fieldset id='{$elid}-list'>
					<legend class='e-hideme'>".$controller->getPluginTitle()."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist' id='{$elid}-list-table'>
						".$this->colGroup($controller->getFields(), $controller->getFieldPref())."
						".$this->thead($controller->getFields(), $controller->getFieldPref(), 'mode='.$request->buildQueryString('field=[FIELD]&asc=[ASC]&from=[FROM]'))."
						<tbody>
		";


		if(!$tree)
		{
			$text .= "
							<tr>
								<td colspan='".count($controller->getFieldPref())."' class='center middle'>".LAN_NO_RECORDS."</td>
							</tr>
			";
		}
		else
		{
			foreach($tree as $model)
			{
				$text .= $this->trow($controller->getFields(), $controller->getFieldPref(), $field->getData(), $controller->getPrimaryName());
			}

		}

		$text .= "
						</tbody>
					</table>
		";
					
		$text .= $tree ? $this->renderBatch() : '';
		
		$text .= "
				</fieldset>
			</form>
		";
		
		if($tree)
		{ 
			$parms = $total.",".$amount.",".$from.",".e_SELF.'?'.$request->buildQueryString('from=[FROM]');
	    	$text .= e107::getParser()->parseTemplate("{NEXTPREV={$parms}}");
		}

		//e107::getRender()->tablerender($this->pluginTitle." :: ".$this->adminMenu['list']['caption'], $mes->render().$text);
		return $text;
	}
	
	function renderFilter($current_query, $input_options = array())
	{
		if(!$input_options) $input_options = array('size' => 20);
		$text = "
			<form method='get' action='".e_SELF."?".e_QUERY."'>
			<div class='left' style='padding-bottom:10px'>
				".$this->text('searchquery', $current_query, 50, $input_options)."
				".$this->select_open('filter_options', array('class' => 'tbox select e-filter-options', 'id' => false))."
					".$this->option('Display All', '')."
					".$this->renderBatchFilter('filter')."
				".$this->select_close()."
				".$this->admin_button('etrigger_filter', LAN_FILTER)."
			</div>
			</form>
		"; //TODO assign CSS
		
		return $text;	
	}
	
	function renderBatch()
	{	
		$fields = $this->getController()->getFields();
		if(!varset($fields['checkboxes']))
		{
			return '';
		}	
		
		$text = "<div class='buttons-bar left'>
         	<img src='".e_IMAGE_ABS."generic/branchbottom.gif' alt='' class='icon action' />";
			$text .= $frm->select_open('etrigger_batch', array('class' => 'tbox select e-execute-batch', 'id' => false)).
			$this->option('With selected...', '').			
			$this->option(LAN_DELETE, 'batch__delete');
		$text .= $this->renderBatchFilter('batch');	
		$text .= "</div>";
		
		return $text;

	}
	
	// TODO - do more
	function renderBatchFilter($type='batch') // Common function used for both batches and filters. 
	{
		$optdiz = array('batch' => 'Modify ', 'filter'=> 'Filter by ');
		$table = $this->getController()->getTableName();
				
		foreach($this->getController()->getFields() as $key=>$val)
		{
			if(!varset($val[$type]))
			{
				continue;
			}
			
			$option = array();
			
			switch($val['type'])
			{
					case 'boolean': //TODO modify description based on $val['parm]
						$option[$type.'__'.$key."__1"] = LAN_YES;
						$option[$type.'__'.$key."__0"] = LAN_NO;
					break;
					
					case 'dropdown': // use the array $parm; 
						foreach($val['parm'] as $k=>$name)
						{
							$option[$type.'__'.$key."__".$k] = $name;
						}
					break;
					
					case 'date': // use $parm to determine unix-style or YYYY-MM-DD 
					    //TODO last hour, today, yesterday, this-month, last-month etc. 
					/*	foreach($val['parm'] as $k=>$name)
						{
							$text .= $frm->option($name, $type.'__'.$key."__".$k);	
						}*/
					break;
					
					case 'userclass':
						$classes = e107::getUserClass()->uc_required_class_list($val['parm']);
						foreach($classes as $k=>$name)
						{
							$option[$type. '__'.$key."__".$k] = $name;
						}
					break;					
				
					case 'method':
						$method = $key;
						$list = $this->$method('', $type);
						foreach($list as $k=>$name)
						{
							$option[$type.'__'.$key."__".$k] = $name;
						}
					break;
			}
				
				if(count($option)>0)
				{
					$text .= "\t".$this->optgroup_open($optdiz[$type].$val['title'], $disabled)."\n";
					foreach($option as $okey=>$oval)
					{
						$sel = ($_SESSION[$table."_".$type] == $okey) ? TRUE : FALSE; //FIXME - GET
						$text .= $this->option($oval, $okey, $sel)."\n";			
					}
					$text .= "\t".$this->optgroup_close()."\n";	
				}
				
					
		}
		
		return $text;
		
	}
	
	/**
	 * @return e_admin_controller_main
	 */
	public function getController()
	{
		return $this->_controller;
	}
}

// One handler to rule them all
// see e107_plugins/release/admin_config.php.  
class e_admin_ui_dummy extends e_form
{	
	/**
	 * @var e_admin_controller_main
	 */
	protected $_controller = null;
	
	/**
	 * Constructor
	 * @param e_admin_controller_main $controller
	 * @return 
	 */
	function __construct($controller)
	{
		$this->_controller = $controller;
		parent::__construct(false);
	}
	
	function init()
	{
		
		global $user_pref; // e107::getConfig('user') ??
		
		$this->mode = varset($_GET['mode']) ? $_GET['mode'] : 'list';
		
		$column_pref_name = "admin_".$this->table."_columns";
				
		if(isset($_POST['submit-e-columns']))
		{		
			$user_pref[$column_pref_name] = $_POST['e-columns'];
			save_prefs('user');
			$this->mode = 'list';
		}
				
		$this->fieldpref = (varset($user_pref[$column_pref_name])) ? $user_pref[$column_pref_name] : array_keys($this->fields);		
		
		foreach($this->fields as $k=>$v) // Find Primary table ID field (before checkboxes is run. ). 
		{
			if(vartrue($v['primary']))
			{
				$this->pid = $k;
			}
		}
		
		
		if(varset($_POST['execute_batch']))
		{
			if(vartrue($_POST['multiselect']))
			{
				// $_SESSION[$this->table."_batch"] = $_POST['execute_batch']; // DO we want this to 'stick'?
				list($tmp,$field,$value) = explode('__',$_POST['execute_batch']);
				$this->processBatch($field,$_POST['multiselect'],$value);
			}
			$this->mode = 'list';	
		}
				
		if(varset($_POST['execute_filter'])) // Filter the db records. 
		{
			$_SESSION[$this->table."_filter"] = $_POST['filter_options'];
			list($tmp,$filterField,$filterValue) = explode('__',$_POST['filter_options']);
			$this->modifyListQry($_POST['searchquery'],$filterField,$filterValue);
			$this->mode = 'list';	
		}
		
			
		if(varset($_POST['update']) || varset($_POST['create']))
		{
		
			$id = intval($_POST['record_id']);
			$this->saveRecord($id);
		}
		
		if(varset($_POST['delete']))
		{
			$id = key($_POST['delete']);
			$this->deleteRecord($id);
			$this->mode = "list";
		}
		
		if(varset($_POST['saveOptions']))
		{
			$this->saveSettings();
		}
		
		if(varset($_POST['edit']))
		{
			$this->mode = 'create';
		}
		
		
		if($this->mode) // Render Page. 
		{
			$method = $this->mode."Page";
			$this->$method();
		}
		
	}


	function modifyListQry($search,$filterField,$filterValue)
	{
		$searchQry = array();
			
			if(vartrue($filterField) && vartrue($filterValue))
			{
				$searchQry[] = $filterField." = '".$filterValue."'";
			}
			
			$filter = array();
			
			foreach($this->fields as $key=>$var)
			{
				if(($var['type'] == 'text' || $var['type'] == 'method') && vartrue($search))
				{
					$filter[] = "(".$key." REGEXP ('".$search."'))";	
				}
			}
			if(count($filter)>0)
			{
				$searchQry[] = " (".implode(" OR ",$filter)." )";
			}
			if(count($searchQry)>0)
			{
				$this->listQry .= " WHERE ".implode(" AND ",$searchQry);
			}
	}




	function processBatch($field,$ids,$value)
	{
		$sql = e107::getDb();
		
		if($field == 'delete')
		{
			return $sql->db_Delete($this->table,$this->pid." IN (".implode(",",$ids).")");	
		}
		
		if(!is_numeric($value))
		{
			$value = "'".$value."'";	
		}
		
		$query = $field." = ".$value." WHERE ".$this->pid." IN (".implode(",",$ids).") ";
		$count = $sql->db_Update($this->table,$query);
	}
	
 
	
	/**
	 * Generic DB Record Creation Form. 
	 * @param object $id [optional]
	 * @return 
	 */
	function createPage()
	{
		global $e_userclass, $e_event;
		
		$id = varset($_POST['edit']) ? key($_POST['edit']) : "";

		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$frm = e107::getForm();
		

		if($id)
		{
			$query = str_replace("{ID}",$id,$this->editQry);
			$sql->db_Select_gen($query);
			$row = $sql->db_Fetch(MYSQL_ASSOC);			
		}
		else
		{
			$row = array();
		}

		$text = "
			<form method='post' action='".e_SELF."?mode=list' id='dataform' enctype='multipart/form-data'>
				<fieldset id='core-cpage-create-general'>
					<legend class='e-hideme'>".$this->pluginTitle."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>";
			
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!==TRUE)
			{
				$text .= "
					<tr>
						<td class='label'>".$att['title']."</td>
						<td class='control'>".$this->renderElement($key,$row)."</td>
					</tr>";
			}
							
		}

		$text .= "
			</tbody>
			</table>	
		<div class='buttons-bar center'>";
					
					if($id)
					{
						$text .= $frm->admin_button('update', LAN_UPDATE, 'update');
						$text .= "<input type='hidden' name='record_id' value='".$id."' />";						
					}	
					else
					{
						$text .= $frm->admin_button('create', LAN_CREATE, 'create');	
					}
					
		$text .= "
			</div>
			</fieldset>
		</form>";	
		
		$ns->tablerender($this->pluginTitle." :: ".$this->adminMenu['create']['caption'], $text);
	}
	
	
	/**
	 * Generic Save DB Record Function. 
	 * @param object $id [optional]
	 * @return 
	 */
	function saveRecord($id=FALSE)
	{
		global $e107cache, $admin_log, $e_event;

		$sql = e107::getDb();
		$tp = e107::getParser();
		$mes = e107::getMessage();
		
		$insert_array = array();
		
		//TODO validation and sanitizing using above classes. 
		
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!=TRUE)
			{
				$insert_array[$key] = $_POST[$key]; 
			}
		}
			
		if($id)
		{
			$insert_array['WHERE'] = $this->primary." = ".$id;
			$status = $sql->db_Update($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_UPDATED;	// deliberately ambiguous - to be used on success or error. 

		}
		else
		{
			$status = $sql->db_Insert($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_CREATED;	
		}
		

		$mes->add($message, $status);		
	}

	/**
	 * Generic Delete DB Record Function. 
	 * @param object $id
	 * @return 
	 */
	function deleteRecord($id)
	{
		if(!$id || !$this->primary || !$this->table)
		{
			return;
		}
		
		$mes = e107::getMessage();
		$sql = e107::getDb();
		
		$query = $this->primary." = ".$id;
		$status = $sql->db_Delete($this->table,$query) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
		$message = LAN_DELETED; 
		$mes->add($message, $status);	
	}



	/**
	 * Render Form Element (edit page)
	 * @param object $key
	 * @param object $row
	 * @return 
	 */
	function renderElement($key,$row)
	{
		$frm = e107::getForm();
		
		$att = ($this->mode == 'options') ? $this->prefs[$key] : $this->fields[$key];
		$value = $row[$key];	
		
		if($att['type']=='method')
		{
			$meth = $key;
			return $this->$meth($value);
		}
		
		if($att['type']=='boolean')
		{
			return $frm->radio_switch($key, $row[$key]);	
		}
		
		return $frm->text($key, $row[$key], 50);
			
	}




	/**
	 * Render Field value (listing page)
	 * @param object $key
	 * @param object $row
	 * @return 
	 */
	function renderValue($key,$row) // NO LONGER REQUIRED. use $frm->trow();
	{
		$att = $this->fields[$key];	
		//TODO add checkbox. 
				
		if($att['type']=='method')
		{
			$meth = $key;
			return $this->$meth($row[$key]);
		}
				
		
		if($key == "options")
		{
			$id = $this->primary;
	//		$text = "<input type='image' class='action edit' name='edit[{$row[$id]}]' src='".ADMIN_EDIT_ICON_PATH."' title='".LAN_EDIT."' />";
	//		$text .= "<input type='image' class='action delete' name='delete[{$row[$id]}]' src='".ADMIN_DELETE_ICON_PATH."' title='".LAN_DELETE." [ ID: {$row[$id]} ]' />";
	//		return $text;
		}
		
		switch($att['type']) 
		{
			case 'url':
				return "<a href='".$row[$key]."'>".$row[$key]."</a>";
			break;
		
			default:
				return $row[$key];
			break;
		}	
		return $row[$key] .$att['type'];	
	}


	/**
	 * Generic Options/Preferences Form. 
	 * @return 
	 */
	function optionsPage()
	{
		$pref = e107::getConfig()->getPref();
		$frm = e107::getForm();
		$ns = e107::getRender();
		$mes = e107::getMessage();

		//XXX Lan - Options
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
				<fieldset id='core-cpage-options'>
					<legend class='e-hideme'>".LAN_OPTIONS."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>\n";
						
						
						foreach($this->prefs as $key => $var)
						{
							$text .= "
							<tr>
								<td class='label'>".$var['title']."</td>
								<td class='control'>
									".$this->renderElement($key,$pref)."
								</td>
							</tr>\n";	
						}
					
						$text .= "</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('saveOptions', LAN_SAVE, 'submit')."
					</div>
				</fieldset>
			</form>
		";

		$ns->tablerender($this->pluginTitle." :: ".LAN_OPTIONS, $mes->render().$text);
	}


	function saveSettings() //TODO needs to use native e_model functions, validation etc.  
	{
		global $pref, $admin_log;
		
		unset($_POST['saveOptions'],$_POST['e-columns']);
		
		foreach($_POST as $key=>$val)
		{
			e107::getConfig('core')->set($key,$val);
		}
						
		e107::getConfig('core')->save();
	}
	
	/**
	 * @return e_admin_controller_main
	 */
	public function getController()
	{
		return $this->_controller;
	}
}

