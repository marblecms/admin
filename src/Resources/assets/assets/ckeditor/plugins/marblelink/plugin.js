CKEDITOR.plugins.add( 'marblelink', {
    init: function( editor ) {
        editor.addCommand( 'marblelink', new CKEDITOR.dialogCommand( 'marblelink' ) );

        editor.ui.addButton( 'MarbleLink', {
            label: 'Insert CMS Link',
            command: 'marblelink',
            toolbar: 'insert',
            icon: (window.MarbleFamiconUrl || '') + '/link.svg'
        });

        CKEDITOR.dialog.add( 'marblelink', this.path + 'dialogs/marblelink.js' );
    }
});
