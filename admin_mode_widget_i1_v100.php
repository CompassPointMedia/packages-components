<?php
/* Title and Metatag editor - abstracted to component 2009-01-24 */
if(false && 'Use Metatag Editor CSS'){
	?><style type="text/css">
	/* CSS used for admin mode toolbar */
	#adminMode{
		background-color:SADDLEBROWN;
		border:1px solid #000;
		width:200px;
		padding:0px 5px;
		color:WHITE;
		font-family:Arial;
		position:fixed;
		top:15px;
		left:15px;
		}
	#adminModeExit{
		float:right;
		padding:0px 2px;
		margin:1px;
		border:1px solid WHITE;
		font-weight:900;
		font-family:Arial;
		}
	#adminModeExit a{
		color:#FFF;
		text-align:center;
		font-size:9px;
		}
	.amBtn{
		float:right;
		padding:0px 2px;
		margin:1px;
		border:1px solid WHITE;
		font-weight:900;
		font-family:Arial;
		}
	.amBtn a{
		color:#FFF;
		text-align:center;
		font-size:9px;
		font-weight:900;
		padding:0px 3px;
		}
	#adminMode{
		text-align:left;
		width:275px;
		-moz-opacity:.85;
		background-color:DARKKHAKI;
		}
	textarea.amFlds{
		font-size:11px;
		line-height:13px;
		width:250px;
		border:1px solid #000;
		}
	#amBody{
		clear:both;
		text-align:left;
		}
	</style><?php
}
if($adminMode){
	$hideCtrlSection=false;
	?><div id="adminMode" style="display:none;">
		<div class="amBtn">
			<a title="Leave Admin Access Mode" onclick="return confirm('Leave Administrative Access Mode?');" href="../admin.php?logout=1&amp;src=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''))?>">X</a>
		</div><div class="amBtn">
			<a id="amMin" title="<?php echo $_COOKIE['amBody']=='none'?'Expand':'Minimize'?> Admin Toolbar" href="#" onclick="return adminAccessMin();"><?php echo $_COOKIE['amBody']=='none'?'+':'-'?></a>
		</div>
		Administrative Access Mode
		<form id="amBody" style="display:<?php echo $_COOKIE['amBody'] ? $_COOKIE['amBody'] : 'block';?>;" action="<?php echo stristr($REQUEST_URI,'~'.$MASTER_DATABASE.'/') ? '/~'.$MASTER_DATABASE : '';?>/index_01_exe.php" target="w2" onsubmit="g('adminModeUpdate').disabled=true;return beginSubmit();" method="post">
		<strong>Page Title</strong>:<br />
		<input title="<?php echo htmlentities($metatags['title']);?>" name="MetaTitle" class="amFlds" id="MetaTitle" style="border:1px solid #000;" value="<?php echo htmlentities($metatags['title']);?>" size="25" <?php 
		if(strlen($metatags['record']['TField']) && !preg_match('/^[a-z0-9_]+$/i',$metatags['record']['TField'])){
			$noEditTitle=true;
			echo 'disabled';
		}
		?> /><?php
		if($noEditTitle){
			?>&nbsp;<img title="Title is compiled from more than one field and cannot be edited directly" src="Library/Library/images/i/del2.gif" alt="no edit" style="cursor:pointer;" onclick="alert(this.title);" width="16" height="18" /><?php
		}
		?>
		<br />
		<strong>Meta Description</strong>:<br />
		<textarea class="amFlds" name="MetaDescription" rows="3" id="MetaDescription"><?php echo htmlentities($metatags['description']);?></textarea><br />
		<strong>Meta Keywords</strong><br />
		<textarea class="amFlds" name="MetaKeywords" rows="3" id="MetaKeywords"><?php echo htmlentities($metatags['keywords']);?></textarea><br />
		
		<input name="thispage" type="hidden" id="thispage" value="<?php echo htmlentities($thispage)?>" />
		<input name="thisfolder" type="hidden" id="thisfolder" value="<?php echo htmlentities($thisfolder)?>" />
		<input name="QUERY_STRING" type="hidden" id="QUERY_STRING" value="<?php echo htmlentities($_SERVER['QUERY_STRING'])?>" />
		
		<input name="mode" type="hidden" id="mode" value="updateMetaTags" />
		<br />
		<input id="adminModeUpdate" type="submit" name="Submit" value=" Update " />
		&nbsp;&nbsp;&nbsp;
		<input type="button" name="Submit" value="Config.." onclick="if(confirm('This feature is only for database administrators. Continue?'))window.location='/resources/configure_metatags.php?page='+g('thispage').value+'&amp;folder='+g('thisfolder').value;" />

		
		<input type="button" name="Submit" value="Image Manager.." onclick="ow('/admin/file_explorer/?uid=imgmanager','l1_imgmanager','700,700');" />
		</form>
	</div><script language="JavaScript" type="text/javascript">g('adminMode').style.display='block'</script><?php
}
?>