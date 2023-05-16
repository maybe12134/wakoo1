define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'teach/class_buy/index' + location.search,
                    add_url: 'teach/class_buy/add',
                    edit_url: 'teach/class_buy/edit',
                    del_url: 'teach/class_buy/del',
                    multi_url: 'teach/class_buy/multi',
                    import_url: 'teach/class_buy/import',
                    table: 'class_buy',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'classname', title: __('Classname'), operate: 'LIKE'},
                        {field: 'sex', title: __('Sex')},
                        {field: 'age', title: __('Age')},
                        {field: 'iphone', title: __('Iphone'), operate: 'LIKE'},
                        {field: 'store_name', title: __('StoreName'),operate:'LIKE'},
                        {field: 'type', title: __('Type'),operate:'LIKE',formatter:function(value,row,index){return value + "-" + row.type_age;}},
                        {field: 'state', title: __('State'),operate:'LIKE'},
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
                url: 'teach/class_buy/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'classname', title: __('Classname'), operate: 'LIKE'},
                        {field: 'sex', title: __('Sex')},
                        {field: 'age', title: __('Age')},
                        {field: 'iphone', title: __('Iphone')},
                        {field: 'store_name', title: __('StoreName'),operate:false},
                        {field: 'type', title: __('Type'),operate:false},
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
                                    url: 'teach/class_buy/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'teach/class_buy/destroy',
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
                    var ur = 'teach/class_buy/destroy';
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
