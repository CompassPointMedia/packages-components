<?php

/*

we need a way to get to a middle form via query string, but to fall back if it's not present
also analyze if the session application is malformend and clear/email admin

the last mode is the escape mode; after payment we store and redirect self to the final mode in the exe page; application is unpacked BEFORE the mode and we just know we are in session application 
we MUST integrate onchange and nav on the buttons
nav previous must happen silently if the current page is a blank form (unchanged)

I can move forward silently IF no changes have been made



*/

//initial settings 
if(!isset($requireApplicationKey))$requireApplicationKey=false; //set this =true in the exe page
if(!isset($clearApplicationVars))$clearApplicationVars=array('dir', 'applicationKey', 'sli', 'mode', 'Button'); //will not be stored in session

//DO WE NEED THIS?
if(!isset($orientation))$orientation='visible'; //visible|exe

if(!$applicationName)error_alert('no application name passed');
if($application=$applications[$applicationName]){
	//application represents the application as declared in settings - integrate any change in node values here if desired
}else{
	mail($developerEmail,'invalid application name passed',get_globals(),$fromHdrBugs);
	error_alert('invalid application name passed');
}
if($requireApplicationKey){
	if(!$applicationKey)error_alert('no application key passed');
	if($sli>1 && !$_SESSION['special']['applications'][$applicationKey]){
		//we should redirect them to the start
		?><script language="javascript" type="text/javascript">
		window.parent.location='/client/account/application.php?applicationName=<?php echo $applicationName;?>&rand=<?php echo rand(1,10000);?>';
		</script><?php
		$assumeErrorState=false;
		exit;
	}
}else if(!$applicationKey){
	$applicationKey=md5(time().rand(1,1000));
}

//information on current node
if(!$sli)$sli=1;

?><div id="application">
	<?php
	//---------- output form navigation if applicable ---------- 
	?>
	<style type="text/css">
	#stepLogicWrap .stepLogicNav{
		background-color:#666699;
		color:white;
		float:left;
		padding:2px 5px;
		margin-right:7px;
		}
	#stepLogicWrap .past{
		-moz-opacity:1.0;
		}
	#stepLogicWrap .present{
		background-color:cornsilk;
		color:#000066;
		}
	#stepLogicWrap .future{
		-moz-opacity:.5;
		}
	</style>
	<div id="stepLogicWrap"><?php
	/*
	NOTE: nav logic also pulls the current node data as $currentNode
	
	*/
	$i=0;
	unset($nodesPresent);
	foreach($application['nodes'] as $n=>$v){
		$i++;
		if(is_array($v)){
			$nodesPresent='multiple';
			$info=$v['config'];
			//title of this nav button
			$title=($info['navTitle'] ? $info['navTitle'] : $n);
		}else if($info=$applicationNodes[$n]){
			$nodesPresent='single';
			//value can be an overriding array
			if(is_array($v))$info=$v;
			$title=($info['title'] ? $info['title'] : $n);
		}else{
			mail($developerEmail,'error in declaration of an application',get_globals(),$fromHdrBugs);
			continue;
		}
		if($i==$sli+$dir /* current node */){
			$currentNodesPresent=$nodesPresent;
			unset($currentNode);
			if($nodesPresent=='single'){
				$currentNode=$info;
			}else{
				foreach($v as $o=>$w){
					if($o=='config')continue;
					$currentNode[$o]=$applicationNodes[$o];
				}
			}
		}
		if(strlen($dir) && $i==$sli){
			//--------- requesting submission of data ----------------
			$action='checkApplicationData';
			if($nodesPresent=='single'){
				//error alerts will get through here OK
				ob_start();
				require($info['component']);
				ob_end_clean();
				if($info['passtomode'])$setPassToMode=$info['passtomode'];
			}else{
				foreach($v as $o=>$w){
					if($o=='config')continue;
					//error alerts will get through here OK
					ob_start();
					require($applicationNodes[$o]['component']);
					ob_end_clean();
					if($applicationNodes[$o]['passtomode'])$setPassToMode=$applicationNodes[$o]['passtomode'];
				}
			}
			//store the data
			$a=$_POST;
			foreach($clearApplicationVars as $w)unset($a[$w]);
			$_SESSION['special']['applications'][$applicationKey][$sli]=stripslashes_deep($a);
			
			if($setPassToMode){
				//assume we are in the exe page
				?><script language="javascript" type="text/javascript">
				window.location='index_01_exe.php?mode=<?php echo $setPassToMode?>&applicationName=<?php echo $applicationName?>&applicationKey=<?php echo $applicationKey?>&unpackApplication=1';
				</script><?php
				$assumeErrorState=false;
				exit;
			}
		}
		?><div class="stepLogicNav <?php echo $i<$sli+$dir ? 'past' : ($i==$sli+$dir ? 'present' : 'future');?>"><?php
		echo $title;
		?></div><?php
	}
	?>
	<div class="cb">&nbsp;</div>
	</div><?php			
	
	//---------- output form title ---------- 
	?><h2 id="formTitleMain" class="formTitle">
	<?php echo $application['title']?>
	</h2><?php

	//---------- output form node and hidden fields ---------- 
	$sm=0;
	unset($action);
	if($currentNodesPresent=='multiple'){
		foreach($currentNode as $v){
			require($v['component']);
			if($v['mode'])$setMode=$v['mode'];
			if($v['submode']){
				$sm++;
				?><!-- submode declared through step logic -->
				<input type="hidden" name="submode[]" id="submode[<?php echo $sm?>]" value="<?php echo $v['submode']?>" />
				<?php
			}
		}
	}else{
		require($currentNode['component']);
		if($currentNode['mode'])$setMode=$currentNode['mode'];
		if($currentNode['mode']){
			$sm++;
			?><!-- submode declared through step logic -->
			<input type="hidden" name="submode[]" id="submode[<?php echo $sm?>]" value="<?php echo $currentNode['submode']?>" />
			<?php
		}
	}
	
	//---------- output hidden fields and buttonset ---------- 
	?>
	<div class="buttonSet">
		<input name="dir" type="hidden" id="dir" />
		<input name="applicationKey" type="hidden" id="applicationKey" value="<?php echo $applicationKey?>" />
		<input name="applicationName" type="hidden" id="applicationName" value="<?php echo $applicationName?>" />
		<input name="sli" type="hidden" id="sli" value="<?php echo $sli+$dir;?>" />
		<input type="hidden" name="mode" id="mode" value="<?php echo $setMode ? $setMode : 'collectApplicationData';?>" />
		<input name="Button" type="submit" id="Button" value="Previous" onclick="g('dir').value='-1';" <?php echo $sli+$dir==1?'disabled':''?> />
		<input name="Button" type="submit" id="Button" value="Next" onclick="g('dir').value='1';" <?php echo $sli+$dir==count($application['nodes'])-1?'disabled':''?> />
		<input name="Button" type="submit" id="Button" value="Finish" onclick="g('dir').value='1';" <?php echo $sli+$dir==count($application['nodes'])-1?'':'disabled'?>/>
	</div>
</div>