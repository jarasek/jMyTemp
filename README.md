#jMyTemp

jMyTemp is template engine rendering on server side and client side. Server side script is jMytemp.php. This script use DOM parser mydom.php. Exactly the same template can be rendered in your browser by jQuery script (file jMyTemp.js). Template format is normal HTML without special tags like for example "{{if data.field}}. This engine use HTML attributes like "data-temp". Attributes are not visible after rendering, but are not removed from code. It means that you can render page again with use different data. Such mechanism is used for AJAX technique. When you open link first time you reach page rendered by server (php script). Next click in link on your page can run java script function witch get only data. After that page can be render by browser with use new data.

##Template language

This engine use tag attributes "data-temp". Html code of page is always valid. In one "data-temp" is possible to include as many template commands as you want, but separated by semicolon. For example:

```
<div data-temp="if page.type main; insert /app/main.html"></div> 
```

Command argument must be separated by single space. Variables in php script are array, but in java script are objects. For example page.type means:

```
php: $data = array("page" => array("type" => "main"));
js:  data = {page: {type: "main"}};
```

###Conditional instructions

Engine use two commands "if" and "ifno":

```
if variable value
ifno variable value
```
Result of first is TRUE only when "variable" is equal  to "value". Result of "ifno" command is FALSE only when "variable" is not equal to "value". If result of these commands is FALSE tag is not visible. To style attribute of this tag is added "display: none;". If in style attribute "display" was used before for example "display: block;" then engine add to this tag attribute: data-disp="display: block;". "data-disp" attribute is removed when result of conditional command is TRUE and it contents is copied to style attribute. If tag is not visible without attribute "data-temp" it can not be visible even if result of conditional command is TRUE.

If result is FALSE next commands in this data-temp are not interpreted!

###Tag content from variable

```
val variable
```
This command put current value of specified variable to innerHTML of this tag. For example:

```
<span data-temp="val page.creation.date"></span>
```
###Tag attribute from variable

```
attr variable attribute
```
This command put current value of specified variable to attribute of this tag. If attribute of specified name not exist then is created, otherwise its value is changed. For example:

```
<span data-temp="attr page.color style"></span>
```
###Tag content from over template

####Include template

```
include template
```
This command put content of external file to innerHTML of this tag. File is loaded if innerHTML is empty. This means that file is loaded only ones. For example:

```
<div data-temp="if page.type main; include /app/templates/main.html"></div>
```
If "page.type" variable is "main" template is inserted. After that variable can change, because user can make some action. Content of this tag is never removed, but only it is hidden. Back to the "main" page change visibility of this tag, but content is not loaded again.

Engine use absolute paths created by: $path = $_SERVER["DOCUMENT_ROOT"].$template; If your real path is /var/www/html/app/templates/main.html and document root is /var/www/html (debian linux) use /app/templates/main.html

####Insert template

```
insert variable
```
Command insert content of this tag from external file. File is loaded even innerHTML is not empty. File path is value of specified variable (created like above). For example:

```
<div data-temp="if page.type book; insert page.file"></div>
```
Variable "page.file" can contain /app/book/chapter_1.html. Tag content can change each time when user switch between chapters.





