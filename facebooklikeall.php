<?php
/***
All rights reserved
Copyright 2014 Charles Wolfe
Free to use; if credits what matters, I'll take credit
charleswolfe@gajo.us
*/

class facebooklikeall {

	const LAST_RAN_TS_FILE = 'facebooklikeall.ini';
	private $user_access_token = '';
	private $user_id = '';
	private $last_ran_ts = 0;

	public function __construct($at, $ui) {
		$this->user_access_token = $at;
		$this->user_id = $ui;
		$this->last_ran_ts = $this->getSetLastRanTS();	
		$this->beginProcess();

	}

	private function beginProcess() {
		$unparsed_home_json = $this->getHome();
                $this->loopThruResults(json_decode($unparsed_home_json, true));
	}

	private function getSetLastRanTS() {
		$current_ts = time() - (30 * 60); //start 1/2 hour early
		$last_ran = $current_ts - (2 * 60 * 60); //default to two hours ago

		$ts_file = self::LAST_RAN_TS_FILE;
		if (file_exists($ts_file)) {
			$handle = fopen($ts_file, "r");
			$last_ran = fgets($handle);
			fclose($handle);

		}
		$handle = fopen($ts_file, "w");
		fwrite($handle, $current_ts);
		fclose($handle);
		
		return $last_ran;
	}

	public function getHome($home_url = null) {
		$access = $this->user_access_token;
		if (!$home_url) {
			$home_url = 'https://graph.facebook.com/me/home?access_token=' . urlencode($access);
			$home_url .= '&since=' . $this->last_ran_ts;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $home_url);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch, CURLOPT_POST, true);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output 
		$result = curl_exec($ch);
		curl_close ($ch);
		return $result;
	}

	private function makeAlike($url, $p_id) {
		echo PHP_EOL . 'makeAlike [' . $url . '] ['. $p_id .']' . PHP_EOL;
	//well here it is folks, the main component of this whole thing
	//we will need to throttle
		$fb_url = 'https://graph.facebook.com/me/likes?access_token=';
		$g_url = 'https://graph.facebook.com/' . $p_id . '/likes?access_token=';
                $access = $this->user_access_token;

                $attachment =  array(
                        'access_token' => urlencode($access),
                        'url' => $url
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $g_url . urlencode($access) );
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output
                $result = curl_exec($ch);
                curl_close ($ch);
echo PHP_EOL;
                echo $result;
echo PHP_EOL;
		if (isset($result['error'])) {
			$ch = curl_init();
                	curl_setopt($ch, CURLOPT_URL, $fb_url . urlencode($access) );
                	curl_setopt($ch, CURLOPT_POST, true);
                	curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
                	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output
                	$result = curl_exec($ch);
                	curl_close ($ch);
echo PHP_EOL;
echo 'TRY #2: ';
                	echo $result;
echo PHP_EOL;
		}
                return $result;


	}

	private function parse_likes($input) {
		$user_id = $this->user_id; 
		$data = $input['data'];
		foreach($data AS $datum) {
			if ($datum['id'] === $user_id) {
				return true;
			}
		}
		return false;
	}

	private function loopThruResults($input) {
		echo '||' . PHP_EOL;
		var_dump($input);
		echo '||' . PHP_EOL;
		//foreach($input AS $ikey => $ivalue) {
		$ivalue = $input['data'];
		//	var_dump($ivalue);
		foreach ($ivalue AS $pkey => $pdata) {
		//		echo '-----------------------------' . $pkey;
			//var_dump($pdata);
			if (isset($pdata['id'])) {
				$use_id = $pdata['id'];
//todo we need to get the correct id for some posts it isnt object_id
				if (isset($pdata['object_id'])) {
					$use_id = $pdata['object_id'];
				}

//get id break up by _
				$id_array = explode('_', $pdata['id']);
				if (isset($id_array[1])) {
					$use_id = $id_array[1];
				}


				if(isset($pdata['type'])) {
//what ypes do we want, what types do we hate
				}
				if(isset($pdata['status_type']) && $pdata['status_type'] != 'shared_story') {
					//we have the correct ststaus type
				}
				//now check to see if we liked it already
				$already_liked = false;
				if (isset($pdata['likes']) ) {
					//need to  look through ALL the likes until we find out user id
					$already_liked = $this->parse_likes($pdata['likes']);
				}
				if (isset($pdata['actions']) && !$already_liked) {
					foreach ($pdata['actions'] AS $actions) {
						if ($actions['name'] == 'Like') {
							echo $actions['link'];
							$this->makeAlike($actions['link'], $use_id);
							sleep(30);
						}
					}
				}

				// EXPERIMENTAL photo liking code
				//some pics arent linked and i dont knwo why
				if (isset($pdata['type'])) {
					if ('photo' === $pdata['type']) {
					if (isset($pdata['object_id'])) {
                                        $use_id = $pdata['object_id'];
					$this->makeAlike($actions['link'], $use_id);
                                }
					}	
				}


			} else {
				continue;
			}
		}
//		}
		if (isset($input['paging'])) {
			$pagnation_urls = $input['paging'];
			if (isset($pagnation_urls['next'])) {
				//omfg - he wrote it to be tail recursive what a lazy fuck
				$next_url = $pagnation_urls['next'];
				echo 'next url: ' . $next_url . PHP_EOL;
				$next_list = $this->getHome($next_url);
				$this->loopThruResults(json_decode($next_list, true));
			}
		}
		

	} //end of loopThruResults
} //end of class

if (isset($argv[1]) && isset($argv[2])) {
//echo PHP_EOL . $argv[1] . PHP_EOL . $argv[2] . PHP_EOL;
	new facebooklikeall($argv[1], $argv[2]);
} else {
	echo 'Usage:  ' . $argv[0] . ' <access toekn> <userid>' . PHP_EOL;
}
?>
