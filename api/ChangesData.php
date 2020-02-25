<?php
class ChangesData
{
	public $TextChanges = "";
	public $NumericChanges = array(-2, -2, -2, -2, -2, -2, -2, -2);
	public function __construct()
	{
		$this ->TextChanges = file_get_contents("../changes");
		$numbers = explode("\n", file_get_contents("../NumericChanges"));
		for($i = 0; $i < 8; $i++)
		{
			$el = $numbers[$i];
			if(is_numeric($el))
			{
				$this -> NumericChanges[$i] = intval($el);
			}
		}
	}
}
?>
