//CSS declaration
ob_start();
?><style type="text/css">
#filterButton{
	position:relative;
	cursor:pointer;
	}
#filterMain{
	<?php echo 'visibility:hidden;';?>
	position:absolute;
	z-index:25;
	right:0px;
	width:345px;
	border:1px solid #000;
	padding:15px;
	background-color:OLDLACE;
	}
#filterGadgetIcon{
	}
</style><?php
$filterGadgetCSS=get_contents();
if($filterGadgetCSSInternal){
	$a=explode("\n", $filterGadgetCSS);
	unset($a[0], $a[count($a)-1]);
	$filterGadgetCSS=implode("\n",$a);
}
if(!$filterGadgetHideCSS)echo $filterGadgetCSS;
