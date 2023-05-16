define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'teach/class_campus/index' + location.search,
                    add_url: 'teach/class_campus/add',
                    edit_url: 'teach/class_campus/edit',
                    del_url: 'teach/class_campus/del',
                    multi_url: 'teach/class_campus/multi',
                    import_url: 'teach/class_campus/import',
                    table: 'class_campus',
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
                        {field: 'store_name', title: __('Store_name'), operate: 'LIKE'},
                        {field: 's_iphone', title: __('S_iphone'), operate: 'LIKE'},
                        {field: 'addres', title: __('Addres'), operate: 'LIKE'},
                        {field: 'number', title: __('Number')},
                        {field: 'rqty', title: __('Rqty')},
                        {field: 'create_time', title: __('Create_time'), operate:'LIKE', addclass:'datetimerange', autocomplete:false},
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
                url: 'teach/class_campus/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'store_name', title: __('Store_name'), operate: 'LIKE'},
                        {field: 'addres', title: __('Addres'), operate: 'LIKE'},
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
                                    url: 'teach/class_campus/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'teach/class_campus/destroy',
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
                    var ur = 'teach/class_campus/destroy';
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
            Controller.api.bindevent();
        },
        edit: function () {
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
