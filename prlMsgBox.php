<?
class msgBox{
	var $msgButtons;
	var $msgPrompt;
	var $msgTitle;
	var $msgIcon;
	var $msgLinks;
	function msgBox($prompt,$buttons,$Title="Application Message"){
		switch($buttons){
			case "OKOnly" :
				$this->msgButtons=array("OK");
				$this->msgIcon="i";
				break;
			case "OKCancel":
				$this->msgButtons=array("OK","Cancel");
				$this->msgIcon="s";
				break;
			case "AbortRetryIgnore":
				$this->msgButtons=array("Abort","Retry","Ignore");
				$this->msgIcon="x";
				break;
			case "YesNoCancel":
				$this->msgButtons=array("Yes","No","Cancel");
				$this->msgIcon="s";
				break;
			case "YesNo":
				$this->msgButtons=array("Yes","No");
				$this->msgIcon="s";
				break;
			case "RetryCancel":
				$this->msgButtons=array("Retry","Cancel");
				$this->msgIcon="r";
				break;
		} // end switch

		// set the title
		$this->msgPrompt=$prompt;
		$this->msgTitle=$Title;
	}
	//
	function makeLinks($linksArray){
		$this->msgLinks=$linksArray;
	}

	function showMsg(){
		//print_r($this->msgButtons);
		echo "<table width=\"40%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"2\" class=\"msg\">";
		echo "  <tr>";
		echo "	<td class=\"msgTitle\" colspan=\"2\" align=\"left\"><b>".$this->msgTitle."&nbsp;</b></td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "	<td width=\"5%\">";
		echo "<span class=\"msgIcon\">".$this->msgIcon."</span>";
		echo "  </td>";
		echo "	<td valign=\"top\">".$this->msgPrompt."</td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "<td colspan=\"2\" valign=\"top\" align=\"center\">";
			for($idx=0;$idx<count($this->msgButtons);$idx++){
				echo "<span class=\"msgButton\">";
				echo "<a href=\"".$this->msgLinks[$idx]."\" class=\"msglinks\">";
				//return $OCStr;
				echo $this->msgButtons[$idx];
				echo "</a>";
				echo "</span>";
				//echo "&nbsp;";
			}
		echo "</td>";
		echo "  </tr>";
		echo "</table>";
	}
		function showYesNo(){
		//print_r($this->msgButtons);
		echo "<table width=\"40%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"2\" class=\"msg\">";
		echo "  <tr>";
		echo "	<td class=\"msgTitle\" colspan=\"2\" align=\"left\"><b>".$this->msgTitle."&nbsp;</b></td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "	<td width=\"5%\">";
		echo "<span class=\"msgIcon\">".$this->msgIcon."</span>";
		echo "  </td>";
		echo "	<td valign=\"top\">".$this->msgPrompt."</td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "<td colspan=\"2\" valign=\"top\" align=\"center\">";
			for($idx=0;$idx<count($this->msgButtons);$idx++){
				echo "<span class=\"msgButton\">";
			//	echo "<a href=\"".$this->msgLinks[$idx]."\" class=\"msglinks\">";
				echo $this->msgButtons[$idx];
				echo "</a>";
				echo "</span>";
				echo "&nbsp;";
			}
		echo "</td>";
		echo "  </tr>";
		echo "</table>";
	}

}
?>
<!-- <link rel="stylesheet" href="msgbox_style.css" type="text/css">   -->
<?
#$links=array("abort.php","retry.php","ignore.php");
#$a=new msgBox("The user login failed","AbortRetryIgnore");<?php
class msgBox {
	var $msgButtons;
	var $msgPrompt;
	var $msgTitle;
	var $msgIcon;
	var $msgLinks;
	function msgBox($prompt, $buttons, $Title = "Application Message") {
		switch ($buttons) {
			case "OKOnly":
				$this->msgButtons = array(
					"OK"
				);
				$this->msgIcon = "i";
				break;
			case "OKCancel":
				$this->msgButtons = array(
					"OK",
					"Cancel"
				);
				$this->msgIcon = "s";
				break;
			case "AbortRetryIgnore":
				$this->msgButtons = array(
					"Abort",
					"Retry",
					"Ignore"
				);
				$this->msgIcon = "x";
				break;
			case "YesNoCancel":
				$this->msgButtons = array(
					"Yes",
					"No",
					"Cancel"
				);
				$this->msgIcon = "s";
				break;
			case "YesNo":
				$this->msgButtons = array(
					"Yes",
					"No"
				);
				$this->msgIcon = "s";
				break;
			case "RetryCancel":
				$this->msgButtons = array(
					"Retry",
					"Cancel"
				);
				$this->msgIcon = "r";
				break;
		} // end switch

		// set the title
		$this->msgPrompt = $prompt;
		$this->msgTitle = $Title;
	}
	//
	function makeLinks($linksArray) {
		$this->msgLinks = $linksArray;
	}

	function showMsg() {
		//print_r($this->msgButtons);
		echo "<table width=\"40%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"2\" class=\"msg\">";
		echo "  <tr>";
		echo "	<td class=\"msgTitle\" colspan=\"2\" align=\"left\"><b>" . $this->msgTitle . "&nbsp;</b></td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "	<td width=\"5%\">";
		echo "<span class=\"msgIcon\">" . $this->msgIcon . "</span>";
		echo "  </td>";
		echo "	<td valign=\"top\">" . $this->msgPrompt . "</td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "<td colspan=\"2\" valign=\"top\" align=\"center\">";
		for ($idx = 0; $idx < count($this->msgButtons); $idx++) {
			echo "<span class=\"msgButton\">";
			echo "<a href=\"" . $this->msgLinks[$idx] . "\" class=\"msglinks\">";
			//return $OCStr;
			echo $this->msgButtons[$idx];
			echo "</a>";
			echo "</span>";
			//echo "&nbsp;";
		}
		echo "</td>";
		echo "  </tr>";
		echo "</table>";
	}
	function showYesNo() {
		//print_r($this->msgButtons);
		echo "<table width=\"40%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"2\" class=\"msg\">";
		echo "  <tr>";
		echo "	<td class=\"msgTitle\" colspan=\"2\" align=\"left\"><b>" . $this->msgTitle . "&nbsp;</b></td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "	<td width=\"5%\">";
		echo "<span class=\"msgIcon\">" . $this->msgIcon . "</span>";
		echo "  </td>";
		echo "	<td valign=\"top\">" . $this->msgPrompt . "</td>";
		echo "  </tr>";
		echo "  <tr>";
		echo "<td colspan=\"2\" valign=\"top\" align=\"center\">";
		for ($idx = 0; $idx < count($this->msgButtons); $idx++) {
			echo "<span class=\"msgButton\">";
			//	echo "<a href=\"".$this->msgLinks[$idx]."\" class=\"msglinks\">";
			echo $this->msgButtons[$idx];
			echo "</a>";
			echo "</span>";
			echo "&nbsp;";
		}
		echo "</td>";
		echo "  </tr>";
		echo "</table>";
	}

}

//$links=array("abort.php","retry.php","ignore.php");
//$a=new msgBox("The user login failed","AbortRetryIgnore");
//$a->makeLinks($links);
//$a->showMsg();
//$a->showYesNo();
?>
#$a->makeLinks($links);
#$a->showMsg();
#$a->showYesNo();
?>