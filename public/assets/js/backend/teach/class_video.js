define(['jquery', 'bootstrap', 'backend','dropzone', 'table', 'form','upload','videoLong'], function ($, undefined, Backend,Dropzone, Table, Form,Upload,Long) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'teach/class_video/index' + location.search,
                    add_url: 'teach/class_video/add',
                    edit_url: 'teach/class_video/edit',
                    del_url: 'teach/class_video/del',
                    multi_url: 'teach/class_video/multi',
                    import_url: 'teach/class_video/import',
                    table: 'class_video',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'type_name', title: __('TypeName'),operate:'LIKE'},
                        {field: 'type', title: __('Type'),operate:'LIKE',formatter:function(value,row,index){return value + "-" + row.type_age;}},
                        {field: 'which', title: __('Which'),operate:false},
                        {field: 'title', title: __('Title'),operate:'LIKE'},
                        {field: 'video_image', title: __('Video_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'long', title: __('Long')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'teach/class_video/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:'LIKE'},
                        {field: 'type', title: __('Type'),operate:'LIKE',formatter:function(value,row,index){return value + "-" + row.type_age;}},
                        {field: 'title', title: __('Title'),operate:'LIKE'},
                        {field: 'video_image', title: __('Video_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {
                            field: 'delete_time',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'teach/class_video/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'teach/class_video/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $('#destroyall').click(function(){
                Layer.confirm(__('Are you sure you want to truncate?'), function () {
                    var ur = 'teach/class_video/destroy';
                    var ids= $("#table").bootstrapTable('getData');// //获取所有条目ID集合
                    var arr = [];
                    if(ids.length != 0){
                        for (let prop in ids){ //prop指对象的属性名
                            arr.push(ids[prop].ids);
                        }
                    }
                    Fast.api.ajax({
                        url:ur,
                        data:{ids:arr},
                    },function () {
                        Layer.closeAll();
                        table.trigger("uncheckbox");
                        table.bootstrapTable('refresh');
                    }, function () {
                        Layer.closeAll();
                    });
                });
            });


        },
        add: function () {
            $(document).on("change", "#v-ftype", function(){
                $("#v-ptype").selectPageClear();
                $("#v-ptype_text").data("selectPageObject").option.data = "teach/class_video/ptype";
                $.ajax({
                    url: 'teach/class_video/ptype',
                    type: 'POST',
                    data: {type:$('#v-ftype').val()},
                    dataType: 'json',
                });
            });
            $(document).on("change", "#c-video", function(){
                let vlong=Long.videoUpload($("#c-video").val());
                vlong.done(function (data){// promise.resolve 走这 
                    $("#c-long").val(data);
                });  
            });
            
            Controller.api.bindevent();
        },
        edit: function () {
            $.ajax({
                url: 'teach/class_video/ptype',
                type: 'POST',
                data: {type:$('#v-ftype').val()},
                dataType: 'json',
            });
            $(document).on("change", "#v-ftype", function(){
                $("#v-ptype").selectPageClear();
                $.ajax({
                    url: 'teach/class_video/ptype',
                    type: 'POST',
                    data: {type:$('#v-ftype').val()},
                    dataType: 'json',
                });
            });
            $(document).on("change", "#c-video", function(){
                let vlong=Long.videoUpload($("#c-video").val());
                vlong.done(function (data){// promise.resolve 走这 
                    $("#c-long").val(data)
                });  
            });


           


            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
