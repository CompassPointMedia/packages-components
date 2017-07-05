<?php
/*
formlets 1.0 created 2008-05-03
------------------------------
hidden fields must be present and be directly after the file element.  They give instructions to the server.  The mode=fileFormlet and is reset after the form is submitted

installation
------------
1. declare .js files up to contextmenu_04_i1.js
2. declare .css for class formlet1
3. make sure icons are available in images/i
4. declare the following variables:
	sample (minimum) declaration to have the formlet work
	-----------------------------------------------------
	$formletFilePrefix='floorplan_';
	$formletConfigure='icons'; //currently only method presented
	$formletFolderHTTP='images/sites/'.$Handle.'/files'; //where the object would look for the files
	$formletFolder='images/sites/'.$Handle.'/files';  //where the exe page would look for the folder
	$formletMode='object'; //this is the default anyway
	require('path/to/formlets_v100.php');


*/
$formletFolderHTTP=$_SERVER['DOCUMENT_ROOT'].'/images/sites/'.$Handle.'/files'; //where the object would look for the files
if(!$formletExePage)$formletExePage='../index_01_exe.php';
if(!$formletConfigure)$formletConfigure='icons';
if(!$formletIconFolder)$formletIconFolder='images/i';
if(!$formletMode)$formletMode='object';
if(!function_exists('create_dirs')) require($FUNCTION_ROOT.'/function_create_dirs_v100.php');

if(!function_exists('formlet_icon')){
	function formlet_icon($file){
		global $formletIconFolder;
		preg_match('/\.([a-z0-9]+)$/i',$file,$a);
		$ext=strtolower($a[1]);
		switch(true){
			case $ext=='jpg' || $ext=='jpeg':
				$icon=$formletIconFolder.'/fileicon_img.gif';
			break;
			case $ext=='gif':
				$icon=$formletIconFolder.'/fileicon_img.gif';
			break;
			case $ext=='pdf':
				$icon=$formletIconFolder.'/fileicon_img.gif';
			break;
			default:
				$icon=$formletIconFolder.'/fileicon_general.gif';
		}
		return $icon;
	}
}

if($formletMode=='object'){
	if(!$formletStyle){
		$formletStyle=true;
		?><style type="text/css">
		.formlet1{
			border:1px solid #000;
			padding:15px;
			}
		.icon{
			float:left;
			padding:2px;
			cursor:pointer;
			}
		.iconHlt{
			float:left;
			border:2px solid darkblue;
			padding:0px;
			cursor:pointer;
			}
		.fct{
			clear:both;
			font-size:4px;
			}
		</style><?php
	}
	ob_start();
	$fi++; //formletInstance
	?>
	<div id="frm_<?php echo $fi?>" class="formlet1" style="">
		<div id="frmWrap<?php echo $fi?>"><input name="formletFile[<?php echo $fi?>]" id="formletFile<?php echo $fi?>" type="file" size="15" onChange="return formlet(this,'submit')" /><span id="frmStatus<?php echo $fi?>">&nbsp;</span></div>
		<?php
		if($formletFolder){
			?><input name="formletFolder[<?php echo $fi?>]" type="hidden" value="<?php echo $formletFolder?>" /><?php
			echo "\n";
		}
		if($formletFolderHTTP){
			?><input name="formletFolderHTTP[<?php echo $fi?>]" type="hidden" value="<?php echo $formletFolderHTTP?>" /><?php
			echo "\n";
		}
		if($formletFilePrefix){
			?><input name="formletFilePrefix[<?php echo $fi?>]" type="hidden" value="<?php echo $formletFilePrefix?>" /><?php
			echo "\n";
		}
		?>
		<table style="width:100%;"><tr><td class="fileList" id="files_<?php echo $fi?>"><?php
		//show content of folder
		$directoryPresent=true;
		if(is_dir($formletFolderHTTP)){
			//OK
		}else if(!create_dirs($formletFolderHTTP)){
			//attempt to create
			$directoryPresent=false;
		}
		if($fp=opendir($formletFolderHTTP)){
			while(false!==($file=readdir($fp))){
				//exclude unneeded items
				if($file=='.' || $file=='..')continue;
				if(is_dir($formletFolder . '/'.$file))continue;
				if($formletFilePrefix && !preg_match('/^'.$formletFilePrefix.'/',$file))continue;
				if($formletFileExtension && !preg_match('/'.$formletFileExtension .'/', $file))continue;
				//--------------------- code block 84 -----------------------
				?><div title="File name <?php echo $file?>" onDblClick="formletAction(event,'open','<?php echo $formletFolderHTTP?>');" id="formlet:<?php echo $fi?>_<?php echo $file?>" class="icon"><?php
				//get extension
				if(file_exists($icon=formlet_icon($file))){
					?><img alt="file" src="<?php echo $icon?>" align="absbottom" /><?php
				}else if(file_exists($icon=$formletIconFolder.'/fileicon_general.gif')){
					?><img alt="file" src="<?php echo $icon?>" align="absbottom" /><?php
				}else{
					//jingtao todo: email admin
					?>File<?php
				}
				?></div><?php
				//-------------------------------------------------------------
			}
		}
		?></td></tr></table>
	</div>
	<?php
	if(!$refreshComponentOnly){
		?>
		<script type="text/javascript" language="javascript">
		AssignMenu('^formlet:<?php echo $fi?>_', 'formletOptions');
		
		function formletPre(){
		}
		function formletAction(event, action, path){
			//this should work basedon the image icon
			var reg=/formlet:[0-9]+_/;
			alert(GetSourceElement(event).innerHTML);
			return;
			var file=(GetSourceElement(event).parentNode.id).replace(reg,'');
			var node=(GetSourceElement(event).parentNode.id).match(reg);
			alert(file);
			for(var j in hl_grp['chopt'])j=j.replace('r_','');
			if(action=='open'){
				ow(path+'/'+file,'l1_files','500,500');
			}else if(action=='delete'){
				window.open('<?php echo $formletExePage?>?mode=formletDelete&file='+file+'&node='+node+'&path='+path,'w2');
			}
		}
		function formlet(o, action){
			if(action=='submit'){
				var buffer=g('mode').value;
				o.form.encoding='multipart/form-data';
				g('mode').value='formlet';
				o.form.submit();
				o.parentNode.childNodes[1].innerHTML='<img src="/images/i/animblue.gif" />';
				g('mode').value=buffer;
				return false;			
			}
		}
		</script>
		<?php
		if(!$formletContextMenuOutput || $forceFormletContextMenuOutput){
			$formletContextMenuOutput=true;
			?><div id="formletOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="formletPre()">
				<div id="frm1" default="1" style="font-weight:900;" class="menuitems" command="formletAction(event,'open')" status="Open and view this file">Open</div>
				<hr class="mhr"/>
				<div id="frm2" class="menuitems" command="formletAction(event, 'delete');" status="Delete this file">Delete</div>
			</div><?php
		}
	}
	$out=ob_get_contents();
	ob_end_clean();
	echo ($directoryPresent ? $out : '<strong>The folder does not exist and was not able to be created</strong>' );
}else if($formletMode=='process' || count($_FILES['formletFile'])){
	if(count($_FILES['formletFile']['name'])){
		foreach($_FILES['formletFile']['name'] as $idx=>$v){
			if(is_uploaded_file($_FILES['formletFile']['tmp_name'][$idx]) && $_FILES['formletFile']['size'][$idx]>0){
				//we have the index
				$file=$formletFolder[$idx].'/'.$formletFilePrefix[$idx].$_FILES['formletFile']['name'][$idx];
				if(file_exists($file)){
					//eventually, compare and see if ext/mime is different and switch icon;
					$fileExists=true;
					unlink($file);
				}
				move_uploaded_file($_FILES['formletFile']['tmp_name'][$idx], $file);
				//configure parent
				if($fileExists)break;
				?><div id="fill"><?php
				$fi=$idx;
				$file=explode('/',$file);
				$file=$file[count($file)-1];
				//--------------------- code block 84 -----------------------
				?><div title="right-click to see options for this file <?php echo $file?>" onDblClick="formletAction(event,'open');" id="formlet:<?php echo $fi?>_<?php echo $file?>" class="icon"><?php
				//get extension
				$icon=formlet_icon($file);
				if(file_exists($icon)){
					?><img src="<?php echo $icon?>" /><?php
				}else if(file_exists($formletIconFolder.'/fileicon_general.jpg')){
					?><img src="<?php echo $formletIconFolder.'/fileicon_general.jpg'?>" /><?php
				}else{
					//jingtao todo: email admin
					?>File<?php
				}
				?></div><?php
				//-------------------------------------------------------------
				?></div>
				
				<script language="javascript" type="text/javascript">
				window.parent.g('frm_<?php echo $idx?>').innerHTML+=document.getElementById('fill').innerHTML;
				//reset element
				window.parent.g('frmWrap<?php echo $idx?>').innerHTML='<input name="formletFile[<?php echo $idx?>]" id="formletFile<?php echo $idx?>" type="file" size="15" onChange="return formlet(this,\'submit\')" /><span id="frmStatus<?php echo $idx?>">&nbsp;</span>';
				</script><?php
				break;
			}
		}
	}
}else if($formletMode=='delete'){
	error_alert('deleting');
}
?>