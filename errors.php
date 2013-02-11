<?php
/*
* Quick class to consolidate all my error handling.
*/
class casError	{
	var $listA;
	/*
	* initialize the error list
	* @return instance of casError
	*/
	function __construct()	{
		$this->listA = array();
	}
	/*
	* Send an error message when this is called, don't add to queue
	* @param $str string message string
	* @param $type string type of message. Error, warning, success are the only ones I use.
	* @return void
	*/
	function messageNow($str,$type)	{
		echo "<div class='$type"."c"."'>$str</div>";
	}
	/*
	* Add error to queue
	* @param $str string message string
	* @param $type string type of message. Error, warning, success are the only ones I use.
	* @return void
	*/
	function message($str,$type)	{
		array_push($this->listA,"<div class='$type"."c"."'>$str</div>");
	}
	/*
	* Print every error in the queue, clear the array
	* @return void
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