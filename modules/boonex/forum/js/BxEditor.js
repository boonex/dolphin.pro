/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/


/**
 * html editor
 */

BxEditor = function (i)
{
	// this._el
	// this._moz
	// this._doc
	this._name = i;
}

BxEditor.prototype.setName = function (i)
{
	this._name = i;
}

BxEditor.prototype.init = function ()
{
	this._el = document.getElementById (this._name);
	if (!this._el.contentDocument)
	{
		this._el = window[this._name];
	}

	this._doc = this._el.document ? this._el.document : this._el.contentDocument;

	if (!this._doc.designMode)
	{
		alert('[L[please upgrade your browser]]');
		return;
	}
	
	this._doc.designMode = 'on';

}


BxEditor.prototype.initMenu = function ()
{
	var a = { 
				0:{'left':0, 'make':'Bold'}, 
				1:{'left':-18, 'make':'Italic'}, 
				2:{'left':-36, 'make':'Underline'},
				3:{'left':-216, 'make':'Code'},
				4:{'left':-162, 'make':'BulletedList'},
				5:{'left':-144, 'make':'NumberedList'},
				6:{'left':-180, 'make':'Outdent'},
				7:{'left':-198, 'make':'Indent'},
				8:{'left':-288, 'make':'RemoveFormat'}
			}
	var len = 9;
	var ul = document.createElement ('ul');
	var img = document.createElement ('img');
	var $this = this;
	

	img.src = "sp.gif";
	img.style.width = '18px';
	img.style.height = '18px';
	img.style.border = 'none';

	ul.style.listStyle = 'none';
	ul.style.margin = '0';
	ul.style.marginTop = '5px';
	ul.style.marginBottom = '5px';
	ul.style.padding = '0';
	ul.style.clear = 'both';
	ul.style.height = '20px';
	ul.style.width = (len*20) + 'px';
	ul.style.backgroundColor = '#999999';
	ul.style.overflow = 'hidden';

	for (var r in a)
	{
		var li = document.createElement('li');
		var img2 = img.cloneNode(false);
		li._func = a[r].make;

		li.style.width = '18px';
		li.style.height = '18px';
		li.style.border = '1px solid #999999';
		li.style.backgroundImage = 'url(toolbar.gif)';
		li.style.backgroundPosition = a[r].left + 'px 0px';
		li.style.overflow = 'hidden';
		li.style.styleFloat = 'left';
		li.style.cssFloat = 'left';
		li.title = a[r].make;

		li.onmouseover = function () { this.style.border = '1px solid #ffffff'; }
		li.onmouseout = function ()  { this.style.border = '1px solid #999999'; this.style.backgroundColor = 'transparent'; }
		li.onmousedown = function () { this.style.backgroundColor = '#bbbbbb'; }
		li.onmouseup = function ()   { this.style.backgroundColor = 'transparent'; }
		li.onclick = function () { eval ('$this.make' + this._func + '()'); }

		li.appendChild (img2);

		ul.appendChild(li);
	}

	if (this._el.frameElement)
		this._el.frameElement.parentNode.insertBefore (ul, this._el.frameElement);
	else
		this._el.parentNode.insertBefore (ul, this._el);
}


BxEditor.prototype.makeBold = function ()
{
	this._el.focus();
	this._doc.execCommand('bold', false, null);
}

BxEditor.prototype.makeItalic = function ()
{
	this._el.focus();
	this._doc.execCommand('italic', false, null);
}

BxEditor.prototype.makeUnderline = function ()
{
	this._el.focus();
	this._doc.execCommand('underline', false, null);
}

BxEditor.prototype.makeBulletedList = function ()
{
	this._el.focus();
	this._doc.execCommand('InsertUnorderedList', false, null);
}

BxEditor.prototype.makeNumberedList = function ()
{
	this._el.focus();
	this._doc.execCommand('InsertOrderedList', false, null);
}

BxEditor.prototype.makeOutdent = function ()
{
	this._el.focus();
	this._doc.execCommand('outdent', false, null);
}

BxEditor.prototype.makeIndent = function ()
{
	this._el.focus();
	this._doc.execCommand('indent', false, null);
}


BxEditor.prototype.makeRemoveFormat = function ()
{
	this._el.focus();
//	this._clean_nodes(this._get_selected_tags(this._el.contentWindow, 'pre'), 'code')
	this._doc.execCommand('RemoveFormat', false, true);
}

BxEditor.prototype.makeCode = function ()
{
	var r = this._doc.execCommand('FormatBlock', false, 'blockquote');
	if (!r)
	{
		this._doc.execCommand('FormatBlock', false, 'Definition');
		this._format_pre_ie();
	}
	else
	{
		this._format_pre_moz ();
	}
	this._el.focus();
}


/*
Formatted = pre
Address = address
Heading 1 = h1
Heading 6 = h6
Numbered List = ol li
Bulleted List = ul li
Directory List = dir li
Menu List = menu li
Definition Term = dl dt
Definition = dl dd

*/

BxEditor.prototype.makeFont = function ()
{
	this._el.focus();
	this._doc.execCommand('FontName', false, 'Arial');
}

BxEditor.prototype.makeHeading = function (h)
{
	this._el.focus();
	if (!this._doc.execCommand('FormatBlock', false, 'h' + h))
		this._doc.execCommand('FormatBlock', false, 'Heading ' + h);
}

BxEditor.prototype.getText = function ()
{
	if (this._el.contentDocument)
	{
		return this._el.contentDocument.body.innerHTML;
	}
	else
	{
		return this._el.document.body.innerHTML;
	}
}

BxEditor.prototype.setText = function (s)
{
	if (this._el.contentDocument)
	{
		this._el.contentDocument.body.innerHTML = s;
	}
	else
	{
		if (this._el.document && this._el.document.body)
			this._el.document.body.innerHTML = s;
	}
}

// private functions -----------------------------------------------------------


BxEditor.prototype._get_selection_bounds = function (editor_window){

   var range, root, start, end

   if(editor_window.getSelection){ // Gecko, Opera
      var selection = editor_window.getSelection()

      range = selection.getRangeAt(0)
      
      start = range.startContainer
      end = range.endContainer
      root = range.commonAncestorContainer
      if(start == end) root = start

      if(start.nodeName.toLowerCase() == "body") return null

      if(start.nodeName == "#text") start = start.parentNode
      if(end.nodeName == "#text") end = end.parentNode
      
      return {
         root: root,
         start: start,
         end: end
      }

   }else if(editor_window.document.selection){ // MSIE
      range = editor_window.document.selection.createRange()
      if(!range.duplicate) return null
      
      var r1 = range.duplicate()
      var r2 = range.duplicate()
      r1.collapse(true)
      r2.moveToElementText(r1.parentElement())
      r2.setEndPoint("EndToStart", r1)
      start = r1.parentElement()
      
      r1 = range.duplicate()
      r2 = range.duplicate()
      r2.collapse(false)
      r1.moveToElementText(r2.parentElement())
      r1.setEndPoint("StartToEnd", r2)
      end = r2.parentElement()
      
      root = range.parentElement()
      if(start == end) root = start
      
      return {
         root: root,
         start: start,
         end: end
      }
   }
   return null // browser not supported
}


// bounds - array [root, start, end]
// tag_name - tag name
BxEditor.prototype._find_tags_in_subtree = function (bounds, tag_name, stage, second){

   var root = bounds['root']
   var start = bounds['start']
   var end = bounds['end']

   if(start == end) return [start]

   if(!second) this._global_stage=stage

   if(this._global_stage == 2) return []
   if(!this._global_stage) this._global_stage = 0

   tag_name = tag_name.toLowerCase()

   var nodes=[]
   for(var node = root.firstChild; node; node = node.nextSibling){
      if(node==start && this._global_stage==0){
         this._global_stage = 1
      }
      if(node.nodeName.toLowerCase() == tag_name && node.nodeName != '#text' || tag_name == ''){
         if(this._global_stage == 1){
            nodes.push(node)
         }
      }
      if(node==end && this._global_stage==1){
         this._global_stage = 2
      }
      nodes=nodes.concat(this._find_tags_in_subtree({root:node, start:start, end:end}, tag_name, this._global_stage, true))
   }
   return nodes
}


BxEditor.prototype._closest_parent_by_tag_name = function (node, tag_name) {

   tag_name = tag_name.toLowerCase()
   var p = node
   do{
      if(tag_name == '' || p.nodeName.toLowerCase() == tag_name) return p
   }while(p = p.parentNode)

   return node
}

BxEditor.prototype._get_selected_tags = function (editor_window, tag_name){

   if(tag_name){
      tag_name = tag_name.toLowerCase()
   }else{
      tag_name = ''
   }
   var bounds = this._get_selection_bounds(editor_window)
   if(!bounds) return null

   bounds['start'] = this._closest_parent_by_tag_name(bounds['start'], tag_name)
   bounds['end'] = this._closest_parent_by_tag_name(bounds['end'], tag_name)
   return this._find_tags_in_subtree(bounds, tag_name)
}

BxEditor.prototype._clean_nodes = function (nodes, class_name){	
   if(!nodes) return
   var l = nodes.length - 1
   var p;
   var html = '';
   for(var i = l ; i >= 0 ; i--){
//      if(!class_name || nodes[i].className == class_name){
         html += '<p>' + nodes[i].innerHTML + '</p>';
         p = nodes[i].parentNode;
         p.removeChild(nodes[i]);

//      }
//      else
//     {
//         html += nodes[i].innerHTML;
//     }
   }

   if (p) p.innerHTML = html;
}


BxEditor.prototype._format_pre_moz = function (){

	var iframe = this._el;
	var wysiwyg = this._doc;

	wysiwyg.execCommand('RemoveFormat', false, true)

	var nodes=this._get_selected_tags(iframe.contentWindow, 'blockquote')
	var new_node
	for(var i=0;i<nodes.length;i++)
	{
		new_node = wysiwyg.createElement('pre')
//		new_node.className = 'code';
		new_node.innerHTML = nodes[i].innerHTML
		nodes[i].parentNode.replaceChild(new_node, nodes[i])
	}
}


BxEditor.prototype._format_pre_ie = function (){

	var iframe = this._el;
	var wysiwyg = this._doc;

	wysiwyg.execCommand('RemoveFormat', false, true)

	this._clean_nodes(this._get_selected_tags(iframe, 'dd'))

	var nodes=this._get_selected_tags(iframe, 'dl')
	var new_node
	for(var i=0;i<nodes.length;i++)
	{
		new_node = wysiwyg.createElement('pre')
//		new_node.className = 'code';
		new_node.innerHTML = nodes[i].innerHTML
		nodes[i].parentNode.replaceChild(new_node, nodes[i])
	}
}

BxEditor.prototype._format_inline = function (tag_name, class_name){

   this._magic_unusual_color='#00f001';

   var iframe = this._el;//document.getElementById(iframe_id)
   var wysiwyg = this._doc;//iframe.contentWindow.document

   wysiwyg.execCommand('RemoveFormat', false, true)

   this._clean_nodes(this._get_selected_tags(iframe.contentWindow, 'span'))

   if(tag_name!=''){
      
		wysiwyg.execCommand('ForeColor', false, this._magic_unusual_color)

      var nodes=this._get_selected_tags(iframe.contentWindow, 'font')
      var new_node
      for(var i=0;i<nodes.length;i++){
         if(nodes[i].getAttribute('color') != this._magic_unusual_color) continue
         new_node = wysiwyg.createElement(tag_name)
//         if(class_name) new_node.className = class_name
         new_node.innerHTML = nodes[i].innerHTML
         nodes[i].parentNode.replaceChild(new_node, nodes[i])
      }
   }
   iframe.focus()
}

BxEditor.prototype._wysiwyg_format_block = function (class_name){

   var tag_name = 'h1';
   var iframe = this._el;//document.getElementById(iframe_id)
   var wysiwyg = this._doc;//iframe.contentWindow.document

//   wysiwyg.execCommand('formatblock', false, tag_name)
   if (!this._doc.execCommand('FormatBlock', false, tag_name))
      this._doc.execCommand('FormatBlock', false, 'Heading 1');

   // asign class for tag
   var nodes = this._get_selected_tags(iframe.contentWindow, tag_name)
   for(var i = 0; i < nodes.length; i++){
      if(class_name)
      {
         nodes[i].className = class_name
      }
      else
      {
         nodes[i].removeAttribute('class')
         nodes[i].removeAttribute('className')
      }
   }
   iframe.focus()
}
