@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => $title])
<div class="container-fluid py-4">
    <div class="card">
        <div class="row-12 p-2 my-2">
            <div class="card-header mt-0 mb-4 py-0">
                <h6 class="m-0">Real Time Data</h6>
                @if ($message != null)
                <p class="text-sm m-0 p-0 text-warning">{{ $message }}</p>
                @endif
                <p class="text-sm m-0 p-0">Last update : {{ $latestUpdated }}</p>
            </div>

            <!-- Section Real Time -->
            <div class="card-body pt-0">
                <div class="d-flex justify-content-between">
                    @foreach ($keys as $i => $key)
                    <div class="col-auto p-2 border border-shadow" style="border-radius: 1rem; background-color:white">
                        <div class="numbers mx-2 px-2">
                            <p class="text-sm mb-2 text-uppercase">
                                @php echo $names[$i]; @endphp
                            </p>
                            <p class="text-lg font-weight-bolder text-dark m-0 p-0">
                                {{ $data[$key] }}
                                <span><small class="text-warning" id="unit1"">{{ $units1[$i]}}</small></span>
                                <!-- <span><small class=" text-warning" id="unit2">{{ $units2[$i] }}</small></span> -->
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Section Graph  -->
        <div class="row-12 mt-0">
            <div class="col-lg-12 mb-lg-0 mb-2">
                <div class="card z-index-2 h-100 mx-4">
                    <div class="card-header pb-0 pt-2 bg-transparent d-flex justify-content-between">
                        <div class="col-lg-8"></div>
                        <div class="col-lg-4 d-flex justify-content-between center mx-1">
                            <button onclick="updateBarChartData('week')">This Week</button>
                            <button onclick="updateBarChartData('month')">This Month</button>
                            <button onclick="updateBarChartData('year')">This Year</button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div id="bar-chart-volume" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row-12 mt-2">
            <div class="col-lg-12 mb-lg-0 mb-2">
                <div class="card z-index-2 h-100 mx-4">
                    <div class="card-header pb-0 pt-2 bg-transparent d-flex justify-content-between">
                        <h6 class="text-capitalize">Real Time Chart</h6>
                        <div class="filter-controls">
                            <label for="startDate">Start Date:</label>
                            <input type="date" id="startDate">

                            <label for="endDate">End Date:</label>
                            <input type="date" id="endDate">
                        </div>
                        <button id="exportButton">Export Data</button>
                    </div>
                    <div class="card-body p-3">
                        <div id="chart-volume" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footers.auth.footer')
</div>
@endsection
@push('js')
<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.2/echarts.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const chartData = @json($chartData1);
        const chartData2 = @json($chartData2);

        let filteredData = [...chartData];
        const chartDom = document.getElementById('chart-volume');
        const myChart = echarts.init(chartDom);
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        function updateChart() {
            const option = {
                // title: {
                //     text: 'Volume Over Time'
                // },
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    data: filteredData.map(item => item.timestamp)
                },
                yAxis: {
                    type: 'value',
                    name: 'Volume'
                },
                legend: {
                    data: ['Volume (m³)', 'Volume 2 (m³)'],
                    textStyle: {
                        color: '#333',
                        fontSize: 12,
                    },
                },
                series: [
                    {
                        data: filteredData.map(item => item.nowVol),
                        type: 'line',
                        name: 'Volume (m³)',
                    },
                    {
                        data: chartData2.map(item => item.nowVol),
                        type: 'line',
                        name: 'Volume 2 (m³)',
                    }
                ],
                dataZoom: [
                    {
                        type: 'inside',
                        start: 0,
                        end: 100
                    },
                    {
                        show: true,
                        type: 'slider',
                        top: '90%',
                        start: 0,
                        end: 100
                    }
                ]
            };
            myChart.setOption(option);
        }

        function filterData() {
            const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
            const endDate = endDateInput.value ? new Date(endDateInput.value) : null;

            filteredData = chartData.filter(item => {
                const itemDate = new Date(item.timestamp);
                return (!startDate || itemDate >= startDate) && (!endDate || itemDate <= endDate);
            });

            updateChart();
        }

        startDateInput.addEventListener('change', filterData);
        endDateInput.addEventListener('change', filterData);

        document.getElementById('exportButton').addEventListener('click', exportToExcel);

        function exportToExcel() {
            const ws = XLSX.utils.json_to_sheet(filteredData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Chart Data");
            XLSX.writeFile(wb, "Water Volume.xlsx");
        }

        // Set initial date range
        const dates = chartData.map(item => new Date(item.timestamp));
        const maxDate = new Date(Math.max.apply(null, dates));
        const minDate = new Date(Math.min.apply(null, dates));

        startDateInput.value = minDate.toISOString().split('T')[0];
        endDateInput.value = maxDate.toISOString().split('T')[0];

        // Initial chart render
        updateChart();
    });
</script>

<script>
    var chartDataWeek = @json($barChartDataWeek);
    var chartDataMonth = @json($barChartDataMonth);
    var chartDataYear = @json($barChartDataYear);
    var chart = echarts.init(document.getElementById('bar-chart-volume'));
    var currentPeriod = 'week';


    function updateBarChartData(period) {
        currentPeriod = period;
        var data;
        switch (period) {
            case 'week':
                data = chartDataWeek;
                break;
            case 'month':
                data = chartDataMonth;
                break;
            case 'year':
                data = chartDataYear;
                break;
        }
        renderBarChart(data);
    }

    function renderBarChart(data) {
        times = data.map(item => item.time);
        var volumes = data.map(item => item.thisVol);

        var option = {
            title: {
                text: 'Water Volume - ' +
                    (currentPeriod === 'week' ? 'This Week' :
                        currentPeriod === 'month' ? 'This Month' : 'This Year')
            },
            tooltip: {
                trigger: 'axis',
                formatter: function (params) {
                    var dataIndex = params[0].dataIndex;
                    return data[dataIndex].time + '<br/>' +
                        'Volume: ' + data[dataIndex].thisVol + ' m³<br/>' +
                        'Cost: Rp ' + data[dataIndex].cost.toLocaleString();
                }
            },
            xAxis: {
                type: 'category',
                data: times
            },
            yAxis: {
                type: 'value',
                name: 'Volume (m³)'
            },
            series: [{
                data: volumes,
                type: 'bar',
                label: {
                    show: true,
                    position: 'top',
                    formatter: function (params) {
                        return params.value + ' m³';
                    }
                }
            }]
        };

        chart.setOption(option);
    }

    // Initial render
    updateBarChartData(currentPeriod);
</script>

@endpush