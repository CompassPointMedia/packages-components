<?php
/* 
Created 2009-06-10, first used in console-rbrfm/members.php

//---------------- SAMPLE CODING TO CALL THIS COMPONENT ------------------
$tabPrefix='mainTabs';
$cg[$tabPrefix]['CGAllTabs']=array(
	'Company'	=>'company',
	'Contact'	=>'contact',
	'Regions'	=>'regions', 
	'Activity'	=>'activity', 
	'Settings'	=>'settings', 
	'Images'		=>'images'
);
$cg[$tabPrefix]['CGLayers']=array();
foreach($cg[$tabPrefix]['CGAllTabs'] as $n=>$v){
	if(!@in_array($v,$hideTabs[$tabPrefix]))$cg[$tabPrefix]['CGLayers'][$n]=$v;
}
if(!isset($cg[$tabPrefix]['defaultLayer'])){
	$cg[$tabPrefix]['defaultLayer']=current($cg[$tabPrefix]['CGLayers']);
}
//in this component, these vars are NOT used but may be in the future
$cg[$tabPrefix]['layerScheme']=2; //thin tabs vs old Microsoft tabs
$cg[$tabPrefix]['schemeVersion']=3.01;
//--------------------------------------------------------------------------

For an example of the use of the layers go to /home/rbase/lib/console-rbrfm/members.php
For an example of adding in a tab, go to /home/cpm075/dev/config.console.php and look for $focusViewTabs[$tabPrefix]

*/
//cg originally stood for "control group" - this firs param is all that needs to be declared if the array above is structured right
if(!isset($tabPrefix))$tabPrefix='mainTabs';

//---------- shortcuts used below; first two REQUIRED ------------------
if(!isset($tabLayers))$tabLayers=$cg[$tabPrefix]['CGLayers'];
if(!isset($tabDefault))$tabDefault=$cg[$tabPrefix]['defaultLayer'];
if(!isset($tabAction))$tabAction='showtabs';
if(!isset($tabLibraryPath))$tabLibraryPath='../';
if(!isset($activeHelpSystem))$activeHelpSystem=true;

if(!function_exists('get_contents')){
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		this function can either return output or start the next buffer
		*/
		$cmds=array('striptabs','beginnextbuffer','trim');
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
		if($trim)$gcontents['out']=trim($gcontents['out'])."\n";
		ob_end_clean();
		if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
		if($beginnextbuffer){
			ob_start();
		}else{
			return $gcontents['out'];
		}
	}
}

if($tabAction=='showtabs'){
	$addInTabs=false;
	if($focusViewTabs[$tabPrefix]){
		$addInTabs=true;
	}

	?><link rel="stylesheet" href="<?php echo $tabLibraryPath;?>Library/css/DHTML/layer_engine_v301.css" type="text/css" /><?php
	//this will generate JavaScript, all instructions are found in this file
	require_once($tabLibraryPath . 'Library/css/DHTML/layer_engine_v302.php');
	ob_start();
	?><div class="tabs">
	<table cellpadding="0" cellspacing="0">
		<tr>
			<?php
			foreach($tabLayers as $title=>$node){
				//this is the default button style - thin tabs
				?>
				<td style="vertical-align:bottom;"><div id="<?php echo $tabPrefix;?>_a_<?php echo $node?>" class="ab <?php echo $node==$tabDefault?'tShow':'tHide'?>"><?php echo $title?></div>
				<div id="<?php echo $tabPrefix;?>_i_<?php echo $node?>" class="ib <?php echo $node==$tabDefault?'tHide':'tShow'?>" onClick="hl_1('<?php echo $tabPrefix;?>',<?php echo $tabPrefix;?>,'<?php echo $node?>');"><?php echo $title?></div></td><?php
				
				//-------------- add in tabs if requested in settings --------------
				if($addInTabs){
					foreach($focusViewTabs[$tabPrefix] as $v){
						if($v['tabafter']==$node){
							$tabJavascriptNodes[$tabPrefix][]=$v['node'];
							?><td style="vertical-align:bottom;"><div id="<?php echo $tabPrefix;?>_a_<?php echo $v['node'];?>" class="ab <?php echo $v['node']==$tabDefault?'tShow':'tHide'?>"><?php echo $v['title']?></div>
							<div id="<?php echo $tabPrefix;?>_i_<?php echo $v['node']?>" class="ib <?php echo $v['node']==$tabDefault?'tHide':'tShow'?>" onClick="hl_1('<?php echo $tabPrefix;?>',<?php echo $tabPrefix;?>,'<?php echo $v['node']?>');"><?php echo $v['title']?></div></td><?php
						}
					}
				}
			}
			//todo: we need the help tab in place if called for
			?>
		</tr>
	</table><input name="<?php echo $tabPrefix;?>_status" id="<?php echo $tabPrefix;?>_status" type="hidden" value="" />
	<?php 
	if($tabJavascriptNodes[$tabPrefix]){
		?><script language="javascript" type="text/javascript">
		//add additional nodes to the array
		<?php foreach($tabJavascriptNodes[$tabPrefix] as $v){ ?><?php echo $tabPrefix?>[<?php echo $tabPrefix?>.length]='<?php echo $v;?>';<?php echo "\n"; }?>
		</script><?php
	}
	?>
	</div><?php
	$tabOutput[$tabPrefix]['tabHTML']=get_contents('striptabs','trim');
	if(!$bufferTabOutput)echo $tabOutput[$tabPrefix]['tabHTML'];
}else if($tabAction=='layerOutput'){
	if($focusViewTabs[$tabPrefix]){
		ob_end_clean();
		//assume we have layer output
		foreach($layerOutput[$tabPrefix] as $layerNode=>$layer){
			echo $layer;
			foreach($focusViewTabs[$tabPrefix] as $v){
				if($layerNode==$v['tabafter']){
					if(!@in_array($v['node'],$hideTabs[$tabPrefix])){
					ob_start();
					?>
					<div id="<?php echo $tabPrefix?>_<?php echo $v['node']?>" class="aArea <?php echo $tabDefault==$layerNode?'tShow':'tHide'?>" style="width:585px;min-height:300px;">
						<?php 
						if(file_exists($COMPONENT_ROOT.'/'.$v['source'])){
							require($COMPONENT_ROOT.'/'.$v['source']);
						}else{
							mail($developerEmail,'file '.__FILE__.',line '.__LINE__,get_globals(),$fromHdrBugs);
						}
						?>
					</div>
					<?php
					//we do NOT store this content in the array - not needed
					echo get_contents('trim');
					}
				}
			}
		}
	}
}
?>