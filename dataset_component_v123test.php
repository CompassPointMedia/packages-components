<?php
/* ---------------------------------------<

---- SEE THE TODO LIST AT THE *BOTTOM* OF THIS FILE ----
2010-10-18
	* added parameter breakafter for any availableCol element, which will add a new row
	* note that a rowspan directive is added through the array colAttribs for the element
2010-09-14
	enhanced the ability of the array datasetBreakFields to control headings
	* this is done via function dataset_break_calcs()
	* passing parameter 'label'=>'default' will echo the field break value
	* alternately you may pass 'label'=>'{rb:field}: {rb:value}'  which would echo e.g. "Category: Elephants" - NOTE USE OF NEW VARIABLE CONVENTIONS: I GOT THIS FROM VBulletin
	
2010-07-31 (version 1.23)
	* sole purpose of this fork off was to implement the new datasetExportMethod
	1=export data from query only
	2=export data from visual output
	4=export data from visual output but annotate with scheme objects so designated (must be publicly available)
	* added $datasetFocusViewCall - as this component is NOT currently to be called in focus view, only list view
	
	pullQueryFieldsLeft
2010-06-16:
	added datasetCustomAttributes
2010-06-03:
	parameterized active/inactive toggle so that it could be assigned/discharged.  first used in comp_50_children_list_v200 -
	see all vars starting with datasetActive in precoding component
2010-05-21:
	added indexability (valuation of Priority field)
		datasetSetIdx=true -must be true to be visible
		datasetSetIdxImgPath=/images/i/arrows/spinner-orange.png; default
		datasetSetIdxImgHeight=19; default (top 9=move up, bottom 9=down)
		-- used in generic precoding file --
		datasetSetIdxPriorityField=Priority
		datasetSetIdxPriorityTable=[default=dataset table itself]
		datasetSetIdxOptions=array (for set_priority() function - if not present, will pass preceding two vars)
		datasetSetIdxWhereFilter
2010-05-20:
	added width attribute for the scheme, will output width="n" in <td> when present
	added $format var and reading of float fields; will output align="right" in <td>
	* NOTE; capabilities are growing faster than the logic to control them :)
2010-05-11:
	changed edit image to a more flexible set of rules, see notes below, vars here:
   [* datasetImagesRelativePath is deprecated - all URL's should be absolute]
	* focusViewDeviceFunction
	* focusViewURL 			[device function should globalize this]
	* focusViewSelfName					ditto
	* focusViewSize						ditto
	* focusViewEditImage (default is the *old* images/i/edit2.gif) - if in subfolder write as s/themename/edit.png
		- for an instance of a function like this go to gc gf5/console/components/comp_101_report_subcontractors_more_CBC_v100
	* similarly, added
		datasetAdditionalClass
		datasetAdditionalClassFunction for custom color coding
2010-05-09: version 1.21 - now located in components folder vs snippets
	* added $datasetHideFooterAddLink=default false
2010-05-01
	* added array datasetControlOptions - very crude; simply a list of <td> cells
	* added string datasetPreContent - paragraph before the table which can be echoed
	* help me locate which component; an identifying comment is placed in the component unless datasetHideComment=true
2010-04-07: version 1.21
	* added tbodyScrollingThreshold - if set, only scrolls when count>this var
2010-04-07: version 1.20
	[this was just placed in DAC Int as a test and the sub-totalling worked just fine]
	* major change in how we get the record collection - now a while loop
	* added descending headers and ascending cals (bottom)
2010-04-04: version 1.10
	The objective is to fork off the dataset->component into the complexData layout, and also a report layout.  Since reporting will be crucial to GiocosaCare, as well as in console-rbrfm, this will make effective design critical and will make this file the "engine" for much of my applications.
	I note that this is the OUTPUT, not the settings; those are in parent objects and specifically dataset_generic_precoding versions 1.0x and above.  So the required settings for this component could be stored and pulled from a database.
	
	The idea is to also introduce higher levels of specification so that I can just set baseLayout=report or =dataobject (like in Thunderbird inbox), and from there just override a few settings
2010-04-03: version 1.00
	* added Dataset_ID as 'ID' by default
	* additional variables
		$datasetAddObjectJSFunction - e.g. 'ow(this.href,\'l1_reps\',\'700,700\');';
		$datasetQueryStringKey - e.g. 'Salesreps_ID';
		$datasetDeleteMode - e.g. 'deleteSalesrep';
*/

for($_i_dataset=1; $_i_dataset<=1; $_i_dataset++){ //--- begin break loop ---
//do not run this page if in focus view
if($datasetFocusViewCall)break;
if(!function_exists('dataset_functions'))require($FUNCTION_ROOT.'/group_dataset_functions_v100.php');

//default settings for this dataset->component
if(!$Dataset_ID)$Dataset_ID='ID';
if(!$datasetTheme)$datasetTheme='dataobject'; //like Thunderbird email list
if(!$datasetHighlightColor)$datasetHighlightColor='#6c7093';

if(!isset($datasetTHAttribs))$datasetTHAttribs=array('colspan','rowspan');

if($datasetTheme=='dataobject'){
	if(!isset($tbodyScrollingThreshold))$tbodyScrollingThreshold=4;
	if(!$datasetTableClass)$datasetTableClass='complexData';
	if(!isset($scrollableTbodyRegion))$scrollableTbodyRegion=true;
	if(!isset($useHighlighting))$useHighlighting=true;
	if(!$footerDisposition)$footerDisposition='tabularControls';
	if(!isset($hideColumnSelection))$hideColumnSelection=false;
	if(!isset($hideSort))$hideSort=false;
}else if($datasetTheme=='report'){
	if(!$datasetTableClass)$datasetTableClass='standardReport';
	if(!isset($datasetShowBreaks))$datasetShowBreaks=false;
	if(!isset($scrollableTbodyRegion))$scrollableTbodyRegion=false;
	if(!isset($useHighlighting))$useHighlighting=false;
	if(!$footerDisposition)$footerDisposition='reportFooter';
	if(!isset($hideColumnSelection))$hideColumnSelection=true;
	if(!isset($hideSort))$hideSort=true;
}

if($submode=='exportDataset'){
	if(!$datasetExportMethod)$datasetExportMethod=2; //export from actual visual dataset
	if(!$datasetExportSeparator)$datasetExportSeparator=',';
	if(!$datasetExportWrapper)$datasetExportWrapper='"';
	if(!$datasetExportWrapperEscape)$datasetExportWrapperEscape=$datasetExportWrapper.$datasetExportWrapper;
	if($datasetExportFields){
		$datasetExportFields=explode('|',rtrim($datasetExportFields,'|'));
	}
}


if(!$refreshComponentOnly && $submode!=='exportDataset'){
	?><style type="text/css">
	</style>
	<script language="javascript" type="text/javascript">
	<?php 
	if(!$hideColumnSelection){ 
	?>
	AssignMenu('^colOptions_<?php echo $dataset?>','optionsAvailableCols');
	function colOptions(){}
	function mgeCol(e,n){
		var posn=g('col'+n).className.indexOf('Visible');
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&col='+n+'&visibility='+(posn> -1? 8 : 16),'w2');
	}
	hl_bg['<?php echo strtolower($datasetWord);?>opt']='<?php echo $datasetHighlightColor;?>';
	hl_baseclass['<?php echo strtolower($datasetWord);?>opt']='normal';
	hl_class['<?php echo strtolower($datasetWord);?>opt']='hlrow';

	<?php 
	}
	if($datasetSetIdx){
	//allow users to click the spinner
	?>
	function indexRow(o,e){
		var f=findPos(o)[1];
		f-=g('<?php echo $datasetComponent?>_tbody').scrollTop;
		//http://forums.digitalpoint.com/showthread.php?t=11965
		if (document.documentElement && !document.documentElement.scrollTop){
			// IE6 +4.01 but no scrolling going on
			var s=0;
		}else if (document.documentElement && document.documentElement.scrollTop){
			// IE6 +4.01 and user has scrolled
			var s=(document.documentElement.scrollTop);
		}else if (document.body && document.body.scrollTop){
			// IE5 or DTD 3.2 
			var s=document.body.scrollTop;
		}
		f=e.clientY+s-f;
		var idxdir=(f><?php echo $datasetIndexHeight?$datasetIndexHeight:19?>/2 ? -1 : 1);
		var idxabs=(e.ctrlKey ? 1 : 0);
		var idxid=o.id.replace('idx_','');
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&idxdataset=<?php echo $datasetComponent?>&idxdir='+idxdir+'&idxid='+idxid+'&idxabs='+idxabs,'w2');
	}
	function findPos(obj) {
		var curleft = curtop = 0;
		if(obj.offsetParent){
			do{
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
			return [curleft,curtop];
		}else{
			alert('Unable to position this');
		}
	}
	<?php
	}
	?>
	</script><?php
}

$datasetOutput='';
ob_start();
?><div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<?php
	if($submode!='exportDataset' && !$datasetHideComment){
		ob_start();
		echo '<!-- component: ';
		foreach(get_included_files() as $v){
			if(preg_match('#components/comp_#',$v))$file=$v;
		}
		echo end(explode('/',$file));
		echo ' -->';
		echo $componentComment=get_contents();
	}
	
	//----- text content before dataset->component - can be in the component file -----
	if($datasetPreContent)echo $datasetPreContent;


	ob_start(); //--- buffer header ---
	?>
	<?php echo '<h'.($datasetComponentHeadingLevel ? $datasetComponentHeadingLevel : '3').' id="'.$datasetComponent.'_heading">'?><?php echo $datasetWordPlural;?> <?php if(!$datasetHideCount){ ?>(<span id="<?php echo $datasetComponent?>_count"><?php echo $count;?></span>)<?php } ?> <?php 
	if($inBatching || $limitClause){	
		echo 'Showing '.$position . '-'.($position+$currentRecordset-1 <= $count ? $position+$currentRecordset-1 : $count);
	}
	?><?php echo '</h'.($datasetComponentHeadingLevel ? $datasetComponentHeadingLevel : '3').'>'?>
	<?php
	echo $componentHeader=get_contents();
	
	//------ component-native required coding --------
	ob_start();
	?><div id="optionsAvailableCols" class="menuskin1" style="width:150px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="colOptions();">
		<?php
		foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
			if(isset($scheme['visibility']) && $scheme['visibility']<COL_AVAILABLE)continue;
			?><div id="col<?php echo $handle?>" class="menuitems colOpt<?php echo !isset($scheme['visibility']) || $scheme['visibility']==COL_VISIBLE?'Visible':'Hidden'?>" command="mgeCol(event,'<?php echo $handle?>');" status="Show or hide this column"><?php echo $scheme['header'] ? $scheme['header'] : $handle;?></div><?php
		}
		?>
	</div>
	<input type="hidden" name="noparams" id="noparams" value="" />
	<?php
	echo $datasetNativePreContent=get_contents();

	//------ actual dataset ------
	?>
	<table border="0" cellspacing="0" cellpadding="0" class="<?php echo $datasetTableClass?>" style="clear:both;">
		<?php ob_start(); //--- buffer thead --- ?>
		<thead>
			<tr>
				<?php
				if($datasetControlOptions){ 
					$countVisible=0;
					foreach($datasetControlOptions as $n=>$v){
						$countVisible++;
						//filter hidden controls
						if(isset($v['show']) && !$v['show'])continue;
						//filter controls not made for left hand position
						if(isset($v['position']) && strtolower($v['position'])!=='left')continue;
					}
					if($countVisible){
						?>
						<!-- additional controls -->
						<th colspan="<?php echo $countVisible?>">&nbsp;</th><?php
					}
				}
				?>
				<!-- control cells -->
				<?php if(!$hideObjectInactiveControl){ ?>
				<th id="toggleActive" class="activetoggle"<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>><a title="Hide or show inactive <?php echo strtolower($datasetWordPlural);?>" href="javascript:toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);">&nbsp;&nbsp;</a></th>
				<?php } ?>
				<?php if(!$hideEditControls){ ?>
				<?php if($datasetSetIdx){ ?>
				<th<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>>&nbsp;</th>
				<?php } ?>
				<th<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>>&nbsp;</th>
				<?php }?>
				<?php
				
				//----------- column headers ----------------
				$cols=0;
				foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
					//new include/exclude logic for exporting
					if($submode=='exportDataset' && isset($datasetExportFields)){
						//we are pulling ONLY from datasetExportFields
						if(!@in_array($handle,$datasetExportFields) || (isset($scheme['visibility']) && $scheme['visibility']<COL_AVAILABLE))continue;
					}else{
						if(isset($scheme['visibility']) && $scheme['visibility']<COL_VISIBLE)continue;
					}
					   
					$cols++;
					?><th nowrap="nowrap" <?php echo $scheme['sortable'] || !isset($scheme['sortable']) ? 'sortable="1"' : ''?> class="<?php 
					$aClass='';
					if($existingSort){
						$s=0;
						foreach($existingSort as $n=>$v){
							$s++;
							if($n==$handle){
								echo 'sorted'.$s;
								$aClass=($v==-1?'desc':'asc');
								break;
							}
						}
					}
					?><?php echo $cols==$visibleColCount?' last':''?>"<?php
					if($scheme['colattribs']){
						foreach($scheme['colattribs'] as $n=>$v){
							if(!in_array(strtolower($n),$datasetTHAttribs))continue;
							echo ' '.$n.'="'.$v.'"';
						}
					}
					?>><?php 
						if($cols==$visibleColCount && !$hideColumnSelection){
							echo '<table width="100%"><tr><td style="padding:0px;background:none;border:none;">';
						}
						//link tag for sort
						$hideSort=false;
						//error_alert(($scheme['sortable'] || !isset($scheme['sortable'])) && !$hideSort);
						if(($scheme['sortable'] || !isset($scheme['sortable'])) && !$hideSort){ 
							?><a href="#" target="w2" title="<?php echo $scheme['title'];?>"<?php echo $aClass?' class="'.$aClass.'"':''?> onclick="return datasetSort('<?php echo $datasetComponent?>','<?php echo $handle?>',event);"><?php
						}
						//output header
						if($submode=='exportDataset' && $datasetExportMethod>1){


							//get left key field if called for
							if(($n=$scheme['exportKey']) && $datasetExportMethod>2){
								if(is_array($n)){
									//undeveloped as of 2010-08-01
								}else{
									//default, label the same as calculated field-exportKey field
									$datasetOutput.=$datasetExportWrapper.
									(strlen($scheme['header']) ? $scheme['header'] : preg_replace('/([a-z])([A-Z])/','$1 $2',$handle)).
									'-'.
									(	strlen($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['header']) ? 
										$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['header'] : 
										preg_replace('/([a-z])([A-Z])/','$1 $2',$n)
									).
									$datasetExportWrapper.$datasetExportSeparator;
								}
							}


							$datasetOutput.=$datasetExportWrapper.(strlen($scheme['header']) ? $scheme['header'] : preg_replace('/([a-z])([A-Z])/','$1 $2',$handle)).$datasetExportWrapper.$datasetExportSeparator;
						}else{
							echo strlen($scheme['header']) ? $scheme['header'] : preg_replace('/([a-z])([A-Z])/','$1 $2',$handle);
						}
						
						//close link tag
						if(($scheme['sortable'] || !isset($scheme['sortable'])) && !$hideSort){ ?></a><?php }

						//select cols icon
						if($cols==$visibleColCount && !$hideColumnSelection){ 
							echo '</td><td id="colOptionsAnchorCell" style="text-align:right;padding:0px;background:none;border:none;">&nbsp;';
							?>
							<a id="colOptions_<?php echo $dataset?>" class="colOptionsAnchor" title="Select and organize columns for this view" style="padding:0px;" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<?php
							echo '</td></tr></table>';
						}
					?></th><?php
					//2010-10-18: add row break for layout mode
					if($scheme['breakafter']){
						echo '</tr><tr class="subrowheader">';
					}
				}
				if($submode=='exportDataset' && $datasetExportMethod>1){
					$datasetOutput=($datasetOutput ? "\n":'').rtrim($datasetOutput,$datasetExportSeparator);
				}
				?>
			</tr>
		</thead>
		<?php 
		echo $componentThead=get_contents();
		ob_start(); //--- buffer tfoot ---
		?><tfoot>
			<tr valign="top">
			<td colspan="100%"><?php
			if($footerDisposition=='tabularControls'){
				if($inBatching || $limitClause){
					$url='resources/bais_01_exe.php?mode=refreshComponent&component='.$datasetComponent.'&batch=';
					?><style type="text/css">
					.bottomNavCtrls .unavailable{
						color:#ccc;
						}
					</style>
					<div class="bottomNavCtrls fr">
					<?php
					//prn($navStats[$dataset]);
					?>
					<a title="Go to the previous set of records (swap with these records)" 
					href="<?php echo $url?><?php echo ($navStats[$dataset]['prevGroupIndex'] ? $navStats[$dataset]['prevGroupIndex'] : $navStats[$dataset]['prevIndex']).','.$currentBatch;?>" 
					class="<?php echo !$navStats[$dataset]['prevGroupIndex'] ? 'unavailable':''?>" 
					onclick="return <?php echo $navStats[$dataset]['prevGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>go to previous</a> | 
					<a 
					title="Add the previous <?php echo $currentBatch?> records to this list" 
					href="<?php echo $url?><?php echo $navStats[$dataset]['prevGroupIndex'] ? $navStats[$dataset]['prevGroupIndex'].','.$currentBatch.','.($batches+1) : '';?>"
					class="<?php echo !$navStats[$dataset]['prevGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['prevGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>add previous</a> | 
					<a 
					title="Go to the next set of records (swap with these records)" 
					href="<?php echo $url?><?php echo ($navStats[$dataset]['nextGroupIndex'] ? $navStats[$dataset]['nextGroupIndex'] : $navStats[$datset]['nextIndex']).','.$currentBatch;?>"
					class="<?php echo !$navStats[$dataset]['nextGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['nextGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>go to next</a> | 
					<a 
					title="Add the next <?php echo $currentBatch?> records to this list" 
					href="<?php echo $url?><?php echo $navStats[$dataset]['nextGroupIndex'] ? $navStats[$dataset]['thisIndex'].','.$currentBatch.','.($batches+1):'';?>"
					class="<?php echo !$navStats[$dataset]['nextGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['nextGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>add next</a> | 
					<?php
					if($allowRemoveBatching){
						?>
						<a 
						title="Expand the list to all records in the database (may be slow)" 
						href="<?php echo $url?><?php echo '-1';?>"
						target="w2"
						>add all</a> | 
						<?php
					}else{
					
					}
					?>
					<a 
					title="Clear settings for which records are being shown"
					href="<?php echo $url?><?php echo '0,0,0';?>"
					target="w2"
					>clear</a>
					</div><?php
				}
				if(!$datasetHideFooterAddLink){
					?><a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return <?php echo $datasetAddObjectJSFunction ? $datasetAddObjectJSFunction : 'add'.$dataset.'()'?>"><img src="<?php echo $datasetImagesRelativePath?>/images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($datasetWord);?>..</a><?php
				}
			}else{
			
			}
			?></td>
			</tr>
		</tfoot><?php
		$componentTfoot=get_contents();
		if(!$hideComponentTfoot)echo $componentTfoot;
		ob_start(); //--- buffer tbody ---
		?>
		<tbody id="<?php echo $datasetComponent?>_tbody" <?php if($browser!=='IE' && $scrollableTbodyRegion){?> style="overflow-y:scroll;overflow-x:hidden;height:350px;" <?php } ?>>
		<?php
		@reset($records);
		if($records){
			$i=0;
			$j=0;
			while(true){
				if(is_array($nextRecord)){
					$record=$nextRecord;
					@$nextRecord=current(each($records));
				}else{
					@$record=current(each($records));
					@$nextRecord=current(each($records));
				}
				if(!$record)break;
				//--------- begin new while loop kernel -----------

				//apply any filters here

				//handle batching
				/*
				$j++;
				if($inBatching && $submode!=='exportDataset' &&
					($j<$position || 
					 $j>=($position + ($navStats[$dataset]['batch'] * ($batches ? $batches : 1))))
					){
					continue;
				}
				*/

				$i++;
				if($i==1 && $datasetShowBreaks){
					if(!is_array($optionsTop))$optionsTop=array('section'=>'top');
					if($submode=='exportDataset')$optionsTop['disposition']='rawdata';
					dataset_breaks_calcs($optionsTop);
				}

				//get permissions
				extract($record);
				$deletable=true;
				
				if($submode=='exportDataset' && $datasetExportMethod==1){
					if(!$headerOutput){
						$datasetOutput.=$datasetExportWrapper.implode($datasetExportWrapper.$datasetExportSeparator.$datasetExportWrapper,$recordCols).$datasetExportWrapper;
						$headerOutput=true;
					}
					$str='';
					foreach($record as $w){
						$quote=(preg_match('/['.$datasetExportSeparator.$datasetExportWrapper.']/',$w) ? $datasetExportWrapper : '');
						$str.=$quote . str_replace($datasetExportWrapper, $datasetExportWrapperEscape, $w). $quote.$datasetExportSeparator;
					}
					$datasetOutput.=($datasetOutput ? "\n" : '').rtrim($str,$datasetExportSeparator);
					continue; //no HTML output
				}
				$rowOutput='';
				if($datasetCustomAttributes){
					$datasetAttributes='';
					foreach($datasetCustomAttributes as $n=>$v){
						eval('$x='.$v.';');
						$datasetAttributes.=' '.$n.'="'.$x.'"';
					}
				}
				
				$hNormalCoding='h(this,\''.strtolower($datasetWord).'opt\',0,0,event);';
				$hContextMenuCoding='h(this,\''.strtolower($datasetWord).'opt\',0,1,event);';
				$focusViewURL=$datasetFocusPage.'?'.($datasetQueryStringKey ? $datasetQueryStringKey : $datasetWordPlural.'_ID').'='.$$Dataset_ID;
				if($datasetAdditionalClassFunction){
					//show globalize $datasetAdditonalClass, set blank when needed
					$datasetAdditionalClassFunction($record);
				}
				?><tr id="r_<?php echo $$Dataset_ID?>" onclick="<?php if($useHighlighting)echo $hNormalCoding;?>" ondblclick="<?php if($useHighlighting)echo $hNormalCoding;?>open<?php echo $dataset?>();" oncontextmenu="<?php if($useHighlighting)echo $hContextMenuCoding;?>" class="normal<?php echo fmod($i,2)?' alt':''?><?php echo $datasetAdditionalClass ? $datasetAdditionalClass : ''?>" deletable="<?php echo $deletable?>" <?php echo strtolower($datasetActiveField);?>="<?php echo $$datasetActiveField?>"<?php if($datasetCustomAttributes)echo $datasetAttributes;?>>
					<?php 
					//2010-05-01: introduced for special select controls and the like
					if($datasetControlOptions){ 
						foreach($datasetControlOptions as $n=>$v){
							//filter hidden controls
							if(isset($v['show']) && !$v['show'])continue;
							//filter controls not made for left hand position
							if(isset($v['position']) && strtolower($v['position'])!=='left')continue;
							if($v['eval']){
								eval(' ?>'.$v['eval'].'<?php ');
							}else{
								?><td>[<a href="#" onclick="return getThis()">select</a>]</td><?php
							}
						}
					}
					if(!$hideObjectInactiveControl){ 
						?>
						<td id="r_<?php echo $$Dataset_ID?>_active" title="<?php echo $$datasetActiveField ? $datasetActiveInactivateTitle : $datasetActiveActivateTitle?>" onclick="<?php eval('echo "'.$datasetActiveControl.'";');?>" class="activetoggle"<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>><?php
						if(!$$datasetActiveField){
							?><img src="<?php echo $datasetImagesRelativePath?>/<?php echo $datasetActiveInactiveImage?>" align="absbottom" /><?php
						}else{
							?>&nbsp;<?php
						}
						?></td>
						<?php 
					} 
					if(!$hideEditControls){
						if($datasetSetIdx){
							?><td<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>>
							<img title="Click to move this row up or down; hold down the Ctrl key to move to the top or bottom" id="idx_<?php echo $$Dataset_ID?>" src="<?php echo $datasetSetIdxImgPath ? $datasetSetIdxImgPath : '/images/i/arrows/spinner-orange.png'?>" height="<?php echo $datasetSetIdxImgHeight ? $datasetSetIdxImgHeight : 19?>" onclick="indexRow(this,event);" />
							</td><?php
						}
						?><td nowrap="nowrap"<?php if($n=$datasetControlOptionsRowspan)echo ' rowspan="'.$n.'"';?>><?php
						if($datasetShowDeletion){
							if($deletable){
								?><a title="Delete this <?php echo strtolower($datasetWord);?>" href="resources/bais_01_exe.php?mode=<?php echo $datasetDeleteMode ? $datasetDeleteMode : 'delete'.$datasetWord?>&<?php echo $datasetQueryStringKey ? $datasetQueryStringKey : $datasetWordPlural.'_ID';?>=<?php echo $$Dataset_ID?>" target="w2" onclick="if(!confirm('This will permanently delete this <?php echo strtolower($datasetWord)?>\'s record.  Are you sure?'))return false;">&nbsp;<img src="<?php echo $datasetImagesRelativePath?>/images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
							}else{
								?>&nbsp;<img src="<?php echo $datasetImagesRelativePath?>/images/i/spacer.gif" width="18" height="18" /><?php
							}
							?>&nbsp;&nbsp;<?php
						}
						/* -----------------------<
						2010-05-11
						parameterizing the edit controls
						normally the click of the edit control= the double-click of the row
						normal device is an a
							we need href (focusViewURL)
							we need window name for interoperability
							we need window size
							[we need opening method; normally ow()]
						normally wraps around an image
							
						these can be generated by a function named in $focusViewDeviceFunction but the function needs to return, standardized:
							* focusViewURL
							* focusViewSelfName
							* focusViewSize
							* focusViewEditImage (default is the *old* images/i/edit2.gif) - if in subfolder write as s/themename/edit.png
							* [alternately]focusViewJSFunction
						>----------------- */
						
						if($focusViewDeviceFunction){
							//call the function with the record as the standard parameter; all other parameters must be globalized
							$focusViewDeviceFunction($record);
							//array with same name as function
							@extract($$focusViewDeviceFunction);
						}else{
							if(!$focusViewEditImageDims){
								$focusViewEditImageDims=true; //regardless of whether we have it or not
								$dsgis=getimagesize($_SERVER['DOCUMENT_ROOT'].'/images/i/'.($focusViewEditImage ? $focusViewEditImage : 'edit2.gif'));
							}
							?><a title="<?php echo $focusViewTitle ? $focusViewTitle : 'Edit '.strtolower($datasetWord).' information';?>" href="<?php echo $focusViewURL?>" onclick="return ow(this.href,'l1_<?php echo $focusViewSelfName ? $focusViewSelfName : strtolower($datasetWord);?>','<?php echo $focusViewSize ? $focusViewSize : '700,700';?>');return false;"><?php
							if($dsgis){
								?><img src="<?php echo $datasetImagesRelativePath?>/images/i/<?php echo $focusViewEditImage ? $focusViewEditImage : 'edit2.gif'?>" <?php echo $dsgis[2]?> alt="edit" /><?php
							}else{
								?>[edit]<?php
							}
							?></a> &nbsp;<?php
						}
						?></td><?php
					}
					//--------------- user columns coding added 2009-10-27 --------------
					$colPosition=0;
					if(!$lastHandle)
					foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
						if($submode=='exportDataset' && isset($datasetExportFields)){
							//we are pulling ONLY from datasetExportFields
							if(!@in_array($handle,$datasetExportFields) || (isset($scheme['visibility']) && $scheme['visibility']<COL_AVAILABLE))continue;
						}
						//get last column
						if(!isset($scheme['visibility']) || $scheme['visibility']>=COL_VISIBLE)$lastHandle=$handle;
					}
					foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
						if($submode=='exportDataset' && isset($datasetExportFields)){
							//we are pulling ONLY from datasetExportFields
							if(!@in_array($handle,$datasetExportFields) || (isset($scheme['visibility']) && $scheme['visibility']<COL_AVAILABLE))continue;
						}else{
							if(isset($scheme['visibility']) && $scheme['visibility']<COL_VISIBLE)continue;
						}
						$colPosition++;
						unset($echoed,$out,$format);

						//handle 1)wrap, 2)addt'l classes 3)overflow of data at some point
						unset($mergeAttribs,$colAttribs);
						if($scheme['colattribs']){
							foreach($scheme['colattribs'] as $n=>$v){
								$n=strtolower($n);
								if($n=='class' || ($n=='nowrap' && $v)){
									$mergeAttribs[$n]=$v;
								}else{
									$colAttribs[$n]=$v;
								}
							}
						}

						//-------- here is the kernel logic for how we present the fields --------
						if(!$scheme['method'] || $scheme['method']=='field'){
							$out=$record[$scheme['fieldExpressionFunction'] ? $scheme['fieldExpressionFunction'] : $handle];
							switch($scheme['datatype']){
								case 'email':
								case 'url':
								case 'linkable':
									if(!function_exists('make_clickable_links'))require_once($FUNCTION_ROOT.'/function_make_clickable_links_v100.php');
									if($scheme['format']=='noformat')break;
									if($submode!=='exportDataset')$out=make_clickable_links($out);
									break;
								case 'date':
									if($scheme['format']=='noformat')break;
									//we'll assume the export wants the reformat as well
									if($scheme['format']){
										//not developed, this would be the format like F js etc. we use
									}else{
										$out=t($out, (strlen($out)==10?f_qbks:f_dspst), $scheme['thisyear']);
									}
									break;
								case 'time':
									$out=date('g:iA',strtotime($out));
									break;
								case 'logical':
									if($scheme['format']=='noformat')break;
									if(strlen($scheme['format'])){
										$out=output_logical($out,$scheme['format']);
									}
								case '':
									if(!$datasetTable){
										//2011-01-16 we cannot get field values for the table
										break;
									}
									/* 2009-12-15: improved default field handling */
									if(!$dataSourceExplained){
										$dataSourceExplained=q("EXPLAIN $datasetTable", O_ARRAY);
										foreach($dataSourceExplained as $n=>$v){
											$dataSourceExplained[$v['Field']]=$v;
											unset($dataSourceExplained[$n]);
										}
									}
									if(!($v=$dataSourceExplained[$handle]))break;
									preg_match('/^([a-z]+)(.*)/i',$v['Type'],$a);
									if($a[1]=='date'){
										$out=($out=='0000-00-00' ? '' : date('m/d/Y',strtotime($out)));
									}else if($a[1]=='datetime'){
										$out=t($out, (strlen($out)==10?f_qbks:f_dspst), thisyear);
									}else if($a[1]=='time'){
										//assume balls is a null for now
										$out=($out=='00:00:00' || $out=='00:00' || is_null($out) ? '' : date('g:iA',strtotime($out)));
									}else if($a[1]=='float'){
										$dims=trim($a[2],'()');
										$dims=explode(',',$dims);
										$out=number_format($out,$dims[1]);
										$format='float';
									}
									break;
							}
						}else if($scheme['method']=='function'){
							ob_start();
							eval('$out='.rtrim($scheme['fieldExpressionFunction'],';').';');
							$echoed=ob_get_contents();
							ob_end_clean();
							if($echoed && !$echonotified){
								$echonotified=true;
								mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
							}
						}
						if($submode=='exportDataset' && $datasetExportMethod>1){
							//$datasetOutput
							//get left key field if called for
							if(($n=$scheme['exportKey']) && $datasetExportMethod>2){
								if(is_array($n)){
									//undeveloped as of 2010-08-01
								}else{
									//default, pull the raw output from the query
									$quote=(preg_match('/['.$datasetExportSeparator.$datasetExportWrapper.']/',$record[$n]) ?
									$datasetExportWrapper : '');
									$rowOutput.=$quote . 
									str_replace($datasetExportWrapper, $datasetExportWrapperEscape, $record[$n]). 
									$quote.$datasetExportSeparator;
								}
							}
							$quote=(preg_match('/['.$datasetExportSeparator.$datasetExportWrapper.']/',$out) ? $datasetExportWrapper : '');
							$rowOutput.=$quote . str_replace($datasetExportWrapper, $datasetExportWrapperEscape, $out). $quote.$datasetExportSeparator;
						}else{
							?><td <?php if($format=='float')echo 'align="right"';?><?php if($scheme['width'])echo ' width="'.$scheme['width'].'"';?><?php echo $scheme['nowrap'] || $mergeAttribs['nowrap']?' nowrap':''?> class="<?php
							if($existingSort){
								$s=0;
								foreach($existingSort as $n=>$v){
									$s++;
									if($n==$handle){
										echo 'sorted'.$s.' '.($v==-1?'desc':'asc');
										break;
									}
								}
							}
							?><?php echo $handle==$lastHandle?' last':'' ?><?php if($mergeAttribs['class'])echo ' '.$mergeAttribs['class']?>"<?php if($colAttribs)foreach($colAttribs as $n=>$v)echo ' '.$n.'="'.$v.'"';?>><?php
							echo strlen($out) ? $out : '&nbsp;';
							?></td><?php
							//2010-10-18: add row break for layout mode
							if($scheme['breakafter']){
								echo '</tr><tr class="subrow">';
							}
						}
					}
					if($submode=='exportDataset' && $datasetExportMethod>1){
						$datasetOutput.=($rowOutput ? "\n" : '').rtrim($rowOutput,$datasetExportSeparator);
					}
					//---------------------------------------------------------------------
					?>
				</tr><?php

				//2010-04-07: new ascending subtotals and descending headers
				if($datasetShowBreaks){
					if(!is_array($optionsMid))$optionsMid=array('section'=>'mid');
					if($submode=='exportDataset')$optionsMid['disposition']='rawdata';
					dataset_breaks_calcs($optionsMid);
				}

				//--------- end new while loop kernel -----------
				if(!$nextRecord)break;
			}

			//2010-04-07: additional code to close out
			if($datasetShowBreaks){
				if(!is_array($optionsBottom))$optionsBottom=array('section'=>'bottom');
				if($submode=='exportDataset')$optionsBottom['disposition']='rawdata';
				dataset_breaks_calcs($optionsBottom);
			}
		}else{
			//no records
		}
		?></tbody>
		<?php
		$componentTbody=get_contents();
		if($tbodyScrollingThreshold && $tbodyScrollingThreshold>$i)$componentTbody=str_replace('style="overflow-y:scroll;overflow-x:hidden;height:350px;"','',$componentTbody);
		echo $componentTbody;
		?>
	</table>
</div>
<?php
echo $componentDiv=get_contents();
if($submode=='exportDataset')ob_end_clean();
if($componentRewrite){
	$standardLayout=ob_get_contents();
	ob_end_clean();
}
} //--- end break loop ---






/* ---

Exporting
---------
sometimes cells that show as one cell need to export as multiple, like address for example.  need a "stop" that can split into multiple cells
also a more elegant system than:
	if(submode==exportDataset){
		short
	}else{
		long
	}

i need to be able to reset sort and multi-sort asc/desc
need to know what the current sort is for api javascript objects and php objects
need to clear the sort
need to hide or show inactive objects and have a visual
need to integrate an essential "status" condition of a row - this is similar to the color that a row is in Thunderbird if the email has been tagged important (will show in red)

2010-03-07: starting to parameterize this component.  We had started toward buffering the entire component, and in addition we need to add limiting.
* this component works in conjunction with another component which declares the parameters.  
* it is designed to work with a 2d SQL query and turn it into a pretty interface which has:
	sortability
	selectability of fields
	highlighting and record opening
	context menuing
	groupability
	batchability
	
This component is /the table/ and also the header and the count parenthesis, therefore is a part of a greater whole, some conventions:
	required vars:
		datasetComponent
		availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']
		datasetWord
		datasetWordPlural
		hideObjectInactiveControl
		submode

batchability
------------
if I show less tan all the records for a particular condition, I need to know about it visually and be able to:
	move to next
	move to previous
	add next
	add previous
	show all
	change batch
	a visual bar would be like [+]---XXXXX-------------------- *
		dragging the XXXXX would quantum thebatch over
		clicking to the right of the batch would move to it
		ctrl-clicking to the right of the batch would add it
		same for the left
		+ means show all, toggles to show batch only (-) when I'm in show all
		* means show textual detail
		all this would go best at the top of the bar in a superheader, or the bottom tfoot
these vars can be stored in session or in settings to restore state
this component is not responsible for checking if the batch is currently within the scope of the recordset, but could be programmed to adjust if the count present is not toward the end.

I'm adding a 4th parameter in function get_navstats(), batches, which is the number of batches currently showing, and these "super vars" will be declared:
	nextGroupIndex (if not present, the next group is not available)
	nextGroupBatch
	prevGroupIndex (same here)
	prevGroupBatch
Iterating records
-----------------
By default the variable $recordsetIsRelative will be set to false; this means this recordset is absolute and if there is batching involved, we are going to bypass records not in any declared range

what I store
------------
_SESSION[userSettings][default{dataset}Batch]= (position,batch,batches) - as 76,15,3 would be position 76, 15 records/batch, and 3 batches (45 records), showing 76-120


we want batching to start happening above a sensible threshold, if it is allowed.  default would be to allow it
we normally do NOT want to batch data, only when it becomes unweildy due to size (or if we're on limited bandwidth or a blackberry etc.)
the applications here are analogous to facebook posts


$datasetBreakFields=array(
	1=>array(
		'column'=>'Country',
	),
	2=>array(
		'column'=>'State',
	),
	3=>array(
		'column'=>'City',
	)
);
$datasetCalcFields=array(
	array(
		'name'=>'Quotes',
		'calc'=>'sum'
	),
);

  --- */
?>