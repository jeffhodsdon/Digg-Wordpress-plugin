function DiggWidgetGenerator(dwObj, showCodeFunc) {
    var dw, dwObj1, dwContainer = $('#widget-container'), user = getUser(), swatches = $('<div class="swatches"></div>'), preTabTitle, userWidth, userTitle, userTabTitles = [], curTab = 0, defaultVals = {};        

	if (dwObj) {		
		init(dwObj);	
	} else {
		dwObj = {
			id: 'digg-widget',
			layout: 1,
			colors: {
			    hdrBg:'#1b5790', 
			    hdrTxt:'#b3daff',
			    tabBg:'#4684be',
			    tabTxt:'#b3daff',
			    tabOnTxt:'#d41717',
			    bdyBg:'#fff',
			    stryBrdr:'#ddd',
			    lnk:'#105cb6',
			    descTxt:'#999999',
			    subHd:'#999999'
			},
			title: 'CBSNews.com on Digg',
			width: 300,
			requests: [{
			    t: 'CBSNews.com',
			    p: { 
			        method: 'story.getPopular',
			        domain: 'CBSNews.com',
			        count: 5
			    }
			}]
		}	
	}

    dwObj1 = $.extend(true, {}, dwObj);
    dw = new DiggWidget(dwObj);
    
    showCode = showCodeFunc ||  showCode;
    
    setTimeout(showCode, 20);
    
    function init(dwObj) {
    	var 
    		i = 0, 
    		l = dwObj.requests.length, 
    		tabEditHtml = dwObj.display == "tabbed" ? '<div class="tab-edit"><h3></h3><input type="text" /><div class="tab-delete" title="Remove this tab">x</div></div>' : '<div class="tab-edit"><h3></h3><input type="text" /><div class="tab-delete" title="Remove this column">x</div></div>',
    		addTabButton = $('.add-tab'),
    		a = [],
    		w = dwObj.width,
    		t = 0,
    		r
    	;
    	
		dwObj.id = 'digg-widget';
	    setFormVals(dwObj.requests[0]);
    	setLayout(dwObj.layout || 1);
    	dwObj.layout == 2 && dwContainer.addClass('layout2');
    	
    	if (dwObj.layout == 2 || dwObj.display == 'columns') {
    		if (w && w != 600) {
    			userWidth = w;	
    		}
    	} else {
    		if (w && w != 300) {
    			userWidth = w;	
    		}    		
    	}
    	
    	$('input[name=widget-title]').val(dwObj.title);
    	
    	if (userWidth) {
    		$('input[name=widget-width]').val(userWidth);	
    	}
    	
    	if (dwObj.height) {
    		$('input[name=widget-height]').val(dwObj.height);    		
    	}
    	
    	$('input[name=descriptions]').attr('checked', dwObj.descriptions == 'show');
    	
    	if (dwObj.hide) {
	    	$('input[name=widget-header]').attr('checked', !dwObj.hide.header);
	    	$('input[name=widget-footer]').attr('checked', !dwObj.hide.footer);   
	    	$('input[name=widget-diggs]').attr('checked', !dwObj.hide.diggs);
	    	$('input[name=widget-thumb]').attr('checked', !dwObj.hide.thumb);	    	
	    }
	    
		$('input[name=widget-rounded]').attr('checked', !!dwObj.rounded);	    
		$('input[name=widget-stylesheet]').attr('checked', !dwObj.suppressCss);
		$('input[name=widget-targ]').attr('checked', dwObj.target == '_blank');
				    	
    	if (l > 1) {
    		
	        addTabButton.hide(); 
	        $('#add-column').parent().hide();
	        $('#single-tab').hide();         
	        
	        if (dwObj.display == 'columns') {
		        l = 2;
		        $('#layout1').removeClass('on');
		        $('#layout2').addClass('on');
		        dwObj.layout = 1;
	        }
    		
    		for (; i < l; i++) {
    			r = dwObj.requests[i];
    			a.push(
    				'<div class="tab-edit',
    				i == 0 ? ' selected' : '',
    				'"><h3>',
    				dwObj.display == 'tabbed' ? 'Tab' : 'Column',
    				i+1,
    				'</h3><input type="text" value="',
    				r.t,
    				'" name="tabtitle',
    				i,
    				'" /><div class="tab-delete" title="Remove this ',
    				dwObj.display == 'tabbed' ? 'tab' : 'column',
    				'">x</div></div>'    				
    			);   
				
				userTabTitles[i] = getTabText(r.p) == r.t ? '' : r.t;
   				
   				if (dwObj.title != getTitleText(r.p)) {
   					t++;
   				}
    		}
    		
   			if (dwObj.title && t == l) {
   				userTitle = dwObj.title;	
   			} 				

			$(a.join('')).insertBefore(addTabButton);    			    		
    	} else {
    		userTitle = dwObj.title && dwObj.title == getTitleText(dwObj.requests[0].p) ? '' : dwObj.title;
    	}	    	
    }
    
    $('.widget-generator-form').submit(function(e) {
    	e.preventDefault();
    });
                
    $('#fields-container input[type=text]').each(function() {
        defaultVals[this.name] = this.value;
    });
        
    (function() {
        var i, l, swatchColors = ['000000','666666','999999','cccccc','eeeeee','ffffff','ff0000','cc0000','990000','660000','330000','00ff00','00cc00','009900','006600','003300','0000ff','0000cc','000099','000066','000033','eeccee','ccccee', '00cc66'];
        for (i = 0, l = swatchColors.length; i < l; i++) {
            swatchColors[i] = '<div class="swatch" style="background:#' + swatchColors[i] + '" rel="' + swatchColors[i] + '"></div>';    
        }
        swatchColors.push('<br />');
        swatches.html(swatchColors.join(''));
        $('.swatches .swatch').live('click', function(e) {
            var c = '#' + $(this).attr('rel'), i = swatches.parent().find('input');
            i.css({
                background:c,
                borderColor:c
            }).val(c).change();
        });
    })();
        
    $('.show-more-style-options').click(function(e) {
        $(this).hide();
        $('.more-style-options').show();        
    });
    
    $('.color').click(function(e) {
        $(this).append(swatches);
        $('input', this).focus();
    });
    
    $('input[name=widget-width], input[name=widget-height]').change(setDimension).blur(setDimension); 
        
    $('input[name=descriptions]').click(function() {
       if (this.checked) {
           dwObj.descriptions = 'show';
           $('#description-color').show();
       } else {
           delete dwObj.descriptions;
           $('#description-color').hide();
       }       
       redisplay();
    }).get().checked = !1;
    
    $('.content-option input[type=checkbox]').click(function() {
        var c, n = this.name;
        if (n != 'descriptions') {
            n = n.replace('widget-', '');  
            if (n == 'targ') {
                if (this.checked) {
                    dwObj.target = '_blank';
                } else {
                    delete dwObj.target;    
                }    
            } else if (n == 'rounded') {                
                dwObj.rounded = !!this.checked;   
            } else if (n == 'stylesheet') {
                if (!this.checked) {
                    dwObj.suppressCss = 1;    
                    for (c in dwObj.colors) {
                    	if (dwObj.colors[c] == dwObj1.colors[c]) {
                    		dwObj.colors[c] = '';
                    	}
                    }
                } else {
                    delete dwObj.suppressCss;
                    for (c in dwObj.colors) {
                    	if (dwObj.colors[c] == '') {
                    		dwObj.colors[c] = dwObj1.colors[c];
                    	}
                    }
                }            
            } else {
                dwObj.hide = dwObj.hide || {};
                dwObj.hide[n] = !this.checked;
            }
            redisplay();
        }
    });
    
    $('#view-stylesheet').click(function(e) {
        open(this.href, 'css', 'width=500,height=550,scrollbars=1');
        e.preventDefault();
    });
        
    $('.add-tab').click(addTab);
    $('#add-column').click(addColumn);
        
    $('#digg-widget .digg-tab').live('click', function() {
        var el = this;    
        $('#digg-widget .digg-tab').each(function(i) {
            if (this == el) {
                setTab(i);    
            }
        });            
    });
    
    $('#layout1').click(function(e) {
           setLayout(1);
           redisplay();
    });

    $('#layout2').click(function(e) {
           setLayout(2);
           redisplay();
    });
            
    $('input[name=widget-title]').change(function(e) {
        userTitle = dwObj.title = this.value;
        $('#digg-widget h2').html(dwObj.title);
    });
    
    $('.color input').change(function(e) {
        var v = this.value;
                
        dwObj.colors[this.name] = v;
        $(this).css({
            background:v,
            borderColor:v
        }); 
        redisplay();
        setInputTextColor(this);
        e.preventDefault();
    }).each(function(e) {
        var c = dwObj.colors[this.name];        
        $(this).val(c);
        setInputTextColor(this);
        $(this).css({
            background:c,
            borderColor:c
        })
    }).attr('maxlength', 7);
    
    $('#fields-container .fld select').live('mousedown', function() {
        $(this).closest('div.fld').click();
    });
    
    $('#fields-container .fld').live('click', function(e) {
        if (!$(this).hasClass('selected')) {
            if (e.target.name != 'news_type') {
                var r = $('input[name=news_type]', this);
                if (!r.attr('disabled')) {
                    r.attr('checked', true).click();
                }
            } else {
                $('#fields-container .fld').removeClass('selected');
                $(this).addClass('selected');            
            }
        }
    });
    
    $('.tab-edit').live('click', function() {
        if (!$(this).hasClass('selected')) {
            setTab($('input', this).attr('name').replace('tabtitle',''));
        }
    });
    
    $('.tab-edit input').live('change', function() {
        var n = $(this).attr('name').replace('tabtitle','');
        userTabTitles[n] = $(this).val();
        dwObj.requests[n].t = userTabTitles[n];
        redisplay();
    });
    
    $('.tab-delete').live('click', function() {
        var n = $(this).parent().find('input').attr('name').replace('tabtitle','');
        
        dwObj.requests.splice(n, 1);
        
        if (dwObj.requests.length == 1) {
            delete dwObj.display;            
            $('.tab-edit').remove();
            $('#single-tab').show();
            $('.add-tab').show();
            $('#tab-selector p').show();    
            setLayout(dwObj.layout || 1);
            curTab = 0;
        } else {
            $('.tab-edit').eq(n).remove();
            if (curTab == n) {
                while (!dwObj.requests[curTab]) {
                    curTab--;    
                }    
            }
            $('.tab-edit').each(function(i) {
                $('h3', this).text('Tab ' + (i+1));
                $('input', this).attr('name', 'tabtitle'+i);
                if (i == curTab) {
                    $(this).addClass('selected');    
                }
            });
        }
        preTabTitle = '';
        dwObj.title = userTitle || getTitleText(dwObj.requests[curTab].p);    
        $('input[name=widget-title]').val(dwObj.title);     
        redisplay();
    });
    
    $('input[name=fallback]').click(function() {
        dwObj.nofallback = !this.checked;    
        redisplay();
    });        
    
    $('input[name=count]').change(setRequest);

    $('input[name=news_type]').live('click', setRequest);
    
    $('select[name=news_front]').change(setRequest);

    $('select[name=news_top]').change(setRequest);
    
    $('input[name=username]').change(setRequest)
    
    $('select[name=news_user]').change(setRequest);
        
    $('select[name=source-poporup]').change(setRequest);    
    $('select[name=mindate]').change(setRequest);
    $('select[name=url-sort]').change(setRequest);

    $('input[name=url]').change(setRequest);

    $('select[name=url-sort]').change(setRequest);

    $('input[name=apisearch]').change(setRequest);
    $('select[name=search-sort]').change(setRequest);    
    $('select[name=search-topics]').change(setRequest);

    $('select[name=news_friends]').change(setRequest); 
    $('input[name=myusername]').change(setRequest);
            
    function setRequest() {
        var val, v, r = {
            t: dwObj.requests[curTab].t,
            p:{
                count:$('input[name=count]').val()
            }    
        };
        
        val = $('input[name=news_type]:checked').val();
        
        if (val == 'front') {
            r.p.method = 'story.getPopular';
            r.p.topic = $('select[name=news_front]').val();
        } else if (val == 'top10') {
            r.p.method = 'story.getTop';
            r.p.container = $('select[name=news_top]').val();
        } else if (val == 'user') {
            r.p.method = $('select[name=news_user]').val() == 'dugg' ? 'user.getDugg' : 'user.getSubmissions';
            r.p.username = $('input[name=username]').val();    
        } else if (val == 'domain') {
            r.p.sort = $('select[name=url-sort]').val();
            v = $('select[name=source-poporup]').val();
            if (v == 'upcoming') {
                r.p.method = 'story.getUpcoming';
            } else if (v == 'popular') {
                r.p.method = 'story.getPopular';                
            } else {
                r.p.method = 'story.getAll';    
                r.p.sort = r.p.sort.replace('promote', 'submit');
            }            
            r.p.domain = $('input[name=url]').val();
            v = $('select[name=mindate]').val();
            if (v) {            
                r.p['min_' + (r.p.method == 'story.getPopular' ? 'promote' : 'submit') + '_date'] = getTimestamp(v);
            }
        } else if (val == 'search') {
            r.p.method = 'search.stories';
            r.p.query = $('input[name=apisearch]').val();
            r.p.sort = $('select[name=search-sort]').val();
            r.p.container = $('select[name=search-topics]').val();
        } else if (val == 'friends') {
            val = $('select[name=news_friends]').val();
            r.p.method = val == 'submissions' ? 'friend.getSubmissions' : val == 'commented' ? 'friend.getCommented' : 'friend.getDugg';
            r.p.username = $('input[name=myusername]').val();
        } 

        r.t = userTabTitles[curTab] || getTabText(r.p);            
        
        $('input[name=tabtitle'+curTab+']').val(r.t);    
        dwObj.title = userTitle || preTabTitle || getTitleText(r.p);
        $('input[name=widget-title]').val(dwObj.title);
                
        dwObj.requests[curTab] = r;
        redisplay(); 
    }
    
    function setFormVals(r) {        
        var rad, v;
        
        $('#fields-container select').not('select[name=mindate]').attr('selectedIndex', 0);
        
        for (v in defaultVals) {
           $('#fields-container input[name=' + v + ']').val(defaultVals[v]);
        }
        
        $('input[name=count]').val(r.p.count);
                                
        if (r.p.method == 'story.getPopular') {
            if (r.p.domain) {                
                rad = $('input[name=news_type][value=domain]');        
                $('input[name=url]').val(r.p.domain);
                $('select[name=url-sort]').val(r.p.sort);
                $('select[name=source-poporup]').val('popular');
            } else {
                rad = $('input[name=news_type][value=front]');            
                r.p.topic && $('select[name=news_front]').val(r.p.topic);
            }
        } else if (r.p.method == 'story.getUpcoming' || r.p.method == 'story.getAll') {
            rad = $('input[name=news_type][value=domain]');
            $('input[name=url]').val(r.p.domain);
            $('select[name=url-sort]').val(r.p.sort);
            $('select[name=source-poporup]').val(r.p.method == 'story.getAll' ? 'all' : 'upcoming');
                                        
        } else if (r.p.method == 'story.getTop') {
            rad = $('input[name=news_type][value=top10]');                
            r.p.container && $('select[name=news_top]').val(r.p.container);
        } else if (r.p.method == 'user.getDugg') {
            rad = $('input[name=news_type][value=user]');
            $('input[name=username]').val(r.p.username);  
            $('select[name=news_user]').val('dugg');
        } else if (r.p.method == 'user.getSubmissions') {
            rad = $('input[name=news_type][value=user]');
            $('input[name=username]').val(r.p.username);                                    
            $('select[name=news_user]').val('submissions');
        } else if (r.p.method == 'friend.getDugg') {
            rad = $('input[name=news_type][value=friends]');
            $('select[name=news_friends]').val('dugg');
            $('input[name=myusername]').val(r.p.username);      
        } else if (r.p.method == 'friend.getSubmissions') {
            rad = $('input[name=news_type][value=friends]');            
            $('select[name=news_friends]').val('submissions');                                    
            $('input[name=myusername]').val(r.p.username);  
        } else if (r.p.method == 'friend.getCommented') {
            rad = $('input[name=news_type][value=friends]');        
            $('select[name=news_friends]').val('commented');
            $('input[name=myusername]').val(r.p.username);                                                              
        } else if (r.p.method == 'search.stories') {
            rad = $('input[name=news_type][value=search]');            
            $('input[name=apisearch]').val(r.p.query);        
            $('select[name=search-sort]').val(r.p.sort);    
            $('select[name=search-topics]').val(r.p.container);
        }        
        
        $('div.fld').removeClass('selected');
        rad.parent().addClass('selected');
        rad.attr('checked', 'checked');
    }
    
    function addColumn() {
        var el, tabEditHtml = '<div class="tab-edit"><h3></h3><input type="text" /><div class="tab-delete" title="Remove this column">x</div></div>';    
        preTabTitle = dwObj.title;
        dwObj.display = 'columns';
        dwObj.layout = 1;
        
        $('#single-tab').hide();         
        $(tabEditHtml).insertBefore($('.add-tab').hide());    
        setWidth(600);
    
        $('.add-tab').hide(); 
        $('#layout1').removeClass('on');
        $('#layout2').addClass('on');

        $('#add-column').parent().hide();

        dwObj.requests.push(getFreshRequest());

        userTabTitles.push('');
                
        $(tabEditHtml).insertBefore($('.tab-edit:first'));            
        
        $('.tab-edit').each(function(i) {
            $('h3', this).text('Column ' + (i+1));
            $('input', this).val(dwObj.requests[i].t).attr('name', 'tabtitle'+i);
            $(this).removeClass('selected');
        }).eq(1).addClass('selected');

        setFormVals(dwObj.requests[0]);
        redisplay();
        setTab(1);
        
        $('#tab-colors').hide();
        $('#subhd-color').show();        
    }
    
    function addTab() {
        var l, el, tabEditHtml = '<div class="tab-edit"><h3></h3><input type="text" /><div class="tab-delete" title="Remove this tab">x</div></div>';
                
        if (dwObj.display != 'tabbed') {
            preTabTitle = dwObj.title;
            dwObj.display = 'tabbed';            

            $('#single-tab').hide();                        
            $(tabEditHtml).insertBefore($('.add-tab'));            
        }
        
        $('#add-column').parent().hide();
        
        l = dwObj.requests.push(getFreshRequest());
                
        userTabTitles.push('');
                
        $(tabEditHtml).insertBefore($('.tab-edit:first'));            
        
        $('.tab-edit').each(function(i) {
            $('h3', this).text('Tab ' + (i+1));
            $('input', this).val(dwObj.requests[i].t).attr('name', 'tabtitle'+i);
            $(this).removeClass('selected');
        }).eq(0).addClass('selected');
        
        setFormVals(dwObj.requests[l-1]);
        
        redisplay(l-1); 
        
        $('#tab-colors').show();
        $('#subhd-color').hide();        
    }
    
    function setTab(n) {
        $('.tab-edit').removeClass('selected').eq(n).addClass('selected');
        if (n != curTab) {
	        curTab = n;
	        setFormVals(dwObj.requests[n]);
        }
        if (dwObj.display == 'tabbed') {
            dw.setTab(n);    
        }
    }
    
    function getFreshRequest() {
        return {
            t: 'CBSNews.com',
            p: { 
                method: 'story.getPopular',
                domain: 'CBSNews.com',
                count: 5
            }
        }    
    }
    
    function getTimestamp(v) {
        var d = new Date(new Date - (24*60*60*1000*v));        
        return Math.round(d / 1000);
    }
    
    function getTabText(p) {
        var t, m = p.method;
        
        if (m == 'story.getPopular') {
            t = getDisplayDomain(p.domain) || capitalize(p.topic) || 'Popular';    
        } else if (m == 'story.getAll') {
            t = getDisplayDomain(p.domain);    
        } else if (m == 'story.getTop') {
            t = capitalize(p.container) || 'Top';    
        } else if (m == 'user.getDugg' || m == 'user.getSubmissions') {
            t = p.username;
        } else if (m == 'friend.getSubmissions' || m == 'friend.getDugg' || m == 'friend.getCommented') {
            t = 'Friends';
        } else if (m == 'story.getUpcoming') {
            t = 'Upcoming';    
        } else if (m == 'search.stories') {
            t = p.query;    
        }
                
        if (t == p.domain) {
            t = getDisplayDomain(t);        
        }
                    
        return t;    
    }
    
    function getTitleText(p) {
        var t, m = p.method;
        
        if (m == 'story.getPopular') {
            if (p.domain) {
                t = getDisplayDomain(p.domain) + ' on Digg'
            } else {
                t = 'Popular ' + (p.topic ? capitalize(p.topic) + ' Stories' : 'Stories');
            }
        } else if (m == 'story.getTop') {
            t = 'Top ' + (capitalize(p.container) || '') + ' Stories';
        } else if (m == 'user.getDugg') {
            t = 'Stories dugg by ' + p.username;    
        } else if (m == 'user.getSubmissions') {
            t = 'Stories submitted by ' + p.username;    
        } else if (m == 'friend.getDugg') {
            t = 'Stories dugg by friends';    
        } else if (m == 'friend.getSubmissions') {
            t = 'Stories submitted by friends';    
        } else if (m == 'friend.getCommented') {
            t = 'Stories commented on by friends';    
        } else if (m == 'story.getUpcoming') {
            t = p.domain ? getDisplayDomain(p.domain) + ' on Digg' : 'Upcoming Stories';
        } else if (m == 'story.getAll') {
            t = getDisplayDomain(p.domain) + ' on Digg';
        } else if (m == 'search.stories') {
            t = p.query + ' Stories';    
        }
        
        return t;
    }
        
    function getDisplayDomain(d) {
        if (d) {
            return d.replace('http://','').replace('https://','').replace('www.','');            
        }
    }
    
    function capitalize(t) {    
        if (t) {
            t = t.split('_');
            for (var c, i = 0, l = t.length; i < l; i++) {
                c = t[i].charAt(0);
                t[i] = t[i].replace(c, c.toUpperCase());
            }            
            
            t = t.join(' ');
        }
        return t;
    }
    
    function setDimension() {
        var v = parseInt(this.value), d = this.name.replace('widget-','');        
        d == 'height' ? setHeight(v) : setWidth(v, 1);
        redisplay();
    }
    
    function setHeight(v) {
        if (v && !isNaN(v)) {
            if (v == dwObj.height) {
                return;
            } else {
                dwObj.height = v;    
            }
        } else {
            delete dwObj.height;    
        }        
    }
    
    function setWidth(v, u) {
        u && (userWidth = v);        
        v = userWidth || v;
        
        if (v && !isNaN(v)) {
            if (v == dwObj.width) {
                return;    
            } else {
                dwObj.width = v;
                dwContainer.css('width', v+'px');            
                $('input[name=widget-width]').val(v);
                v > 400 ? dwContainer.addClass('layout2') : dwContainer.removeClass('layout2');    
            }    
        } else {
            delete dwObj.width;    
            dwContainer.css('width', 'auto');            
            dwContainer.addClass('layout2');            
        }
    }
    
    function setLayout(n) {
        if (dwObj.display != 'columns') { 
            dwObj.layout = n;
            setWidth(n == 1 ? 300 : 600);
            $('.layout').removeClass('on');
            $('#layout' + n).addClass('on');
        }
    }    
    
    function setInputTextColor(el) {
        var a, c, dark = 0, i = 3, v = el.value.replace('#','');
        
        if (v.length < 6) {
            a = [v.substr(0,1), v.substr(1,1), v.substr(2,1)];
        } else {
            a = [v.substr(0,2), v.substr(2,2), v.substr(4,2)];            
        }
        
        while(i) {
            i--;
            a[i] += a[i].length < 2 ? a[i] : '';
            dark += parseInt(a[i], 16) < 126 ? 1 : 0;  
        }
        
        c = dark >= 2 ? '#fff' : '#000';
        $(el).css('color', c).parent().css('color', c)                    
    }
    
    function getHtml() {
       var i, l, d, id = 'digg-widget-' + +new Date, code = ['<div id="' + id + '">'];
       
       for (i = 0, l = dwObj.requests.length; i < l; i++) {
           d = dw.getMoreLink(dwObj.requests[i]);
           code.push('<a href="' + d.h + '">' + d.t + '&lt;/a&gt;');
       }
       code.push('</div>', '<script type="text/javascript" src="http://widgets.digg.com/widgets.js">&lt;/script>');
       code.push('<script type="text/javascript">');
       dwObj.id = id;
       code.push('new DiggWidget(' + toSource(dwObj) + ');');
       dwObj.id = 'digg-widget';  
       code.push('</script>');
       
       for (i = 0, l = code.length; i < l; i++) {
           code[i] = code[i].replace('<', '&lt;', 'g').replace('>', '&gt;', 'g').replace('"', '&quot;', 'g');
       }
       
       return code.join('<br />');
    }
    
    function showCode() {
 		$('#widget-code code').html(getHtml());	
    }    
    
    function redisplay(n) {
        dw.remove(1);
        $('#digg-widget').removeClass('digg-widget');
        dw = new DiggWidget(dwObj);
        setTab(n || curTab);
        showCode();        
    }
        
    function getUser() {
        var u = document.cookie.split('loginname=');    
        return u.length > 1 ? u[1].split(';')[0] : 0;        
    }
        
    // toSource stuff    
    function toSource(o) {
        var a = ['{'];
        iter(o, a, 1);
        a.push('}');                
        return a.join('');
    }
        
    function iter(o, a, isObj) {
        var i, l, c = 0;        
        if (isObj) {
            for (i in o) {            
                push(a, o[i], c, i);
                c++;
            }                
        } else {
            for (i = 0, l = o.length; i < l; i++) {            
                push(a, o[i], i);
            }                    
        }
    }
    
    function push(a, o, i, p) {
        var t = typeOf(o);            
        i && a.push(', ');
        p && a.push(p + ': ');        
        if (o === null) {
            a.push('null');
        } else if (o === void 0) {
            a.push('undefined');
        } else if (t == 'Object') {
            a.push('{');
            iter(o, a, 1);
            a.push('}');
        } else if (t == 'Array') {
            a.push('[');
            iter(o, a);
            a.push(']');
        } else if (t == 'String') {
            a.push('"' + o + '"');    
        } else {
            a.push(o);
        }    
    }
    
    function typeOf(o) {
        return Object.prototype.toString.call(o).replace('[object ', '').replace(']', '');        
    }       

	this.getWidget = function() {
		var w;
		if (window.JSON) {
			w = JSON.stringify(dwObj);	
		} else {
			w = toSource(dwObj);	
		}    	
		return w;	
	};	    
	
	this.getCode = function(forDisplay) {
		if (forDisplay) {
			return getHtml();
		} else {
			return $('<div></div>').html(getHtml()).text();					
		}	
	};  
}
