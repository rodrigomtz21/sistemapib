
<style type="text/css"> 
  .td-class{
    /*width: 30px;
    height: 20px;*/
    border-left: 4px thick #FFFFFF;
    border-right: 4px thick #FFFFFF;
    border-top: 1px thick #FFFFFF;
    border-bottom: 1px thick #FFFFFF;
    
  }
  th{
    font-size: 9!important;
  }
  tr{
    font-size: 9;
  }
  .tab-header{
    /*height: 25px;*/
    font-size: 10;
  }
  .th-title{
    text-align: center;
    height: 20px;
    color: #ffffff;
    background-color: #104E8B;
  }
  .table-striped tbody tr:nth-of-type(odd) {
      /*background: #66FF66;*/
  }
  .tbody-class tr:nth-child(odd) {
     /*background: #66FF66;*/
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
  .neutro{
    /*background: rgb(218, 150, 148);*/
    background: #FFD39B;
  }
  .negativo{
    color: #ffffff !important;
    background: #c00000;
    
  }
  .muy-alto, .alto, .intermedia, .neutro, .negativo{
    border-color: #ffffff;
  }
</style> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
@if($data['valoracion_status'])
  <table class="table-striped">
      <thead class="">
        <tr>
          <?php $size = count($data['headers']); ?>
          <th colspan="{{$size}}" class="th-title">{{ $data['title_string'] }}<th>
        </tr>
        <tr>
          
          <th colspan="{{$size}}" class="th-title"> {{$data['valoracion']['title_valores']}} <th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['valoracion']['clases'];?>
          @foreach($data['valoracion']['data'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endif

@if($data['participacion_status'])
  <table class="table-striped">
      <thead class="">
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['title_string']}}</th>
        </tr>
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['participacion']['title_participacion']}}</th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['participacion']['clases'];?>
          @foreach($data['participacion']['data'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endif

@if($data['variacion_status'])
  <table class="table-striped">
      <thead class="">
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['title_string']}}</th>
        </tr>
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['variacion']['title_variacion']}}</th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['variacion']['clases'];?>
          @foreach($data['variacion']['data'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endif

@if($data['contribucion_status'])
  <table class="table-striped">
      <thead class="">
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['title_string']}}</th>
          </tr>
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['contribucion']['title_participacion_puntos']}}</th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['contribucion']['clases_participacion_puntos'];?>
          @foreach($data['contribucion']['data_participacion_puntos'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>

  <table class="table-striped">
      <thead class="">
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['title_string']}}</th>
        </tr>
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['contribucion']['title_participacion_porcentaje']}}</th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['contribucion']['clases_participacion_porcentaje'];?>
          @foreach($data['contribucion']['data_participacion_porcentaje'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endif

@if($data['diferencia_status'])
  <table class="table-striped">
      <thead class="">
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['title_string']}}</th>
        </tr>
        <tr>
          <th colspan="{{count($data['headers'])}}" class="th-title">{{$data['diferencia']['title_diferencia']}}</th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['nickname'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['diferencia']['clases'];?>
          @foreach($data['diferencia']['data'] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endif