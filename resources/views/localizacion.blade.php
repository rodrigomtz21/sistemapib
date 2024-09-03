
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
  .alto-vocacion{
    color: #ffffff;
    background: #165A0E;
  }
  .intermedio-vocacion{
    background: #f9fc7d;
  }
  .bajo-vocacion{
    background: #fff;
  }
  .normal-vocacion{
    background: #FFDEAD;
  }
  .predominante-vocacion{
    background: #B0F1B5;
  }
</style> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
@foreach($data['name_tables'] as $name)
  <table class="">
      <thead class="">
        <tr>
          <?php $size = count($data['headers']); ?>
          <th colspan="{{$size}}" class="th-title">{{ $name['nickname'] }}<th>
        </tr>
        <tr>

          @foreach($data['headers'] as $header)
              <th class="tab-header" scope="col">{{ $header['header'] }}</th> 
          @endforeach
        </tr>
      </thead>
      <tbody class="tbody-class">
        <?php $contador = 0; ?>
        <?php $class = $data['clases'];?>
          @foreach($data['consulta'][$name['name']] as $column)
          
              <tr>
                  @foreach($data['headers'] as $header)
                    
                    <td class="td-class {{ $class[$name['name']][$contador][$header['header']] }}" style="mso-number-format:'0.00';mso-number-format:'#,##0.00';">{{ $column[$header['header']] }}</td>
                  @endforeach
              </tr>
              <?php $contador++; ?>
          @endforeach
      </tbody>
  </table>
@endforeach