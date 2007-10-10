/* Original code by Stewart Rosenberger at http://www.stewartspeak.com/headings/ */

function com_stewartspeak_replacement() 
{
	var class_to_replace = "freshout";

	var plugin_path = "<?=get_bloginfo('url');?>" + "/wp-content/plugins/fresh_text/";
	
	replaceSelector("." + class_to_replace, plugin_path + "fresh_text.php", true);
	
	var testURL = plugin_path + "test.png";
	
	var doNotPrintImages = false;
	var printerCSS = "replacement-print.css";
	
	var hideFlicker = false;
	var hideFlickerCSS = "replacement-screen.css";
	var hideFlickerTimeout = 3000;
	
	var items;
	var imageLoaded = false;
	var documentLoaded = false;
	var escapeText = (typeof(encodeURIComponent) != 'undefined') ? encodeURIComponent : function(text){ return escape(text).replace(/\+/g,'%2B'); } ;


	function replaceSelector(selector,url,wordwrap)
	{
		if(typeof items == "undefined")
			items = new Array();
	
		items[items.length] = {selector: selector, url: url, wordwrap: wordwrap};
	}
	
	if(hideFlicker)
	{		
		document.write('<link id="hide-flicker" rel="stylesheet" media="screen" href="' + hideFlickerCSS + '" />');		
		window.flickerCheck = function()
		{
			if(!imageLoaded)
				setStyleSheetState('hide-flicker',false);
		};
		setTimeout('window.flickerCheck();',hideFlickerTimeout)
	}
	
	if(doNotPrintImages)
		document.write('<link id="print-text" rel="stylesheet" media="print" href="' + printerCSS + '" />');
	
	var test = new Image();
	test.onload = function() { imageLoaded = true; if(documentLoaded) replacement(); };
	test.src = testURL + "?date=" + (new Date()).getTime();
	
	addLoadHandler(function(){ documentLoaded = true; if(imageLoaded) replacement(); });
	
	
	function documentLoad()
	{
		documentLoaded = true;
		if(imageLoaded)
			replacement();
	}
	
	function replacement()
	{
		for(var i=0;i<items.length;i++)
		{
			var elements = getElementsBySelector(items[i].selector);
	
			if(elements.length > 0) for(var j=0;j<elements.length;j++)
			{
				if(!elements[j])
					continue ;
	
				if( elements.title != "undefined" )
				{
					var attributes = elements[j].title.replace(/\s+/g,'').split(',');
	
					if( attributes[0] != "undefined" )
					{
						var size = attributes[0];
					}
	
					if( attributes[1] != "undefined" )
					{
						var font = attributes[1];
					}
	
					if( attributes[2] != "undefined" )
					{
						var color = attributes[2];
					}
				}
	
	
				var text = extractText(elements[j]);
	
		    		while(elements[j].hasChildNodes())
					elements[j].removeChild(elements[j].firstChild);
	
				var tokens = items[i].wordwrap ? text.split(' ') : [text] ;
	
				for(var k=0;k<tokens.length;k++)
				{
					//var url = items[i].url + "?text="+escapeText(tokens[k]+' ')+"&selector="+escapeText(items[i].selector);
					var url = items[i].url + "?text="+escapeText(tokens[k])+"&selector="+escapeText(items[i].selector);
					
					if( size )
					{
						url += "&size=" + size;
					}
	
					if( font )
					{
						url += "&font=" + font;
					}
	
					if( color )
					{
						url += "&color=" + color;
					}
	
					var image = document.createElement("img");
					image.className = "replacement";
					image.alt = tokens[k] ;
					image.src = url;
					
					elements[j].appendChild(image);
				}
	
				if(doNotPrintImages)
				{
					var span = document.createElement("span");
					span.style.display = 'none';
					span.className = "print-text";
					span.appendChild(document.createTextNode(text));
					elements[j].appendChild(span);
				}
			}
		}
	
		if(hideFlicker)
			setStyleSheetState('hide-flicker',false);
	}
	
	function addLoadHandler(handler)
	{
		if(window.addEventListener)
		{
			window.addEventListener("load",handler,false);
		}
		else if(window.attachEvent)
		{
			window.attachEvent("onload",handler);
		}
		else if(window.onload)
		{
			var oldHandler = window.onload;
			window.onload = function piggyback()
			{
				oldHandler();
				handler();
			};
		}
		else
		{
			window.onload = handler;
		}
	}
	
	function setStyleSheetState(id,enabled) 
	{
		var sheet = document.getElementById(id);
		if(sheet)
			sheet.disabled = (!enabled);
	}
	
	function extractText(element)
	{
		if(typeof element == "string")
			return element;
		else if(typeof element == "undefined")
			return element;
	
		var text = "";
		var kids = element.childNodes;
		for(var i=0;i<kids.length;i++)
		{
			if(kids[i].nodeType == 1)
			text += extractText(kids[i]);
			else if(kids[i].nodeType == 3)
			text += kids[i].nodeValue;
		}
	
		return text;
	}
	
	/*
		Finds elements on page that match a given CSS selector rule. Some
		complicated rules are not compatible.
		Based on Simon Willison's excellent "getElementsBySelector" function.
		Original code (with comments and description):
			http://simon.incutio.com/archive/2003/03/25/getElementsBySelector
	*/
	function getElementsBySelector(selector)
	{
		var tokens = selector.split(' ');
		var currentContext = new Array(document);
		for(var i=0;i<tokens.length;i++)
		{
			token = tokens[i].replace(/^\s+/,'').replace(/\s+$/,'');
			if(token.indexOf('#') > -1)
			{
				var bits = token.split('#');
				var tagName = bits[0];
				var id = bits[1];
				var element = document.getElementById(id);
				if(tagName && element.nodeName.toLowerCase() != tagName)
					return new Array();
				currentContext = new Array(element);
				continue;
			}
	
			if(token.indexOf('.') > -1)
			{
				var bits = token.split('.');
				var tagName = bits[0];
				var className = bits[1];
				if(!tagName)
					tagName = '*';
	
				var found = new Array;
				var foundCount = 0;
				for(var h=0;h<currentContext.length;h++)
				{
					var elements;
					if(tagName == '*')
						elements = currentContext[h].all ? currentContext[h].all : currentContext[h].getElementsByTagName('*');
					else
						elements = currentContext[h].getElementsByTagName(tagName);
	
					for(var j=0;j<elements.length;j++)
						found[foundCount++] = elements[j];
				}
	
				currentContext = new Array;
				var currentContextIndex = 0;
				for(var k=0;k<found.length;k++)
				{
					if(found[k].className && found[k].className.match(new RegExp('\\b'+className+'\\b')))
						currentContext[currentContextIndex++] = found[k];
				}
	
				continue;
		    }
	
			if(token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/))
			{
				var tagName = RegExp.$1;
				var attrName = RegExp.$2;
				var attrOperator = RegExp.$3;
				var attrValue = RegExp.$4;
				if(!tagName)
					tagName = '*';
	
				var found = new Array;
				var foundCount = 0;
				for(var h=0;h<currentContext.length;h++)
				{
					var elements;
		        	if(tagName == '*')
						elements = currentContext[h].all ? currentContext[h].all : currentContext[h].getElementsByTagName('*');
					else
						elements = currentContext[h].getElementsByTagName(tagName);
	
					for(var j=0;j<elements.length;j++)
						found[foundCount++] = elements[j];
				}
	
				currentContext = new Array;
				var currentContextIndex = 0;
				var checkFunction;
				switch(attrOperator)
				{
					case '=':
						checkFunction = function(e) { return (e.getAttribute(attrName) == attrValue); };
						break;
					case '~':
						checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('\\b'+attrValue+'\\b'))); };
						break;
					case '|':
						checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('^'+attrValue+'-?'))); };
						break;
					case '^':
						checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) == 0); };
						break;
					case '$':
						checkFunction = function(e) { return (e.getAttribute(attrName).lastIndexOf(attrValue) == e.getAttribute(attrName).length - attrValue.length); };
						break;
					case '*':
						checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) > -1); };
						break;
					default :
						checkFunction = function(e) { return e.getAttribute(attrName); };
				}
	
				currentContext = new Array;
				var currentContextIndex = 0;
				for(var k=0;k<found.length;k++)
				{
					if(checkFunction(found[k]))
						currentContext[currentContextIndex++] = found[k];
				}
	
				continue;
			}
	
			tagName = token;
			var found = new Array;
			var foundCount = 0;
			for(var h=0;h<currentContext.length;h++)
			{
				var elements = currentContext[h].getElementsByTagName(tagName);
				for(var j=0;j<elements.length; j++)
					found[foundCount++] = elements[j];
			}
	
			currentContext = found;
		}
	
		return currentContext;
	}
	
	
}// end of scope, execute code


if(document.createElement && document.getElementsByTagName && !navigator.userAgent.match(/opera\/?6/i))
{
	com_stewartspeak_replacement();
}
