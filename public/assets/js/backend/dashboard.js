define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    // var Controller = {
    //     index: function () {
    //         // 基于准备好的dom，初始化echarts实例
    //         var myChart = Echarts.init(document.getElementById('echart'), 'walden');
    //
    //         // 指定图表的配置项和数据
    //         var option = {
    //             title: {
    //                 text: '',
    //                 subtext: ''
    //             },
    //             color: [
    //                 "#18d1b1",
    //                 "#3fb1e3",
    //                 "#626c91",
    //                 "#a0a7e6",
    //                 "#c4ebad",
    //                 "#96dee8"
    //             ],
    //             tooltip: {
    //                 trigger: 'axis'
    //             },
    //             legend: {
    //                 data: [__('Register user')]
    //             },
    //             toolbox: {
    //                 show: false,
    //                 feature: {
    //                     magicType: {show: true, type: ['stack', 'tiled']},
    //                     saveAsImage: {show: true}
    //                 }
    //             },
    //             xAxis: {
    //                 type: 'category',
    //                 boundaryGap: false,
    //                 data: Config.column
    //             },
    //             yAxis: {},
    //             grid: [{
    //                 left: 'left',
    //                 top: 'top',
    //                 right: '10',
    //                 bottom: 30
    //             }],
    //             series: [{
    //                 name: __('Register user'),
    //                 type: 'line',
    //                 smooth: true,
    //                 areaStyle: {
    //                     normal: {}
    //                 },
    //                 lineStyle: {
    //                     normal: {
    //                         width: 1.5
    //                     }
    //                 },
    //                 data: Config.userdata
    //             }]
    //         };
    //
    //         // 使用刚指定的配置项和数据显示图表。
    //         myChart.setOption(option);
    //
    //         $(window).resize(function () {
    //             myChart.resize();
    //         });
    //
    //         $(document).on("click", ".btn-refresh", function () {
    //             setTimeout(function () {
    //                 myChart.resize();
    //             }, 0);
    //         });
    //
    //     }
    // };
    //
    // return Controller;
    var Controller = {
        index: function () {
            //这句话在多选项卡统计表时必须存在，否则会导致影响的图表宽度不正确
            $(document).on("click", ".charts-custom a[data-toggle=\"tab\"]", function () {
                var that = this;
                setTimeout(function () {
                    var id = $(that).attr("href");
                    var chart = Echarts.getInstanceByDom($(id)[0]);
                    chart.resize();
                }, 0);
            });


            $.ajax({
                url:'teach/class_user/unumber',
                type:'POST',
                dataType:'json',
                success:function(jsonData){
                    week = jsonData.data.week;
                    num  = jsonData.data.num;
                    line(week,num);
                    area(week,num);
                }
            });

            $.ajax({
                url:'dashboard/visitor',
                type:'POST',
                dataType:'json',
                success:function(jsonData){
                    
                      a = jsonData.data.week;
                      b  = jsonData.data.num;
                      visitor(a,b);
                }
            });



            $.ajax({
                url:'dashboard/indent',
                type:'POST',
                dataType:'json',
                success:function(jsonData){
                     week = jsonData.week;
                     num  = jsonData.data;
                     indent(week,num);
                }
            });
           
            function line(){
                // 基于准备好的dom，初始化echarts实例
                var lineChart = Echarts.init(document.getElementById('line-chart'), 'walden');
                lineChart.setOption({
                    // 指定图表的配置项和数据
                    xAxis: {
                        type: 'category',
                        data: week
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        type: 'line',
                        data: num
                    }]
                });
            }


           
            function area(){
                // 基于准备好的dom，初始化echarts实例
                var areaChart = Echarts.init(document.getElementById('area-chart'), 'walden');
                areaChart.setOption({
                    // 指定图表的配置项和数据
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: week
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        type: 'line',
                        data: num,
                        areaStyle: {}

                    }]
                });
            }


            
            var pieChart = Echarts.init(document.getElementById('pie-chart'), 'walden');
            var option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 10,
                    data: ['普罗旺斯校区', '美林新东城A区店', '鑫乐汇店']
                },
                series: [
                    {
                        name: '销售记录',
                        type: 'pie',
                        radius: ['50%', '70%'],
                        avoidLabelOverlap: false,
                        label: {
                            normal: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                show: true,
                                textStyle: {
                                    fontSize: '30',
                                    fontWeight: 'bold'
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data: [
                            {value: 335, name: '普罗旺斯校区'},
                            {value: 310, name: '美林新东城A区店'},
                            {value: 234, name: '鑫乐汇店'},
                        ]
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            pieChart.setOption(option);





            var barChart = Echarts.init(document.getElementById('bar-chart'), 'walden');
            option = {
                legend: {},
                tooltip: {},
                dataset: {
                    source: [
                        ['产品销售', '2015', '2016', '2017'],
                        ['风扇', 43.3, 85.8, 93.7],
                        ['电视机', 83.1, 73.4, 55.1],
                        ['空调', 86.4, 65.2, 82.5],
                        ['冰箱', 72.4, 53.9, 39.1]
                    ]
                },
                xAxis: {type: 'category'},
                yAxis: {},
                // Declare several bar series, each will be mapped
                // to a column of dataset.source by default.
                series: [
                    {type: 'bar'},
                    {type: 'bar'},
                    {type: 'bar'}
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            barChart.setOption(option);


            

            function visitor(){
                var barChart = Echarts.init(document.getElementById('simplebar-chart'));
                option = {
                    xAxis: {
                        type: 'category',
                        axisLine: {
                            lineStyle: {
                                color: "#fff"
                            }
                        },
                        data: a
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: {
                            lineStyle: {
                                color: "#fff"
                            }
                        }
                    },
                    series: [{
                        data: b,
                        type: 'bar',
                        itemStyle: {
                            color: "#fff",
                            opacity: 0.6
                        }
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                barChart.setOption(option);
            }
           

            
            function indent(){
                var barChart = Echarts.init(document.getElementById('smoothline-chart'));
                option = {
                    textStyle: {
                        color: "#fff"
                    },
                    color: ['#fff'],
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: week,
                        axisLine: {
                            lineStyle: {
                                color: "#fff"
                            }
                        }
                    },
                    yAxis: {
                        type: 'value',
                        splitLine: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                color: "#fff"
                            }
                        }
                    },
                    series: [{
                        data: num,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            opacity: 0.4
                        }
    
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                barChart.setOption(option);
            }
        }
    };
    return Controller;
});
