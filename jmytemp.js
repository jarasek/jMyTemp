function render(element, obj, loop) {
		element.children().each(function(index){
		if(this.className === "loop-cache")	{
			return;
		}
		var skip = loop;		
		var data = {};
		$.extend(data, obj);				
		var commands = this.getAttribute("data-temp");		
		if(commands != null && commands != "")
		{
			var coms = commands.split(";");
			for(c=0; c<coms.length; c++) {			
				if(coms[c] !== "" && coms[c] !== null) {
					var command = coms[c];	
					command = $.trim(command);
					command = command.replace(/\s+/g, ' ');
					var args = command.split(" ");
					switch(args[0]) {
						case "if":
							var con = getData(args[1], data);
							if(con === args[2] && typeof args[2] !== "undefined") {
								show(this);
							}
							else {
								hide(this);
								return;
							}
							break;
						case "ifno":
							var con = getData(args[1], data);
							if(con === args[2] && typeof args[2] !== "undefined") {
								hide(this);
								return;
							}
							else {
								show(this);
							}
							break;	
						case "data":
							$.ajax({url: args[1], dataType: 'json', success: function(response) {
									$.extend(true, data, response);								
								}, async: false});
							break;
						case "val":
							$(this).text(getData(args[1], data));
							break;
						case "attr":
							var con = getData(args[1], data);
							$(this).attr(args[2], con);
							break;	
						case "include":
						   var el = this;
						   if($.trim($(el).html()) === "") {
								$.ajax({url: args[1], success: function(template) {
									$(el).html(template);
								}, async: false});
							}
							break;
						case "insert":
						   var el = this;
							$(el).html("");
							var val = getData(args[1], data);
							$.ajax({url: val, success: function(template) {
								$(el).html(template);
							}, async: false});
							break;
						case "loop":
							var index = getData(args[1], data);
							var name = args[2];
							if(typeof name == "undefined" || name == null) {
								name = "";
							}
							var temp = null;
							$(this).contents().each(function(index, node) {
								if (node.nodeType == 8) {
									temp = node.nodeValue;
									return false;
								}
							});							
							var cache = "";
							if(typeof temp === "undefined" || temp == null) {
								if(typeof skip == "undefined" || skip == null) {
								  cache = '<!-- ' 
										+ $(this).html() + " -->";
								}
								temp = $(this).html();
							}
							else {
							  cache = '<!-- ' + temp + " -->";
							}		
							skip = true;
							$(this).html(cache);
							var el = $("<div></div>"); 
							for(i = 0; i < index.length; i++) {
								el.html(temp);							
								el.find("[data-temp]").each(function(index) {
									var attr = $(this).attr("data-temp");
									if(name !== "") {
										attr = attr.replace(" ." + name, " " + args[1] + "." + i);								
										attr = attr.replace(" #" + name, " #"  + i);
									}
									$(this).attr("data-temp", attr);
							   });							
								$(this).last().append(el.html());
							}
							break;
						default:
							break;
					}
				}
			}
		}
		render($(this), data, skip);
	});
}

function getData(variable, data) {
	if(variable.substring(0, 1) == '#') {
		return variable.substring(1);
	}
	
	var keys = variable.split(".");
	var v = data[keys.shift()];
    for (var i = 0, l = keys.length; i < l; i++) {
		v = v[keys[i]];
		if(typeof v == "undefined" || v == null) return "";
	}
	return (typeof v !== "undefined" && v !== null) ? v : "";
}

function show(element) {
	if(element.style.display != "none") {
		return;
	}
	var disp = $(element).attr("data-disp");	
	if(typeof disp !== "undefined") {			
		element.style.display = disp;
		//element.style.color = "black";
		
		$(element).removeAttr("data-disp");
	}
	else {
		element.style.display = "";
	}
}

function hide(element) {
	var disp = element.style.display;
	if(disp == "none") {
		return;
	}
	if(disp != "") {
		$(element).attr("data-disp", disp);	
	}
	element.style.display = "none";
}
