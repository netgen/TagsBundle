YUI.add('netgen-tags-tagstree', function (Y) {
    'use strict';

    function TagsStructureMenu( params, attribute_id )
    {
        this.cookieName     = "tagsStructureMenu";
        this.cookieValidity = 3650; // days
        this.useCookie      = params.useCookie;
        this.cookie         = this.useCookie ? _getCookie( this.cookieName ) : '';
        this.open           = ( this.cookie ) ? this.cookie.split( '/' ): [];
        this.autoOpenPath   = params.path;
        this.attribute_id   = attribute_id;
        this.perm           = params.perm;
        this.expiry         = params.expiry;
        this.modal          = params.modal;
        this.hideTagIDVal   = jQuery( '#hide_tag_id_' + this.attribute_id ).val();
        this.hideTagID      = typeof this.hideTagIDVal == 'string' ? this.hideTagIDVal.split(';') : [];
        this.context        = params.context;
        this.showTips       = params.showTips;
        this.autoOpen       = params.autoOpen;

        this.updateCookie = function()
        {
            if ( !this.useCookie )
                return;
            this.cookie = this.open.join('/');
            var expireDate  = new Date();
            expireDate.setTime( expireDate.getTime() + this.cookieValidity * 86400000 );
            _setCookie( this.cookieName, this.cookie, expireDate );
        };

        // cookie functions
        function _setCookie( name, value, expires, path )
        {
            document.cookie = name + '=' + escape(value) + ( expires ? '; expires=' + expires.toUTCString(): '' ) + '; path='+ (path ? path : '/');
        }

        function _getCookie( name )
        {
            var n = name + '=', c = document.cookie, start = c.indexOf( n ), end = c.indexOf( ";", start );
            if ( start !== -1 )
            {
                return unescape( c.substring( start + n.length, ( end === -1 ? c.length : end ) ) );
            }
            return null;
        }

        function _delCookie( name )
        {
            _setCookie( name, '', ( new Date() - 86400000 ) );
        }

        this.setOpen = function( tagID )
        {
            if ( jQuery.inArray( '' + tagID, this.open ) !== -1 )
            {
                return;
            }
            this.open[this.open.length] = tagID;
            this.updateCookie();
        };

        this.setClosed = function( tagID )
        {
            var openIndex = jQuery.inArray( '' + tagID, this.open );
            if ( openIndex !== -1 )
            {
                this.open.splice( openIndex, 1 );
                this.updateCookie();
            }
        };

        this.generateEntry = function( item, lastli, isRootTag )
        {
            item.keyword = String(item.keyword).replace(/&/g,'&amp;').replace(/'/g,"&#39;").replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

            var liclass = '';
            if ( lastli )
            {
                liclass += ' lastli';
            }
            if ( params.path && ( params.path[params.path.length-1] == item.id || ( !item.has_children && jQuery.inArray( item.id, params.path ) !== -1 ) ) )
            {
                liclass += ' currentnode';
            }
            if ( jQuery.inArray( item.id.toString(), this.hideTagID ) >= 0 )
            {
                liclass += ' disabled';
            }

            var html = '<li id="n-' + this.attribute_id + '-' + item.id + '"' + ( ( liclass ) ? ' class="' + liclass + '"': '' ) + '>';
            if ( item.has_children && !isRootTag )
            {
                html += '<a class="openclose-open" id="a-' + this.attribute_id + '-'
                    + item.id
                    + '" href="#" onclick="this.blur(); return treeMenu_' + this.attribute_id + '.load( this, '
                    + item.id
                    + ', '
                    + item.modified
                    +' )"><\/a>';
            }

            if ( !this.modal )
            {
                if ( this.context != 'browse' && item.id >= 0 )
                {
                    var languagesString = [];
                    for (var locale in item.language_name_array)
                        languagesString.push('{locale:"' + locale + '", name:"' + item.language_name_array[locale] + '"}');

                    languagesString = '[' + languagesString.join(',') + ']';
                    languagesString = languagesString.replace(/&/g,'&amp;').replace(/'/g,"\\'").replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

                    html += '<a class="nodeicon" href="#" onclick="ezpopmenu_showTopLevel( event, '
                        + ((isRootTag) ? '\'TagMenuSimple\'' : '\'TagMenu\'')
                        + ', {\'%tagID%\':'
                        + item.id
                        + ', \'%languages%\':'
                        + languagesString
                        + ' }, \''
                        + item.keyword
                        + '\', '
                        + -1
                        + ', '
                        + -1
                        + ' ); return false"><img src="'
                        + item.icon
                        + '" title="' + item.keyword + '" /><\/a>';
                }
                else
                {
                    html += '<img src="'
                        + item.icon
                        + '" title="' + item.keyword + '" />';
                }

                html += '&nbsp;<a class="image-text" href="' + item.url + '"';
            }
            else
            {
                html += '<a class="nodeicon" href="#" rel="' + item.id + '"><img src="' + item.icon + '" alt="" title="Icon" /><\/a>&nbsp;<a class="image-text" href="#" rel="' + item.id + '"';
            }

            if ( this.showTips )
            {
                html += ' title="' + params.tag_id_string + ': ' + item.id + ', ' + params.parent_tag_id_string + ': ' + item.parent_id + '"';
            }

            if ( !this.modal )
            {
                html += '><span class="node-name-normal' + ( ( item.subtree_limitations_count > 0 ) ? ' disabled' : '' ) + '">' + item.keyword;
            }
            else
            {
                html += '><span class="node-name-normal">' + item.keyword;
            }

            if( item.synonyms_count > 0 )
            {
                html += ' (+' + item.synonyms_count + ')';
            }

            html += '<\/span>';

            html += '<\/a>';
            html += '<div id="c-' + this.attribute_id + '-' + item.id + '"><\/div>';
            html += '<\/li>';

            return html;
        };

        this.load = function( aElement, tagID, modifiedSubnode )
        {
            var divElement = document.getElementById('c-' + this.attribute_id + '-' + tagID);

            if ( !divElement )
            {
                return false;
            }

            if ( divElement.className == 'hidden' )
            {
                divElement.className = 'loaded';
                if ( aElement )
                {
                    aElement.className = 'openclose-close';
                }

                this.setOpen( tagID );

                return false;
            }

            if ( divElement.className == 'loaded' )
            {
                divElement.className = 'hidden';
                if ( aElement )
                {
                    aElement.className = 'openclose-open';
                }

                this.setClosed( tagID );

                return false;
            }

            if ( divElement.className == 'busy' )
            {
                return false;
            }

            var url = params.treemenu_base_url + "/" + tagID
                + "/" + modifiedSubnode
                + "/" + this.expiry
                + "/" + this.perm;

            divElement.className = 'busy';
            if ( aElement )
            {
                aElement.className = "openclose-busy";
            }

            var thisThis = this;

            var request = jQuery.ajax({
                'url': url,
                'dataType': 'json',
                'success': function( data, textStatus )
                {
                    var html = '<ul>';
                    // Generate html content
                    for ( var i = 0, l = data.children_count; i < l; i++ )
                    {
                        html += thisThis.generateEntry( data.children[i], i == l - 1, false );
                    }
                    html += '<\/ul>';

                    divElement.innerHTML += html;
                    divElement.className = 'loaded';
                    if ( aElement )
                    {
                        aElement.className = 'openclose-close';
                    }

                    thisThis.setOpen( tagID );
                    thisThis.openUnder( tagID );

                    return;
                },
                'error': function( xhr, textStatus, errorThrown )
                {
                    if ( aElement )
                    {
                        aElement.className = 'openclose-error';

                        switch( xhr.status )
                        {
                            case 403:
                            {
                                aElement.title = params.not_allowed_string;
                            } break;

                            case 404:
                            {
                                aElement.title = params.no_tag_string;
                            } break;

                            case 500:
                            {
                                aElement.title = params.internal_error_string;
                            } break;
                        }
                        aElement.onclick = function()
                        {
                            return false;
                        }
                    }
                }
            });

            return false;
        };

        this.openUnder = function( parentTagID )
        {
            var divElement = document.getElementById( 'c-' + this.attribute_id + '-' + parentTagID );
            if ( !divElement )
            {
                return;
            }

            var ul = divElement.getElementsByTagName( 'ul' )[0];
            if ( !ul )
            {
                return;
            }

            var children = ul.childNodes;
            for ( var i = 0; i < children.length; i++ )
            {
                var liCandidate = children[i];
                if ( liCandidate.nodeType == 1 && liCandidate.id )
                {
                    var tagID = liCandidate.id.substr( 3 + this.attribute_id.length ), openIndex = jQuery.inArray( tagID, this.autoOpenPath );
                    if ( this.autoOpen && openIndex !== -1 )
                    {
                        this.autoOpenPath.splice( openIndex, 1 );
                        this.setOpen( tagID );
                    }
                    if ( jQuery.inArray( tagID, this.open ) !== -1 )
                    {
                        var aElement = document.getElementById( 'a-' + this.attribute_id + '-' + tagID );
                        if ( aElement )
                        {
                            aElement.onclick();
                        }
                    }
                }
            }
        };

        this.collapse = function( parentTagID )
        {
            var divElement = document.getElementById( 'c-' + this.attribute_id + '-' + parentTagID );
            if ( !divElement )
            {
                return;
            }

            var aElements = divElement.getElementsByTagName( 'a' );
            for ( var index in aElements )
            {
                var aElement = aElements[index];
                if ( aElement.className == 'openclose-close' )
                {
                    var tagID        = aElement.id.substr( 3 + this.attribute_id.length );
                    var subdivElement = document.getElementById( 'c-' + this.attribute_id + '-' + tagID );
                    if ( subdivElement )
                    {
                        subdivElement.className = 'hidden';
                    }
                    aElement.className = 'openclose-open';
                    this.setClosed( tagID );
                }
            }

            var aElement = document.getElementById( 'a-' + this.attribute_id + '-' + parentTagID );
            if ( aElement )
            {
                divElement.className = 'hidden';
                aElement.className   = 'openclose-open';
                this.setClosed( parentTagID );
            }
        };
    }

});
