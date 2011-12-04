<?php
$this->Html->css('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/themes/smoothness/jquery-ui.css','stylesheet',array('inline'=>false));
$this->Html->script(array('https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js'),array('inline'=>false));
$this->Html->scriptBlock('$(function() {$("#startdate,#enddate").datepicker({ dateFormat: "yy-mm-dd" })});',array('inline'=>false));
echo $this->Form->create(false,array('type'=>'get'));
echo $this->Form->input('event',array('label'=>'Event:','options'=>$eventsArray));
echo $this->Form->input('firstpage',array('div'=>'page','label'=>'Pages'));
echo $this->Form->input('lastpage',array('div'=>'page','label'=>' to'));
echo $this->Form->input('startdate',array('label'=>'Start date:'));
echo $this->Form->input('enddate',array('label'=>'End date:'));
echo $this->Form->end('Update');
if(isset($pageVisits)) {
	echo '<br><img src="http://chart.apis.google.com/chart?chxr=0,0,';
	echo $maxVisits;
	echo '|1,0,';
	echo $maxTime;
	echo '&chxt=y,x&chs=600x400&cht=lc&chco=AA0033&chd=t:';
	echo $dataPoints;
	echo '&chdl=Visits%2FTime+interval+(s)&chg=14.3,-1,0,0&chls=2&chm=B,EEBCCB,0,0,0&chtt=Pages+';
	echo $firstPage."+to+".$lastPage."+of+".$eventName.",+".$startDate."-".$endDate;
	echo '" width="600" height="400" alt="AFOD Student Engagement" />';
}
?>