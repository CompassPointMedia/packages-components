<?php
/*
2010-06-04:
* sunsetted the code which attempts to set the "top 2" filters - instead we signal to check ALL and the first time FG is submitted, session.userSettings will include this.  Note that although generic_precoding is before the filtergadget using inStatusSet, and purges bad filters, inStatusSet is blank because it's never set in precoding - HEY, THAT IS THE CATCH 22! What I did in comp_50_children_list_v200 is declare inStatusSet prior to precoding.  This should work..
* added outputStatusFilterOptions, default=true.  For comp_50_children_list_v200 we do not need "status"=office filters listed on the FG
* added datasetStatusField, default=Statuses_ID.  This may not be necessary
* added available statusList or statusListSQL as options to the stock query for status values
2010-05-13:
* added filterGadgetPassthroughFields so things like cb=selector & hideObjectInactiveControl=1 can be passed
2010-05-09: version 1.05
* moved from console-rbrfm/components, bumped to .05

2009-06-03
* This was imported over from FOG/KFD (1.02) and made completely generic; note the settings declared below
* submission passes vars dataset and datasetTable which are needed when building the query filters for refresh
* component receives itself and does its own updating

*/

if(!$dataset)error_alert('Filter gadget requires $dataset to be declared'); //replaces all instances of this word; e.g. Member, Item, Article, Calevent or Calendar
if(!$datasetTable)error_alert('Filter gadget requires $datasetTable to be declared'); 

//statuses of the dataset - originally derived from finan_clients_statuses for client dataset
if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
if(!isset($outputStatusFilterOptions))$outputStatusFilterOptions=true;
if(!$statusWord)$statusWord='Status';
if(!$statusFilterIDField)$statusFilterIDField='ID';
if(!$statusFilterNameField)$statusFilterNameField='Name';
if(!$statusFilterTable)$statusFilterTable='finan_clients_statuses';
if(!$statusFilterQueryWhere)$statusFilterQueryWhere='1';
if(!$statusFilterQueryOrder)$statusFilterQueryOrder='ORDER BY ID';
if(!$statusFilterDefaultShown)$statusFilterDefaultShown=2; //e.g. top two statuses
if(!$statusFilterIconSet)$statusFilterIconSet='hlw-25x25-9EA9B4';
if(!$statusFilterField)$statusFilterField='Statuses_ID';

//component receives its own mode - presume we are in an exe page
/* 2009-12-15: NOTE!!! this codeblock was moved to snippet root -> dataset_generic_precoding_v100.php to solve the catch-22: to show the filter gadget I need to have already run the dataset query but to run the dataset query I need the "mind" of the filtergadget */


//this block moved below the block just above
if($useStatusFilterOptions){
	unset($inStatusSet);
	if(is_array($_SESSION['userSettings']))
	foreach($_SESSION['userSettings'] as $n=>$v){
		if(preg_match('/^filter'.$dataset.'Status:(.+)/i',$n,$a)){
			if(!$v)continue;
			$inStatusSet[]=$a[1];
		}
	}
	if(!count($inStatusSet)){
		/*
		I took this coding out 2010-06-04: with comp_50_children_list_v200, the "status" is actually the office but a very convenient delimiter.  The default for this is to have ALL the offices show until the user deselects one or more, with the exception that he/she cannot deselect all (or we would not have any records showing)
		
		//-------- be default we expect at least one status to be shown if using statuses ------------
		mail($developerEmail, 'notice file '.__FILE__.', line '.__LINE__.', initial declaration of top '.$statusFilterDefaultShown.' filters',get_globals(),$fromHdrNotices);
		if($a=q("SELECT $statusFilterIDField FROM $statusFilterTable WHERE $statusFilterQueryWhere ORDER BY $statusFilterIDField DESC LIMIT $statusFilterDefaultShown", O_COL)){
			foreach($a as $v){
				q("REPLACE INTO bais_settings SET UserName='".($filterGadgetUserName ? $filterGadgetUserName : ($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $_SERVER['PHP_AUTH_USER']))."', vargroup='$dataset', varnode='filter".$dataset."Status', varkey='$v', varvalue=1");
				$_SESSION['userSettings']['filter'.$dataset.'Status:'.$v]=1;
				$inStatusSet[]=$v;
			}
		}else{
			mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__.', using filterStatus but unable to initially set top '.$statusFilterDefaultShown.' filters',get_globals(),$fromHdrBugs);
		}
		*/
	}
}

//this was in !refreshComponentOnly, but the component is what is rewritten, and the CSS/JS is inside of it
//--- output CSS ---
ob_start();
?><style type="text/css">
#filterButton{
	float:right;
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
#fgFieldList{
	position:absolute;
	left:0px; 
	top: 25px;
	min-width:200px;
	height:250px;
	overflow:scroll;
	padding:2px 5px;
	background-color:papayawhip;
	}
.fgField{
	padding:2px 0px 1px 2px;
	}
.fgFieldOn{
	padding:2px 0px 1px 2px;
	background-color:peru;
	}
.flb{
	float:left;
	}
</style>
<?php
$filterGadgetCSS=trim(get_contents());
if($filterGadgetCSSInternal){
	$a=explode("\n",$filterGadgetCSS);
	unset($a[0]);
	$filterGadgetCSS=implode("\n",$a);
	$filterGadgetCSS=preg_replace('#</style>\s*$#i','',$filterGadgetCSS);
}
if(!filterGadgetHideCSS)echo $filterGadgetCSS;


//--- output JS ---
ob_start();
?><script language="javascript" type="text/javascript">
var ssS=false; var ssF=false;
function saveSearch(){
	g('filterMain').style.visibility='visible';
	setTimeout('hidefilterMain()',1000);
}
function hidefilterMain(){
	if(ssS || ssF){
		setTimeout('hidefilterMain()',1000);
	}else{
		g('filterMain').style.visibility='hidden';
		g('fgFieldList').style.visibility='hidden';
	}
}
function filterUpdateReady(){
	g('updateFilters').disabled=false;
}
function addFilterRow(o){
	var str='<div class="filterRow">';
	str+='<input name="querytext[]" type="text" onfocus="ssF=true;fgbuffer=this.value;" onblur="ssF=false;" onkeyup="if(this.value!==fgbuffer)g(\'updateFilters\').disabled=false;" value="" size="35" maxlength="255" />';
	str+='<input tabindex="-1" title="Add another filter criteria" type="button" value="+" onclick="addFilterRow(this)" class="filterCtrl" style="width:24px;" />';
	if(o.value=='+'){
		if(o.previousSibling.value==''){
			alert('Enter a value for this row first');
			o.previousSibling.focus();
			return;
		}
		//store dynamically entered values - see below for how used
		n=g('filterRows').childNodes;
		var refill=[];
		for(i=0; i<n.length; i++){
			refill[i]=n[i].firstChild.value;
		}
		o.value='-';
		o.title='Remove this filter criteria';
		o.parentNode.parentNode.innerHTML+=str;
		//refill the values - not sure why they disappear
		for(i=0; i<refill.length; i++) n[i].firstChild.value=refill[i];
		//if(i)g('joinInclusive').disabled=false;
	}else if(o.value=='-'){
		o.parentNode.style.display='none';
		o.parentNode.innerHTML='';
		g('updateFilters').disabled=false;
	}else{
		if(o.id=='clearFilters' || o.id=='clearFiltersIcon')g('filterRows').innerHTML=str;
		try{
		g('hdr-ctrls').innerHTML='searching..';
		}catch(e){}
		g('form.filters').submit();
		return false;
	}
}
function showFieldList(){
	g('fgFieldList').style.visibility='visible';
	}
</script><?php
$filterGadgetJS=trim(get_contents());
if($filterGadgetJSInternal){
	$a=explode("\n",$filterGadgetJS);
	unset($a[0]);
	$filterGadgetJS=implode("\n",$a);
	$filterGadgetJS=preg_replace('#</script>\s*$#i','',$filterGadgetJS);
}
if(!filterGadgetHideJS)echo $filterGadgetJS;



//output the gadget
$imax=count($_SESSION['special']['filterQuery'][$dataset]);
?>

<div id="filterGadget">
	<div id="filterButton" onmouseover="ssS=true;" onmouseout="ssS=false;" onclick="saveSearch()" title="Modify and update filters (which records are shown)"><img id="filterGadgetIcon" src="/images/i/s/<?php echo $statusFilterIconSet?>/<?php echo $imax ? 'filter-plus.png':'filter.png'?>" alt="filter" class="noghost" style="margin-top:7px;" /> <?php if($imax){ ?><a id="clearFiltersIcon" tabindex="-1" title="clear existing filters" href="#" onclick="return addFilterRow(this)"><img src="/images/i/s/<?php echo $statusFilterIconSet?>/filter-sub-delete.png" align="clear" style="margin-top:7px;" /></a><?php } ?> Filters 
		<div id="filterMain" onmouseover="ssS=true;" onmouseout="ssS=false;" >
			<form name="filters" id="form.filters" action="resources/bais_01_exe.php" method="post" target="w2" style="display:inline;">
				<?php if($useStatusFilterOptions && $outputStatusFilterOptions){ ?>
				<div class="status">
					<div class="statusword"><?php echo $statusWord?></div>
					<?php 
					if($statusList){
						//OK
					}else if($statusListSQL){
						$statusList=q($statusListSQL, O_COL_ASSOC);
					}else{
						$statusList=q("SELECT $statusFilterIDField, $statusFilterNameField FROM $statusFilterTable WHERE $statusFilterQueryWhere $statusFilterQueryOrder", O_COL_ASSOC);
					}
					foreach($statusList as $n=>$v){
						?><label>
						<input name="<?php echo $statusFilterField?>[]" type="checkbox" id="<?php echo $statusFilterField?>[<?php echo $n?>]" value="<?php echo $n?>" <?php echo $_SESSION['userSettings']['filter'.$dataset.'Status:'.$n] || !isset($inStatusSet) ?'checked':''?> onfocus="ssF=true;" onblur="ssF=false;" onchange="filterUpdateReady()" /> <?php echo h($v)?>
						</label><br /><?php
					}
					?>
				</div>
				-and-<br />
				<?php } ?>
				<div id="filterRows"><?php
				for($i=1;$i<=$imax+1;$i++){
					?><div class="filterRow"><input name="querytext[]" type="text" onfocus="ssF=true;fgbuffer=this.value" onblur="ssF=false;" onkeyup="if(this.value!==fgbuffer)g('updateFilters').disabled=false;" value="<?php echo h($_SESSION['special']['filterQuery'][$dataset][$i-1])?>" size="35" maxlength="255" /><input tabindex="-1" title="<?php echo $i==$imax+1?'Add another filter criteria':'Remove this filter criteria'?>" type="button" value="<?php echo $i==$imax+1?'+':'-'?>" onclick="addFilterRow(this)" class="filterCtrl" style="width:24px;" />
					</div><?php
				}
				?></div>
				<input type="hidden" name="joinInclusive" id="mode" value="0" />
				<label><input type="checkbox" name="joinInclusive" id="joinInclusive" value="1" <?php echo $_SESSION['special']['filterQueryJoin'][$dataset] || !isset($_SESSION['special']['filterQueryJoin'][$dataset]) ? 'checked':''?> onchange="filterUpdateReady()" />	ANY of these search conditions
				</label>
				<br /> 
				<input type="submit" name="updateFilters" id="updateFilters" value="Update" disabled="disabled" onfocus="ssF=true;" onblur="ssF=false;" onchange="filterUpdateReady()" class="flb" />
				<input type="button" name="clearFilters" id="clearFilters" value="Clear" onfocus="ssF=true;" onblur="ssF=false;" onclick="addFilterRow(this)" class="flb" />
				<div class="flb" style="position:relative;"><input type="button" name="fieldList" id="fieldList" value="Field List" onfocus="ssF=true;" onblur="ssF=false;" onclick="showFieldList()" />
					<div id="fgFieldList" style="visibility:hidden;" onmouseover="ssS=true;" onmouseout="ssS=false;">
					<?php
					/*
					2010-05-10: two methods of field list
					#1. add filters.fieldlist to the available columns (trumps)
					#2. get a field list from the table or view
					*/					
					if($availableCols[$datasetGroup][$modApType][$modApHandle]['filters']['fieldlist']){
						
					}else{
						$fieldlist=array();
						$a=q("EXPLAIN $datasetTable", O_ARRAY);
						foreach($a as $n=>$v){
							?><div class="fgField" onmouseover="this.className='fgFieldOn';" onmouseout="this.className='fgField';"><?php echo preg_replace('/([a-z])([A-Z])/','$1 $2',$v['Field']);?></div><?php
						}
					}
					?>
					</div>
				</div>
				<input type="hidden" name="mode" id="mode.filters" value="<?php echo $filterGadgetMode ? $filterGadgetMode : 'refreshComponent'?>" />
				<input type="hidden" name="dataset" id="dataset" value="<?php echo $dataset?>" />
				<input type="hidden" name="datasetTable" id="datasetTable" value="<?php echo $datasetTable?>" />
	
				<input type="hidden" name="useStatusFilterOptions" id="useStatusFilterOptions" value="<?php echo $useStatusFilterOptions?>" />
				<input type="hidden" name="statusFilterIDField" id="statusFilterIDField" value="<?php echo $statusFilterIDField?>" />
				<input type="hidden" name="statusFilterNameField" id="statusFilterNameField" value="<?php echo $statusFilterNameField?>" />
				<input type="hidden" name="statusFilterTable" id="statusFilterTable" value="<?php echo $statusFilterTable?>" />
				<input type="hidden" name="statusFilterQueryWhere" id="statusFilterQueryWhere" value="<?php echo $statusFilterQueryWhere?>" />
				<input type="hidden" name="updateDatasetFilters" id="updateDatasetFilters" value="1" />
				<input type="hidden" name="component" id="component" value="<?php echo $datasetComponent?>" />
				<?php
				if($filterGadgetPassthroughFields){
					foreach($filterGadgetPassthroughFields as $v){
						?><input type="hidden" name="<?php echo $v?>" value="<?php echo h(stripslashes($_REQUEST[$v]))?>" /><?php
					}
				}
				?>
			</form>
		</div>
	</div>
</div>