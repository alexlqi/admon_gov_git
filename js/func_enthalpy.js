//Objeto con las funciones enthalpy
var enthalpy={
	ajax:function(url,datos,tipo,callback){
		$.ajax({
			url:url,
			cache:false,
			type:tipo,
			data:datos,
			success: function(r){
				if(typeof callback != "undefined"){
					callback(r);
				}else{
					console.log(r);
				}
			}
		});
	},
	
	json2option:function(d,mtx){
		//mtx={value:'',name:''};
		opt = ['<option disabled selected value="0">Elige empresa</option>'];
		$.each(d,function(i,v) {
			//console.log("mtx (length): " + arr.length + "; " + v[mtx.value] + " - " + v[mtx.nombre]);
			opt.push('<option value="' + v[mtx.value] + '">' + v[mtx.nombre] + '</option>');
		});
		return opt.join("");
	},
	
	cotejarArrId:function(d,campo){
		var mtxId=[];
		$.each(d,function(i,v){
			mtxId[v[campo]]=i;
		});
		return mtxId;
	},
	
	rellenarCampos:function(d,puntero){
		puntero = (typeof puntero != "undefined" || puntero !="") ? puntero : document ;
		if(typeof d != 'undefined'){
			$.each(d,function(i,v){
				i="."+i;
				$(puntero).find(i).val(v);
			});
		}else{
			$(puntero).get(0).reset();
			console.log('rellenarCampos: El parámetro está vacío');
		}
	},
	
	//json to array
	json2array:function(json){
		arr = $.map(json, function(el) { return el; });
		return arr;
	}
}

//insertar la alertas y notificaciones
$(document).ready(function(e) {
	$("<span style='color:#FF0000'>*</span>").insertAfter($(".requerido"));
	
	if(!$(".alerta_wrap").length){
		$('<style>.alerta_wrap{cursor:pointer;top:0;left:0;background-color:rgba(0,0,0,0.4);height:100%;width:100%;position:fixed;z-index:9999;display:table;}.alerta_cell{display:table-cell;height:100%;width:100%;vertical-align:middle;}.alerta_elem{min-height:10%;min-width:17.786%;max-height:40%;max-width:80%;display:inline-block;position:relative;background-color:#FFF;border:2px solid #03F;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);}.alerta_header{padding:1%;background-color:#09F;color:#FFF;font-size:1.5em;font-weight:bold;height:20%;}.alerta_content{padding:1%;height:80%;font-size:1.1em;color:#444;word-wrap:break-word;}</style><div class="alerta_wrap"><div class="alerta_cell" align="center"><div class="alerta_elem"><h2 class="alerta_header"></h2><p class="alerta_content"></p></div></div></div>').appendTo("article");
		$(".alerta_wrap").hide();
		$(".alerta_wrap").click(function(e) {
			$(this).fadeToggle('fast');
			if($(this).is(":visible")){
				$("html").css("overflow","hidden");
			}else{
				$("html").css("overflow","normal");
			}
		});
	}
	
	if(!$(".notificacion_wrap").length){
		$('<style>.notificacion_wrap{position:fixed;z-index:9998;bottom:1%;right:1%;max-width:90%;}.notificacion_content{font-size:1.1em;display:block;background-color:#093;color:#FFF;margin-bottom:1%;padding:2% 5%;}</style><div class="notificacion_wrap"></div>').appendTo("article");
		$(".notificacion_wrap").hide();
		$(".notificacion_wrap").click(function(e) {
			$(this).fadeToggle('fast');
		});
	}
	setInterval(function(){
		if($(".notificacion_wrap").children().length == 0){
			$(".notificacion_wrap").hide();
		}
	},1000);
}); /*termina el document ready para alertas y notificaciones*/
function alerta(header,content){
	$(".alerta_header").html(header);
	$(".alerta_content").html(content);
	$("html").css("overflow","hidden");
	if(typeof callback != 'undefined'){
		$(".alerta_wrap").fadeIn('slow');
	}else{
		$(".alerta_wrap").fadeIn('slow');
	}
}
function notificacion(configs){
	var notifid="notif_"+Date.now();
	var cfg={
		'content':'Texto de la notificación',
		'dismiss':3000,
	}
	if(typeof configs=='object'){
		$.each(configs,function(i,v){
			cfg[i]=v;
		});
	}
	$('<p class="notificacion_content '+notifid+'" style="display:none;">'+cfg.content+'</p>').appendTo(".notificacion_wrap");
	$('.notificacion_wrap').show();
	$("."+notifid).fadeIn('normal',function(){
		setTimeout(function(){
			$("."+notifid).fadeOut('slow',function(){
				$("."+notifid).remove();
			});
		},cfg.dismiss);
	});
}

//funciones que solo funcionan con jQuery para añadirlas
if(typeof $ != "undefined"){
	//NOTA: se debe regresar this para que elem tenga el elemento usado
	jQuery.fn.extend({
		conEnter:function(callback){
			this.keyup(function(e){
				if(e.keyCode==13){
					if(typeof callback != "undefined"){callback($(this));}
				}
			});
			return this;
		}
	});
}