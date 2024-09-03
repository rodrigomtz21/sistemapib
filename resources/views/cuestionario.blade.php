<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<h1>cuestionario</h1>

<ul class="nav nav-tabs" id="myTab" role="tablist">
@foreach($ejes as $eje)
    <li class="nav-item">
	    <a class="nav-link" id="{{$eje->id}}-tab" data-toggle="tab" href="#id{{$eje->id}}" role="tab" aria-controls="{{$eje->id}}" aria-selected="true">{{ $eje->eje }}</a>
	</li>
@endforeach
</ul>


<div class="tab-content" id="myTabContent">
	@foreach($ejes as $eje)
	    <div class="tab-pane fade show" id="id{{ $eje->id }}" role="tabpanel" aria-labelledby="{{ $eje->id }}-tab">
		  	<div class="row">
		  	<h1>{{ $eje->eje }}</h1>
		  		<?php
					$preguntas = DB::connection('mysql')->table('subje_nivel3')
					->join('subje_nivel2', 'subje_nivel3.id_subeje2', '=', 'subje_nivel2.id')
					->where('subje_nivel2.id_eje', '=', $eje->id)
					->select('subje_nivel3.id', 'subje_nivel3.nombre')->distinct()->get();
					//dd($preguntas);
				?>
				@foreach($preguntas as $pregunta)
					<?php
						$respuestas = DB::connection('mysql')->table('respuestas')
						->where('id_nivel3', '=', $pregunta->id)
						->select('id', 'nombre')->distinct()->get();
						//dd($preguntas);
					?>
				    <!--div class="form-group col-md-4">
				      <label for="inputState">{{ $pregunta->nombre }}</label>
				      <select id="p-{{ $pregunta->id }}" class="form-control">
				        <option selected>Choose...</option>
				        @foreach($respuestas as $respuesta)	
							<option>{{ $respuesta->nombre }}</option>
						@endforeach
				      </select>
				    </div-->

				    <div class="col-md-12">
				  		<label>{{ $pregunta->nombre }}</label>
				  		@foreach($respuestas as $respuesta)
				  			<div class="form-check">
							  <input class="form-check-input" type="radio" name="name{{ $pregunta->id }}" id="res{{ $respuesta->id }}" value="option1">
							  <label class="form-check-label" for="res{{ $respuesta->id }}">
							    {{ $respuesta->nombre }}
							  </label>

							</div>	
						@endforeach
				  		
		
				  	</div>
				@endforeach
		  		
		  	</div>

		  	<!--div class="col-md-4">
		  		<label>Pregunta</label>
		  		<div class="form-check">
				  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1">
				  <label class="form-check-label" for="exampleRadios1">
				    Respuesta 4
				  </label>
				</div>		
		  	</div-->
		  	

		</div>
	@endforeach
  


</div>