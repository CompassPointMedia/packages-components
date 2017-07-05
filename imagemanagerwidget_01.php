<?php
/* 
Image manager widget started 2008-10-18
image tag can carry the following attributes:
	filename
	path
	size(kb)
	dims(w,h)

the image manager is bound to an <img> tag currently

changelog
---------
2009-08-11:
* changed fOdefaultFolder to client-based so it can be changed

*/


if(!isset($fOdefaultFolder))$fOdefaultFolder='';
//folder boxing; * means no box constraint
if(!isset($fOBoxWidth))$fOBoxWidth='*';
if(!isset($fOBoxHeight))$fOBoxHeight='*';
if(!isset($fOnofile))$fOnofile='4510533-imgfileicon.gif';
if(!isset($fOAssignToRegex))$fOAssignToRegex = '^imgInset_';
if(!isset($fOMenuAlignment))$fOMenuAlignment = 'topleftaligndown';
if(!isset($fOSetFileTabNew))$fOSetFileTabNew = true;
if(!isset($fOiPath))$fOiPath='i/';


if(!function_exists('get_contents')){
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		this function can either return output or start the next buffer
		*/
		$cmds=array('striptabs','beginnextbuffer');
		global $gcontents;
		unset($gcontents);
		if($a=func_get_args()){
			foreach($a as $v){
				if(in_array(strtolower($v),$cmds)){
					$v=strtolower($v);
					$$v=true;
				}
			}
		}
		$gcontents['out']=ob_get_contents();
		ob_end_clean();
		if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
		if($beginnextbuffer){
			ob_start();
		}else{
			return $gcontents['out'];
		}
	}
}
?>
<style type="text/css">
/*tabs*/
#fileObject{
	width:175px;
	position:absolute;
	display:none;
	text-align:left;
	}
#fileBodyNew{
	position:relative;
	}
#fileBodyNewPending{
	position:absolute;
	top:-8px;
	/* left:5px; top:35px; */
	display:none;
	}
.t1{

	opacity:.92;
	background-color:#FFF;
	border:1px solid #555;
	float:left;
	margin:2px 3px 2px 7px;
	padding:0px 5px 2px 5px;
	cursor:pointer;
	}
.desc{
	opacity:.92;
	background-color:#FFF;
	border-bottom:none; /* 1px solid #fff */
	padding:0px 5px 6px 5px;
	margin:0px 3px 4px 7px;
	}
#fileBodyCurrent, #fileBodyNew{
	opacity:.92;
	background-color:#FFF;
	border:1px solid #888;
	padding:15px;
	margin-top:-5px;
	}
#fileTab{
	background-color:none;
	}
#fileTab a{
	color:inherit;
	text-decoration:none;
	padding:0px 5px;
	}
</style>
<div id="fileObject" onmouseover="override_hidemenuie5=true;" onmouseout="override_hidemenuie5=false;" precalculated="imWidgetCalc()">
	<div id="fileTab">
		<div id="fileTabCurrent" style="position:relative;z-index:900;" title="See information about the current file" onClick="tabs('fileTabCurrent')" class="t1<?php echo strtolower($fOWhichTab)=='fileTabNew'?'':' desc'?>"><a accesskey="1" href="javascript:void('#');">Current</a></div>
		<div id="fileTabNew" style="position:relative;z-index:900;" title="Link to a different file (locally or on the website)" onClick="tabs('fileTabNew')" class="t1<?php echo strtolower($fOWhichTab)=='fileTabNew'?' desc':''?>"><a accesskey="2" href="javascript:void('#')">New..</a></div>
		<br style="clear:both;" />
	</div>
	<div id="fileBodyCurrent" style="display:<?php echo strtolower($fOWhichTab)=='fileTabNew'?'none':'block'?>;z-index:899;">
		<span id="fOthumbdesc"><img id="fOthumb" alt="no picture available" src="<?php echo $fOnofile?>" width="75" /></span><br />
		Current File:<br />
		<span id="fOfilename">&nbsp;</span><br />
		Type: <span id="fOtype">&nbsp;</span><br />
		Size: <span id="fOsize">&nbsp;</span><br />
		<span id="fOimg">
			Width: <span id="fOwidth">&nbsp;</span><br>
			Height: <span id="fOheight">&nbsp;</span><br>
		</span>
		<input name="fOBoundToElement" type="hidden" id="fOBoundToElement" />
		<input name="uploadFile1Path" type="hidden" id="uploadFile1Path" />
		<input name="folder" type="hidden" id="folder" />
		<input name="uid" type="hidden" id="uid" value="<?php echo $fOUID ? $fOUID : ($GLOBALS['PHPSESSID'] ? $GLOBALS['PHPSESSID'] : 'fileObject');?>" />
		<input name="APICall" type="hidden" id="APICall" value="1" />
		<input name="fmwFile" type="hidden" id="fmwFile" value="" />
		<input name="fmwExt" type="hidden" id="fmwExt" value="" />
		<input name="fmwPath" type="hidden" id="fmwPath" value="" />
		<?php
		if($fODeclareBoxedFields){ 
		ob_start();
		?>
		<input name="boxed[0]" type="hidden" id="boxed[0]" value="<?php echo $fOBoxWidth?>" />
		<input name="boxed[1]" type="hidden" id="boxed[1]" value="<?php echo $fOBoxHeight?>" />
		<?php
		echo $boxedFields=get_contents();
		}
		?>
		
	</div>
	<div id="fileBodyNew" style="display:<?php echo strtolower($fOWhichTab)=='fileTabNew'?'block':'none'?>;">
		<div id="fileBodyNewPending">
		<img src="<?php echo $fOiPath?>loading2___.gif" alt="loading">
		</div>
		From my computer<br />
		<div style="overflow:hidden;width:75px;height:24px;"><div id="uploadFileWrap" style="margin-left:-148px;"><input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile()" /></div></div>
		From the server..<br>
		
		<input type="button" name="Button" value="Find.." onclick="return ow('/admin/file_explorer/index.php?uid=fmw&folder='+fOdefaultFolder+'&disposition=selector&cbTarget=fmwFile&cbTargetExt=fmwExt&cbTargetNode=fmwPath&cbFunction=assignPicture&cbParam=fixed:hello','l1_fmw','700,700');" />
		<div style="clear:both;">&nbsp;</div>
	</div>
</div>
<?php
if(!$refreshComponentOnly){
	?>
	<script type="text/javascript" language="javascript">
	<?php ob_start();?>
	var menuSetBlock=false;
	var pendingFileObjectLoads=[];
	var fOSetFileTabNew=<?php echo $fOSetFileTabNew?'true':'false';?>;
	var fOdefaultFolder='<?php echo $fOdefaultFolder?>';
	function uploadFile(){
		if(g('uploadFile1').value=='')return;
		g('uploadFile1Path').value=g('uploadFile1').value;
		var buffer=g('mode').value;
		g('mode').value='uploadFileAPI';
		
		g('folder').value=fOdefaultFolder;
		
		g('fileBodyNewPending').style.display='block';
		pendingFileObjectLoads[cmBoundToElement]=g('form1').getAttribute('target');
		//here we would find a new load target if available
		
		g('form1').submit();
		g('mode').value=buffer;
	}
	function tabs(id){
		if(id=='fileTabNew'){
			g('fileTabCurrent').className='t1';
			g('fileTabNew').className='t1 desc';
			g('fileBodyNew').style.display='block';
			g('fileBodyCurrent').style.display='none';
			//g('fOWhichTab').value='fileTabNew';
			//g('locationAny').checked=false;
		}else{
			g('fileTabCurrent').className='t1 desc';
			g('fileTabNew').className='t1';
			g('fileBodyNew').style.display='none';
			g('fileBodyCurrent').style.display='block';
			//g('fOWhichTab').value='fileTabCurrent';
		}
	}
	function imWidgetCalc(){
		/*
		ATTRIBUTES USED FOR THIS FUNCTION
		---------------------------------
		size=35.44 (in KB), dims=350,450 (width,height - this represents ACTUAL dimensions, not the thumbnail rep)
		description (of object)
		filename, filepath, filedomain
		expectedfiletypes=jpg,gif,png || doc,xls || [recognizedGroupType:] || [recognizedExclusionList:]
		isimage=1 means this is an image (vs. a video link, Word doc, etc.) - if null set to 1
		nofile=1 means this image is a placeholder, no actual file is present
		
		plus the standard src, alt, width, height, and style
		
		*/
		if(!menuSetBlock){
			//done once
			menuSetBlock=true;
			g('fileObject').style.display='block';
		}
		var i=g(cmBoundToElement).firstChild;
		//set relationship to parent object
		g('fOBoundToElement').value=cmBoundToElement;
		var isfile=!parseInt(i.getAttribute('nofile'));

		//set the appropriate tab
		tabs(isfile && !pendingFileObjectLoads[cmBoundToElement] && !fOSetFileTabNew? 'fileTabCurrent' : 'fileTabNew');
		
		//retain loading if necessary
		g('fileBodyNewPending').style.display=(pendingFileObjectLoads[cmBoundToElement] ? 'block':'none');
		
		//pull the image
		g('fOthumb').src=g(cmBoundToElement).firstChild.src;
		g('fOthumb').width=g(cmBoundToElement).firstChild.width;
		g('fOthumb').height=g(cmBoundToElement).firstChild.height;
		g('fOthumbdesc').setAttribute('title',i.getAttribute('description'));
		
		var isimage=i.getAttribute('isimage');
		if(isimage==null)isimage=1;
		g('fOfilename').innerHTML=(isfile ? i.getAttribute('filename') : '(none; <a href="#" onclick="tabs(\'fileTabNew\');return false;">select a file</a>)');
		g('fOtype').innerHTML=(isfile ? i.getAttribute('type') : '(N/A)');
		g('fOsize').innerHTML=(isfile ? i.getAttribute('size') : 'OKB');
		if(isfile && isimage && i.getAttribute('dims')){
			dims=i.getAttribute('dims').split(',');
			g('fOwidth').innerHTML=dims[0];
			g('fOheight').innerHTML=dims[1];
			g('fOimg').style.display='block';
		}else g('fOimg').style.display='none';

	}
	<?php 
	echo $fileManagerWidgetJS=get_contents();
	ob_start();
	?>
	g('fileObject').style.visibility='visible';
	AssignMenu('<?php echo $fOAssignToRegex?>', 'fileObject');
	menuAlign['^<?php echo $fOAssignToRegex?>']='<?php echo $fOMenuAlignment?>';
	<?php 
	echo $fileManagerWidgetJSAssignment=get_contents();
	?>
	</script>
	<?php
}
?>
