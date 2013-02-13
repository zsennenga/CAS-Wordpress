<?php
/**
 * casError
 *
 * Error handling class
 *
 * @author Zachary Ennenga
 *
 */
class casError	{
	var $listA;
	/**
	 * __construct
	 *
	 * initialize the error list
	 *
	 */
	function __construct()	{
		$this->listA = array();
	}
	/**
	 * messageNow
	 *
	 * Send an error message when this is called, don't add to queue
	 *
	 *  @param string $str message string
	 *  @param string $type type of message. Error, warning, success are the only ones I use.
	 */
	function messageNow($str,$type)	{
		echo "<div class='$type"."c"."'>$str</div>";
	}
	/**
	 * message
	 *
	 * Add error to queue
	 *
	 * @param string $str message string
	 * @param string $type type of message. Error, warning, success are the only ones I use.
	 */
	function message($str,$type)	{
		array_push($this->listA,"<div class='$type"."c"."'>$str</div>");
	}
	/**
	 * doError
	 *
	 * Print every error in the queue, clear the array
	 */
	function doError()	{
		global $casError;
		while (count($casError->listA) > 0)	{
			echo $casError->listA[0];
			array_shift($casError->listA);
		}
		$casError->listA = array();
	}
	/**
	 * 
	 * handleError
	 * 
	 * Used to changes warnings into catchable exceptions
	 * 
	 * @param int $errno
	 * @param string $errstr
	 * @param file $errfile
	 * @param int $errline
	 * @param array $errcontext
	 * @throws Exception
	 * @return boolean
	 */
	function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
	{
		// error was suppressed with the @-operator
		if (0 === error_reporting()) {
			return false;
		}
	
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}
?>