<h1>jMyTemp</h1>
<p>
jMyTemp is template engine rendering on server side and client side. Server side script is jMytemp.php. This script use DOM parser mydom.php. Exactly the same template can be rendered in your browser by jQuery script (file jMyTemp.js). Template format is normal HTML without special tags like for example "{{if data.field}}. This engine use HTML attributes like "data-temp". Attributes are not visible after rendering, but are not removed from code. It means that you can render page again with use different data. Such mechanism is used for AJAX technique. When you open link first time you reach page rendered by server (php script). Next click in link on your page can run java script function witch get only data. After that page can be render by browser with use new data.
</p>
<p>
This project can solve problem with search engines (ex. google robots) with AJAX sites. Proposal is to use anchors with href attributes rendered by php script and onclick function to get data and render page by jQuery script. Sites created with this technique can be viewed by java disabled browsers too. 
</p>
