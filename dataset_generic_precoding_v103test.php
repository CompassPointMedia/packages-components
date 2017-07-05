<?php
/*
2010-12-27 - view was crashing b/c of pulling a sort on a column that was not present; now existingSort will not build on cols that have sortable===false
2010-07-10 - version 1.03
this was forked off same time as dataset_component_v122 for 
* improvements on LIMITS esp when query changes
	cleaned up coding a LOT; -1 removes all batching, and 0,0,0 resets it; this is used by filtering when the filters change or are released
	added variable allowRemoveBatching, default=true. used in the ds component - "all" only shows if true
* transmission of variables in search for consistency
* modification of (prepending) order by when we are grouping and placing headers
* fixing show all feature
* (*) implementing an export interface which is generic - some logic in the scheme array
* multiple sorts and the ability to spit out a css dec for this automatically
	click a header=sort by header; flip ascendency if present else with with ASC
	ctrl-click a header=add sort criteria
	shift-click a header=retain ordering order, but flip ascendancy
	ctrl-shift-click a header=remove all sorting (expect sorting for grouping)
	datasetOverrideSort syntax changed to comma-separated list as follows: Edited[:-1],LastName[:1],FirstName[:1] (1|-1 optional)
	now, this dataset will set a sort and ascendancy the first time it is called (if not in db) based on first visible column
	
* for dataset component, better integration between "left controls" and context of double-click and right-click

changes:
* functions array_merge_accurate and subkey_sort no longer included

2010-06-09
* some queries need an internal filter for security; I added datasetInternalFilter to the query; first used in comp_50_children_list_v200 - as "ID IN( [list_children()] )"
2010-06-06
-------------------------------------
* DOCUMENTATION: the sql query (which this component is all about) references $inStatusSet, which is dealt with in the filterGadget (FG).  Grouping inStatusSet with filters may not be the best logically; however the bigger issue is the catch 22 - I have gadgets (as of 2010-06-06) *SET UP to be after the query, however the query depends on variables and arrays declared by (and also needed by) the gadgets.  I could put gadgets before this precoding file, but it seems more logical that I run the query before any output.

FOR NOW, it is necessary to hand-code $inStatusSet in the dataset->component setup before this file - this first came up in comp_50_children_list_v200.php
-------------------------------------
2010-06-04
* added var statusFilterField=default Statuses_ID
* corrected an obvious error on the $limit var preg_match logic
* organized the datasetActive group of vars
2010-05-21
* received idxdatase && idxdir for changing priority field
	datasetSetIdxPriorityField=Priority
	datasetSetIdxPriorityTable=[default=dataset table itself]
	datasetSetIdxOptions=array (for set_priority() function - if not present, will pass preceding two vars)
	datasetSetIdxWhereFilter
	datasetSetIdxPriorityFieldSort - in case sort by field for dataset is named differently
	- this will reset sort=datasetSetIdxPriorityFieldSort | datasetSetIdxPriorityField | 'Priority'
2010-05-09 - v1.02
* moved to /devteam/php/components vs. snippets
* now session.special.filterQueryJoin=1 (or unset) means use OR, and =0 means use AND; using OR as default is more inclusive
* tweaked the filter query coding; basically this version is the first to come close to working with filters
* recordsets can conceivably have NO single primary key and therefore no handle into the record to open/delete.  Added $datasetArrayType=O_ARRAY_ASSOC by default but can be changed to O_ARRAY.  In conjunction with $datasetKeyField=array(field1, field2) - we're getting closer to the old RelateBase system but not there quite yet (please no..)
2010-04-03 - v1.01
* changed variable sqlQueries (an internal value) over to filterExpressions - much better term
* see logic below for filters, simplified but still allowed flexibility.  No effect on anything external
2010-03-07
* this snippet produces no output except a javascript if needed (see below) but sets variables needed for the dataset component 
* added batch setting storage as position,batch,batches where batches=number of batches present
* passed variable inventory
	datasetGroup
	dataset
	datasetTable
	moduleConfig['dataobjects'][dataset] - where is this declared ??
	[userSettings] - from bais_settings table
	dir
	sort
	limit
	batch
	col
	hideInactive
	filterOverride
	filterExpressions

	updateDatasetFilters
	useStatusFilterOptions
	statusFilterIDField
	statusFilterNameField
	statusFilterTable 
	statusFilterQueryWhere
	* filterGadgetUserName
	statusWord
	
	availableCols.datasetGroup.modApType.modApHandle
 ---- */

if(!$functionVersions['colors'])require($FUNCTION_ROOT.'/group_colors_v110.php');
if(!function_exists('dataset_functions'))require($FUNCTION_ROOT.'/group_dataset_functions_v100.php');


//this is stored in rbase_AccountModules.Settings for the Account (e.g. cpm103)
@extract($moduleConfig['dataobjects'][$dataset]);

//datasetActive Group - ability to control a boolean value such as Active/Inactive or Assigned/Discharged
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='Active=0';
if(!isset($datasetActiveAllExpression))$datasetActiveAllExpression='1';
if(!isset($datasetActiveField))$datasetActiveField='Active';
if(!$datasetActiveInactivateTitle)$datasetActiveInactivateTitle='Make this '.strtolower($datasetWord).' inactive';
if(!$datasetActiveActivateTitle)$datasetActiveActivateTitle='Make this '.strtolower($datasetWord).' active';
#note: this was tough; picture this expression wrapped in double-quotes as, << echo "$expression"; >>
if(!$datasetActiveControl)$datasetActiveControl='toggleActiveObject(\'$dataset\', $$Dataset_ID, \'$datasetComponent\');';
if(!$datasetActiveInactiveImage)$datasetActiveInactiveImage='images/i/garbage2.gif';
if(!$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']){
	if($datasetTable){
		$availableCols=q("EXPLAIN $datasetTable",O_ARRAY);
		foreach($availableCols as $n=>$v){
			$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$v['Field']]=array();
		}
	}else if($datasetQuery){
		q( preg_replace('/LIMIT\s+[ ,0-9]+$/i','',trim($datasetQuery)).' LIMIT 1', O_ARRAY);
		if(!$qr['cols'])error_alert('unable to get available columns for view display, dataset_generic_precoding line '.__LINE__);
		foreach($qr['cols'] as $v)$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$v]=array();
	}else{
		error_alert('unable to get available columns for view display, dataset_generic_precoding line '.__LINE__);
	}
}
if(!$datasetFieldList)$datasetFieldList='*';

//dataset user name - no override for security right now
$datasetUserName=($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $_SERVER['PHP_AUTH_USER']);

//batch variables
if(!isset($allowBatching))$allowBatching=true;
if(!isset($allowRemoveBatching))$allowRemoveBatching=true;
if(!isset($datasetDefaultBatch))$datasetDefaultBatch=($globalBatchThreshold ? $globalBatchThreshold : 50);

//deletion
if(!isset($datasetShowDeletion))$datasetShowDeletion=true;

if(!$statusFilterField)$statusFilterField='Statuses_ID';

define('QEHANDLE_CONTINUE',4);

//------------------------------- passed variables -------------------------------------
#handle column selection
if($col){
	q("REPLACE INTO bais_settings SET UserName='$datasetUserName', 
	vargroup='".$dataset."',varnode='".$dataset."ColVisibility',varkey='".$col."',varvalue='".($visibility ? $visibility : COL_VISIBLE)."'");
	$_SESSION['userSettings'][$dataset.'ColVisibility:'.$col]=($visibility ? $visibility : COL_VISIBLE);
}


//----------------------- work with avaialableCols array -------------------------------
#1. merge override settings
if($mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]){
	$availableCols[$datasetGroup][$modApType][$modApHandle]=array_merge_accurate($availableCols[$datasetGroup][$modApType][$modApHandle], $mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]);
}
#2 set column order
$maxcolposition=0;
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	if($v['colposition'])continue;
	$maxcolposition++;
	$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['colposition']=$maxcolposition;
}
$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=subkey_sort($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'],'colposition');
#3 assign visible column count
$visibleColCount=0;
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	//override col visibility from database
	if(isset($_SESSION['userSettings'][$dataset.'ColVisibility:'.$n])){
		$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']=$_SESSION['userSettings'][$dataset.'ColVisibility:'.$n];
	}
	if(
		!isset($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']) || 
		$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']>=COL_VISIBLE){
		$visibleColCount++;
	}
}


//--------- at this point we have all settings declared, operations following -----------

//handle indexing added 2010-05-21
if($idxdataset==$datasetComponent && $idxdir){
	if($functionVersions['set_priority']<1.10)error_alert('You must include a version of set_priority() greater than 1.00');
	set_priority(
		$idxid, 
		$idxdir, 
		$idxabs, 
		($datasetSetIdxOptions ? $datasetSetIdxOptions : array(
			'whereFilter'=>$datasetSetIdxWhereFilter,
			'priorityTable'=>($datasetSetIdxPriorityTable ? $datasetSetIdxPriorityTable : $datasetTable),
			'priorityField'=>($datasetSetIdxPriorityField ? $datasetSetIdxPriorityField : 'Priority')
		))
	);
	//reset sort so prioritization can be seen
	$sort=($datasetSetIdxPriorityFieldSort ? $datasetSetIdxPriorityFieldSort : ($datasetSetIdxPriorityField ? $datasetSetIdxPriorityField : 'Priority'));
	$asc='ASC';
}

//handle sort
unset($existingSort);
if($datasetOverrideSort){
	//this is temporary; does not persist in database
	$a=explode(',',$datasetOverrideSort);
	$orderBy='';
	foreach($a as $v){
		$v=explode(':',$v);
		if($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$v[0]]){
			$existingSort[$v[0]]=($v[1]==-1 ? $v[1] : 1);
		}
	}
}else if($sort){
	//passed variable to modify or clear sort
	if($sortAlt){
		//clear all sort in session and db
		unset($_SESSION['userSettings']['default'.$dataset.'Sort'], $_SESSION['userSettings']['default'.$dataset.'SortDirection']);
		q("DELETE FROM bais_settings WHERE UserName='$datasetUserName' AND vargroup='".$dataset."' AND varnode='default".$dataset."Sort'");
		q("DELETE FROM bais_settings WHERE UserName='$datasetUserName' AND vargroup='".$dataset."' AND varnode='default".$dataset."SortDirection'");
	}else{
		@$a=explode(',',$userSettings['default'.$dataset.'Sort']);
		@$b=explode(',',$userSettings['default'.$dataset.'SortDirection']);
		if($a)foreach($a as $n=>$v) $existingSort[$v]=($b[$n]==-1 ? $b[$n] : 1);
		$i=0;
		if($existingSort)
		foreach($existingSort as $n=>$v){
			$i++;
			if($sort==$n) $sortPosn=$i;
		}
		if($sortCtrl){
			//add this to the stack.  If in the stack, move to the last position
			if($existingSort[$sort] && $sortPosn!==count($existingSort)){
				$sortDir=$existingSort[$sort];
				unset($existingSort[$sort]);
			}
			//hold down shift with control to flip direction while adding to stack (or moving to end; NO WAY to flip direction on anything besides last on stack)
			$existingSort[$sort]=($sortDir ? $sortDir : 1) * ($sortShift?-1:1);
		}else{
			$sortDir=($existingSort[$sort] ? $existingSort[$sort] : 1);
			unset($existingSort);
			$existingSort[$sort]=$sortDir * -1;
		}
		$n=$_SESSION['userSettings']['default'.$dataset.'Sort']=implode(',',array_keys($existingSort));
		q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$n'");
		$n=$_SESSION['userSettings']['default'.$dataset.'SortDirection']=implode(',',$existingSort);
		q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='".$dataset."',varnode='default".$dataset."SortDirection',varkey='',varvalue='$n'");
	}
}else if($n=$userSettings['default'.$dataset.'Sort']){
	@$a=explode(',',$n);
	@$b=explode(',',$userSettings['default'.$dataset.'SortDirection']);
	foreach($a as $n=>$v){
		if($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$v]['sortable']===false)continue;
		$existingSort[$v]=($b[$n]==-1 ? -1 : 1);
	}
}else{
	foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
		if($v['visibility']>=COL_AVAILABLE || !isset($v['visibility'])){
			//way to set DESC
			$existingSort[$n]=($v['orderDesc']==-1 ? -1 : 1);
			$_SESSION['userSettings']['default'.$dataset.'Sort']=$n;
			q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$n'");
			$_SESSION['userSettings']['default'.$dataset.'SortDirection']=1;
			q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='".$dataset."',varnode='default".$dataset."SortDirection',varkey='',varvalue='".$existingSort[$n]."'");
			break;
		}
	}
}

//integrate headings' sorts with requested sorts
if($datasetShowBreaks && $datasetBreakFields){
	if(!is_array($existingSort))$existingSort=array();
	foreach($datasetBreakFields as $v){
		$mergeExistingSort[$v['column']]=1;
	}
	$existingSort=array_merge($mergeExistingSort,$existingSort);
}
if($existingSort){
	$orderBy='ORDER BY';
	foreach($existingSort as $n=>$v){
		$orderBy.=' ';
		$asc=($v==-1 ? 'DESC' : 'ASC');
		if($m=$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['orderBy']){
			if(strstr($m,'$asc')){
				$orderBy.=str_replace('$asc',$asc,$m);
			}else{
				$orderBy.=$m . ' '.$asc;
			}
		}else{
			$orderBy.=$n.' '.$asc;
		}
		$orderBy.=',';
	}
	$orderBy=rtrim($orderBy,',');
}else{
	$orderBy='';
}

//handle batch -> limit
if(preg_match('/^([0-9]+),([0-9]+)(,([0-9]+))*$/',$limit,$a)){
	//this is a passed parameter and is not permanently stored
	$limitClause='LIMIT '.($a[1]-1).', '.($a[2] * ($a[4]?$a[4]:1));
	$position=$a[1];
	$currentBatch=$a[2];
	$currentRecordset=$a[2] * ($a[4]?$a[4]:1);
	$batches=($a[4]?$a[4]:1);
	//protect against neative values
	if($a[1]-1<0 || $a[2]<1) unset($limitClause);
}else{
	/*
	0,0,0 means reset to position 1 and default batch and 1 set of the batch
	-1 means no limit at all (no position or batch or batch count)
	
	*/
	if($batch=='-1'){
		q("DELETE FROM bais_settings WHERE UserName='$datasetUserName' AND 
		vargroup='".$dataset."' AND varnode='default".$dataset."Batch' AND varkey='' AND varvalue='$batch'");
		unset($_SESSION['userSettings']['default'.$dataset.'Batch']);
	}else if($batch){
		if($batch=='0,0,0'){
			$position=1;
			$currentBatch=$currentRecordSet=$datasetDefaultBatch;
			$batches=1;
			//reset batch
			$batch=$position.','.$currentRecordSet;
		}
		q("REPLACE INTO bais_settings SET UserName='$datasetUserName', 
		vargroup='".$dataset."',varnode='default".$dataset."Batch',varkey='',varvalue='$batch'");
		$_SESSION['userSettings']['default'.$dataset.'Batch']=$batch;
	}else if($batch=$userSettings['default'.$dataset.'Batch']){
		//OK
	}else{
		//batch is indeterminate, we may still batch based on size of recordset
	}
	if($batch!='-1'){
		$a=explode(',',$batch);
		$limitClause='LIMIT '.($a[0]-1).', '.($a[1] * ($a[2]?$a[2]:1));
		$position=$a[0];
		$currentBatch=$a[1];
		$currentRecordset=$a[1] * ($a[2]?$a[2]:1);
		$batches=($a[2]?$a[2]:1);
		if($a[0]-1<0 || $a[1]<1)unset($batch, $limitClause);
	}
}
if($testLimit)$limitClause='LIMIT 0,'.$testLimit;

//filter for inactive
if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='$datasetUserName', varnode='hideInactive$dataset',varkey='',varvalue='$hideInactive'");
	$_SESSION['userSettings']['hideInactive'.$dataset]=$hideInactive;
	if($submode!=='exportDataset' && !$datasetFocusViewCall){
		?><script language="javascript" type="text/javascript">
		hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
		window.parent.hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
		</script><?php	
	}
}
$datasetActive = ( $userSettings['hideInactive'.$dataset]==1 ? $datasetActiveActiveExpression : ( $userSettings['hideInactive'.$dataset]==-1 ? $datasetActiveInactiveExpression : $datasetActiveAllExpression ));

//set where clause including statuses and filter pairs
/*
1. set temporary query:
	a. pass page.php?filterOverride=Region='Duluth'&Title='Rep' (must be urlencoded)
	b. pass page.php?filters[]=Region='Duluth'&filters[]=Title='Rep'
	c. to OVERRIDE session filters, just pass filterOverride=''
2. set permanent query: pass updateDatasetFilters=1, pass either filters[] (querystring) or querytext[] (from filter gadget)
3. clear query: just pass updateDatasetFilters=1, with no other variables; session filter queries will be unset
*/

unset($filterQuery, $filterExpressions, $validFilterExpressions);
if(isset($filterOverride)){
	//temporary, query_string-based method to filter the data (long string method)
	$filterQuery=$filterOverride;
}else if($updateDatasetFilters || count($filters) || count($querytext)){
	//form post only from filter gadget - passing set of fields "querytext"
	//or, collection of $filters in query string
	$filterExpressions=(count($filters) ? $filters : $querytext);
	if(count($filterExpressions)){
		foreach($filterExpressions as $v){
			if(!trim($v))continue;
			if(!($x=parse_query(stripslashes($v),$datasetQuery ? $datasetQuery : $datasetTable))){
				if($datasetQueryErrorHandling==QEHANDLE_CONTINUE){
					continue;
				}else{
					error_alert($err='Your query "' . str_replace("'","\\\'",$v) . '" is not understood');
				}
			}
			$validFilterExpressions[]=stripslashes($x);
		}
	}
	if(isset($joinInclusive))$_SESSION['special']['filterQueryJoin'][$dataset]=$joinInclusive;

	if(count($validFilterExpressions)) $filterQuery=' AND (' . implode($_SESSION['special']['filterQueryJoin'][$dataset] || !isset($_SESSION['special']['filterQueryJoin'][$dataset]) ? ' OR ' : ' AND ', $validFilterExpressions) . ')';
	if($updateDatasetFilters){
		//this resets them
		$_SESSION['special']['filterQuery'][$dataset]=$validFilterExpressions;
	}
}else if(count($_SESSION['special']['filterQuery'][$dataset])){
	foreach($_SESSION['special']['filterQuery'][$dataset] as $v){
		//double-verify this
		if($x=parse_query($v,$datasetQuery ? $datasetQuery : $datasetTable)) $validFilterExpressions[]=$x;
	}
	if($validFilterExpressions) $filterQuery=' AND (' . implode($_SESSION['special']['filterQueryJoin'][$dataset] || !isset($_SESSION['special']['filterQueryJoin'][$dataset]) ? ' OR ' : ' AND ', $validFilterExpressions) . ')';
}

//status filter options - best place to put this that I can figure
if($updateDatasetFilters && $useStatusFilterOptions){
	if(!count($Statuses_ID))error_alert('select at least one '.($statusWord ? strtolower($statusWord) : 'status'));
	foreach(q("SELECT $statusFilterIDField, $statusFilterNameField FROM $statusFilterTable WHERE $statusFilterQueryWhere $statusFilterQueryOrder", O_COL_ASSOC) as $n=>$v){
		q("REPLACE INTO bais_settings SET UserName='".($filterGadgetUserName ? $filterGadgetUserName : ($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $_SERVER['PHP_AUTH_USER']))."', vargroup='$dataset', varnode='filter".$dataset."Status', varkey=$n, varvalue=".(in_array($n,$Statuses_ID)?1:0));
		$_SESSION['userSettings']['filter'.$dataset.'Status:'.$n]=(in_array($n,$Statuses_ID)?1:0);
	}
}

//run the query
if(!$datasetFocusViewCall){
	//we now want to accept a string query vs a table and modify the query as needed.  This gives us freedom because we are not limited to a specific table or set of fields any more (though availableCols will still be used for calculation)
	if($datasetQuery){
		if($datasetQueryValidation!==md5($MASTER_PASSWORD)){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('Incorrect validation for datasetQuery in dataset '.$dataset);
		}
		//modify the order by clause as needed
		/*
		2010-12-19: note you cannot use statusFilterField IN(), datasetActive, or datasetInternalFilter with this method
		note also that the orderBy clause had better contain the same fields that the query string will recognize AND avoid ambiguity
		*/
		$sql=preg_split('/\bORDER BY\b/',$datasetQuery);
		$sql=$sql[0].' '.$orderBy . ' ' . $limitClause;
	}else{
		$sql="SELECT $datasetFieldList FROM $datasetTable WHERE 1 ".
			($useStatusFilterOptions && count($inStatusSet) ? " AND $statusFilterField IN('".implode("','",$inStatusSet)."')":'').
			($datasetActiveUsage==true ? " AND $datasetActive" : '').
			($datasetInternalFilter ? " AND $datasetInternalFilter" : '').
			$filterQuery.
			" $orderBy $limitClause";
	}
	$fl=__FILE__;
	$ln=__LINE__+1;
	$records=q($sql, ($datasetArrayType ? (int) $datasetArrayType : O_ARRAY_ASSOC), ERR_ECHO);
	$recordsParams=$qr;
	$recordCols=$qr['cols'];
	if($datasetDebug['query']==md5($MASTER_PASSWORD))prn($qr);
	//get count
	if($limitClause){
		$count=q(
			($datasetQuery ? preg_replace('/\bSELECT\b(.|\s)+\bFROM\b/i','SELECT COUNT(*) FROM ',$datasetQuery) : "SELECT COUNT(*) FROM $datasetTable WHERE 1 ").
			($useStatusFilterOptions && count($inStatusSet) ? " AND $statusFilterField IN('".implode("','",$inStatusSet)."')":'').
			($datasetActiveUsage==true ? " AND $datasetActive" : '').
			($datasetInternalFilter ? " AND ($datasetInternalFilter)" : '').
			$filterQuery, O_VALUE, ERR_ECHO
		);
		$recordsetIsRelative=!($count==count($records));
	}else{
		$count=count($records);
		$recordsetIsRelative=false;
	}
	//logic to trigger batching
	if($limitClause){
		//OK- we have position, currentBatch, currentRecordset and batches
	}else if($allowBatching && $count > $datasetDefaultBatch && $batch!='-1'){
		$inBatching=true;
		//we have the query below the batch, how am I going to do this
		$position=1;
		$currentBatch=$datasetDefaultBatch;
		$currentRecordset=$datasetDefaultBatch;
		$batches=1;
	}
	$navStats[$dataset]=get_navstats(
		$count,
		$position ? $position : 1, 
		$currentBatch, 
		($batches?$batches:1)
	);
}
?>