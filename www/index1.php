<?php

$api_key = '2321d44502d42675f576911da4789449';
$clarifai_key = 'LTxTWHwkvLRjPxaSW5rDnXialbx7n3wWa2vrCgbq';

$perPage = 25;
$url = 'https://api.flickr.com/services/rest/?method=flickr.interestingness.getList';
$url.= '&api_key='.$api_key;
$url.= '&per_page='.$perPage;
$url.= '&format=json';
$url.= '&nojsoncallback=1';
$response = json_decode(file_get_contents($url));
$photo_array = $response->photos->photo;

$tags = array();

foreach($photo_array as $single_photo){

$farm_id = $single_photo->farm;
$server_id = $single_photo->server;
$photo_id = $single_photo->id;
$secret_id = $single_photo->secret;
$size = 'm';

$title = $single_photo->title;

$photo_url = 'https://farm'.$farm_id.'.staticflickr.com/'.$server_id.'/'.$photo_id.'_'.$secret_id.'_'.$size.'.'.'jpg';

// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Authorization: Bearer RZDqUaz1lttgQSJLPJlowPDt1mLDEX"
  )
);

$context = stream_context_create($opts);

$response = json_decode(file_get_contents('https://api.clarifai.com/v1/tag/?url='.$photo_url, false, $context),true);
$response_arr = $response['results'][0]['result']['tag']['classes'];
echo "<img src='".$photo_url."' height='300px' width=auto>";
echo "<br>";
print_r($response_arr);
echo "<br>";
foreach ($response_arr as $single_tag){
  if(!array_key_exists($single_tag,$tags)){
    $tags[$single_tag]=0;
  }
  $tags[$single_tag]+=1;
}
}
arsort($tags);
foreach ($tags as $key=>$val) {
    printf($key." = ".$val."<br>");
}








 ?>
