<?php
class VisitsController extends AppController {
	var $uses = array('Visit','Update','PageVisit');
	var $helpers = array('Html','Form');
	function triage() {
		if(!empty($this->params['form'])) {
			$data_array['Visit']['id'] = $this->params['form']['key'];
			$data_array['Visit']['user_agent'] = $this->params['form']['userAgent'];
			//save visit if new:
			$this->Visit->save($data_array);
			//save data:
			$this->Visit->id = $data_array['Visit']['id'];
			$updates_array = $this->data;
			foreach($updates_array as $update_item) {
				$update['Update']['visit_id'] = $data_array['Visit']['id'];
				$update['Update']['time'] = date('Y-m-d H:i:s',$update_item['time']);
				$update['Update']['place'] = $update_item['hash'];
				$this->Update->create();
				$this->Update->save($update);
			}
		}
	}
	function treatment() {
		//find all visits from the past 2 days (to make sure none are missed) with a last modified date earlier than five minutes ago
		$visits = $this->Visit->find('all',array('conditions' => array('Visit.created >' => date('Y-m-d H:i:s', strtotime("-2 days")),'Visit.modified <' => date('Y-m-d H:i:s', strtotime("-5 minutes")))));
		foreach($visits as $visit) {//for each visit
			if (empty($visit['PageVisit'])) {//if there are no pagevisit children, find all updates belonging to the visit
				foreach($visit['Update'] as $key => $update) {//for each update belonging to the visit
					if (isset($visit['Update'][$key+1])) { //don't use the last update since duration is unknown
						//parse the place into module and page
						$pieces = explode('/',$update['place']);
						if (isset($pieces[2])) {
							$modName = $pieces[1];
							$pageNum = $pieces[2];
							
							//calculate the duration based on the next update (and never longer than a minute)
							$intDuration = strtotime($visit['Update'][$key+1]['time'])-strtotime($update['time']);
							if ($intDuration > 60) {
								$intDuration = 60;
							}
							
							//if pagevisit with same identifiers exists, add duration to it, otherwise create it
							$pageVisit = $this->PageVisit->find('first',array('conditions' => array('PageVisit.visit_id' => $visit['Visit']['id'],'PageVisit.event_name' => $modName,'PageVisit.number' => $pageNum)));
							if(empty($pageVisit['PageVisit'])) {
								$this->PageVisit->create();
								$this->PageVisit->save(array('PageVisit'=>array('visit_id' => $visit['Visit']['id'],'event_name' => $modName,'number'=>$pageNum,'time'=>$update['time'],'duration'=>$intDuration)));
							} else {
								$this->PageVisit->id = $pageVisit['PageVisit']['id'];
								$newIntDuration = $pageVisit['PageVisit']['duration']+$intDuration;
								$pageVisit['PageVisit']['duration'] = $newIntDuration;
								$this->PageVisit->save($pageVisit);
							}
						}
					}
				}
			}
		}
	}
	function circus() {
		//set form options
		$distinctEvents = $this->PageVisit->find('all',array('fields'=>'DISTINCT PageVisit.event_name'));
		$events = array();
		$eventsArray = array();
		foreach($distinctEvents as $distinctEvent) {
			$maxPage = $this->PageVisit->find('first',array('conditions'=>array('PageVisit.event_name'=>$distinctEvent['PageVisit']['event_name']),'order'=>array('PageVisit.number DESC')));
			$events[] = array('name'=>$distinctEvent['PageVisit']['event_name'],'maxpage'=>$maxPage['PageVisit']['number']);
			$eventsArray[$distinctEvent['PageVisit']['event_name']] = $distinctEvent['PageVisit']['event_name'];
		}
		
		$this->set(array('events'=>$events,'eventsArray'=>$eventsArray));
		
		if (isset($this->params['url']['event'])) {
			$starttime = $this->params['url']['startdate']." 00:00:00";
			$endtime = $this->params['url']['enddate']." 00:00:00";
			//pull out relevant data
			$pageVisits = $this->PageVisit->find('all',array('conditions'=>array(
									'PageVisit.event_name' => $this->params['url']['event'],
									'PageVisit.number >=' => $this->params['url']['firstpage'],
									'PageVisit.number <=' => $this->params['url']['lastpage'],
									'PageVisit.time >=' => $starttime,
									'PageVisit.time <=' => $endtime)));
			
			//calculate graph data
			$longestPageVisit = $this->PageVisit->find('first',array('conditions'=>array(
																	'PageVisit.event_name' => $this->params['url']['event'],
																	'PageVisit.number >=' => $this->params['url']['firstpage'],
																	'PageVisit.number <=' => $this->params['url']['lastpage'],
																	'PageVisit.time >=' => $starttime,
																	'PageVisit.time <=' => $endtime),
																'order'=>array('PageVisit.duration DESC')));
			$maxDuration = $longestPageVisit['PageVisit']['duration'];
			$maxVisits = 0;
			$dataPoints = array_fill(0,21,0);
			foreach ($pageVisits as $pageVisit) {
				$fraction = $pageVisit['PageVisit']['duration']/$maxDuration;
				$fractionOfTwenty = round($fraction*20);
				if ($dataPoints[$fractionOfTwenty]++ > $maxVisits) {
					$maxVisits = $dataPoints[$fractionOfTwenty];
				}
			}
			if ($maxVisits>0) {
				foreach ($dataPoints as $key => $value) {
					$dataPoints[$key] = $value*100/$maxVisits;
				}
			}
			
			$this->set(array('pageVisits'=>$pageVisits,'maxVisits'=>$maxVisits,'maxTime'=>$maxDuration,'dataPoints'=>implode(',',$dataPoints),'firstPage'=>$this->params['url']['firstpage'],'lastPage'=> $this->params['url']['lastpage'],'eventName'=> $this->params['url']['event'],'startDate' => $this->params['url']['startdate'],'endDate' => $this->params['url']['enddate']));
			$this->data = $this->params['url'];
		}
	}
}
?>