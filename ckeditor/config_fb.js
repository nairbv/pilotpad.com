/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    config.enterMode = CKEDITOR.ENTER_BR;

    config.extraPlugins = 'Logout';

    config.toolbar = 'MyToolbar';
    
    config.toolbarStartupExpanded = false;

    config.toolbar_MyToolbar = 
    [
        { name: 'document', items : [ 'Save'] },
        { name: 'editing', items : [ 'RemoveFormat','Find','Replace','-','SelectAll','-','Scayt','SpellChecker' ] },
        '/',

        { name: 'styles', items : [ 'Styles','Format' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Strike' ] },

        { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
        { name: 'links', items : [ 'Link','Unlink' ] },

        { name: 'insert', items : [ 'Table','HorizontalRule','Smiley','SpecialChar','PageBreak'] },
        { name: 'tools', items : [ 'Source'] },

    ]
};




/**
 * Override the default 'toolbarCollapse' command to hide
 * only toolbars in the row two and onwards.
 */
CKEDITOR.on('instanceReady', function(e) {

    function switchVisibilityAfter1stRow(toolbox, show)
    {
        var inFirstRow = true;
        var elements = toolbox.getChildren();
        var elementsCount = elements.count();
        var elementIndex = 0;
        var element = elements.getItem(elementIndex);
        for (; elementIndex < elementsCount; element = elements.getItem(++elementIndex))
        {
            inFirstRow = inFirstRow && !(element.is('div') && element.hasClass('cke_break'));

            if (!inFirstRow)
            {
                if (show) { element.show();} else { element.hide();}
            }
        }
    }

    var editor = e.editor;
    var collapser = (function()
    {
        try
        {
            // We've HTML: td.cke_top {
            //  div.cke_toolbox {span.cke_toolbar, ... }
            //  , a.cke_toolbox_collapser }
            var firstToolbarId = editor.toolbox.toolbars[0].id;
            var firstToolbar = CKEDITOR.document.getById(firstToolbarId);
            var toolbox = firstToolbar.getParent();
            var collapser = toolbox.getNext();
            return collapser;
        }
        catch (e) {}
    })();

    // Copied from editor/_source/plugins/toolbar/plugin.js & modified
    editor.addCommand( 'toolbarCollapse',
    {

        exec : function( editor )
        {
            if (collapser == null) return;

            var toolbox = collapser.getPrevious(),
            contents = editor.getThemeSpace( 'contents' ),
            toolboxContainer = toolbox.getParent(),
            contentHeight = parseInt( contents.$.style.height, 10 ),
            previousHeight = toolboxContainer.$.offsetHeight,

            collapsed = toolbox.hasClass('iterate_tbx_hidden');//!toolbox.isVisible();

            if ( !collapsed )
            {
                switchVisibilityAfter1stRow(toolbox, false);    // toolbox.hide();
                toolbox.addClass('iterate_tbx_hidden');
                if (!toolbox.isVisible()) toolbox.show(); // necessary 1st time if initially collapsed

                collapser.addClass( 'cke_toolbox_collapser_min' );
                collapser.setAttribute( 'title', editor.lang.toolbarExpand );
            }
            else
            {
                switchVisibilityAfter1stRow(toolbox, true);    // toolbox.show();
                toolbox.removeClass('iterate_tbx_hidden');

                collapser.removeClass( 'cke_toolbox_collapser_min' );
                collapser.setAttribute( 'title', editor.lang.toolbarCollapse );
            }

            // Update collapser symbol.
            collapser.getFirst().setText( collapsed ?
                '\u25B2' :      // BLACK UP-POINTING TRIANGLE
                '\u25C0' ); // BLACK LEFT-POINTING TRIANGLE

            var dy = toolboxContainer.$.offsetHeight - previousHeight;
            contents.setStyle( 'height', ( contentHeight - dy ) + 'px' );

            editor.fire( 'resize' );
        },

        modes : {
            wysiwyg : 1,
            source : 1
        }
    } )

    // Make sure advanced toolbars initially collapsed
    editor.execCommand( 'toolbarCollapse' );
});



