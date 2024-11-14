@extends('admin.layouts.index')
<style>
    .vertical-text {
        writing-mode: vertical-rl; /* or vertical-lr for the opposite direction */
        transform: rotate(180deg); /* Adjust rotation if needed */
        text-align: center; /* Center the text */
        height: 150px; /* Set a height to accommodate vertical text */
    }
    .xander {
        font-weight: bold;
    }
    .xander {
        border-collapse: collapse;
    }
    .xander td{
        border: none;
    }
</style>

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Operator Daily Averages') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Filter Form for Date Range -->
                            <form action="{{ route('admin.tablereport') }}" method="GET" class="mb-3">
                                <div class="form-group row">
                                    <label for="from_date" class="col-sm-2 col-form-label">{{ __('From Date') }}:</label>
                                    <div class="col-sm-2">
                                        <input type="date" class="form-control" name="from_date" value="{{ $from_date->format('Y-m-d') }}">
                                    </div>
                                    <label for="to_date" class="col-sm-2 col-form-label">{{ __('To Date') }}:</label>
                                    <div class="col-sm-2">
                                        <input type="date" class="form-control" name="to_date" value="{{ $to_date->format('Y-m-d') }}">
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="margin-left: 100px;">{{ __('Filter') }}</button>
                                </div>
                            </form>
                            <br>
                            <h2 id="product"> 
                                    {{__('Product') }}
                            </h2>
                            <!-- Scrollable Table Container -->
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sortedOperators as $id => $operator) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $date }}&to_date={{ $date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                            {{ isset($averages[$id][$date->format('Y-m-d')]) ? number_format($averages[$id][$date->format('Y-m-d')], 1) : '-' }}
                                                        </a>
                                                    </td> <!-- Format to 1 decimal places -->
                                                    @endforeach
                                                    <td>
                                                        <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $from_date }}&to_date={{ $to_date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                            {{ isset($averages[$id]['Total']) ? number_format($averages[$id]['Total'], 1) : '-' }}
                                                        </a>
                                                    </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <h2 id="script"> {{__('Script') }}</h2>
                            <!-- Scrollable Table Container -->
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($scriptOperators as $id => $operator) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $date }}&to_date={{ $date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                            {{ isset($averages_script[$id][$date->format('Y-m-d')]) ? number_format($averages_script[$id][$date->format('Y-m-d')], 1) : '-' }}
                                                        </a>
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $from_date }}&to_date={{ $to_date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                        {{ isset($averages_script[$id]['Total']) ? number_format($averages_script[$id]['Total'], 2) : '-' }}
                                                    </a>
                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <h2 id="like"> {{__('Like') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($likeOperators as $id => $operator) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $date }}&to_date={{ $date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                            {{ isset($table_likes[$id][$date->format('Y-m-d')]) && $table_likes[$id][$date->format('Y-m-d')] > 0 ? number_format($table_likes[$id][$date->format('Y-m-d')], 0) : '-' }}
                                                        </a>
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $from_date }}&to_date={{ $to_date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                        {{ isset($table_likes[$id]['total']) ? number_format($table_likes[$id]['total'], 2) : '0' }}
                                                    </a>
                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <h2 id="punishment"> {{__('Punishment') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($punishmentOperators as $id => $operator) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $date }}&to_date={{ $date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                            {{ isset($table_punishment[$id][$date->format('Y-m-d')]) && $table_punishment[$id][$date->format('Y-m-d')] < 0 ? number_format($table_punishment[$id][$date->format('Y-m-d')], 0) : '-' }}    
                                                        </a>
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    <a href="https://sms.sddev.uz/admin/products?operator={{ $id }}&from_date={{ $from_date }}&to_date={{ $to_date }}&date={{ $date->format('Y-m-d') }}" style="text-decoration: none; color: inherit;">
                                                        {{ isset($table_punishment[$id]['total']) ? number_format($table_punishment[$id]['total'], 0) : '0' }}
                                                    </a>
                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <h2  id="workly"> {{__('Workly') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($worklyID as $id) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row $rowInde
                                            @endphp
                                            <tr>
                                                <td>{{ $pivot[$id]['fullname'] }}</td>
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                            {{ isset($pivot[$id][$date->format('Y-m-d')]) ? $pivot[$id][$date->format('Y-m-d')]['time_diff'] : '-' }}    
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <br>
                            <h2 id="personal_missed"> {{__('Personal missed') }}</h2>
                            <div style="overflow-x: auto;" >
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sortedDestinationNumbers as $id) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $userJsonArray[$id]['name'] }}</td>  
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        {{ 
                                                            isset($groupedData[$date->format('Y-m-d')][$id]) ? 
                                                            number_format($groupedData[$date->format('Y-m-d')][$id], 0) : 
                                                            0 
                                                        }}
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    {{ 
                                                        isset($groupedData["Total"][$id]) ? 
                                                        number_format($groupedData["Total"][$id], 0) : 
                                                        0 
                                                    }}
                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <br>
                            <h2 id="unreg_calls"> {{__('Unregistered calls') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unregData as $operator => $row) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $userJsonArray[$operator]['name'] }}</td>  
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        {{ 
                                                            isset($row[$date->format('Y-m-d')]["inbound"]) ? 
                                                            number_format($row[$date->format('Y-m-d')]["inbound"], 0) : 
                                                            0 
                                                        }}/
                                                        {{ 
                                                            isset($row[$date->format('Y-m-d')]["outbound"]) ? 
                                                            number_format($row[$date->format('Y-m-d')]["outbound"], 0) : 
                                                            0 
                                                        }}
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    {{ 
                                                        isset($row["Total"]["inbound"]) ? 
                                                        number_format($row["Total"]["inbound"], 0) : 
                                                        0 
                                                    }} / 
                                                    {{ 
                                                        isset($row["Total"]["outbound"]) ? 
                                                        number_format($row["Total"]["outbound"], 0) : 
                                                        0 
                                                    }}

                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <br>
                            <h2 id="online_times"> {{__('Online times') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($onlineTime as $operator => $row) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $userJsonArray[$operator]['name'] }}</td>  
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        {{ 
                                                            isset($row[$date->format('Y-m-d')]) ? 
                                                            number_format($row[$date->format('Y-m-d')]/3600, 1) : 
                                                            0 
                                                        }}
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    {{ 
                                                        isset($row["Total"]) ? 
                                                        number_format($row["Total"]/3600, 1) : 
                                                        0 
                                                    }} 

                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <br>
                            <h2 id="marks_count"> {{__('Marks count') }}</h2>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($marksCount as $operator => $row) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>  
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        {{ 
                                                            isset($row[$date->format('Y-m-d')]['count']) ? 
                                                            number_format($row[$date->format('Y-m-d')]['count'], 0) : 
                                                            0 
                                                        }}
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    {{ 
                                                        isset($row["TotalCount"]) ? 
                                                        number_format($row["TotalCount"], 0) : 
                                                        0 
                                                    }} 

                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <br>
                            <h2 id="marks3"> {{__('Marks 3') }}</h2>
                            <div style="overflow-x: auto;" >
                                <table class="table table-bordered xander">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Operator') }}</th>
                                            @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                <th class="vertical-text" style="padding: 10px">{{ $date->format('Y-m-d') }}</th>
                                            @endforeach
                                            <th>{{__('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($marks as $operator => $row) <!-- Adjusted for associative array -->
                                            @php
                                                // Determine the row index
                                                $rowIndex = $loop->index; // or you can use a separate counter if needed
                                            @endphp
                                            <tr style="background-color: {{$rowIndex < 3 ? '#6add6a' : ($rowIndex < 6 ? '#f1dc48' : ($rowIndex < 9 ? '#f19648' : '#f16363')) }}">
                                                <td>{{ $operator }}</td>  
                                                @foreach (Carbon\CarbonPeriod::create($from_date, $to_date) as $date)
                                                    <td style="border-width: 1px 0; padding: 10px 0; text-align: center; align-items: center;">
                                                        {{ 
                                                            isset($row[$date->format('Y-m-d')]['mark3']) ? 
                                                            number_format($row[$date->format('Y-m-d')]['mark3']*3, 0) : 
                                                            0 
                                                        }}
                                                    </td> <!-- Format to 2 decimal places -->
                                                @endforeach
                                                <td>
                                                    {{ 
                                                        isset($row["TotalMark3"]) ? 
                                                        number_format($row["TotalMark3"]*3, 0) : 
                                                        0 
                                                    }} 

                                                </td> <!-- Format total to 2 decimal places -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- Scrollable Table Container -->
                        </div>
                        
                        <!-- /.card-body -->
                        <div class="card-footer clearfix">
                            <!-- Pagination could go here if required -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
  var averages = {!! json_encode($averages) !!};
  console.log(averages); // Check structure in console
</script>
@endsection
