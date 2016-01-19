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
 * forum functionality
 */


/**
 * constructor
 */
function Forum (base, min_points) {	    
	this._base = base;
	this._forum = 0;
	this._topic = 0;
	this._min_points = min_points;
    this._speed = 400;
    this._idEditPostTimer = 0;
}   

/**
 * edit post
 * @param id	post id 
 */
Forum.prototype.editPost = function (id) {

	var node = $('#post_row_'+id+' > td > .forum_post_text');
	if (!node.size()) {
		this.showHiddenPost(id, '$this.editPost (id);');
		return;
	}

    if ($('#post_row_'+id+' > td > .forum_post_text:hidden').size() && $('#post_row_'+id+' > td > form[name=edit_post_'+id+']').size())
        return;

	var $this = this;
	var h = function (r) {		

        var postText = $this.getPostText(id);

        node.hide();
		node.after(r);

        orca_add_editor('tinyEditor_'+id, postText);
	}	
	new BxXslTransform(this._base + "?action=edit_post_xml&post_id=" + id + "&topic_id=" + this._topic, urlXsl + "edit_post.xsl", h);

	return false;

}

/**
 * cancel post editing
 * @param id	post id 
 */
Forum.prototype.editPostCancel = function (id) {

    var ff = $('#'+id+'>form');
	if (!ff.size()) return false;    

    orca_remove_editor('tinyEditor_'+id);

	ff.remove();
    $('#'+id+'>.forum_post_text').show();
}

/**
 * expand/collapse rearch result row
 * @param id	post id 
 */
Forum.prototype.expandPost = function (id) {	

    if ($('#' + id + ':hidden').size()) {
        $('#' + id).show(this._speed);
        $('#' + id).parent().find('div.colexp2').html('<i class="sys-icon minus-circle"></i>');
    } else {
        $('#' + id).hide(this._speed);
        $('#' + id).parent().find('div.colexp2').html('<i class="sys-icon plus-circle"></i>');
    }
}

/**
 * search the forum
 */
Forum.prototype.search = function (text, type, forum, u, disp, start) {	

	this.loading ('[L[SEARCHING]]');

	var m = document.getElementById('orca_main');
	if (!m) 
	{
		new BxError("[L[orca_main div is not defined]]", "[L[please name orca_main content container]]");
	}

    if (undefined == start)
        start = 0;

	var $this = this;

    u = encodeURIComponent(u);
    if (-1 == text.search('%'))
        text = encodeURIComponent(text);

	var h = function (r)
	{		
		var m = document.getElementById('orca_main');		

		m.innerHTML = r;

		$this.runScripts ('orca_main');

        $this.setWindowTitle(null); 
		
		$this.stopLoading ();
	}

	new BxXslTransform(this._base + "?action=search&text=" + text + "&type=" + type + "&forum=" + forum + "&u=" + u + "&disp=" + disp + "&start=" + start, urlXsl + "search.xsl", h);

	document.h.makeHist('action=goto&search_result=1&text=' + text + '&type=' + type + '&forum=' + forum + '&u=' + u + '&disp=' + disp + '&start=' + start);

	return false;
}
	
/**
 * returns new topic page XML
 */
Forum.prototype.selectCat = function (cat, id) {		

	var e = $('#'+id);

	if (!e.size()) {
		new BxError("[L[category id is not defined]]", "[L[please set category ids]]");
		return false;
	}

    // hide category forums
    if (e.next("[cat="+cat+"]").size()) {
        e.nextAll("[cat="+cat+"]").fadeOut(this._speed, 
                function () { 
                    $(this).remove(); 
                } 
        );
        e.find('div.colexp').html('<i class="sys-icon folder"></i>');
        return false;
    }

	this.loading ('[L[LOADING FORUMS]]');

	var $this = this;	
	var h = function (r) {		        
        e.after($('<table>'+r+'</table>').find('tr').hide());
        if (document.all)
            e.nextAll("[cat="+cat+"]").css('display', 'block');
        else
            e.nextAll("[cat="+cat+"]").fadeIn($this._speed);
        e.find('div.colexp').html('<i class="sys-icon folder-open"></i>');
        $this.setWindowTitle(null);
		$this.stopLoading ();
	}
	new BxXslTransform(this._base + "?action=list_forums&cat=" + encodeURIComponent(cat), urlXsl + "cat_forums.xsl", h);

    //var ha = document.h.rw('cat');
	//document.h.makeHist (ha['pre'] + cat + ha['ext']);

	return false;
}

/**
 * select forum
 *	@param id	forum id
 */
Forum.prototype.selectForum = function (id, start) {

    this._forum = id;
    var ha = document.h.rw('forum');
    return this._replacePage (
            '[L[LOADING FORUM TOPICS]]', 
            null, 
            "?action=list_topics&forum=" + encodeURIComponent(this._forum) + "&start=" + start, 
            "forum_topics.xsl", 
            ha['pre'] + this._forum + ha['page'] + start + ha['ext']);
}

/**
 * select recent topics
 *	@param id	forum id
 */
Forum.prototype.selectRecentTopics = function (start) {

    var ha = document.h.rw('recent_topics');
    return this._replacePage (
            '[L[LOADING]]', 
            null, 
            "?action=recent_topics&start=" + start, 
            "recent_topics.xsl", 
            "action=goto&recent_topics=1&start=" + start);
}

/**
 * select forum
 *	@param id	forum id
 */
Forum.prototype.selectForumIndex = function (cat) {

    var ha = document.h.rw('cat');
    return this._replacePage (
            '[L[LOADING FORUM INDEX]]', 
            null, 
            "?action=forum_index" + (cat ? ("&cat=" + cat) : ''), 
            "home.xsl", 
            ha['pre'] + cat + ha['ext'],
            function () { 
                var e = $('#cat'+ cat).get(0); 
                if (e) e.blur(); 
            } 
    );
}

/**
 * show profile page
 *	@param user	usrname to show 
 */
Forum.prototype.showProfile = function (user) {

    var ha = document.h.rw('user');
    return this._replacePage (
            '[L[LOADING PROFILE PAGE]]', 
            null, 
            "?action=profile&user=" + user, 
            "profile.xsl", 
            ha['pre'] + user + ha['ext']
    );
}

/**
 * select topic
 *	@param id	topic id
 */
Forum.prototype.selectTopic = function (id) {

    var $this = this;
    this._topic = id;
    var ha = document.h.rw('topic');
    return this._replacePage (
            '[L[LOADING TOPIC POSTS]]', 
            null, 
            "?action=list_posts&topic=" + encodeURIComponent(this._topic), 
            "forum_posts.xsl", 
            ha['pre'] + this._topic + ha['ext'],
            function () {                 
                $this.runScripts ('orca_main');
            } 
    );
}

/**
 * open new 'post new topic' page
 *	@param id	forum id
 */
Forum.prototype.newTopic = function (id) { 

	if ($('#tinyEditor').size()) 
        orca_remove_editor('tinyEditor');

    if (tinyMCE.activeEditor) 
        tinyMCE.activeEditor.remove();

    var $this = this;
    this._forum = id;
    return this._replacePage (
            '[L[LOADING POST TOPIC PAGE]]',
            null, 
            "?action=new_topic&forum=" + encodeURIComponent(this._forum), 
            "new_topic.xsl", 
            'action=goto&new_topic=' + this._forum,
            function (r) {
                if (document.getElementById('tinyEditor')) {
                    if (0 < $('#tinyEditor').val().length)
                        $('#tinyEditor').val('');
                    orca_add_editor('tinyEditor');
                }
            } 
    );
}

/**
 * cancel new topic submission
 */
Forum.prototype.cancelNewTopic = function (forum, start) {

	if (document.getElementById('tinyEditor'))
        orca_remove_editor('tinyEditor');

    if (forum != '0' && forum.length) {
	    return this.selectForum (forum, start);
    } else {
        document.location = this._base;
        return false;
    }
}

/**
 * my threads page
 */
Forum.prototype.showMyThreads = function (start) {

    if (!isLoggedIn) {
        alert('[L[Please login to view topics you participate in]]');
        return;
    }

    return this._replacePage (
            '[L[LOADING]]',
            null, 
            "?action=show_my_threads&start=" + start, 
            "my_topics.xsl", 
            'action=goto&my_threads=1&start=' + start
    );
}


/**
 * my flags page
 */
Forum.prototype.showMyFlags = function (start) {

    if (!isLoggedIn) {
        alert('[L[Please login to view flagged topics]]');
        return;
    }

    return this._replacePage (
            '[L[LOADING]]',
            null, 
            "?action=show_my_flags&start=" + start, 
            "flagged_topics.xsl", 
            'action=goto&my_flags=1&start=' + start
    );
}

/**
 * open new 'search' page
 */
Forum.prototype.showSearch = function ()
{
    return this._replacePage (
            '[L[LOADING SEARCH PAGE]]',
            null, 
            "?action=show_search", 
            "search_form.xsl",
            'action=goto&search=1'
    );
}

/**
 * open new 'post reply' page
 *	@param id_f	forum id
 *	@param id_t	topic id
 */
Forum.prototype.postReply = function (id_f, id_t) {

	this.loading ('[L[LOADING POST REPLY PAGE]]');

	var m = $('#reply_container');
	if (!m.size()) 
		new BxError("[L[orca_main div is not defined]]", "[L[please name orca_main content container]]");

	if ($('#tinyEditor').size())
        orca_remove_editor('tinyEditor');

    if ('object' == typeof(tinyMCE) && tinyMCE.activeEditor)
        tinyMCE.activeEditor.remove();

	this._forum = id_f;
	this._topic = id_t;

	var $this = this;
	var h = function (r) {		

		m.hide().html(r).slideDown($this._speed);

        if ($('#tinyEditor').length)
            $('#tinyEditor').get(0).focus();

        if (document.getElementById('tinyEditor'))
        {
            if (0 < document.getElementById('tinyEditor').value.length)
                document.getElementById('tinyEditor').value = '';
            orca_add_editor('tinyEditor');
        }
		$this.stopLoading ();
	}
	new BxXslTransform(this._base + "?action=reply&forum=" + this._forum + "&topic=" + this._topic, urlXsl + "post_reply.xsl", h);

	return false;
}



/**
 * open new 'post reply' page
 *	@param id_f	forum id
 *	@param id_t	topic id
 */
Forum.prototype.postReplyWithQuote = function (id_f, id_t, p_id)
{
	this.loading ('[L[LOADING POST REPLY PAGE]]');

	var m = $('#reply_container');
	if (!m.size()) 
		new BxError("[L[orca_main div is not defined]]", "[L[please name orca_main content container]]");

	if ($('#tinyEditor'))
        orca_remove_editor('tinyEditor');

    if (tinyMCE.activeEditor) 
        tinyMCE.activeEditor.remove();

	this._forum = id_f;
	this._topic = id_t;

	var $this = this;
	var h = function (r) {		

		m.html(r).slideDown($this._speed);
    
        if ($('#tinyEditor').length)
            $('#tinyEditor').get(0).focus();

		var post = $this.getPostText(p_id, true);

		post = post.replace (/<text>/ig, '')
		post = post.replace (/<\/text>/ig, '')
		post =  '<p>&#160;</p><div class="quote_post">' + post + '</div> <p>&#160;</p>';

        orca_add_editor('tinyEditor', post);

		$this.stopLoading ();
	}
	new BxXslTransform(this._base + "?action=reply&forum=" + this._forum + "&topic=" + this._topic, urlXsl + "post_reply.xsl", h);

	return false;
}

/**
 * cancel reply
 */
Forum.prototype.cancelReply = function () {

	if ($('#tinyEditor').size())
        orca_remove_editor('tinyEditor');

	var m = $('#reply_container');
	if (!m.size()) 
        return;
	m.slideUp(this._speed).html('&#160;');
}

/**
 * show access denied page
 */
Forum.prototype.accessDenied = function ()
{
    return this._replacePage (
            '[L[LOADING]]',
            null, 
            "?action=access_denied", 
            "default_access_denied.xsl"
    );
}


/**
 * show reply success page
 *	@param f_id	forum id
 *	@param t_id	topic id
 */
Forum.prototype.replySuccess = function (f_id, t_id)
{
    orca_remove_editor('tinyEditor');
    
	return this.selectTopic(t_id);
}


/**
 * delete post
 *	@param p	post id
 *	@param f	forum id
 *	@param t	topic id
 *	@param ask	confirm deletetion
 */
Forum.prototype.deletePost = function (p, f, t, ask) {

	if (ask) 
        if (!confirm('[L[Are you sure ?]]')) 
            return false;

	var form = document.getElementById('tmp_del_form');

	if (!form) 	{
		form = document.createElement('form');
		form.style.display = 'none';
		form.id = 'tmp_del_form';
		form.method = 'post';
		form.target = 'post_actions';
		document.body.appendChild(form);
	}

	if (!form) return;

	form.action = this._base + '?action=delete_post&post_id=' + p + '&forum_id=' + f + '&topic_id=' + t;
	form.submit();

	return false;
}

/**
 * show delete success page
 *	@param forum_id	forum id
 */
Forum.prototype.deleteSuccess = function (f_id, t_id, t_exists) {

	if (f_id) {
		if (t_exists)
			this.selectTopic (t_id);
		else
			this.selectForum (f_id, 0);
	} else if (0 == f_id && 0 == t_id) {
		orca_admin.reportedPosts();
	}

	return false;
}


/**
 * delete topic
 *	@param t_id   topic id
 *	@param f_uri  forum id
 *	@param ask    ask confirmation
 */
Forum.prototype.delTopic = function (t_id, f_uri, ask) {

	if (ask)
        if (!confirm('[L[Are you sure ?]]'))
            return false;

	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
        	if (f_uri) {
        		$this.selectForum (f_uri, 0);
        	} else {
        		orca_admin.hiddenTopics();
        	}
			return false;
		}
		alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=del_topic&topic_id=" + t_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;	
}

/**
 * show edit success page
 *	@param forum_id	forum id
 */
Forum.prototype.editSuccess = function (t) {

	this.selectTopic(t);
	return false;
}

/**
 * check string value
 */
Forum.prototype.checkSubject = function (s)
{
	if (s.length < 5 || s.length > 50)
		return false;
	return true;
}

/**
 * check string value
 */
Forum.prototype.checkText = function (s) {
	return ((s.length > 4 && s.length < 128000) ? true : false);
}

/**
 * check string value
 */
Forum.prototype.checkSignature = function (s) {
	return (s.length <= 100 ? true : false);
}

/**
 * check form values
 */
Forum.prototype.checkPostTopicValues = function (s, t, g, f, n) {	

	var ret1 = false;
	var ret2 = false;
    var ret3 = false;
    var ret4 = false;
	var e;

	if (true == n) {
		e = document.getElementById('err_' + s.name);	
		if (!this.checkSubject(s.value)) {		
			if (e) e.style.display = "block";
			s.style.backgroundColor = "#ffaaaa";
			s.focus();
		} else {
			if (e) e.style.display = "none";
			s.style.backgroundColor = "#ffffff";
			ret1 = true;
		}

		e = document.getElementById('err_' + f.name);
		if (!parseInt(f.value)) {
			if (e) e.style.display = "block";
			f.style.backgroundColor = "#ffaaaa";
		} else {
			if (e) e.style.display = "none";
			f.style.backgroundColor = "#ffffff";
			ret4 = true;
		}
	}

	e = document.getElementById('err_' + t.name);	
	if (!this.checkText(t.value)) {
		if (e) e.style.display = "block";
		t.style.backgroundColor = "#ffaaaa";
	} else {
		if (e) e.style.display = "none";
		t.style.backgroundColor = "#ffffff";
		ret2 = true;
	}

	e = document.getElementById('err_' + g.name);	
	if (!this.checkSignature(g.value)) {
		if (e) e.style.display = "block";
		g.style.backgroundColor = "#ffaaaa";
        jQuery('#forum_signature').show(f._speed);
	} else {
		if (e) e.style.display = "none";
		g.style.backgroundColor = "#ffffff";
		ret3 = true;
	}

	return (n ? (ret1 && ret2 && ret3 && ret4) : (ret2 && ret3));
}

/**
 * create and display loading message
 */
Forum.prototype.stopLoading = function () {
	var l = $("#loading");
	if (l.size())
		l.fadeOut(this._speed);
}

/**
 * create and display loading message
 */
Forum.prototype.loading = function (sid) {

	var d = document.getElementById ("loading");
	var e = document.body; 

	if (d) 	{        

		d.style.display = "block";

	} else {

		var d = document.createElement("div");
        var t = document.createTextNode("[L[LOADING]]");

        d.appendChild (t);
        e.appendChild (d);

        d.id = "loading";
        d.style.position = "fixed";
        d.style.zIndex = "50000";
        d.style.textAlign = "center";
        d.style.width = "200px";
        d.style.height = "20px";
        d.style.lineHeight = "20px";                                                             
        d.style.left = (parseInt(e.clientWidth/2) - 100) + "px";
        d.style.top = "0px";
        d.style.display = "block";
        d.style.backgroundColor = "#FFF1A8";
        d.style.fontWeight = "bold";

	}
}



/**
 * create and display loading message
 */
Forum.prototype.hideHTML = function (w, h, html) {
	var l = $("#show_html");
	if (l.size()) {
		$("#show_html>div").hide(this._speed, function () { $("#show_html").remove(); } );
	}
}

/**
 * create and display loading message
 */
Forum.prototype.showHTML = function (html, w, h)
{
	var d = document.getElementById ("show_html");
	var e = document.body; 
    var div;

	if (d) {
		div = d.firstChild;
        div.style.display = "none";
		div.innerHTML = html;
		d.style.top = getScroll() - 30 + "px";
		d.style.left = 0 + "px";
		d.style.display = "block";
		if (w) div.style.width = w + 'px';
		if (h) div.style.height = h + 'px';
		div.style.top = parseInt(d.style.height)/2 - h/2 + 'px';
		div.style.width = parseInt(d.style.width)/2 - w/2 + 'px';
	} else {
		var d = document.createElement("div");
		div = document.createElement("div");

		e.appendChild (d);

		d.id = "show_html";
		d.style.position = "absolute";
		d.style.zIndex = "49000";
		d.style.textAlign = "center";
		d.style.width = e.clientWidth + "px";
		d.style.height = (window.innerHeight ? (window.innerHeight + 30) : screen.height) + "px";			
		d.style.top = getScroll() - 30 + "px";
		d.style.left = 0 + "px";
		d.style.display = "block";

		div.innerHTML = html;
        div.style.display = "none";
		div.style.position = "absolute";
		if (w) div.style.width = w + 'px';
		if (h) div.style.height = h + 'px';
		div.style.top = parseInt(d.style.height)/2 - h/2 + 'px';
		div.style.left = parseInt(d.style.width)/2 - w/2 + 'px';

		d.appendChild(div);
	}
    $(div).show(this._speed);
}


Forum.prototype.hideHiddenPost = function (id)
{
	this.loading ('[L[POST IS LOADING]]');

	var m = $('#post_row_'+id);
	if (!m) 
		return false;
        
	var $this = this;
	var h = function (r) {		
        var html = $('<table>'+r+'</table>').find('#post_row_'+id).html();
		m.html(html);
		$this.stopLoading ();
	}
	new BxXslTransform(this._base + "?action=hide_hidden_post&post_id=" + id, urlXsl + "forum_posts.xsl", h);

	return false;		
}

Forum.prototype.showHiddenPost = function (id, run)
{
	this.loading ('[L[POST IS LOADING]]');

	var m = $('#post_row_'+id);
	if (!m) 
		return false;

	var $this = this;
	var h = function (r) {		   

        var html = $('<table>'+r+'</table>').find('#post_row_'+id).html();
        html = $this.replaceTildaA (html);
		m.html(html);

        $this.runScripts ('post_row_'+id);
		$this.stopLoading ();
		if (run) 
            eval (run);
	}
	new BxXslTransform(this._base + "?action=show_hidden_post&post_id=" + id, urlXsl + "forum_posts.xsl", h);

	return false;	
}

Forum.prototype.hidePost = function (isHide, id)
{
	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
            if (isHide)
                $this.hideHiddenPost(id);
            else
                $this.showHiddenPost(id);
			return false;
		}
		alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=hide_post&is_hide=" + (isHide ? 1 : 0) + "&post_id=" + id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;	
}

Forum.prototype.hideTopic = function (isHide, id)
{
	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
            if (isHide)
                alert ('[L[Topic has been successfully hidden]]');
            else
                alert ('[L[Topic has been successfully un-hidden]]');
			return false;
		}
		alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=hide_topic&is_hide=" + (isHide ? 1 : 0) + "&topic_id=" + id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;	
}

Forum.prototype.stick = function (id)
{
	var h = function (r) {
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
            alert ('[L[Topic has been successfully sticked]]');
		else if ('-1' == ret)
            alert ('[L[Topic has been successfully un-sticked]]');	
        else
		    alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=stick&topic_id=" + id,
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;	
}


/*
 * lock/unlock
 */
Forum.prototype.lock = function (topic_id, locked)
{				
	var h = function (r) {
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) 
			alert ('[L[Topic has been successfully locked]]');
		else if ('-1' == ret)
			alert ('[L[Topic has been successfully unlocked]]');
        else
		    alert ('Only admin can lock/unlock topics');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=lock_topic&topic_id=" + topic_id + "&ts=" + (new Date()),
		dataType: 'text',
		type: 'POST',
		success: h
	});

	return false;
}

/*
 * good vote post 
 */
Forum.prototype.voteGood = function (post_id) {				

	var $this = this;
	var h = function (r) {				
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
			var e = $('#points_'+post_id);
            var s = e.html();
            var m = s.match(/([0-9\-]+)/);
            if (m) {
    			e.html(s.replace(/[0-9\-]+/, parseInt(m) + 1));
            }
			$this.hideVoteButtons (post_id);
			$this.hideReportButton  (post_id);
			return false;
		}
		alert ('Vote error');
		return false;
	}	
	jQuery.ajax ({
		url: this._base + "?action=vote_post_good&post_id="+post_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});
	return false;		
}

/*
 * flag/unflag 
 */
Forum.prototype.flag = function (topic_id) {				
	var $this = this;
	var h = function (r) {				
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
			alert ('[L[Topic has been successfully added to your flagged topics]]');
        else if ('-1' == ret)
			alert ('[L[Topic has been successfully removed from your flagged topics]]');
        else
    		alert ('[L[Please login to flag topics]]');	
	}
	
	jQuery.ajax ({
		url: this._base + "?action=flag_topic&topic_id="+topic_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});
	return false;
}

/*
 * report post 
 */
Forum.prototype.report = function (post_id, ask) {
	if ('undefined' !== typeof(ask) && ask) 
        if (!confirm('[L[Are you sure ?]]')) 
            return false;

	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
		if ('1' == ret)
			alert ('[L[Post has been reported]]');
        else
    		alert ('[L[Report error]]');	
	}
	//new BxXmlRequest (this._base + "?action=report_post&post_id="+post_id, h, true);
	jQuery.ajax ({
		url: this._base + "?action=report_post&post_id="+post_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});
	return true;
}

/*
 * place -1 vote for post
 */
Forum.prototype.voteBad = function (post_id, ask) {
	if ('undefined' !== typeof(ask) && ask) 
        if (!confirm('[L[Are you sure ?]]')) 
            return false;

	var $this = this;
	var h = function (r) {				
        var ret = orca_get_xml_ret(r);
		if ('1' == ret) {
			var e = $('#points_'+post_id);
			e.html(parseInt(e.html()) - 1);
			$this.hideHiddenPost (post_id);
		} else {
    		alert ('Vote error');
        }
	}
	jQuery.ajax ({
		url: this._base + "?action=vote_post_bad&post_id="+post_id,
		dataType: 'text',
		type: 'POST',
		success: h
	});
	return true;
}

/*
 * make vote buttons inactive
 */
Forum.prototype.hideVoteButtons = function (post_id) {
	$('#rate_' + post_id + ' .sys-icon').each (function () {
        $(this).addClass('bx-def-font-grayed');
        $(this).parent()[0].onclick = function () {};
    });
}

/*
 * make report button inactive
 */
Forum.prototype.hideReportButton = function (post_id) {
}

Forum.prototype.getPostText = function (post_id, selected) {
    if ('undefined' != typeof(selected) && selected) {
        var sSelected = this.getSelectedText();
        if (sSelected.length) {
            var sPostText = $('#post_row_'+post_id+' > td > .forum_post_text').text();
            aSelected = sSelected.split("\n");
            if (-1 != sPostText.indexOf(aSelected[0]))
                return aSelected.join('<br />');
        }
    }
    return $('#post_row_'+post_id+' > td > .forum_post_text').html();
}

function getScroll() {
	if (navigator.appName == "Microsoft Internet Explorer")
		return document.documentElement.scrollTop
	return window.pageYOffset;
}

Forum.prototype.livePost = function (ts)
{
	var to = 3000;  // timeout
	var $this = this;
	var lt = document.getElementById('live_tracker');
	var h = function (r) {		

		var o = new BxXmlRequest('','','');			
		var ret = o.getRetNodeValue (r, 'ret');
		if (ret > 0)
		{			
			// get new post and insert it 
			var hh = function (r) {	

				if (!lt) return;

                if (r.length > 0) {
    				// delete oldest

                    $(lt).find('.live_post:last-child').remove();

		    		// insert new                
			    	lt.innerHTML = r + lt.innerHTML;
                
                    $(lt).find('.live_post:first-child').hide().slideDown('slow');
                }

				// watch latest post
				setTimeout('f.livePost('+ret+')', to);
			}

			new BxXslTransform ($this._base + "?action=get_new_post&ts=" + ts +"&now=" + (new Date()), urlXsl + "live_tracker_main.xsl",hh);
		
			return false;
		}	

		// watch latest post	
		setTimeout('f.livePost('+ts+')', to);
		return false;
	}

	
	if (lt)
		new BxXmlRequest (this._base + "?action=is_new_post&ts=" + ts +"&now=" + (new Date()), h, true);	

	return false;		
}

Forum.prototype.setWindowTitle = function (s) {

    if (!s || !s.length)
        s = $("#orca_main .disignBoxFirst:first .boxContent .forum_topic_caption").text();

    if (!s || !s.length)
        window.document.title = defTitle;
    else
        window.document.title = s + '[L[add this to title]]';
}

Forum.prototype.runScripts = function (id) {
    var ee = document.getElementById(id);
    var a = ee.getElementsByTagName('script');
    if (!a.length) return;
    var ajs = new Array(a.length);
    
    for (var i=0 ; i<a.length ; ++i) {        
        if (!a[i]) continue;
        ajs[i] = a[i].innerHTML;
    }

    for (var i=0 ; i<ajs.length ; ++i) { 
        eval (ajs[i]);
    }
}

Forum.prototype.replaceTildaA = function (s) { 
    return s.replace (/\xC2/gm, '');    
}

Forum.prototype._replacePage = function (sLoading, sTitle, sUrlData, sXslFile, sMakeHist, f) {

	this.loading (sLoading);
	var $this = this;
	var h = function (r) {		        

        $('#orca_main').html(r).show('slow', function () {
            if (f) f(r);
            $this.setWindowTitle(sTitle);

            var iTop = parseInt($("#orca_main").offset().top);
            $($.browser.safari ? "body" : "html,body").scrollTop(iTop);

            $(this).find('a.bx-link').dolEmbedly();
        })

		$this.stopLoading ();        
	}
	new BxXslTransform(this._base + sUrlData, urlXsl + sXslFile, h);
    if (sMakeHist && sMakeHist.length)
	    document.h.makeHist (sMakeHist);
	return false;
}

/**
 * display form to move topic to another forum
 *	@param topic_id	topic id to move
 */
Forum.prototype.moveTopicForm = function (topic_id)
{	
	var $this = this;

	var h = function (r)
	{			
		$this.showHTML (r, 400, 200);
	}

	new BxXslTransform(this._base + "?action=move_topic_form&topic_id=" + topic_id, urlXsl + "move_topic_form.xsl", h);

	return true;
}

/**
 * to move topic to another forum
 *	@param topic_id	topic id to move
 */
Forum.prototype.moveTopicSubmit = function (topic_id, forum_id, old_forum_id, goto_new_location)
{	
	var $this = this;
	var h = function (r) {		
        var ret = orca_get_xml_ret(r);
        var goto_forum_uri = ret;
		if (goto_forum_uri && '' != goto_forum_uri && '0' != goto_forum_uri) {
            f.hideHTML();
        	$this.selectForum (goto_forum_uri, 0);            
			return false;
		}
		alert ('[L[Error occured]]');
		return false;
	}

	jQuery.ajax ({
		url: this._base + "?action=move_topic_submit&topic_id=" + topic_id + "&forum_id=" + forum_id + "&old_forum_id=" + old_forum_id + "&goto_new_location=" + goto_new_location,
		dataType: 'text',
		type: 'POST',
		success: h
	});
}

/**
 * run edit post timer
 *	@param post_id	post id
 */
Forum.prototype.editPostTimer = function (post_id) {
    this.stopEditPostTimer(post_id);
    this._idEditPostTimer = setInterval('f._onEditPostTimer(' + post_id + ')', 1000);
}

Forum.prototype.stopEditPostTimer = function (post_id) {
    if (0 == this._idEditPostTimer)
        return;
    clearInterval(this._idEditPostTimer);
    this._idEditPostTimer = 0;
}

Forum.prototype._onEditPostTimer = function (post_id) {
    var e = jQuery('#edit_timeout_' + post_id);
    if (!e.length)
        this.stopEditPostTimer(post_id);

    var s = e.html();
    var m = s.match(/([0-9\-]+)/);
    if (null == m)
        this.stopEditPostTimer(post_id);

    s = s.replace(/([0-9\-]+)/, parseInt(m[1]) - 1);

    e.html(s);

    if (m[1] - 1 < 120 && m[1] - 1 > 30) 
        e.removeClass('edit_timeout30').addClass('edit_timeout120');

    if (m[1] - 1 < 30) 
        e.removeClass('edit_timeout120').addClass('edit_timeout30');

    if (m[1] - 1 < 1) 
        this.stopEditPostTimer(post_id);
}

Forum.prototype.removeImageFromPost = function (sEditorId, iFileId) {
    var eEditor = 'object' == typeof(tinymce) ? tinymce.get(sEditorId) : undefined;
    if ('undefined' == typeof(eEditor))
        return;

    var aFiles = $(eEditor.dom.doc).find('.forum-post-img-' + iFileId);
    aFiles.each(function () { eEditor.execCommand('mceRemoveNode', false, this); });
}

Forum.prototype.insertImageToPost = function (sEditorId, iFileId) {

    var eEditor = 'object' == typeof(tinymce) ? tinymce.get(sEditorId) : undefined;
    if ('undefined' == typeof(eEditor))
        return;

    if (eEditor.dom && eEditor.dom.doc && $(eEditor.dom.doc).find('.forum-post-img-' + iFileId).length > 0)
        return;

    var sImgUrl = this._base + '?action=download&hash=' + iFileId;
    eEditor.execCommand('mceInsertContent', false, '<img class="forum-post-img-' + iFileId + ' forum-post-img bx-def-shadow bx-def-round-corners" src="' + sImgUrl + '" />');
}

Forum.prototype.processSelectedText = function () {
    this._sSelectedText = '';
    if (window.getSelection)
        this._sSelectedText = window.getSelection().toString();
    else if (document.selection && document.selection.type != 'Control')
        this._sSelectedText = document.selection.createRange().text;
}

Forum.prototype.getSelectedText = function () {
    return 'undefined' == typeof(this._sSelectedText) ? '' : this._sSelectedText;
}

