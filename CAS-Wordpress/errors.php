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
}
?>