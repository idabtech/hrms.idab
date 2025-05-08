<table>
    <tr>
        <th>
            <div class="heading">
              <h1><b> FORM D  </b></h1>
              <h2> ATTENDANCE REGISTER </h2>
            </div>
        </th>
    </tr>
    <tr>
        <th>
            <div class="sub-heading">
                <h1>Name of Establishment:{{\Auth::user()->name}}</h1>
                @if(isset($company_address))
                  <h2>{{$company_address->value}}</h2>
                @endif
            </div>
        </th>
    </tr>
    <tr>
        <th>
            <h1><b>Name of the Principal Employer : 
                {{\Auth::user()->name}}</b></h1>
            @if(isset($company_address))
             <h3><b>{{$company_address->value}}</b></h3>
            @endif
        </th>
    </tr>
    <tr>
        <th>
            <h1><b>Site Location:  {{\Auth::user()->name}}</b></h1>@if(isset($company_address))<h3><b>{{$company_address->value}}</b></h3>@endif
        </th>
    </tr>
    <tr>
        <th>
            <h1><b>LIN : 1234567890</b></h1>
        </th>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th rowspan="2">Sr no. of Employee Register</th>
            <th rowspan="2">Name</th>
            <th rowspan="2">Relay # or Set  Work</th>
            <th rowspan="2">Place of Work* </th>
            <th colspan="{{$total_date}}">Date</th>
            <th rowspan="2">Summary No Of Days</th>
            <th rowspan="2">Remarks No Of Hours</th>
            <th rowspan="2">Signature Of Register Keeper</th>
        </tr>
        <tr>
            @for ($i = 1; $i <= $total_date; $i++)
              <th>{{$i}}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @php $j=0 @endphp
        @foreach ($attendance_details as $key => $item)
            @php $j++; @endphp
            <tr>
                <td rowspan="2">{{$j}}</td>
                <td rowspan="2">{{$item['name']}}</td>
                <td rowspan="2">-</td>
                <td rowspan="2">{{ (isset($company_address)) ? $company_address->value : '-'}}</</td>
                <th colspan="{{$total_date}}"></th>
                <td rowspan="2">-</td>
                <td rowspan="2">-</td>
                <td rowspan="2">-</td>
            </tr>
            <tr>
                @for ($i = 1; $i <= $total_date; $i++)
                @php $day = ($i < 9) ? "0".$i : $i @endphp
                <td>
                    {{$item['attendence'][$year.'-'.$month.'-'.$day]}}
            </td>
            @endfor
            </tr>
        @endforeach
    </tbody>
</table>