<?php
/*
2008-05-25 by Samuel
requires array $q which is a getStats call, plus the fields implicit below
*/

?>
<style type="text/css">
.searchCtrls{
	width:90%;
	}
.searchCtrls td{
	width:33%;
	}
</style>
<table class="searchCtrls">
<tr>
	<td>
	<a title="<?php echo $qq['thisIndex']==1?'You are already on the first set of results':'Previous set of results'?>" class="<?php echo $qq['thisIndex']==1 ? 'navlinkdisabled' : 'navlink'; ?>" <?php if(!$qq['prevIndex']){ ?>onclick="return false;"<?php }?> href="../search.php?position=<?php echo $qq['prevIndex']?>&amp;batch=<?php echo $qq['batch']?>&amp;q=<?php echo urlencode(stripslashes($q))?>">&laquo; Previous</a>
	</td>
	<td>
		<a title="<?php echo $qq['nextIndex']?'Next set of results':'Your are at the end of the results'?>" class="<?php echo $qq['nextIndex']?'navlink':'navlinkdisabled'?>" <?php if(!$qq['nextIndex']){ ?>onclick="return false;"<?php }?> href="../search.php?position=<?php echo $qq['nextIndex']?>&amp;batch=<?php echo $qq['batch']?>&amp;q=<?php echo urlencode(stripslashes($q))?>">Next &raquo;</a>
	</td>
	<td nowrap="nowrap" style="background-color:burlywood;">
		<a title="Start this search over again" href="#" onClick="g('searchForm').style.display='inline';window.location='#top';">Search Again/Start Over</a>
	</td>
</tr>
</table>