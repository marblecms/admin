CKEDITOR.plugins.add('marbleai', {
    init: function(editor) {
        editor.addCommand('marbleai', {
            exec: function(editor) {
                if (window.MarbleAI) {
                    window.MarbleAI.open(editor);
                }
            }
        });
        editor.ui.addButton('MarbleAI', {
            label: 'AI Assistant',
            command: 'marbleai',
            toolbar: 'marble',
            icon: (window.MarbleFamiconUrl || '') + '/wand.svg'
        });
    }
});
