<div class="hover">
  <div class="tabla full_wh">
    <div class="celda full_wh" align="center">
	  <style>
	  #login{
		  display:inline-block;
		  padding:20px;
		  background-color:#FFF;
		  -webkit-border-radius: 6px;
		  -moz-border-radius: 6px;
		  border-radius: 6px;
		  min-width:inherit;
		  text-align:center;
		  border:2px solid #EEE;
	  }
	  #login input{
		  margin:5px;
	  }
	  #logresp{
		  width:150px;
		  margin:0 auto;
		  text-align:center;
	  }
      </style>
	<script type="text/javascript">
    $(document).ready(function(e) {
		$(".login").click(function(e) {
            data=$("#login").serialize();
			enthalpy.ajax('scripts/login.php',data,'POST',function(r){
				if(!r.err){
					location.reload();
				} else {
					$("#logresp").html(r.msg);
				}
			});
		});
		$("form").keyup(function(e){
			if(e.keyCode==13){
				data=$("#login").serialize();
				enthalpy.ajax('scripts/login.php',data,'POST',function(r){
					if(!r.err){
						location.reload();
					} else {
						$("#logresp").html(r.msg);
					}
				});
			}
			$("#logresp").html('');
		});
    });
    </script>
      <form id="login" class="sombra1">
      	<input type="hidden" name="ctrl" value="login" />
        <input type="text" name="user" placeholder="Usuario" /><br />
        <input type="password" name="pass" placeholder="Password" /><br />
        <input class="login" type="button" value="Inicar Sesión" />
        <p id="logresp"></p>
      </form>
    </div>
  </div>
</div>