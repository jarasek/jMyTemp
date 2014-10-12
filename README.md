#jMyTemp

jMyTemp is template engine rendering on server side and client side. Server side script is jMytemp.php. This script use DOM parser mydom.php. Exactly the same template can be rendered in your browser by jQuery script (file jMyTemp.js). Template format is XHTML without special tags like for example "{{if data.field}}. Template must be well formed XHTML file or its fragment. You can not use tags like <br> but only <br />

This engine use HTML attributes like "data-temp". Attributes are not visible after rendering, but are not removed from code. It means that you can render page again with use different data. Such mechanism is used for AJAX technique. When you open link first time you reach page rendered by server (php script). Next click in link on your page can run java script function witch get only data. After that page can be render by browser with use new data.

<a href="mailto:jaroslaw.posadzy@onet.pl">Send e-mail to author</a>

##1. Template language

This engine use tag attributes "data-temp". Html code of page is always valid. In one "data-temp" is possible to include as many template commands as you want, but separated by semicolon. For example:

```
<div data-temp="if page.type main; insert /app/main.html"></div> 
```

Command argument must be separated by single space. Variables in php script are array, but in java script are objects. For example page.type means:

```
php: $data = array("page" => array("type" => "main"));
js:  data = {page: {type: "main"}};
```

###1.1 Conditional instructions

Engine use two commands "if" and "ifno":

```
if variable value
ifno variable value
```
Result of first is TRUE only when "variable" is equal  to "value". Result of "ifno" command is FALSE only when "variable" is not equal to "value". If result of these commands is FALSE tag is not visible. To style attribute of this tag is added "display: none;". If in style attribute "display" was used before for example "display: block;" then engine add to this tag attribute: data-disp="display: block;". "data-disp" attribute is removed when result of conditional command is TRUE and it contents is copied to style attribute. If tag is not visible without attribute "data-temp" it can not be visible even if result of conditional command is TRUE.

If result is FALSE next commands in this data-temp are not interpreted!

###1.2 Tag content from variable

```
val variable
```
This command put current value of specified variable to innerHTML of this tag. For example:

```
<span data-temp="val page.creation.date"></span>
```
###1.3 Tag attribute from variable

```
attr variable attribute
```
This command put current value of specified variable to attribute of this tag. If attribute of specified name not exist then is created, otherwise its value is changed. For example:

```
<span data-temp="attr page.color style"></span>
```
###1.4 Tag content from template file

####1.4.1 Include template

```
include template
```
This command put content of external file to innerHTML of this tag. File is loaded if innerHTML is empty. This means that file is loaded only ones. For example:

```
<div data-temp="if page.type main; include /app/templates/main.html"></div>
```
If "page.type" variable is "main" template is inserted. After that variable can change, because user can make some action. Content of this tag is never removed, but only it is hidden. Back to the "main" page change visibility of this tag, but content is not loaded again.

Engine use absolute paths created by: $path = $_SERVER["DOCUMENT_ROOT"].$template; If your real path is /var/www/html/app/templates/main.html and document root is /var/www/html (debian linux) use /app/templates/main.html

####1.4.2 Insert template

```
insert variable
```
Command insert content of this tag from external file. File is loaded even innerHTML is not empty. File path is value of specified variable (created like above). For example:

```
<div data-temp="if page.type book; insert page.file"></div>
```
Variable "page.file" can contain /app/book/chapter_1.html. Tag content can change each time when user switch between chapters.

###1.5 Loop

```
loop variable item_name
```
Example:

```
template:

<div data-temp="loop chapters i">
  chapter:<span data-temp="val .i.title"></span><br>
  <div data-temp="loop .i.authors k">
    autor:<span data-temp="val .k"></span><br>
  </div>
</div>

data:

data = {chapters: [{title: "one", authors: ["John","Peter"]}, {title: "two", authors: ["x","y","z"]}]};

result:

chapter:one
author:John
author:Peter
chapter:two
author:x
author:y
author:z
```
This command loops by array variables. It is possible to use only simple arrays (not associative). If array is empty, content of tag is not rendered. Second parameter of command is name of temporary variable used in loop. Name of this variable can be used inside the loop, but must be preceded by dot. After rendering tag with loop command, content of this tag is moved inside created special DIV tag with style="display: none;" and  class="loop-cache". Next rendering process remove previously rendered content and regenerate it from this DIV. Cache DIV is created only for main loop.

###1.6 Data manipulation

```
data url
```
This command gets additional data from specified url and merge it with data used to render this template. 

##2. Using template engine in your code

###2.1 Server side

Include jMyTemp.php:

```
require_once 'jmytemp.php';
```
Create template class and load rendered file:

```
$temp = new Template();
$temp->loadFile("main.html");
```
Prepare $data for rendering and render it:
```
$temp->render($data, "tag-id");
```
Second parameter is value of id attribute of rendered tag. If second parameter is omitted, file is rendered from beginning. $data used for render can be transfered inside created HTML file. In HEAD of this HTML is created SCRIPT tag with data in js format. You can use it in your js code:
```
$data = array("page" => "main");
$temp->saveData($data);

<html>
  <head>
    ...
    <script type="text/javascript">
      var data = {page: "main"};
    </script>
    ...
  <head>
  ...
</html>
```
Output renderd code:
```
echo $temp->getHtml();
```
###2.2 Client side

Include jQuery and after jMyTemp.js:

```
<script type="text/javascript" src="jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="jmytemp.js"></script>
```
On user action get new data end render page:
```
$.ajax({url: your_url, dataType: 'json', success: function(data) {
	render($("#tag-id"), data);
}});
```
