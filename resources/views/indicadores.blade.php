
<style type="text/css"> 
  .td-class{
    border: 4px thick #FFFFFF;
    
  }
  th{
    font-size: 9!important;
  }
  tr{
    font-size: 9;
  }
  .tab-header{
    font-size: 10;
  }
  .th-title{
    text-align: center;
    height: 20px;
    color: #ffffff;
    background-color: #104E8B;
  }
  .table-striped tbody tr:nth-of-type(odd) {
      background: #66FF66;
  }
  .tbody-class tr:nth-child(odd) {
     background: #66FF66;
  }
  .muy-alto{
    /*background: rgb(102, 255, 102);*/
    color: #ffffff !important;
    background: #6B8E23;
  }
  .alto{
    /*background: rgb(255, 255, 0);*/
    
    background: #66FF66;
  }
  .intermedia{
    /*background: rgb(218, 150, 148);*/
    
    background: #ffff00;
  }
</style> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <table class="table-striped">
      <thead class="">
        <tr>
          <?php $size = count($data['cabeceras']); ?>
          <th colspan="{{$size}}" class="th-title">{{ $data['title'] }}<th>
        </tr>
        <tr>
          
          <th colspan="{{$size}}" class="th-title"> <th>
        </tr>
        <tr>

          @foreach($data['cabeceras'] as $header)
              <th class="tab-header" scope="col">{{ $header['title'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
          @foreach($data['consulta'] as $column)
          
              <tr>
                  @foreach($data['cabeceras'] as $header)
                    
                    <td class="td-class" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['data']] }}</td>
                  @endforeach
              </tr>
          @endforeach
      </tbody>
  </table>