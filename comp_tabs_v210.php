<?php
/* 
2010-11-26: (version 2.10) 
	* added some coding for setting the default tab based on get params, then on cookie, then on default tabs
	* begin to capture layer output after the $tabAction='showtabs' declaration - the ob_start() was previously on the page

Created 2009-06-10, first used in console-rbrfm/members.php

$layerWidth

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
if(!isset($tabInitialClasses))$tabInitialClasses=$cg[$tabPrefix]['tabInitialClasses'];
if(!isset($tabAction))$tabAction='showtabs';
if(!isset($tabLibraryPath))$tabLibraryPath='../';
if(!isset($activeHelpSystem))$activeHelpSystem=true;
if(!isset($generateLayerDivs))$generateLayerDivs=true;

if(!$layerWidth)$layerWidth=585;
if(!$layerMinHeight)$layerMinHeight=300;

if(!function_exists('get_contents')){
	$functionVersions['get_contents']=.02;
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		2009-11-29 - made an "official" function in a_f; it was in 5 files.  Only in comp_tabs v2.00 (+?) the end logic is NOT if(beginnextbuffer) then ob_start() ELSE return gcontents.out - instead the logic is if(beginnextbuffer) then ob_start(); return gcontents.out PERIOD
		HOWEVER, beginnextbuffer is never flagged in comp_tabs so I have no fear of back-compat problems
		this function will return output and can optionally start the next buffer.
		GOTCHA! since this is a function, we must ob_start() before we return the contents.  Therefore, if you store the value returned as a variable, thats great, but if you wish to echo it, you are already in the next buffer.  So you cannot do a rewrite as done in cal widget and etc.
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
		}
		return $gcontents['out'];
	}
}
if(!function_exists('get_contents_layer')){
	function get_contents_layer($node=''){
		if(!$node)$node=substr(md5(time().rand(1,10000)),0,5);
		global $layerOutput,$tabPrefix;
		//much simpler system
		$layerOutput[$tabPrefix][$node]=ob_get_contents();
		ob_end_clean();
		ob_start();
	}
}

if($tabAction=='showtabs'){

	//----- 2010-11-26: this coding was stored in the page previously ------
	if($x=$setTab[$tabPrefix] && in_array($setTab[$tabPrefix], $cg[$tabPrefix]['CGLayers'])){
		$cg[$tabPrefix]['defaultLayer']=$x;
	}else if(in_array($_COOKIE['tabs'.$tabPrefix], $cg[$tabPrefix]['CGLayers'])){
		$cg[$tabPrefix]['defaultLayer']=$_COOKIE['tabs'.$tabPrefix];
	}else if(!isset($cg[$tabPrefix]['defaultLayer'])){
		$cg[$tabPrefix]['defaultLayer']=current($cg[$tabPrefix]['CGLayers']);
	}
	if(!$cg[$tabPrefix]['layerScheme']) $cg[$tabPrefix]['layerScheme']=2; //thin tabs vs old Microsoft tabs
	if(!$cg[$tabPrefix]['schemeVersion']) $cg[$tabPrefix]['schemeVersion']=3.01;
	//------------------------------------------------------------------------

	$addInTabs=false;
	if($focusViewTabs[$tabPrefix]){
		$addInTabs=true;
	}
	?>
	<link rel="stylesheet" href="<?php echo $tabLibraryPath;?>Library/css/DHTML/layer_engine_v301.css" type="text/css" /><?php
	//this will generate JavaScript, all instructions are found in this file
	require_once($_SERVER['DOCUMENT_ROOT'] . '/Library/css/DHTML/layer_engine_v302.php');
	ob_start();
	?><div class="tabs" style="width:<?php echo $layerWidth?>px;">
	<table cellpadding="0" cellspacing="0" class="tabsWrap">
		<tr>
			<?php
			foreach($tabLayers as $title=>$node){
				//this is the default button style - thin tabs
				?>
				<td class="<?php if($c=$tabInitialClasses[$node])echo $c;?>" style="vertical-align:bottom;" nowrap="nowrap"><div id="<?php echo $tabPrefix;?>_a_<?php echo $node?>" class="ab <?php echo $node==$tabDefault?'tShow':'tHide'?>"><?php echo $title?></div>
				<div id="<?php echo $tabPrefix;?>_i_<?php echo $node?>" class="ib <?php echo $node==$tabDefault?'tHide':'tShow'?>" onclick="<?php echo $tabsPreJS[$node];?>hl_1('<?php echo $tabPrefix;?>',<?php echo $tabPrefix;?>,'<?php echo $node?>');"><?php echo $title?></div></td><?php
				
				//-------------- add in tabs if requested in settings --------------
				if($addInTabs){
					foreach($focusViewTabs[$tabPrefix] as $v){
						if($v['tabafter']==$node){
							$tabJavascriptNodes[$tabPrefix][]=$v['node'];
							?><td class="<?php if($c=$tabInitialClasses[$node])echo $c;?>" style="vertical-align:bottom;"><div id="<?php echo $tabPrefix;?>_a_<?php echo $v['node'];?>" class="ab <?php echo $v['node']==$tabDefault?'tShow':'tHide'?>"><?php echo $v['title']?></div>
							<div id="<?php echo $tabPrefix;?>_i_<?php echo $v['node']?>" class="ib <?php echo $v['node']==$tabDefault?'tHide':'tShow'?>" onclick="<?php echo $tabsPreJS[$v['node']];?>hl_1('<?php echo $tabPrefix;?>',<?php echo $tabPrefix;?>,'<?php echo $v['node']?>');"><?php echo $v['title']?></div></td><?php
						}
					}
				}
			}
			//todo: we need the help tab in place if called for
			?>
			<td class="rightUnderline"><div class="ib">&nbsp;</div></td>
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
	
	//2010-11-26: begin to capture layer output now
	ob_start();
}else if($tabAction=='layerOutput'){
	//clear out last buffer from get_content_layers()
	ob_end_clean();
	//assume we have layer output
	foreach($layerOutput[$tabPrefix] as $layerNode=>$layer){
		if($generateLayerDivs){
			?><div id="<?php echo $tabPrefix?>_<?php echo $layerNode?>" class="aArea <?php echo $tabDefault==$layerNode?'tShow':'tHide'?>" style="width:<?php echo $layerWidth?>px;min-height:<?php echo $layerMinHeight?>px;"><?php
		}
		echo $layer;
		if($generateLayerDivs){
			?></div><?php
		}
		if(count($focusViewTabs[$tabPrefix]))
		foreach($focusViewTabs[$tabPrefix] as $v){
			if($layerNode==$v['tabafter']){
				if(!@in_array($v['node'],$hideTabs[$tabPrefix])){
				ob_start();
				?>
				<div id="<?php echo $tabPrefix?>_<?php echo $v['node']?>" class="aArea <?php echo $tabDefault==$tabNode?'tShow':'tHide'?>" style="width:<?php echo $layerWidth?>px;min-height:<?php echo $layerMinHeight?>px;">
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
?>