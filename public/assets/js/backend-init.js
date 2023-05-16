define(['backend'], function (Backend) {
    require.config({
        paths: {
            'qiniu': '../libs/qiniu/dist/qiniu.min',
        },
        // shim: {
        //     'qiniu': {
        //         deps: [
        //             'css!../libs/'
        //         ],
        //     },
        // }
    });
});