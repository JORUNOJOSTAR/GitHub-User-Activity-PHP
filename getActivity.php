<?php

[,$userName] = $argv;
$url = "https://api.github.com/users/{$userName}/events";

// Api won't work if user-agent is not set.(status code : 403)
//\r\n to properly terminate HTTP header line according to HTTP protocol.
$options = ['http'=>["header"=>"User-Agent: Activity-Fetcher/1.0\r\n"]];
$context = stream_context_create($options);

//suppress error for HTTP error
$response = @file_get_contents($url,false,$context);

function caculateEvent($eventData){
    $resultArray = [];
    foreach($eventData as $data){
        $repoName = $data["repo"]["name"];
        $eventType = $data["type"];

        //initialize element with repo name not exist  
        if(!isset($resultArray[$repoName])){
            $resultArray[$repoName] = [$eventType => 1];
            continue;
        }

        //add 1 if event type exist , otherwise initialize with 1
        if(!isset($resultArray[$repoName][$eventType])){
            $resultArray[$repoName][$eventType] = 1;
        }else{
            $resultArray[$repoName][$eventType] += 1;
        }
    }
    return $resultArray;
}

if($response){

    //set true to get response data in string as array
    $activityData = json_decode($response,true);

    //Array be empty if there is no activity
    if(count($activityData)<1){
        die("{$userName} has not public activity....");
    }

    //caculate event and outputing it
    foreach(caculateEvent($activityData) as $repoName=>$events){
        foreach($events as $eventName=>$eventCount){
            echo "{$eventCount} {$eventName} on {$repoName}\n";
        }
    };


}else{
    echo "User not found";
}