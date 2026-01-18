"use strict"
window.onload = function() {
    class UploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file.then(file => {
                return new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    data.append('_token', _token);

                    fetch(filebrowserUploadUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(result => {
                        resolve({ default: result.url });
                    })
                    .catch(error => {
                        reject(uploadFailmessage);
                    });
                });
            });
        }

        abort() {}
    }

    function UploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new UploadAdapter(loader);
        };
    }

    ClassicEditor
        .create(document.querySelector('#content'), {
            extraPlugins: [ UploadAdapterPlugin ],
            removePlugins: [ 'MediaEmbed' ]
        })
        .catch(error => {
           
        });
};