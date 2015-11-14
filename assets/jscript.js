function countdown(){	
	var timer = new Date();
	var day = timer.getDate();
	var month = timer.getMonth()+1;
	if(month<10) month="0"+month;
	var year = timer.getFullYear();
	var hour = timer.getHours();
	if(hour<10) hour="0"+hour;
	var minute = timer.getMinutes();
	if(minute<10) minute="0"+minute;
	var second = timer.getSeconds();
	if(second<10) second="0"+second;

	document.getElementById("timer").innerHTML= day+"."+month+"."+year+" - godzina: "+hour+":"+minute+":"+second;
	setTimeout("countdown()",1000);
}
	var xmlHttp = createXmlHttpRequestObject();

function createXmlHttpRequestObject(){
	var xmlHttp;

	if(window.ActiveXObject){
		try{
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}catch(e){
			xmlHttp = false;
		}
	}else{
		try{
			xmlHttp = new XMLHttpRequest();
		}catch(e){
			xmlHttp = false;
		}
	}
	if(!xmlHttp)
		alert("Impossible to create an object!");
	else
		return xmlHttp;
}

function process(){
	if(xmlHttp.readyState==0 || xmlHttp.readyState==4){
		userInput= encodeURIComponent(document.getElementById("userInput").value);
		xmlHttp.open("GET", "/Ad9bisCMS/ajax.php?userInput=" + userInput, true);
		xmlHttp.onreadystatechange = handleServerResponse;
		xmlHttp.send(null);
	}else{
		setTimeout('process()',1000);
	}
}
function handleServerResponse(){
	if(xmlHttp.readyState==4){
		if(xmlHttp.status==200){
			xmlResponse = xmlHttp.responseXML;
			xmlDocumentElement = xmlResponse.documentElement;
			message = xmlDocumentElement.firstChild.data;
			document.getElementById("underInput").innerHTML ='<span style="color:brown">' + message + '</span>';
			setTimeout('process()',1000);
		}else{
			alert('Something went wrong!');
		}
	}
}

function wyswietlAlert(){
	if(document.log.login.value=="" && document.log.password.value==""){
		alert("Nie wypełniono żadnego pola!");
	}
	else if(document.log.login.value==""){
		alert("Nie wpisano pola \"Login\"!");
	}
	else if(document.log.password.value==""){
		alert("Nie wpisano pola \"Hasło\"!");
	}
}

function opacityUp(){
	document.getElementById("next").style.opacity = "1";
	document.getElementById("previous").style.opacity = "1";
}

function opacityDown(){
	document.getElementById("next").style.opacity = "0.5";
	document.getElementById("previous").style.opacity = "0.5";
}

function showAlert(){
	if(document.form.quantity.value==""){
		alert("Proszę podać ilość produktu do zapisania!");
		return false;
	}else if(document.form.text.value==""){
		alert("Proszę podać nową nazwę produktu!");
		return false;
	}
}	

function focus(){
	var el = document.getElementById("quantityAdd");
	el.focus();
}

function ajax_select_json(){
	var id = document.getElementById("idValue");
	var hr = new XMLHttpRequest();

	hr.open("POST", "/Ad9bisCMS/controllers/jsonController.php", true);
	hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	hr.onreadystatechange = function() {
		if(hr.readyState == 4 && hr.status ==200) {
			var data = JSON.parse(hr.responseText);
			for(var obj in data){
				if(data.nameLinux!=''){
					if(data.quantityOgicom==data.quantityLinux){
						jsonDiv.innerHTML = "<hr />Nazwa: <b>" + data.nameLinux + "</b>.<br>"; 
						productQuant.innerHTML = 'Ilość w obu panelach';
						document.getElementById("fastValue").value = data.quantityLinux ; 
					}else{
						jsonDiv.innerHTML = "<hr />Nazwa: <b>" + data.nameLinux + "</b>.<br>"; 
						jsonDiv.innerHTML += "Ilość w starej bazie: <b>" + data.quantityOgicom + "</b><br>"; 
						jsonDiv.innerHTML += "Ilość w nowej bazie: <b>" + data.quantityLinux + "</b><hr />"; 
					}
				}else{
					jsonDiv.innerHTML = '<h3>W bazie nie istnieje produkt o podanym numerze ID!</h3>';
				}
			}
		}
	}
	hr.send("jsonId=" + id.value);
	jsonDiv.innerHTML = 'proszę czekać...'+"<br>";
}

function ajax_update_json(){
	var id = document.getElementById("idValue");
	var quantity = document.getElementById("fastValue");
	var results = document.getElementById("jsonDiv");
	var hr = new XMLHttpRequest();

	hr.open("POST", "/Ad9bisCMS/controllers/jsonController.php", true);
	hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	hr.onreadystatechange = function() {
		if(hr.readyState == 4 && hr.status ==200) {
			var data = JSON.parse(hr.responseText);
			for(var obj in data){
				jsonDiv.innerHTML = "Operacja zakończona powodzeniem!" + "<br />"; 
				jsonDiv.innerHTML += "ID: <b>" + data[obj].propertyA ; 
				jsonDiv.innerHTML += " - poprzednia ilość: " + data[obj].propertyC; 
				jsonDiv.innerHTML += "</b> ; nowa ilość: <b>" + data[obj].propertyB + "</b>.<hr />"; 
			}
		}
	}
	hr.send("jsonId=" + id.value + "&jsonQuantity=" + quantity.value);
	results.innerHTML = "proszę czekać..."+"<br>";
}

function autoSuggestNew(){
	var autoSuggestVal = $('#autoSuggest').val();
	var autoSuggestManufacturer = $('#orderNumber').val();
	var autoSuggestCategory = $('#categoryNumber').val();
	if (autoSuggestManufacturer ==''){
		autoSuggestManufacturer = 'notSelected';
	}
	if (autoSuggestCategory ==''){
		autoSuggestCategory = 'notSelected';
	}
	if (autoSuggestVal !=''){
		$.ajax({
			url: "/Ad9bisCMS/controllers/jsonController.php",
			type: "get",
			data: {
				"productQuery": autoSuggestVal,
				"manufacturer": autoSuggestManufacturer,
				"category": autoSuggestCategory,
			},
			success: function(result)
			{
				var jsonData = JSON.parse(result);
				switch(jsonData){
					case 'tooShort':
					$('#autoSuggest-container').html('Zbyt krótka fraza do wyszukania!');
					break;

					case 'null':
					$('#autoSuggest-container').html('Nie znaleziono produktu z nazwą: <b>'+autoSuggestVal+'</b>.');
					break;

					default:
					var arr = jsonData.map(function(object){ return 'ID: '+object.id+' ->'+object.name + '-> ilość: ' +object.quantity});
					var number = $(jsonData).length;
					if(number==1){
						$('#autoSuggest-container').html('Znaleziono 1 produkt z nazwą: "'+autoSuggestVal+'"');
					} else if(number>1 && number <5){
						$('#autoSuggest-container').html('Znaleziono '+number + ' produkty z nazwą: '+autoSuggestVal);
					} else {
						$('#autoSuggest-container').html('Znaleziono '+number + ' produktów z nazwą: '+autoSuggestVal);
					}
					$('#autoSuggest').autocomplete({
						source: arr,
						select: function (event, ui) {
							ui.item.value=ui.item.value.split("->")[1];
						}
					});
				}
			}
		});
	}
}

$(document).ready(function(){
	$('input').focus(function(){
		$(this).css("background-color","#abeeed");
	});
	$('input').blur(function(){
		$(this).css("background-color","#ffffff")
	});
});
