<?php
function fetchVids($photo_url,$token,$photoTagSampleSize,$videoSamples) {
  $relTags = array();
  $tags = array();
  // Create a stream
  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Authorization: Bearer $token"
    )
  );

  $context = stream_context_create($opts);

  $response = json_decode(file_get_contents('https://api.clarifai.com/v1/tag/?url='.$photo_url, false, $context),true);
  $response_arr = $response['results'][0]['result']['tag']['classes'];
  $ind = 20;
  foreach ($response_arr as $single_tag){
    $tags[$single_tag]=$ind;
    $ind--;
  }
  #arsort($tags);
  $count = 0;
  $vids = array();
  foreach ($tags as $key=>$val) {
    if ($count == $photoTagSampleSize){
      break;
    }
      if (strpos($key,' ')){
        continue;
      }
      $count++;
      $vineVids = json_decode(file_get_contents('https://api.vineapp.com/timelines/tags/'.$key));
      for ($i = 0; $i < $videoSamples; $i++){
        $vidUrl = $vineVids->data->records[$i]->videoUrl;
        $needle = strpos($vidUrl,'?');
        $vidUrl = substr($vidUrl,0,$needle);
        array_push($vids,$vidUrl);
      }
  }
  $winners = array();
  foreach ($vids as $vid){
    $winners[$vid] = 0;
    $response = json_decode(file_get_contents('https://api.clarifai.com/v1/tag/?url='.$vid, false, $context),true);
    $response_arr = $response['results'][0]['result']['tag']['classes'];

    $vid_tags = array();
    foreach($response_arr as $query){
      foreach ($query as $single_tag){
        if(!array_key_exists($single_tag,$vid_tags)){
          $vid_tags[$single_tag]=0;
        }
        $vid_tags[$single_tag]++;
      }
    }
    arsort($vid_tags);
    $tag_count = 0;
    foreach ($vid_tags as $key => $val){
      if (array_key_exists($key,$tags)) {
        $relTags[$key] = 1;
        $winners[$vid]+=$tags[$key];
      }
      if ($tag_count == 10){
        break;
      }
      $tag_count++;
    }
  }
  arsort($winners);
  $ret = array();
  $ret["tags"] = $relTags;
  $ret["results"] = $winners;
  return $ret;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PhotoVine - Capture. Discover. Share.</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/landing-page.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/dropzone.css" rel="stylesheet" type="text/css">

    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">


    <script src="js/dropzone.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery -->
    <script src="js/jquery.js"></script>
    <script type="text/javascript" src="js/html5gallery.js"></script>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-fixed-top topnav" role="navigation">
        <div class="container topnav">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand topnav" href="http://159.203.19.72/photovine" >PhotoVine</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>


    <!-- Header -->
    <a name="about"></a>
    <div class="intro-header">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="intro-message">
                        <h1>Pho<em><b style="color:#ffcccc">to</b></em><img src="img/vine.png" style="height:84px;padding-bottom:20px;"></img></h1>

                        <h3>Capture. &nbsp;&nbsp;&nbsp; Discover. &nbsp;&nbsp;&nbsp; Share.</h3>
                        <hr class="intro-divider">
                          <?php
                          if(isset($_GET['submit_photovine'])) {
                            $photo_url = $_GET['submit_photovine'];//'https://s-media-cache-ak0.pinimg.com/736x/0a/fe/b8/0afeb818a3d50b3bf7524b720191d666.jpg';
                            $token = "i55Fnb9jpJ3GyxMPMnhgo2Il7pYNUj";
                            $photoTagSS = 8;
                            $videoSamples = 1;
                            $results = fetchVids($photo_url,$token,$photoTagSS,$videoSamples);
                            $winners = $results['results'];
                            $tags = $results['tags'];

                            echo "<div style=\"display:none;margin-left:36%;\" class=\"html5gallery\" data-skin=\"darkness\" data-width=\"300\" data-height=\"400\">";

                            $count = 0;
                            foreach ($winners as $vid => $pts){
                              if ($count == 4){
                                 break;
                               }
                              $count++;
                              echo '<video width="320" height="240" controls>';
                              echo '<source src="'.$vid.'" type="video/mp4">';
                              echo '</video><br>';
                              #echo "Score: $pts";
                              echo "<br>";
                              echo "<a href=\"".$vid."\"><img src=\"img/number-".$count.".png\"></a>";
                            }
                             echo "</div>";
                            // echo "<div ";
                            // echo "<img src='".$photo_url."' height='300px' width=auto>";
                            // echo "<br>";
                            // print_r($tags);
                            // echo "<br>";
                            ?>
                            <hr class="intro-divider">
                            <div class="form-group text-center">
                              <a href="https://twitter.com/share" class="btn btn-share btn-raised" style="background-color:#4099FF;font-size:25px;" data-url="http://159.203.19.72/photovine/?submit_photovine=<?php echo $_GET['submit_photovine'];?>" data-size="large" data-dnt="true"><i class="fa fa-twitter-square"></i></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
                              <button class="btn btn-share btn-raised" style="background-color:#3b5998;font-size:25px;"><i class="fa fa-facebook-official"></i></button>
                              <button class="btn btn-share btn-raised" style="background-color:#36465d;font-size:25px;"><i class="fa fa-tumblr-square"></i></button>
                            <div class="row">
                              <div class="col-lg-6">
                                <div class="outerlayer">
                                  <h2 style="color:black">Your Image</h2>
                                  <hr class="intro-divider">
                              <?php
                                echo "<img src='".$photo_url."' style='display:block; margin:auto; max-width:300px;' width=auto>";
                                echo "<br>";
                                echo "</div>";
                                echo "</div>";
                                echo "<div class='col-lg-6' style='margin-top:27px;'>";
                                echo " <h2 style='color:black'>Your Tags</h2>
                                  <hr class='intro-divider'>";
                                foreach ($tags as $tag=>$key){

                                  echo "<div class='col-lg-4' style='color:white;background-color:rgba(0, 0, 0, 0.8);border-radius:5px;'>";
                                  echo $tag;

                                  echo "</div>";
                                }
                              ?>
                            </div>
                          </div>
                          </div>
                        </div>
                            <?php
                          } else {
                            ?>
                            <!-- <form action="/help" class="dropzone" id="uploader"></form> -->

                            <div class="form-group text-center">
                            <div>
                              <input type="text" name="urllink" id="urlLink" placeholder="Paste Your Url Here!" style="color:black;height:70px;width:80%;border-radius:5px;padding-left:50px;"/>
                              <br>
                              <button class="btn btn-success btn-raised"  id="sendLink" name="submit_photovine">Pho<em>to</em><img src="img/vine.png" style="height:25px;padding-bottom:5px;"></img> This!</button>
                              <button type="button" class="btn btn-success btn-raised" style="display:none;" id="upload">Pho<em>to</em><img src="img/vine.png" style="height:25px;padding-bottom:5px;"></img> This!</button>
                              <!-- <button type="button" style="margin-left:20px;" id="link" class="btn btn-success btn-raised">Provide a Link!</button> -->
                            </div>
                            </div>
                            <?php
                          }
                          ?>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.container -->

    </div>
    <!-- /.intro-header -->

    <!-- Page Content -->


	<a  name="contact"></a>
    <div class="banner">

        <div class="container">

            <div class="row">
              <div class="form-group text-center">
                <div class="col-lg-12">
                    <h2 style="color:black;">The Developers</h2>
                  </div>
                <div class="col-lg-4">
                  <h3 style="color:black;">Shiyang Han</h3>
                </div>
                <div class="col-lg-4">
                  <h3 style="color:black;">Nathan Liu</h3>
                </div>
                <div class="col-lg-4">
                  <h3 style="color:black;">Gordon Duzhou</h3>
                </div>
              </div>
            </div>

        </div>
        <!-- /.container -->

    </div>
    <!-- /.banner -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <p class="copyright text-muted small">Made with love at NHacks 2016</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
       Dropzone.autoDiscover = false;
       Dropzone.options.uploader = {
         autoProcessQueue: false,
         acceptedFiles: "image/png, image/jpg, image/jpeg"
       };
       var uploader = new Dropzone("#uploader");
       $('#upload').click(function() {
         uploader.processQueue();
       });
        uploader.on("sending", function(file, xhr, formData) {
      		console.log(file);
      	  // Will send the filesize along with the file as POST data.
        });
    </script>
    <script>
    $( document ).ready(function() {
      $("#sendLink").click(function() {
        window.location=".?submit_photovine="+$("#urlLink").val();
      });
      var toggle=0;
      $("#link").click(function() {
        if (toggle == 0) {
        $("#upload").fadeOut();
        $("#uploader").fadeOut();
        $("#urlLink").fadeIn();
          $("#sendLink").fadeIn();

          $("#link").html('Provide A File!');
          toggle = 1;
        } else {
          $("#urlLink").fadeOut();
          $("#sendLink").fadeOut();
          $("#upload").fadeIn();
          $("#uploader").fadeIn();
          $("#link").html('Provide A Link!');
          toggle = 0;
        }
      });
    });
    </script>
    <script>window.twttr = (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0],
        t = window.twttr || {};
      if (d.getElementById(id)) return t;
      js = d.createElement(s);
      js.id = id;
      js.src = "https://platform.twitter.com/widgets.js";
      fjs.parentNode.insertBefore(js, fjs);

      t._e = [];
      t.ready = function(f) {
        t._e.push(f);
      };

      return t;
    }(document, "script", "twitter-wjs"));</script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

</body>

</html>
