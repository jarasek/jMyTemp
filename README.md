#jMyTemp

jMyTemp is template engine rendering on server side and client side. Server side script is jMytemp.php. This script use DOM parser mydom.php. Exactly the same template can be rendered in your browser by jQuery script (file jMyTemp.js). Template format is normal HTML without special tags like for example "{{if data.field}}. This engine use HTML attributes like "data-temp". Attributes are not visible after rendering, but are not removed from code. It means that you can render page again with use different data. Such mechanism is used for AJAX technique. When you open link first time you reach page rendered by server (php script). Next click in link on your page can run java script function witch get only data. After that page can be render by browser with use new data.

##Template language

This engine use only tag attributes "data-temp". Html code of page is always valid. In one "data-temp" is possible to include as many template commands as you want, but separated by semicolon. For example:

```
<div data-temp="if page.type main; insert /app/main.html"></div> 
```

Command argument must be separated by single space.

###Conditional instructions

Engine use two commands "if" and "ifno":

```
if variable value
ifno variable value
```
Result of first is TRUE only when "variable" is equal  to "value". Result of "ifno" command is FALSE only when "variable" is not equal to "value". If result of these commands is FALSE tag is not visible. To style attribute of this tag is added "display: none;". If in style attribute "display" was used before for example "display: block;" then engine add to this tag attribute data-disp="display: block;". "data-disp" attribute is removed when result of conditional command is TRUE and it contents is copied to style attribute. If tag is not visible without attribute "data-temp" it can not be visible even if result of conditional command is TRUE.

If result is FALSE next commands in this data-temp are not interpreted!
