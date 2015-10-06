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
 * Enable back browser button for ajax
 */
function BxHistory (sBaseUrl)	
{
    this._base_url =  'undefined' == typeof(sBaseUrl) ? site_url + 'forum/' : sBaseUrl;
	this._hash = ""; // current hash (after #)
	this._en = '';

    this._rw = {
    
     'cat': { // categories
            'regexp' : '^group/(.*)\\.htm$', 
            'eval' : 'document.f.selectForumIndex (m[1]);',
            'pre' : 'group/',
            'ext' : '.htm'
            },

     'forum': { // forums
            'regexp' : '^forum/(.*)-(\\d+)\\.htm$', 
            'eval' : 'document.f.selectForum (m[1], m[2]);',
            'pre' : 'forum/',
            'page' : '-',
            'ext' : '.htm'
            },

     'topic': { // topics
            'regexp' : '^topic/(.*)\\.htm$', 
            'eval' : 'document.f.selectTopic (m[1]);',
            'pre' : 'topic/',
            'ext' : '.htm'
            },

     'user': { // user
            'regexp' : '^user/(.*)\\.htm$', 
            'eval' : 'document.f.showProfile (m[1]);',
            'pre' : 'user/',
            'ext' : '.htm'
            },

     'edit_cats': { // edit cats
            'regexp' : '^action=goto&edit_cats=', 
            'eval' : 'if (document.orca_admin) document.orca_admin.editCategories ();'
            },

     'new_topic': { // new topic
            'regexp' : '^action=goto&new_topic=(.*)$', 
            'eval' : 'document.f.newTopic (m[1]);'
            },

     'search': { // search
            'regexp' : '^action=goto&search=', 
            'eval' : 'document.f.showSearch ();'
            },

     'search_result': { // search results
            'regexp' : '^action=goto&search_result=1&text=(.*?)&type=(.*?)&forum=(.*?)&u=(.*?)&disp=(.*?)&start=(.*?)$', 
            'eval' : 'document.f.search (m[1], m[2], m[3], m[4], m[5], m[6]);'
            },

     'my_flags': { // my flags
            'regexp' : '^action=goto&my_flags=1&start=(\\d+)', 
            'eval' : 'document.f.showMyFlags (m[1]);'
            },

     'my_threads': { // my threads
            'regexp' : '^action=goto&my_threads=1&start=(\\d+)',
            'eval' : 'document.f.showMyThreads (m[1]);'
            },

     'recent_topics': { // recent topics
            'regexp' : '^action=goto&recent_topics=1&start=(\\d+)', 
            'eval' : 'document.f.selectRecentTopics (m[1]);'
            }
    };
}

/**
 * go to the specified page - override this function to handle specific actions
 * @param h		hash (#)
 */
BxHistory.prototype.go = function (h)
{
    for (var i in this._rw)
    {
        var pattern = new RegExp(this._rw[i]['regexp']); 
        var m = h.match(pattern);
        if (!m) continue;
        eval (this._rw[i]['eval']);
        return true;
    }

	return false;
}

/**
 * history initialization
 * @param name		hame of history object
 */
BxHistory.prototype.init = function (name)
{
	this._en = name;

	this.handleHist();

    this._actual_url = location.href;

    // listen when we press back and forward buttons in browser
    var $this = this;

    if ('undefined' == typeof(History.pushState))
        History.init();

    History.Adapter.bind(window, 'statechange', function(event) { 
        var oState = History.getState();

        if ('undefined' === typeof(oState) || $this._actual_url == oState.url)
            return;

        var sUrl = oState.url;
        if (0 == sUrl.indexOf($this._base_url))
            sUrl = sUrl.substr($this._base_url.length);
        if (0 == sUrl.indexOf($this._base_url + 'index.php'))
            sUrl = sUrl.substr(($this._base_url + 'index.php').length);

        if (!$this.go(sUrl.replace(/^[#?]+/, '')))
            document.location = oState.url;
    });

	return true;
}

/**
 * handle history
 */
BxHistory.prototype.handleHist =  function ()
{
    if (window.location.hash.length && window.location.hash != this._hash) {
        var h = window.location.hash.replace(/^[#?]/, '');
	
        if (h.match(/^[\d]+$/))
            return true;

        if (!this.go(h))
            window.location = this._base_url + h;

        return true;

    } else if ('undefined' == typeof(History.pushState)) {

        History.init();
        History.replaceState({url:window.location.href}, null, window.location.href);

	}

	return false;
}

/**
 * record history
 * @param h	hash
 */
BxHistory.prototype.makeHist = function (h)
{
    h = h.replace(/^[#?]+/, '');
    if (h.match(/^action=/))
        h = '?' + h;
    h = this._base_url + h;

    this._actual_url = h;

    if ('undefined' == typeof(History.pushState)) {
        History.init();
        History.replaceState({url:h}, null, h);
    } else {
        History.pushState({url:h}, null, h);
}

    return true;
}

BxHistory.prototype.rw = function (s)
{
    return this._rw[s];
}
