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
 * admin functionality
 */


/**
 * constructor
 */
function Admin (base, forum)
{	
	this._base = base;
	this._forum = forum;
}   



/**
 * edit categories admin page
 */
Admin.prototype.editCategories = function ()
{
	this._forum.loading ('[L[LOADING]]');

	var $this = this;

	var h = function (r)
	{		
		var m = document.getElementById('orca_main');		

		m.innerHTML = r;

		$this._forum.stopLoading ();
	}

	new BxXslTransform(this._base + "?action=edit_categories", urlXsl + "edit_categories.xsl", h);

	document.h.makeHist('action=goto&edit_cats=1');

	return false;
}

/**
 * reported posts
 */
Admin.prototype.reportedPosts = function ()
{
	this._forum.loading ('[L[LOADING]]');

	var $this = this;

	var h = function (r)
	{		
		var m = document.getElementById('orca_main');		

		m.innerHTML = r;

		$this._forum.stopLoading ();
	}

	new BxXslTransform(this._base + "?action=reported_posts", urlXsl + "forum_posts.xsl", h);

	return false;
}

/**
 * hidden posts
 */
Admin.prototype.hiddenPosts = function ()
{
	this._forum.loading ('[L[LOADING]]');

	var $this = this;

	var h = function (r)
	{		
		var m = document.getElementById('orca_main');		

		m.innerHTML = r;

		$this._forum.stopLoading ();
	}

	new BxXslTransform(this._base + "?action=hidden_posts", urlXsl + "forum_posts.xsl", h);

	return false;
}

/**
 * hidden topics
 */
Admin.prototype.hiddenTopics = function (start)
{
	this._forum.loading ('[L[LOADING]]');

	var $this = this;

	var h = function (r)
	{		
		var m = document.getElementById('orca_main');		

		m.innerHTML = r;

		$this._forum.stopLoading ();
	}

	new BxXslTransform(this._base + "?action=show_hidden_topics&start=" + start, urlXsl + "hidden_topics.xsl", h);

	return false;
}

/**
 * delete category
 *	@param id	category id
 */
Admin.prototype.delCat = function (cat_id)
{
	if (!confirm ('[L[Are you sure to delete category with all forums, topics and post?]]')) return false;

	var $this = this;

	var h = function (r)
	{						
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
		{
			alert ('[L[Category has been successfully deleted]]');
			$this.editCategories();
			return;
		}

		alert ('[L[Can not delete category]]');
	}	

	jQuery.ajax ({
		url: this._base + "?action=edit_category_del&cat_id="+cat_id,		
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return true;
}

/**
 * delete forum
 *	@param forum_id	forum id
 */
Admin.prototype.delForum = function (forum_id)
{
	if (!confirm ('[L[Are you sure to delete forum with topics and posts]]')) return false;

	var $this = this;

	var h = function (r)
	{				
        var cat_uri = orca_get_xml_val ('cat_uri', r);
        var cat_id = orca_get_xml_val ('cat_id', r);
		if (cat_id > 0)
		{
	    	alert ('[L[Forum has been successfully deleted]]');			
    		$this.selectCat(cat_uri, 'cat'+cat_id, true, true);            
			return;
		}
        alert ('[L[Can not delete forum]]');
	}

	jQuery.ajax ({
		url: this._base + "?action=edit_forum_del&forum_id="+forum_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return true;
}

/**
 * edit category
 *	@param id	category id
 */
Admin.prototype.editCat = function (cat_id)
{	
	var $this = this;

	var h = function (r)
	{			
		$this._forum.showHTML (r, 400, 200);
	}

	new BxXslTransform(this._base + "?action=edit_category&cat_id="+cat_id, urlXsl + "edit_cat_form.xsl", h);

	return true;
}

/**
 * new group
 */
Admin.prototype.newCat = function ()
{	
	var $this = this;

	var h = function (r)
	{			
		$this._forum.showHTML (r, 400, 200);
	}

	new BxXslTransform(this._base + "?action=edit_category&cat_id="+0, urlXsl + "edit_cat_form.xsl", h);

	return true;
}

/**
 * edit category
 *	@param cat_name	new group name
 *	@param cat_id	category id 
 */
Admin.prototype.editCatSubmit = function (cat_id, cat_name, cat_order, cat_expanded)
{
	var $this = this;

	var h = function (r)
	{								
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
		{
			if (cat_id > 0)
				alert ('[L[Group has been successfully modified]]');
			else
				alert ('[L[New group has been successfully added]]');
			$this._forum.hideHTML();
			$this.editCategories();
			return false;
		}

		if (cat_id > 0)
			alert ('[L[Can not modify group]]');
		else
			alert ('[L[Can not add new group]]');
		return false;
	}

    cat_name = encodeURIComponent (cat_name); 

	jQuery.ajax ({
		url: this._base + "?action=edit_category_submit&cat_id="+cat_id+"&cat_name="+cat_name+"&cat_order="+cat_order+"&cat_expanded="+(cat_expanded ? 1 :0),
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;
}


/**
 * edit forum
 *	@param id	category id
 */
Admin.prototype.editForum = function (forum_id)
{	
	var $this = this;

	var h = function (r)
	{			
		$this._forum.showHTML (r, 400, 200);
	}

	new BxXslTransform(this._base + "?action=edit_forum&forum_id="+forum_id, urlXsl + "edit_forum_form.xsl", h);

	return true;
}


/**
 * new category
 */
Admin.prototype.newForum = function (cat_id)
{	
	var $this = this;

	var h = function (r)
	{			
		$this._forum.showHTML (r, 400, 200);
	}
	
	new BxXslTransform (this._base + "?action=edit_forum&forum_id=0&cat_id="+cat_id, urlXsl + "edit_forum_form.xsl", h);

	return true;
}


/**
 * edit forum
 *	@param forum_id	forum id
 *	@param title 	forum title
 *	@param desc 	forum description
 *	@param type 	forum type
 */
Admin.prototype.editForumSubmit = function (cat_id, cat_uri, forum_id, title, desc, type, order)
{
	var $this = this;

	var h = function (r)
	{		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
		{
			if (forum_id > 0)
				alert ('[L[Forum has been successfully modified]]');
			else
				alert ('[L[New forum has been successfully added]]');
			$this._forum.hideHTML();
			$this.selectCat (cat_uri, 'cat'+cat_id, true, true);			
			return false;
		}

		if (forum_id > 0)
			alert ('[L[Can not modify forum]]');
		else
			alert ('[L[Can not add new forum]]');
		return false;
	}

    title = encodeURIComponent(title); 
    desc = encodeURIComponent(desc); 

	jQuery.ajax ({
		url: this._base + "?action=edit_forum_submit&cat_id="+cat_id+"&forum_id="+forum_id+"&title="+title+"&desc="+desc+"&type="+type+"&order="+order,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;
}


/**
 * returns new topic page XML
 */
Admin.prototype.selectCat = function (cat, id, force_show, force_reload)
{	
	var e = $('#'+id);

	if (!e.size()) {
		new BxError("[L[category id is not defined]]", "[L[please set category ids]]");
		return false;
	}

    // hide category forums
    if (e.next("[cat="+cat+"]").size()) {
        e.nextAll("[cat="+cat+"]").fadeOut(this._forum._speed, 
                function () { 
                    $(this).remove(); 
                } 
        );
        e.find('div.colexp').html('<i class="sys-icon folder"></i>');

        if (!force_show)
            return false;
    }

	this._forum.loading ('[L[LOADING FORUMS]]');

	var $this = this;
	this._cat = cat;
	var h = function (r)
	{	
        e.after($('<table>'+r+'</table>').find('tr').hide());
        if (document.all)
            e.nextAll("[cat="+cat+"]").css('display', 'block');
        else
			e.nextAll("[cat="+cat+"]").fadeIn($this._forum._speed);
        e.find('div.colexp').html('<i class="sys-icon folder-open"></i>');
		$this._forum.stopLoading ();
	}
	new BxXslTransform(this._base + "?action=list_forums_admin&cat=" + cat, urlXsl + "edit_cat_forums.xsl", h);

	return false;
}

/*
 * compile language files
 */
Admin.prototype.compileLangs = function (sLang)
{

	var h = function (r)
	{						
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
		{                   
			alert ('[L[Language files have been successfully compiled]]');
		}
		else
		{         
			alert ('[L[Language files compilation have been failed]]');
		}
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=compile_langs&lang=" + sLang + "&ts=" + (new Date()),
		dataType: 'text',
		type: 'POST',
		success: h
	});

}


Admin.prototype.clearReport = function (id)
{
	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
        	var m = $('#post_row_'+id);
        	if (!m) 
		        return false;
            m.fadeOut(this._speed, 
                function () { 
                    $(this).remove(); 
                } 
            );
			return false;
		}
		alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=clear_report&post_id=" + id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;	
}
