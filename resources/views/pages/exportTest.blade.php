@extends('app')

@section('content')
    <script src="js/excellentexport.js"></script>


    <h1>ExcellentExport.js</h1>

    Check on <a href="http://jordiburgos.com">jordiburgos.com</a> and  <a href="https://github.com/jmaister/excellentexport">GitHub</a>.

    <h3>Test page</h3>

    <br/>

    <a download="somedata.xls" href="#" onclick="return ExcellentExport.excel(this, 'datatable', 'Sheet Name Here');">Export to Excel</a>
    <br/>

    <a download="somedata.csv" href="#" onclick="return ExcellentExport.csv(this, 'datatable');">Export to CSV</a>
    <br/>

    <table id="datatable">
        <tr>
            <th>Column 1</th>
            <th>Column "cool" 2</th>
            <th>Column 3</th>
        </tr>
        <tr>
            <td>100,111</td>
            <td>200</td>
            <td>300</td>
        </tr>
        <tr>
            <td>400</td>
            <td>500</td>
            <td>600</td>
        </tr>
        <tr>
            <td>Text</td>
            <td>More text</td>
            <td>Text with new line</td>
        </tr>
    </table>



@endsection
