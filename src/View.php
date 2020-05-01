<?php

namespace Fantom;

use Fantom\Validation\Validator;

/**
 * Base View class
 */
class View
{
	/**
	 * View path of currently rendering View
	 *
	 * @var string
	 */
	private $views_path;

	/**
	 * Full path of template while which is used
	 * by current view.
	 *
	 * @var string
	 */
	private $template = "";

	/**
	 * Any arguments to the template file is
	 * stored in this var.
	 * Example : argument like "title" of the page
	 * "title" => "Contact page"
	 *
	 * @var array  Associative array
	 */
	private $template_args;

	/**
	 * Stack to track rendering of view section
	 * Keeps track of which section is being rendered currently.
	 * It prevents from nesting of section
	 *
	 * @var array  Name of section
	 */
	private $section_stack = [];

	/**
	 * Stores rendered content
	 *
	 * @var array  a key value pair of section as key and content
	 *             of section as value
	 */
	private $rendered = [];

	/**
	 * Name of the main content of view
	 *
	 * @var string
	 */
	private $content_key_name = "content";

	/**
	 * Error bag instance
	 *
	 * @var Validation::ErrorMessageBag
	 */
	protected $errors;

	/**
	 * Constructor to instantiate the class
	 *
	 * @param string  Views path
	 */
	public function __construct($view_path)
	{
		$this->views_path = $view_path;

		// Check the $view_path is actually a directory
		if (! is_dir($this->views_path)) {
			$this->handleException(
				new \Exception("View path \"{$view_path}\" is not a directory.")
			);
		}
	}

	/**
	 * Render a view file
	 *
	 * @param string $view  The  view file
	 * @return void
	 */
	public function render($view, $args = [])
	{
		$file = $this->views_path . DS . $view;	

		if(!is_readable($file)) {
			$this->handleException(
				new \Exception("\"$file\" not found!")
			);
		}

		// Set error bag before rendering starts, as the error might be
		// needed by the view
		$this->errors = Validator::validationErrors();

		$this->cleanup();
		ob_start();
		array_push($this->section_stack, $this->content_key_name);
		$this->rendered[$this->content_key_name] = "";

		// Render the main view passed to render method
		$this->renderView($file, $args);

		if ($this->isTemplateUsed()) {
			$this->rendered[$this->content_key_name] = ob_get_contents();
			ob_end_clean();
			ob_start();
			$this->renderView($this->template, $this->template_args);
		}

		ob_end_flush();
		$this->cleanup();
	}

	/**
	 * Defines which template file to use
	 *
	 * @var string $template  The path to template file relative to Views dir.
	 * @var array $args  Pass the arguments to template file.
	 * @return void
	 */
	public function use($template, array $args = array())
	{
		if (! is_readable($this->views_path . DS . $template)) {
			$this->handleException(
				new \Exception("Template file \"{$template}\" not found!")
			);
		}

		$this->template = $this->views_path . DS . $template;
		$this->template_args = $args;
	}

	/**
	 * Returns the main content of a view
	 * It should be used only when we don't
	 * define and use the section() method in main view file.
	 * Then any left over or undefined html/php content will be
	 * treated as content of the view.
	 *
	 * @return void
	 */
	public function content()
	{
		echo $this->rendered[$this->content_key_name];
	}

	/**
	 * Defines section of the view file and starts the
	 * output buffer.
	 * Used to mark the starting of the section.
	 *
	 * @var string $section  The name of the section
	 * @return void
	 */
	public function section($section)
	{
		// If stack element count is more than 1 that means
		// more than one section is nested
		if (count($this->section_stack) >= 1) {
			$this->handleException(
				new \Exception("A Section is already in rendering progress. Nesting of section is not allowed.")
			);
		}

		// If duplicate section name is given which has already been
		// rendered then handle the exception
		if (array_key_exists($section, $this->rendered)) {
			$this->handleException(
				new \Exception("Duplicate section key given")
			);
		}

		array_push($this->section_stack, $section);
		$this->rendered[$section] = "";
		ob_start();
	}

	/**
	 * Stops and cleans the output buffer.
	 * Used to mark the end of the last started section.
	 *
	 * @return void
	 */
	public function endSection()
	{
		if (count($this->section_stack) !== 2) {
			$this->handleException(
				new \Exception("You are ending a section which was never begun.")
			);
		}

		$section = array_pop($this->section_stack);
		$this->rendered[$section] = ob_get_contents();
		ob_end_clean();
	}

	/**
	 * Fetches rendered section and injects it where this
	 * method is used.
	 *
	 * @var string $section  Name of the section to be fetched
	 * @return string
	 */
	public function fetchSection($section)
	{
		if (! array_key_exists($section, $this->rendered)) {
			$this->handleException(
				new \Exception("Section \"{$sectino}\" does not exist.")
			);
		}

		echo $this->rendered[$section];
	}

	/**
	 * Renders a given view
	 *
	 * @var string $view  Path to view file
	 * @var array $args  Pass the arguments to view file
	 */
	protected function renderView($view, $args = array())
	{
		////////////////////////
		// TODO
		// Catch any exception generated by the include file.
		// and handle it properly, and don't allow the
		// leaking of partial view to client.
		////////////////////////

		extract($args, EXTR_SKIP);

		try {

			include $view;

		} catch (Exception $e) {

			$this->handleException($e);
		}
	}

	/**
	 * Handles any Exception raised in the view file.
	 *
	 * @var \Exception $e  Exception object
	 * @return \Exception
	 */
	protected function handleException(\Exception $e)
	{
		$this->cleanup();

		// Start output buffering since all of the previous buffers
		// are cleaned up above, we need to show the exception to the
		// client therefore an ob_start is required
		ob_start();
		throw $e;
	}

	/**
	 * Output method to escape output
	 *
	 * @var string $data  Data to be escaped
	 * @return string  Escaped string
	 */
	public function escape($data = "")
	{
		if (! $data) {
			return "";
		}

		return e($data);
	}

	/**
	 * Alias for the escape() method
	 *
	 * @var string $data  Data to be escaped
	 * @return string  Escaped string
	 */
	public function e($data = "")
	{
		return $this->escape($data);
	}

	/**
	 * Checks if template is used in the view.
	 *
	 * @return bool
	 */
	protected function isTemplateUsed()
	{
		return $this->template !== "";
	}

	/**
	 * Clean the buffer, discard and stop previous output buffering
	 *
	 */
	private function cleanup()
	{
		/* Discard all the buffer and close it one by one */
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

}
