<?php
/**
 * Function for finding different between two dates
 * @var date_1 date of the format YYYY-MM-DD
 * @var date_2 date of the format YYYY-MM-DD
 */
if (! function_exists('date_difference'))
{
	function date_difference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
		$datetime1 = date_create($date_1);
		$datetime2 = date_create($date_2);

		$interval = date_diff($datetime1, $datetime2);

		return $interval->format($differenceFormat);
	}
}

if (! function_exists('get_page'))
{
	function get_page()
	{
		$page = isset($_GET["page"]) && !empty($_GET['page'])? (int) $_GET["page"] : 1;

		return $page === 0? 1 : abs($page);
	}
}

if (! function_exists('calc_page_offset'))
{
	function calc_page_offset($page, $item)
	{
		return ($page - 1) * $item;
	}
}

if (! function_exists('pagination_link'))
{
	function pagination_link($total_items, $items_per_page)
	{
		$pages = ceil($total_items / $items_per_page);
		$prev_page = isset($_GET["page"]) && $_GET["page"] > 1? $_GET["page"] - 1 : 1;
		$next_page = isset($_GET["page"]) && $_GET["page"] < $pages? $_GET["page"] + 1 : $pages;
		$current_page = isset($_GET["page"]) && !empty($_GET["page"])? $_GET["page"] : 1;
		// $get = $_SERVER["QUERY_STRING"];

		$link = htmlspecialchars($_SERVER["PHP_SELF"]);
		$html = '<div class="pagination">
				<a class="btn txt-dark bg-pagination" href="'. $link . '?page=' . $prev_page .'" class="href">&laquo;</a>';

		for($i = 1; $i <= $pages; $i++) {
			if($i == $current_page)
				$html .= '<a class="btn txt-dark pagination-active bg-pagination" href="'. $link . '?page=' . $i .'" class="href">'. $i .'</a>';
			else
				$html .= '<a class="btn txt-dark bg-pagination" href="'. $link . '?page=' . $i .'" class="href">'. $i .'</a>';
		}
		$html .= '<a class="btn txt-dark bg-pagination" href="'. $link . '?page=' . $next_page .'" class="href">&raquo;</a>';
		$html .= "</div>";

		return $html;
	}
}

// Building dash header navigation (e.g. Dashboard > Member > Add Member)
if (! function_exists('get_running_page'))
{
	function get_running_page()
	{
		$page = $_SERVER["SCRIPT_NAME"];
		$page = explode("/", $page);
		$page = $page[count($page) - 1];
		$page = trim($page, '/');
		$page = explode(".", $page)[0];

		return ucfirst(strtolower($page));
	}
}

if (! function_exists('title_case'))
{
	function title_case($string)
	{
		return ucwords(strtolower($string));
	}
}

if (! function_exists('gen_file_name'))
{
	function gen_file_name($length = 20)
	{
		 $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		 $clen   = strlen( $chars )-1;
		 $id  = '';

		 for ($i = 0; $i < $length; $i++) {
				 $id .= $chars[mt_rand(0,$clen)];
		 }
		 return ($id);
	}
}

if (! function_exists('gen_option_html_tag'))
{
	function gen_option_html_tag($from, $to, $selected = null)
	{
		$html = '';
		if ($selected) {
			for ($i = $from; $i <= $to; $i++) { 
				if($i == $selected)
					$html .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
				else
					$html .= '<option value="' . $i . '">' . $i . '</option>';
			}
		} else {
			for ($i = $from; $i <= $to; $i++) { 
				$html .= '<option value="' . $i . '">' . $i . '</option>';
			}
		}

		return $html;
	}
}

if (! function_exists('is_english_text'))
{
	function is_english_text($text)
	{
		return strlen($text) == strlen(utf8_decode($text));
	}
}

if (! function_exists ( "path_for" ))
{
	function path_for( $uri )
	{
		$uri = trim($uri);
		$uri = ltrim($uri, "/");
		$uri = rtrim($uri, "/");
		$uri = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https://" : "http://") . $_SERVER["SERVER_NAME"] . "/" . $uri;

		return $uri;
	}
}

if (! function_exists( "redirect" ))
{
	function redirect($url)
	{
		header("Location: " . path_for($url));
		exit;
	}
}

if (! function_exists( "uri_referer" ))
{
	function uri_referer()
	{
		// If REFERER does not exist then default to root
		if (!isset($_SERVER['HTTP_REFERER'])) {
			return path_for("/");
		}

		// If HTTP_REFERER is not part of our domain then default to root
		$server = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "";
		if (strpos($_SERVER['HTTP_REFERER'], $server) === false) {
			return path_for("/");
		}

		// Sanetize URL
		$server = filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL);
		if ($server === false) {
			return path_for("/");
		}

		return strip_tags($server);
	}
}

/**
 * Function for restructing $_FILES array and returning
 * easy to use data struct for my image upload library.
 *
 * @return array
 */
if (! function_exists( "disverse_files" ))
{
	function disverse_files(array $files)
    {   
        $result = array();

        foreach($files as $key1 => $value1) {
            foreach($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }   
        }   

        return $result; 
    }
}

/**
 * Returns next page number using get_page function which
 * uses $_GET to find the value of 'page' parameter and do
 * not exceeds $max.
 *
 * @return int
 */
if (! function_exists( "get_page_next" ))
{
	function get_page_next($max = 100)
    {
        return (($page = get_page()) < $max) ? $page + 1 : $max;
    }
}

/**
 * Returns previous page number using get_page function which
 * uses $_GET to find the value of 'page' parameter and do
 * not go below $min.
 *
 * @return int
 */
if (! function_exists( "get_page_prev" ))
{
	function get_page_prev($min = 1)
    {
        return (($page = get_page()) > $min) ? $page - 1 : $min;
    }
}

/**
 * Returns value of $key key in POST super global variable if present
 * otherwise empty string.
 *
 * @return string
 */
if (! function_exists( "post_or_empty" ))
{
	function post_or_empty($key)
    {
		return isset($_POST[$key])? $_POST[$key] : '';
    }
}

/**
 * Returns value of $key key in GET super global variable if present
 * otherwise empty string.
 *
 * @return string
 */
if (! function_exists( "get_or_empty" ))
{
	function get_or_empty($key)
    {
		return isset($_GET[$key])? $_GET[$key] : '';
    }
}

/**
 * Takes associative array (key value pairs) and converts it into url
 * encoded string that can be used as GET parameter string.
 * Then returns the given array by appending it with current URI.
 * Useful when adding page param in pagination, in filter etc.
 *
 * @return string  Only returns the query part
 */
if (! function_exists( "get_query_append" ))
{
	function get_query_append(array $query)
	{
		$server_query_string = "";
		if (isset($_SERVER["QUERY_STRING"])) {
			$server_query_string = $_SERVER["QUERY_STRING"];
		}
		$get = $_GET;

		// Removing route part from the query string
		// i.e. $_GET contains post/2/show
		//      post/2/show will be removed
		if ($server_query_string) {
			array_shift($get);
		}

		// Merging the two array, $get and user $query
		$query = array_merge($get, $query);

		$query = http_build_query($query);
		if ($query) {
			$query = preg_replace("/%5B[0-9]+%5D/simU", "%5B%5D", $query);
		}

		return $query;
	}
}


/**
 * --------------------------------------------------------
 *  Remember last post data
 * --------------------------------------------------------
 * These four functions are for handling last post data
 * this is useful when we need to pre-populate the form
 * when a validation error occurs and we redirect back
 * to previous page.
 *
 * Usage:
 * o When you need to remember last post request, then call
 *    remember_post();
 *
 * o Retrieve old post data in view
 *    old_post('name', $default_value = 'Guest');
 *
 * o Check old post data exist or not
 *    has_remembered_post();
 *
 * o Destroy when you used it and no longer needed.
 *   Best place to use this function is after validation
 *   of form in post route.
 *   forget_post();
 *
 *
 * Example:
 *    First call the remember_post() function above validation
 *    inside post route method.
 *    public function store()
 *    {
 *     		remember_post();
 *			// do you validation stuff here
 *			// if validation has error then it will redirect
 * 			// back to previous page immediately.
 *			forget_post();
 *    }
 *
 *    Retrieve old post data in view
 *    old_post('name');
 *
 */
if (! function_exists('remember_post'))
{
	function remember_post()
	{
		$sess_name = \App\Config::get('old_post_sessname');
		if (!isset($_POST)) {
			return false;
		}

		\Fantom\Session::set($sess_name, $_POST);
		return true;
	}
}

if (! function_exists('old_post'))
{
	function old_post($key, $default_value = "")
	{
		$sess_name = \App\Config::get('old_post_sessname');
		if (!\Fantom\Session::exist($sess_name)){
			return $default_value;
		}

		return isset($_SESSION[$sess_name][$key])? $_SESSION[$sess_name][$key] : $default_value;
	}
}

if (! function_exists('has_remembered_post'))
{
	function has_remembered_post()
	{
		$sess_name = \App\Config::get('old_post_sessname');

		return \Fantom\Session::exist($sess_name);
	}
}

if (! function_exists('forget_post'))
{
	function forget_post()
	{
		$sess_name = \App\Config::get('old_post_sessname');

		\Fantom\Session::delete($sess_name);
	}
}

/**
 * -------------------------------------------------------
 * Security Helpers fucntions
 * -------------------------------------------------------
 *
 * Handles all security functions
 */
if (! function_exists( "e" ))
{
	function e($data) {
		return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
	}
}

if (! function_exists("csrf_field"))
{
	function csrf_field()
	{
		$token = \Fantom\Token\Token::generate();
		\Fantom\Token\Token::set("csrf_token", $token);
		return '<input type="hidden" name="csrf_token" value="' . $token . '">';
	}
}
