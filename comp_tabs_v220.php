<?php
/* 
2010-12-16
----------
this is completely different from 2.10; all that is needed to be declared on the page is:
$__tabs__['tabGroup']['tabSet']=array(
	'Label 1'=>'node1',
	'Label 2'=>'node2',
	'Label 3'=>'node3',
);

alternately you may declare

this may also be declared and the specific nodes will not appear:
$hideTabs['tabGroup']=array(
	'node1','node3',
);
*/
if(!count($__tabs__))exit('comp_tabs_v220.php requires array $__tabs__ to be declared');
if(!count($__tabs__[$tabGroup]['tabSet'])){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='There has been an abnormal error; unable to get data for a tab group'),$fromHdrBugs);
	exit($err);
}
//cancel out the last ob_start(); note we presume that this file is called RIGHT AFTER get_contents_tabsection() was called
ob_end_clean();

if(!isset($tabLibraryHTTPPath))$tabLibraryHTTPPath='/';
if(!isset($tabGenerateGroupDivs))$tabGenerateGroupDivs=true;
if(!$tabGroupWidth)$tabGroupWidth=585;
if(!$tabGroupMinHeight)$tabGroupMinHeight=300;

//not used yet
if(!isset($tabHelpSystem))$tabHelpSystem=true;
if(!$__tabs__[$tabGroup]['tabScheme']) $__tabs__[$tabGroup]['tabScheme']=2;


//-------------------------------------- tab labels ---------------------------------------
if($setTab[$tabGroup] && in_array($setTab[$tabGroup], $__tabs__[$tabGroup]['tabSet']) && !@in_array($setTab[$tabGroup], $hideTabs[$tabGroup])){
	#1 manually set the tab by query string
	$__tabs__[$tabGroup]['defaultTab']=$setTab[$tabGroup];
}else if(in_array($_COOKIE['tabs'.$tabGroup], $__tabs__[$tabGroup]['tabSet']) && !@in_array($_COOKIE['tabs'.$tabGroup], $hideTabs[$tabGroup])){
	#2 last tab shown or visible
	$__tabs__[$tabGroup]['defaultTab']=$_COOKIE['tabs'.$tabGroup];
}else if($__tabs__[$tabGroup]['defaultTab'] && !@in_array($_COOKIE['tabs'.$tabGroup], $hideTabs[$tabGroup])){
	#3 first tab on the list
	$__tabs__[$tabGroup]['defaultTab']=current($__tabs__[$tabGroup]['tabSet']);
}else{
	foreach($__tabs__[$tabGroup]['tabSet'] as $n=>$v){
		if(!@in_array($v, $hideTabs[$tabGroup])){
			$__tabs__[$tabGroup]['defaultTab']=$v;
			reset($__tabs__[$tabGroup]['tabSet']);
			break;
		}
	}
}
if(!$__tabs__[$tabGroup]['defaultTab']){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Unable to determine default tab for '.$tabGroup),$fromHdrBugs);
	exit($err);
}

$addInTabs=false;
if($focusViewTabs[$tabGroup]){
	$addInTabs=true;
}
?>
<link rel="stylesheet" href="<?php echo $tabLibraryHTTPPath;?>Library/css/DHTML/tab_engine_v220.css" type="text/css" /><?php
//this will generate JavaScript, all instructions are found in this file
require_once($_SERVER['DOCUMENT_ROOT'] . '/Library/css/DHTML/tab_engine_v220.php');
ob_start();
?><div class="tabs" style="width:<?php echo $tabGroupWidth?>px;">
<table cellpadding="0" cellspacing="0" class="tabsWrap">
	<tr>
		<?php
		foreach($__tabs__[$tabGroup]['tabSet'] as $title=>$node){
			if(@in_array($node, $hideTabs[$tabGroup]))continue;
			//this is the default button style - thin tabs
			?>
			<td class="<?php if($c=$__tabs__[$tabGroup]['tabInitialClasses'][$node])echo $c;?>" style="vertical-align:bottom;" nowrap="nowrap"><div id="<?php echo $tabGroup;?>_a_<?php echo $node?>" class="ab <?php echo $node==$__tabs__[$tabGroup]['defaultTab']?'tShow':'tHide'?>"><?php echo $title?></div>
			<div id="<?php echo $tabGroup;?>_i_<?php echo $node?>" class="ib <?php echo $node==$__tabs__[$tabGroup]['defaultTab']?'tHide':'tShow'?>" onclick="<?php echo $tabsPreJS[$node];?>hl_1('<?php echo $tabGroup;?>',<?php echo $tabGroup;?>,'<?php echo $node?>');"><?php echo $title?></div></td><?php
			
			//-------------- add in tabs if requested in settings --------------
			if($addInTabs){
				foreach($focusViewTabs[$tabGroup] as $v){
					if($v['tabafter']==$node){
						$tabJavascriptNodes[$tabGroup][]=$v['node'];
						?><td class="<?php if($c=$__tabs__[$tabGroup]['tabInitialClasses'][$node])echo $c;?>" style="vertical-align:bottom;"><div id="<?php echo $tabGroup;?>_a_<?php echo $v['node'];?>" class="ab <?php echo $v['node']==$__tabs__[$tabGroup]['defaultTab']?'tShow':'tHide'?>"><?php echo $v['title']?></div>
						<div id="<?php echo $tabGroup;?>_i_<?php echo $v['node']?>" class="ib <?php echo $v['node']==$__tabs__[$tabGroup]['defaultTab']?'tHide':'tShow'?>" onclick="<?php echo $tabsPreJS[$v['node']];?>hl_1('<?php echo $tabGroup;?>',<?php echo $tabGroup;?>,'<?php echo $v['node']?>');"><?php echo $v['title']?></div></td><?php
					}
				}
			}
		}
		//todo: we need the help tab in place if called for
		?>
		<td class="rightUnderline"><div class="ib">&nbsp;</div></td>
	</tr>
</table><input name="<?php echo $tabGroup;?>_status" id="<?php echo $tabGroup;?>_status" type="hidden" value="" />
<?php 
if($tabJavascriptNodes[$tabGroup]){
	?><script language="javascript" type="text/javascript">
	//add additional nodes to the array
	<?php foreach($tabJavascriptNodes[$tabGroup] as $v){ ?><?php echo $tabGroup?>[<?php echo $tabGroup?>.length]='<?php echo $v;?>';<?php echo "\n"; }?>
	</script><?php
}
?>
</div><?php
$tabOutput[$tabGroup]['tabHTML']=get_contents('striptabs','trim');
if(!$bufferTabOutput)echo $tabOutput[$tabGroup]['tabHTML'];

//-------------------------------------- tab sections ---------------------------------------
//make sure we have tab section(s) output
foreach($tabOutput[$tabGroup]['tabSet'] as $tabNode=>$tabString){
	//use the hideTabs array as needed
	if(@in_array($tabNode, $hideTabs[$tabGroup]))continue;

	if($tabGenerateGroupDivs){
		?><div id="<?php echo $tabGroup?>_<?php echo $tabNode?>" class="aArea <?php echo $__tabs__[$tabGroup]['defaultTab']==$tabNode?'tShow':'tHide'?>" style="width:<?php echo $tabGroupWidth?>px;min-height:<?php echo $tabGroupMinHeight?>px;"><?php
	}
	echo $tabString;
	if($tabGenerateGroupDivs){
		?></div><?php
	}
	if(count($focusViewTabs[$tabGroup]))
	foreach($focusViewTabs[$tabGroup] as $v){
		if($tabNode==$v['tabafter']){
			if(!@in_array($v['node'],$hideTabs[$tabGroup])){
			ob_start();
			?>
			<div id="<?php echo $tabGroup?>_<?php echo $v['node']?>" class="aArea <?php echo $__tabs__[$tabGroup]['defaultTab']==$tabNode?'tShow':'tHide'?>" style="width:<?php echo $tabGroupWidth?>px;min-height:<?php echo $tabGroupMinHeight?>px;">
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
?>