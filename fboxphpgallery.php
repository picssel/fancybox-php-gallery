<?php
header('X-Frame-Options: SAMEORIGIN');
/************************************************************************
    Filtered Fancybox image gallery - Part 2: Generate thumbnails with php
    Jose Francisco Diaz / picssel.com
    Version 2.0
************************************************************************/
//  THE LOOP
/*  To do :
  - Get a list of all files under the "gallery" directory and its sub-directories
  - Filter that list to include image files only
    (we may have other type of files like .ini, .dat, .log or system files that may have been added by other external processes)
  - Create the image thumbnails from the original images and place them in the "thumbs" sub-directory
  - Gather information regarding the location and the category each image belongs to
  - Render the html thumbnails' links and the category tabs 
*/
// general variables
$imgListArray = array(); // main image array list 
$imgExtArray = array("jpg"); // accepted image extensions (in lower-case !important)
$thumbsDir = "./gallery/thumbs/"; // path to the thumbnails destination directory
$galleryFiles = "./gallery/*/*"; // path to all files and sub-directories (use your own gallery name directory)
// iterate all subdirectories and files 
foreach( glob( $galleryFiles ) as $file ) {
    $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ); // get extension in lower-case for validation purposes
    $imagePath = pathinfo( $file, PATHINFO_DIRNAME ) . "/"; // get path for validation purposes (added trailing slash)
    // if image extension is valid (is in the $imgExtArray array) AND the image is not inside the "thumbs" sub-directory
    if( in_array( $ext, $imgExtArray ) && $imagePath != $thumbsDir ){
        // additional image variables 
        $imageName = pathinfo( $file, PATHINFO_BASENAME ); // returns "cheeta.jpg"
        $thumbnail = $thumbsDir.$imageName; // thumbnail full path and name, i.e "./gallery/thumbs/cheeta.jpg"
        $dataFilter = substr( $file, 10, 4 ); // from "./gallery/animals/cheeta.jpg" returns "anim" 
        // for each image, get width and height
        $imageSize = getimagesize( $file ); // image size 
        $imageWidth = $imageSize[0];  // extract image width 
        $imageHeight = $imageSize[1]; // extract image height
        // set the thumb size
        if( $imageHeight > $imageWidth ){
            // images is portrait so set thumbnail width to 100px and calculate height keeping aspect ratio
            $thumbWidth = 100;
            $thumbHeight = floor( $imageHeight * ( 100 / $imageWidth ) );           
            $thumbPosition  = "margin-top: -" . floor( ( $thumbHeight - 100 ) / 2 ) . "px; margin-left: 0";
        } else {
            // image is landscape so set thumbnail height to 100px and calculate width keeping aspect ratio
            $thumbHeight = 100;
            $thumbWidth = floor( $imageWidth * ( 100 / $imageHeight ) ); 
            $thumbPosition  = "margin-top: 0; margin-left: -" . floor( ( $thumbWidth - 100 ) / 2 ) . "px";
        } // END else if
        // verify if thumbnail exists, otherwise create it
        if ( !file_exists( $thumbnail ) ){
            $createFromjpeg = imagecreatefromjpeg( $file );
            $thumb_temp = imagecreatetruecolor( $thumbWidth, $thumbHeight );
            imagecopyresized( $thumb_temp, $createFromjpeg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight );
            imagejpeg( $thumb_temp, $thumbnail );
        } // END if()
        // Create sub-array for this image
        // notice the key,value pair
        $imgListSubArray = array( 
            LinkTo=>$file, 
            ImageName=> $imageName,
            Datafilter=>$dataFilter, 
            Thumbnail=>$thumbnail, 
            Position=>$thumbPosition
        );
        // Push this sub-array into main array $imgListArray
        array_push ( $imgListArray, $imgListSubArray ); 
    } // END if()
} // END foreach()
unset($file); // destroy the reference after foreach()
// END the loop
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="author" content="Jose Francisco Diaz / picssel.com" />
<title>Filtered Fancybox image gallery - Part 2: Generate thumbnails with php</title>
<!-- general styles: use your own -->
<link rel="stylesheet" type="text/css" href="/demos/demos.css" />
<!-- Set your paths accordingly -->
<link rel="stylesheet" type="text/css" href="/scripts/fancybox2.1.5/jquery.fancybox.css" />
<link rel="stylesheet" type="text/css" href="/scripts/fancybox2.1.5/helpers/jquery.fancybox-buttons.css" />
<style type="text/css">
/* this demo specific styles */
.imgContainer {
  width: 100px;
  height: 100px;
  overflow: hidden;
  text-align: center;
  margin: 10px 20px 10px 0;
  float: left;
  border: solid 1px #999;
  display: block;
}
.imgContainer:hover{
  border-bottom: solid 1px #444;
  border-left: solid 1px #444;
  margin: 9px 19px 11px 1px;
 -webkit-box-shadow: -3px 3px 10px 1px #777;
    -moz-box-shadow: -3px 3px 10px 1px #777;
         box-shadow: -3px 3px 10px 1px #777;
}
#galleryTab {
  margin: 10px 5px 20px 0;
  top: 26px;
  width: 450px;
}
.galleryWrap {
  padding: 0 0 30px;
}
.filter {
  border: 1px solid #ccc;
  color: #333333;
  float: left;
  font-size: 12px;
  margin: 0 12px 0 0;
  padding: 5px 15px;
  text-align: center;
  text-decoration: none;
  text-transform: capitalize;
 -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
         border-radius: 3px;
}
.filter:hover {
  background-color: #f8f8f8;
  margin: -1px 11px 1px 1px;
  border-bottom: solid 1px #aaa;
  border-left: solid 1px #aaa;
 -webkit-box-shadow: -2px 2px 5px 1px #a3a3a3;
    -moz-box-shadow: -2px 2px 5px 1px #a3a3a3;
         box-shadow: -2px 2px 5px 1px #a3a3a3;
}
.filter.active {
  background-color: #e2e2e2;
  border: 1px solid #ccc;
  color: #000;
  cursor: default;
  margin: 0 12px 0 0;
 -webkit-box-shadow: none;
    -moz-box-shadow: none;
         box-shadow: none;  
}
</style>
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script>window.jQuery || document.write("<script src='/scripts/jquery-1.8.3.min.js'>\x3C/script>")</script>
<script src="/scripts/fancybox2.1.5/jquery.fancybox.pack.js"></script>
<script src="/scripts/fancybox2.1.5/helpers/jquery.fancybox-buttons.js"></script>
<script>
jQuery(function($){
  // The Fancybox script
  $(".fancybox").fancybox({
    modal: true,
    helpers : { buttons: { } }
  });
  // The category selector jQuery script
  $(".filter").on("click", function () {
    var $this = $(this);
    // if we click the active tab, do nothing
    if (!$this.hasClass("active")) {
      $(".filter").removeClass("active");
      $this.addClass("active"); // set the active tab
      var $filter = $this.data("rel"); // get the data-rel value from selected tab and set as filter
      $filter == 'all' ? // if we select "view all", return to initial settings and show all
        $(".fancybox").attr("data-fancybox-group", "gallery").not(":visible").fadeIn() 
        : // otherwise
        $(".fancybox").fadeOut(0).filter(function () { 
          return $(this).data("filter") == $filter; // set data-filter value as the data-rel value of selected tab
        }).attr("data-fancybox-group", $filter).fadeIn(1000); // set data-fancybox-group and show filtered elements
    } // if
  }); // on
}); // ready
</script>
</head>
<body>
<div id="logo"></div><!-- picssel logo here ;) -->
<div id="wrap" class="cf">
<h2>Filtered Fancybox image gallery - Part 2: Generate thumbnails with php [DEMO]</h2>
<div id="galleryTab" class="cf">
  <a data-rel="all" href="javascript:;" class="filter active">View all</a>
  <?php 
  // render category selector tabs
  $galleryDir = "./gallery/*"; // target directories under gallery : notice the star "*" after the trailing slash  
  foreach( glob( $galleryDir, GLOB_ONLYDIR ) as $dir ) {
      // render category selector tabs and exclude the thumbnail directory
      if( $dir != "./gallery/thumbs" ){
          $dataRel = substr( $dir, 10, 4 ); // return first 4 letters of each folder as category
          $dirName = trim( substr( $dir, 10, 200 ) ); // returns a trimmed string (200 chars length) with name of folder without parent folder
          echo "<a data-rel=\"" . $dataRel . "\" href=\"javascript:;\" class=\"filter\">" . $dirName . "</a>"; 
      } // END if()
  } // END foreach()
  unset($dir);
  ?>
</div>
<div class="galleryWrap cf">
<?php 
// shuffle and render
shuffle( $imgListArray ); // random order otherwise is boring
$countedItems = count( $imgListArray ); // number of images in gallery
// render html links and thumbnails
for ( $row = 0; $row < $countedItems; $row++ ){
    // watch out for escaped double quotes 
    echo "<a class=\"fancybox imgContainer\" data-fancybox-group=\"gallery\" href=\"" .
          $imgListArray[$row][LinkTo] . "\" data-filter=\"" .
          $imgListArray[$row][Datafilter] . "\"><img src=\"" . 
          $imgListArray[$row][Thumbnail] . "\" style=\"" . 
          $imgListArray[$row][Position] . "\" alt=\"" . 
          $imgListArray[$row][ImageName] . "\" /></a>\n";          
} // END for()
?>
<br />
</div>
<p style="font-size: 11px; padding: 10px 0"><strong>Disclaimer</strong> : Images belong to their respective authors and are used for demo purposes only.</p>
</div><!--wrap-->
</body>
</html>