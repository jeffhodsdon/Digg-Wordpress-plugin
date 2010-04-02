<?php
/*
Plugin Name: Digg
Plugin URI: http://digg.com/
Description: Digg plugin.
Version: 0.1.1
Author: Jeff Hodsdon
Author URI: http://jeffhodsdon.com/
*/

function digg_admin_menus() {
    add_menu_page('Digg', 'Digg', 8, basename(__FILE__), 'digg_button_options');
    add_submenu_page(basename(__FILE__), 'Button', 'Button', 8,
                     basename(__FILE__), 'digg_button_options');
    add_submenu_page(basename(__FILE__), 'Widget', 'Widget', 8,
                     basename(__FILE__) . '?widget', 'digg_widget_options');
}

function _digg_option($option) {
    return get_option('digg_setting_' . $option);
}

function _digg_checked($option, $value) {
    if (_digg_option($option) == $value) {
        echo 'checked="checked"';
    }
}

function _digg_selected($option, $value) {
    if (_digg_option($option) == $value) {
        echo 'selected';
    }
}

function _digg_topics() {
    if (function_exists('fsockopen')) {
        $socket = @fsockopen('services.digg.com', 80);
        if (!$socket) {
            return null;
        }

        fwrite($socket, "GET /1.0/endpoint?method=topic.getAll&type=json HTTP/1.1\r\n");
        fwrite($socket, "Host: services.digg.com\r\n");
        fwrite($socket, "Connection: close\r\n");
        fwrite($socket, "User-Agent: PHP Digg Wordpress plugin\r\n\r\n");
        $data = '';
        while (!feof($socket)) {
            $data .= fgets($socket, 128);
        }
        fclose($socket);
        $data = @json_decode(end(explode("\r\n\r\n", $data)), true);
        if (!is_array($data) || !isset($data['topics'])) {
            return null;
        }

        return $data['topics'];
    }

    return null;
}

function digg_button_options() {
?>
        <div class="wrap">
        <div class="icon32" id="icon-options-general"><br/></div><h2>Digg plugin settings: button</h2>
        <?php if (isset($_GET['updated'])) { echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>'; } ?>
        <p>This is the settings page for the Digg button.</p>
        <form method="post" action="options.php">
<?php
    if(function_exists('settings_fields')){
        settings_fields('digg-settings');
    } else {
        wp_nonce_field('update-options');
?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="digg_button_setting_enabled" />
<?php 
    }
?>
        <table class="form-table">
            <tr>
                <th scope="row"></th>
                <td>
                    <fieldset>
                        <input type="checkbox" value="1" <?php _digg_checked('button_enabled', 1); ?> name="digg_setting_button_enabled" id="digg_setting_button_enabled"/>
                        <label for="digg_setting_button_enabled">Enable the Digg button</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Button Type</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_type">
                            <option value="medium" <?php _digg_selected('button_type', 'medium'); ?>>Medium</option>
                            <option value="compact" <?php _digg_selected('button_type','compact'); ?>>Compact</option>
                            <option value="large" <?php _digg_selected('button_type', 'large'); ?>>Large</option>
                        </select>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Display</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_position">
                            <option value="after" <?php _digg_selected('button_position', 'after'); ?>>After post</option>
                            <option value="before" <?php _digg_selected('button_position', 'before'); ?>>Before post</option>
                            <option value="function" <?php _digg_selected('button_position', 'function'); ?>>PHP Function in template</option>
                        </select>
                    </fieldsetp>
            <tr>
                <th scope="row">Alignment</th>
                <td>
                    <fieldset>
                        <input type="radio" value="right" name="digg_setting_button_alignment" id="digg_setting_button_alignment_right" <?php _digg_checked('button_alignment', 'right'); ?>/>
                        <label for="digg_setting_button_alignment_right">Right of content</label>
                        <br />
                        <input type="radio" value="left" name="digg_setting_button_alignment" id="digg_setting_button_alignment_left" <?php _digg_checked('button_alignment', 'left'); ?>/>
                        <label for="digg_setting_button_alignment_left">Left of content</label>
                    </fieldset>
               </td>
            </tr>
            <tr>
                <th scope="row">Custom style (optional)</th>
                <td>
                    <fieldset>
                        <input type="text" value="<?php echo get_option('digg_setting_button_style'); ?>" name="digg_setting_button_style"/>
                        <br />
                        <label>CSS style for the Digg button e.g. <code>margin-right: 10px;</code></label>
                    </fieldset>
                </td>
            </tr>
<?php
            $topics = _digg_topics();
            if (is_array($topics)) {
?>
            <tr>
                <th scope="row">Default topic</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_topic">
                            <option value="">(none)</option>
<?php
                        foreach ($topics as $topic) {
?>
                            <option value="<?php echo $topic['short_name']; ?>" <?php _digg_selected('button_topic', $topic['short_name']); ?>><?php echo $topic['name']; ?></option>
<?php
                        }
?>
                        </select>
                        <br />
                        <label>You can choose a default topic that will be selected if your post gets submitted via the Digg button</label>
                    </fieldset>
                </td>
            </tr>
<?php
            }
?>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
        </form>
        </div>
<?php
}

function digg_widget_options() {
    $base = get_option('siteurl') . "/wp-content/plugins/digg/";
    if (isset($_GET['updated'])) {
?>
        <div id="message" style="height: 302px; background: #fff url(<?php echo $base; ?>img/now.gif) no-repeat" class="updated fade">
        </div>
        <div>
            <p><a href="?page=digg.php?widget"><< Back to creating a widget</a>
        </div>
<?php
    } else {
?>
<style>
#sidebar-left { display:none; }
div.node-inner ul.links { display:none }
#main-inner #content { margin-left:0; }
div#content-header h1 { display:none; }
#digg-widget span.ext { background:none; }
#widget-preview span.ext  { display:none } 
#widget-generator, #widget-generator h1 { line-height:normal; }
#widget-generator h2 { line-height:normal; margin-bottom: 1em; }
#widget-generator h3, #widget-generator h4 { line-height:normal; font-weight:bold; margin-bottom: 1em; }
#widget-generator p { margin-top:1em; }
div.field-field-page-files { display:none; }

#widget-generator {
width:960px;
font-family:arial,helvetica;
font-size:12px;    
}

#widget-generator input, #widget-generator select {
font-family:arial,helvetica;
}

#widget-generator div.intro {
position:relative;
padding:20px;
}

#widget-generator div.intro h1 {
font-size:28px;
font-weight:normal;
padding:0;
margin:0;
}

#widget-generator .intro h1 span {
margin:0;    
width:293px;
display:inline-block;
}

#widget-generator .intro h1 span.intro {
font-size:12px;
width:607px;
letter-spacing:0;
font-size:.4em;
}

#widget-generator strong {
color:#bf1313
}

.widget-preview {
margin:0 0 20px 0;
padding:0 0 13px 0;
background:url(<?php echo $base; ?>img/cut_black.png) 300px bottom no-repeat;
}

#widget-preview {
position:relative;
width:100%;
background:url(<?php echo $base; ?>img/cut_white.png) 300px top no-repeat #000;
color:#fff;
padding:20px 0;
}

#widget-preview h4 {
color:#fff;    
font-size:16px;
margin:10px 0 15px 0;
}

#widget-preview .style-options {
float:left;
clear:none;
width:293px;
margin:0 0 0 20px;
}

#widget-preview  .more-style-options {
display:none;    
}

#widget-preview .style-options form {
border-top:20px #000 solid;    
}

.show-more-style-options {
cursor:pointer;
font-size:12px;    
}

#widget-preview input {
border:1px #999 solid;
background:#000;
color:#999;
padding:4px;
padding:4px;
}

.widget-title label {
color:#999;
}

#widget-preview .dimensions, #widget-preview .content-option {
margin-top:5px;
color:#999;
}

#widget-preview .dimensions input {
width:auto;
margin-right:10px;
}

#widget-preview .content-option a {
color:#999;
}

#widget-preview .content-option input {
width:auto;
margin:0;
border:0;
padding:0;
}

#description-color, #subhd-color {
display:none;
}

#widget-preview .color {
position:relative;
padding:0 0 6px 0;
margin:10px 0 0 0;
color:#fff;
width:244px;
}

#widget-preview .color label {
font-size:12px;    
position:absolute;
z-index:2;
right:5px;
top:5px;
line-height:normal;
}

#widget-preview .color input {
display:block;
-moz-border-radius-topleft: 3px;
-webkit-border-top-left-radius:3px;    
-moz-border-radius-topright: 3px;
-webkit-border-top-right-radius:3px;    
-moz-border-radius-bottomleft: 3px;
-webkit-border-bottom-left-radius:3px;    
-moz-border-radius-bottomright: 3px;
-webkit-border-bottom-right-radius:3px;    
border:1px #1b5790;
background:#1b5790;
color:#fff;
margin:5px 0 0 0;
font-weight:bold;    
width:244px;
}

#tab-colors {
display:none;    
}

.swatch {
float:left;
margin:5px 3px 0 1px;
cursor:point;
width:14px;
height:14px;
border:1px #484848 solid;    
}

#layout-select {
clear:both;    
}

#layout-select div {
cursor:pointer;    
}

#layout1, #layout2, #layout3 {
float:left;
clear:none;
}

.layout {
color:#484848;    
font-weight:bold;
}

.layout.on {
color:#fff;    
}

#layout1 {
padding:0 15px 0 0;
border-right:1px #484848 solid;    
}

#layout2 {
padding:10px 0 0 15px;
border-left:1px #484848 solid;    
margin:0 0 0 -1px;
}

#layout-select .single {
background:url(<?php echo $base; ?>img/icn_1col_off.png) no-repeat;
width:50px;
height:60px;    
margin:0 0 10px 0;
}

#layout-select .on .single {
background:url(<?php echo $base; ?>img/icn_1col_on.png) no-repeat;
}

#layout-select .double {
background:url(<?php echo $base; ?>img/icn_2col_off.png) no-repeat;
width:84px;
height:50px;
margin:0 0 10px 0;        
}

#layout-select .on .double {
background:url(<?php echo $base; ?>img/icn_2col_on.png) no-repeat;
}

#widget-preview .preview-hint {
position:absolute;
right:20px;
top: 40px;
color:#484848;
text-align:right; 
font-size:11px;
}

#widget-preview .preview-hint h4 {
color:#484848;
margin:0;    
}

.widget-container {
float:left;
clear:none;
width:607px;
}

#widget-container {
position:relative;
z-index:100;
margin:15px 0 0 0;
padding:0;
width:300px;
min-height:350px;
margin:0 auto;
}

#widget-container.layout2 {
padding:80px 0 0 0;
width:auto;
}

#widget-code {
clear:both;
margin:15px 0 0 0;
padding:15px 0 0 0;
border-top:1px #b8b8b8 solid;
}

#tab-selector {
float:left;
position:relative;
border-left:20px #fff solid;
}

#tab-selector h4, #tab-selector h3 {
color:#b8b8b8;
font-size:13px;
}

#tab-selector h4#single-tab {
margin:5px 0 0 5px;    
}

#tab-selector button {
color:#fff;
font-weight:bold;
padding:5px 8px;
background:#b8b8b8;    
margin:10px 0 0 0;
}

#tab-selector button, #tab-selector input {
border:1px #b8b8b8 solid;
-moz-border-radius-topleft: 3px;
-webkit-border-top-left-radius:3px;    
-moz-border-radius-topright: 3px;
-webkit-border-top-right-radius:3px;    
-moz-border-radius-bottomleft: 3px;
-webkit-border-bottom-left-radius:3px;    
-moz-border-radius-bottomright: 3px;
-webkit-border-bottom-right-radius:3px;        
}

#tab-selector input {
color:#b8b8b8;
padding:4px;    
}

.tab-edit {
position:relative;
padding:5px 5px 10px 5px;
border-bottom:1px #b8b8b8 solid;
}

.tab-edit h4 {
margin:0 0 5px 0;    
}

.tab-edit.selected {
background:#fff0c5;    
}

.tab-edit .tab-delete {    
position:absolute;
right:5px; 
top:5px;
font-size:13px;
line-height:13px;
color:#b8b8b8;
cursor:pointer;
display:none;
}
    
.tab-edit.selected .tab-delete {
display:block;
}

#what-to-show {
width: auto;
font-size:11px;
font-weight:bold;
padding:0 0 20px 0;
}

#what-to-show div.intro {
padding: 0 0 15px 0;
border-bottom:2px #b8b8b8 solid;    
margin:20px 20px 15px 20px;
color:#b8b8b8;
font-size:12px;
font-weight:normal;
line-height:15px;
}

#what-to-show .intro h4 {
margin:0;
padding:0;
color:#000;
font-size:16px;
float:left;
clear:none; 
width:293px;
}

#what-to-show .fld {
padding:15px 10px;    
border-bottom:1px #b8b8b8 solid;
}

#what-to-show .storycount {
padding:10px 0 0 0;
text-align:right;
}

#what-to-show .fld input, #what-to-show .fld select {
vertical-align:baseline;    
}

#what-to-show .fld .ffld {
padding:5px 0 0 0;
border:0;    
}

#what-to-show .fld .ffld .r {
visibility:hidden;    
}

#fields-container {
float:right;
width: 627px;
margin-right:20px;
}

.fld.selected {
background:#fff0c5;    
}

#yourusername {
margin-left:10px;
}

#widget-generator .finished {
margin:0;
background:#f5f5f5;
padding:15px 20px;
clear:both;
}

br {
clear:both;    
}

.br {
line-height:1px;
clear:both;
}
</style>




<div id="widget-generator">
<div class="intro">
<h1><span>Create a Widget</span><span class="intro">Display the latest Digg news on your site by adding a Digg Widget, now with more choices for what to show and how it's displayed. When news is updated on Digg, it will automatically be displayed on your site, in the format that you choose. <strong>To get started, follow the steps below to customize your widget.</strong></span></h1>
</div>
<div class="widget-preview">
<div id="widget-preview">
<div class="style-options">
<h4>Select a type</h4>
<div id="layout-select">
<div id="layout1" class="layout on">
<div class="single"></div>
Single<br />Column
</div>
<div id="layout2" class="layout">
<div class="double"></div>        
Double<br />Columns
</div>
<br />
</div>
<form class="widget-generator-form">
<h4>Change appearance</h4>
<div><label class="content-option">Title:</label> <input type="text" name="widget-title" value="CBSNews.com on Digg" /></div>
<div class="color"><label>Header background</label><input type="text" name="hdrBg" /></div>
<div class="show-more-style-options">More display options</div>
<div class="more-style-options">
<div class="color"><label>Header text</label><input type="text" name="hdrTxt" /></div>
<div class="color"><label>Content background</label><input type="text" name="bdyBg" /></div>
<div class="color"><label>Horizontal rule</label><input type="text" name="stryBrdr" /></div>
<div class="color"><label>Links</label><input type="text" name="lnk" /></div>
<div id="tab-colors">
<div class="color"><label>Tab background</label><input type="text" name="tabBg" /></div>
<div class="color"><label>Tab text</label><input type="text" name="tabTxt" /></div>
<div class="color"><label>Selected tab text</label><input type="text" name="tabOnTxt" /></div>        
</div>
<div id="subhd-color" class="color"><label>Column heading</label><input type="text" name="subHd" /></div>
<div id="description-color" class="color"><label>Story description text</label><input type="text" name="descTxt" /></div>                
<div class="dimensions">Width: <input type="text" name="widget-width" value="300" size="3" /> Height: <input type="text" name="widget-height" value="" size="3" /></div>               
<div class="content-option"><input type="checkbox" name="descriptions" id="show-descriptions" /> <label for="show-descriptions">Show story descriptions</label></div>
<div class="content-option"><input type="checkbox" name="widget-header" id="show-header" checked="checked" /> <label for="show-header">Show header</label></div>
<div class="content-option"><input type="checkbox" name="widget-footer" id="show-footer" checked="checked" /> <label for="show-footer">Show footer</label></div>
<div class="content-option"><input type="checkbox" name="widget-diggs" id="show-digg-counts" checked="checked" /> <label for="show-digg-counts">Show diggs</label></div>
<div class="content-option"><input type="checkbox" name="widget-thumb" id="show-thumbs" checked="checked" /> <label for="show-thumbs">Show thumbnails</label></div>
<div class="content-option"><input type="checkbox" name="widget-rounded" id="show-rounded" checked="checked" /> <label for="show-rounded">Round corners (Mozilla and Webkit)</label></div>
<div class="content-option"><input type="checkbox" name="widget-stylesheet" id="use-stylesheet" checked="checked" /> <label for="use-stylesheet">Use default stylesheet</label> (<a id="view-stylesheet" href="http://widgets.digg.com/css/widgets.css" target="_blank">view</a>)</div>
<div class="content-option"><input type="checkbox" name="widget-targ" id="link-targ" /> <label for="link-targ">Open links in a new window</label></div>
</div>
</form>
</div>
<div class="preview-hint">
<h4>Preview</h4> Your Widget
</div>
<div class="widget-container">
<div id="widget-container">
<div id="digg-widget"></div>
</div>
</div>
<br />
</div>
</div>
<div id="what-to-show">
<div class="intro">
<h4>Choose what to show</h4>
<span>Select from the following ways to populate your widget.</span>
</div>
<div id="tab-selector">
<h4 id="single-tab">Single Tab</h4>
<form class="widget-generator-form">
<button class="add-tab" type="button">Add a Tab</button>
<p>Or add more stories in <a id="add-column" href="javascript://">another column</a>.</p>
</form>
</div>
<div id="fields-container">
<form class="widget-generator-form" id="method-form">
<div class="fld selected"> <input type="radio" class="r" name="news_type" value="domain" checked="checked" /> <select name="source-poporup">
<option value="popular">Popular</option>
<option value="upcoming">Upcoming</option>
<option value="all">All</option>
</select> <label >stories from the source site</label> <input type="text" name="url" size="17" value="CBSNews.com" />
<div class="ffld">
<input type="radio" class="r" /> <select name="url-sort">
<option value="promote_date-desc">sorted by date</option>
<option value="digg_count-desc">sorted by diggs</option>
</select> in the
<select name="mindate">
<option value="">last millenium</option>
<option value="1">last 24 hours</option>
<option value="7">last 7 days</option>
<option value="30">last 30 days</option>
<option value="365">last 365 days</option>
</select> <input type="checkbox" name="fallback" checked="checked" /> Use fallback content if too few stories are found
</div>
</div>        
<div class="fld"><input type="radio" class="r" name="news_type" value="front" />
<label for="news-type1">All popular stories in</label>&nbsp;<select name="news_front">
<option value="" selected="selected">All Topics</option>
<option name="apple" value="apple">Apple</option>
<option name="design" value="design">Design</option>
<option name="gadgets" value="gadgets">Gadgets</option>
<option name="hardware" value="hardware">Hardware</option>
<option name="tech_news" value="tech_news">Industry News</option>
<option name="linux_unix" value="linux_unix">Linux/Unix</option>
<option name="microsoft" value="microsoft">Microsoft</option>
<option name="mods" value="mods">Mods</option>
<option name="programming" value="programming">Programming</option>
<option name="security" value="security">Security</option>
<option name="software" value="software">Software</option>
<option name="business_finance" value="business_finance">Business & Finance</option>
<option name="world_news" value="world_news">World News</option>
<option name="politics" value="politics">Political News</option>
<option name="political_opinion" value="political_opinion">Political Opinion</option>
<option name="celebrity" value="celebrity">Celebrity</option>
<option name="movies" value="movies">Movies</option>
<option name="music" value="music">Music</option>
<option name="television" value="television">Television</option>
<option name="comics_animation" value="comics_animation">Comics & Animation</option>
<option name="gaming_news" value="gaming_news">Industry News</option>
<option name="pc_games" value="pc_games">PC Games</option>
<option name="playable_web_games" value="playable_web_games">Playable Web Games</option>
<option name="nintendo" value="nintendo">Nintendo</option>
<option name="playstation" value="playstation">PlayStation</option>
<option name="xbox" value="xbox">Xbox</option>
<option name="baseball" value="baseball">Baseball</option>
<option name="environment" value="environment">Environment</option>
<option name="general_sciences" value="general_sciences">General Sciences</option>
<option name="basketball" value="basketball">Basketball</option>
<option name="extreme_sports" value="extreme_sports">Extreme</option>
<option name="space" value="space">Space</option>
<option name="football" value="football">Football - US/Canada</option>
<option name="golf" value="golf">Golf</option>
<option name="hockey" value="hockey">Hockey</option>
<option name="motorsport" value="motorsport">Motorsport</option>
<option name="olympics" value="olympics">Olympics</option>
<option name="soccer" value="soccer">Soccer</option>
<option name="tennis" value="tennis">Tennis</option>
<option name="other_sports" value="other_sports">Other Sports</option>
<option name="arts_culture" value="arts_culture">Arts & Culture</option>
<option name="autos" value="autos">Autos</option>
<option name="educational" value="educational">Educational</option>
<option name="food_drink" value="food_drink">Food & Drink</option>
<option name="health" value="health">Health</option>
<option name="travel_places" value="travel_places">Travel & Places</option>
<option name="comedy" value="comedy">Comedy</option>
<option name="odd_stuff" value="odd_stuff">Odd Stuff</option>
<option name="people" value="people">People</option>
<option name="pets_animals" value="pets_animals">Pets & Animals</option>
</select>
</div>        
<div class="fld">
<input type="radio" class="r" name="news_type" value="top10"/> <label for="news-type0">Top 10 list from</label> <select name="news_top">
<option value="" selected="selected">All Topics</option>
<option value="technology">Technology</option>
<option value="science">Science</option>
<option value="world_business">World &amp; Business</option>
<option value="sports">Sports</option>
<option value="entertainment">Entertainment</option>
<option value="gaming">Gaming</option>
<option value="lifestyle">Lifestyle</option>
<option value="offbeat">Offbeat</option>
<option value="" title="media=news">News</option>
<option value="" title="media=videos">Videos</option>
<option value="" title="media=images">Images</option>
</select>
</div>
<div class="fld">
<input type="radio" class="r" name="news_type" value="user" /> <label for="news-type3">Stories </label> <select name="news_user" >
<option value="dugg">dugg</option>
<option value="submissions">submitted</option>
</select> <label for="username">by user</label> <input type="text" name="username" value="dtrinh" size="17" />
</div>        
<div class="fld"> <input type="radio" class="r" name="news_type" value="search" /> <label>Search results for</label> <input type="text" name="apisearch" size="17" value="Lady Gaga" /> in <select name="search-topics">
<option value="" selected="selected">All Topics</option>
<option value="technology">Technology</option>
<option value="science">Science</option>
<option value="world_business">World &amp; Business</option>
<option value="sports">Sports</option>
<option value="entertainment">Entertainment</option>
<option value="gaming">Gaming</option>
<option value="lifestyle">Lifestyle</option>
<option value="offbeat">Offbeat</option>
</select>
<select name="search-sort">
<option value="promote_date-desc">sorted by date</option>
<option value="digg_count-desc">sorted by diggs</option>
</select>
</div>        
<div class="fld f"> <input type="radio" class="r" id="news-type6" name="news_type" value="friends" /> <label>Stories your friends have</label> <select name="news_friends">
<option value="dugg">dugg</option>
<option value="submissions">submitted</option>
<option value="commented">commented on</option>
</select> <span id="yourusername">Your username: <input type="text" name="myusername" value="dtrinh" /></span>
</div>
<div class="storycount">
Number of Items
<input name="count" value="5" size=2" maxlength="2" />
</div>
</form>
</div>
<br />
</div>
<div class="finished">
<strong>Finished? Then your widget is ready to go!</strong>

<form method="post" action="options.php">
<?php
    settings_fields('digg-settings');
?>
    <input type="hidden" id="widget-code-html" value="" name="digg_setting_widget_html" />
    <input type="hidden" id="widget-code" value="" name="digg_setting_widget_json" />

    <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </p>
</form>

</div>
</div>

<script type="text/javascript" language="JavaScript" src="http://widgets.digg.com/widgets.js"></script>
<script type="text/javascript" language="JavaScript" src="http://about.digg.com/files/page/jquery-1.4.1.min_.js.txt"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo $base; ?>widget-generator.js"></script>
<script type="text/javascript">
(function() {
    var myGenerator = new DiggWidgetGenerator(<?php echo get_option('digg_setting_widget_json'); ?>, function() {
        $('input[name=digg_setting_widget_html]').val(myGenerator.getCode());
        $('input[name=digg_setting_widget_json]').val(myGenerator.getWidget());
    });    
})();
</script>

<?php
    }
}

function digg_fetch_button() {
    $button = '';
    if (!get_option('digg_setting_button_enabled')) {
        return $button;
    }

    $id = get_the_ID();
    if (!is_numeric($id)) {
        return $button;
    }

    $post = get_post($id);
    if (!is_object($post)) {
        return $button;
    }


    $url = get_page_link();
    if (get_post_status($record->ID) == 'publish') {
        $url = get_permalink();
    }

    $style = 'float: left; padding-right: 5px;';
    if (_digg_option('button_alignment') == 'right') {
        $style = 'float: right; padding-left: 5px;';
    }
    $style .= ' ' . _digg_option('button_style');

    $class = '';
    if (_digg_option('button_type') == 'medium') {
        $class = ' DiggThisButtonMedium';
    }

    $button .= '<div class="digg_button" style="' . $style . '">';
    $button .= '<a class="DiggThisButton' . $class . '" href="' . $url . '" rel="external" rev=", ' . _digg_option('button_topic') . '">';
    $button .= '<span style="display: none;">';
    if (empty($post->post_excerpt)) {
        # Take off the last word, Jeff style!
        $content = explode(' ', substr($post->post_content, 0, 300));
        array_pop($content);
        $button .= implode(' ', $content) . '...';
    } else {
        $button .= $post->post_excerpt;
    }
    $button .= '</span>';

    switch (_digg_option('button_type')) {
        case 'normal':
            $button .= '';
            break;
        case 'compact':
            $button .= '<img src="http://widgets.digg.com/img/button/diggThisCompact.png" alt="DiggThis" />';
            break;
        case 'icon':
            $button .= '<img src="http://digg.com/img/diggThisIcon.gif" height="16" width="16"  alt="DiggThis" />';
            break;
        default:
            break;
    }

    $button .= '</a>';
    $button .= '</div>';

    return $button;
}

function add_digg_button_script()
{
    if (!get_option('digg_setting_button_enabled')) {
        return;
    }

    echo '<script src="http://widgets.digg.com/buttons.js" type="text/javascript"></script>';
}

function add_digg_button_to_a_post($post)
{
    $position = _digg_option('button_position');
    if ($position == 'before') {
        $post = digg_fetch_button() . $post;
    } else if ($position == 'after') {
        $post = $post . digg_fetch_button();
    }

    return $post;
}

function digg_widget() {
    echo htmlspecialchars_decode(get_option('digg_setting_widget_html'));
}

function digg_widget_setup() {
    register_sidebar_widget('The Digg widget', 'digg_widget');
}

function digg_activate() {
    # Button settings
    add_option('digg_setting_button_enabled', '1');
    add_option('digg_setting_button_location', 'before');
    add_option('digg_setting_button_type', 'normal');
    add_option('digg_setting_button_position', 'before');
    add_option('digg_setting_button_alignment', 'left');
    add_option('digg_setting_button_style', '');
    add_option('digg_setting_button_topic', '');

    # Widget settings
    add_option('digg_setting_widget_html', '');
    add_option('digg_setting_widget_json', '');
}

function digg_register_settings() {
    if (!function_exists('register_setting')) {
        return;
    }

    # Button settings
    register_setting('digg-settings', 'digg_setting_button_enabled');
    register_setting('digg-settings', 'digg_setting_button_location');
    register_setting('digg-settings', 'digg_setting_button_type');
    register_setting('digg-settings', 'digg_setting_button_position');
    register_setting('digg-settings', 'digg_setting_button_alignment');
    register_setting('digg-settings', 'digg_setting_button_style');
    register_setting('digg-settings', 'digg_setting_button_topic');

    # Widget settings
    register_setting('digg-settings', 'digg_setting_widget_enabled');
    register_setting('digg-settings', 'digg_setting_widget_html');
    register_setting('digg-settings', 'digg_setting_widget_json');
}

if(is_admin()){
    add_action('admin_menu', 'digg_admin_menus');
    add_action('admin_init', 'digg_register_settings');
}

add_filter('the_content', 'add_digg_button_to_a_post');
add_filter('get_footer', 'add_digg_button_script');
add_action('widgets_init','digg_widget_setup');

register_activation_hook( __FILE__, 'digg_activate');

?>
