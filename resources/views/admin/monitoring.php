<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="robots" content="noindex">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
  	<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
	<script src="https://test17.salesdoc.io/js/axios.min.js"></script>
	<meta charset="UTF-8">
	<link rel="icon" href="../assets/logo.png">
	<title>Monitoring | Sales Doctor</title>
	<style scoped>
		.dot {
			height: 15px;
			width: 15px;
			background-color: #bbb;
			border-radius: 50%;
			display: inline-block;
		}
		.cl-red {
		background-color: #b0160c;
		font-size: 10px !important;
		color: white;
		}
		.cl-warn-red {
			background-color: #EF7C24;
			font-size: 10px !important;
			color: white;
		}
		.v-data-table>.v-data-table__wrapper>table>thead>tr>th {
			padding: 0 12px;
		}
		.switch {
		position: relative;
		display: inline-block;
		width: 60px;
		height: 34px;
		}

		.switch input { 
		opacity: 0;
		width: 0;
		height: 0;
		}

		.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .4s;
		transition: .4s;
		}

		.slider:before {
		position: absolute;
		content: "";
		height: 26px;
		width: 26px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
		}

		input:checked + .slider {
		background-color: #2196F3;
		}

		input:focus + .slider {
		box-shadow: 0 0 1px #2196F3;
		}

		input:checked + .slider:before {
		-webkit-transform: translateX(26px);
		-ms-transform: translateX(26px);
		transform: translateX(26px);
		}

		/* Rounded sliders */
		.slider.round {
		border-radius: 34px;
		}

		.slider.round:before {
		border-radius: 50%;
		}
		.date_ymd{
			font-size: 10px;
			background-color: grey;
			color: white;
			border-radius: 20px;
			padding: 3px;
		}
		.date_none{
			display: none;
		}
	</style>
</head>
<body>
	

<div id="app">
 <template>
  <v-container fluid class="grey lighten-5">
    <v-row class="mb-6" no-gutters>
      	<v-col>
			<v-card
			elevation="2"
			outline
			>
				<v-list-item three-line>
					<v-list-item-content>
						<!-- <div class="text-overline mb-4">
							<h5>Входящие: {{inbounds_5995.length}}</h5>
							<div class="progress">
								<div class="progress-bar" role="progressbar" :style="{width: inGetProg_5995+'%'}" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">{{inTalk_5995.length}}</div>
							</div>
						</div>
						<v-list-item-title class="text-h5 mb-1">
							Ответили: {{inTalk_5995.length}}
						</v-list-item-title>
						<v-list-item-title class="text-h5 mb-1">
							Пропущенные: {{notTalk_5995.length}}
						</v-list-item-title>
						<v-list-item-title class="text-h5 mb-1">
							Время разговора: {{calcHMS(inSumTalk_5995)}}
						</v-list-item-title> -->
						<v-simple-table>
							<template v-slot:default>
								<thead>
									<tr>
										<th>
											<h5>Входящие: {{todayData.answered + todayData.missed}}</h5>
										</th>
										<th class="text-left">Сегодня</th>
										<th class="text-left">На этой неделе</th>
										<th class="text-left">В этом месяце</th>
										
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Всего:</td>
										<td>{{todayData.answered + todayData.missed}}</td>
										<td>{{weekData.answered + weekData.missed}}</td>
										<td>{{monthData.answered + monthData.missed}}</td>
									</tr>
									<tr>
										<td>Ответили:</td>
										<td>{{todayData.answered}}</td>
										<td>{{weekData.answered}}</td>
										<td>{{monthData.answered}}</td>
									</tr>
									<tr>
										<td>Пропущенные:</td>
										<td>{{todayData.missed}} ({{((todayData.missed/(todayData.answered + todayData.missed))*100).toFixed(0)}} %)</td>
										<td>{{weekData.missed}} ({{((weekData.missed/(weekData.answered + weekData.missed))*100).toFixed(0)}} %)</td>
										<td>{{monthData.missed}} ({{((monthData.missed/(monthData.answered + monthData.missed))*100).toFixed(0)}} %)</td>
									</tr>
									<tr>
										<td>Пропущенний в раб. время:</td>
										<td>{{todayData.missed_in}} ({{((todayData.missed_in/(todayData.answered + todayData.missed))*100).toFixed(0)}} %)</td>
										<td>{{weekData.missed_in}} ({{((weekData.missed_in/(weekData.answered + weekData.missed))*100).toFixed(0)}} %)</td>
										<td>{{monthData.missed_in}} ({{((monthData.missed_in/(monthData.answered + monthData.missed))*100).toFixed(0)}} %)</td>
									</tr>
									<tr>
										<td>Среднее время разговора:</td>
										<td>{{calcHMS(todayData.talking_time/(todayData.answered + todayData.missed))}}</td>
										<td>{{calcHMS(weekData.talking_time/(weekData.answered + weekData.missed))}}</td>
										<td>{{calcHMS(monthData.talking_time/(monthData.answered + monthData.missed))}}</td>
									</tr>
									<tr>
										<td>Общее время разговора:</td>
										<td>{{calcHMS(todayData.talking_time)}}</td>
										<td>{{calcHMS(weekData.talking_time)}}</td>
										<td>{{calcHMS(monthData.talking_time)}}</td>
									</tr>
								</tbody>
							</template>
						</v-simple-table>
					
					</v-list-item-content>
				</v-list-item>
			</v-card>
     	</v-col>
     	<v-col>
			<v-card
			class="ml-2"
			elevation="2"
			outline
			>
				<v-list-item three-line>
					<v-list-item-content>
						<!-- <div class="text-overline mb-4">
							<h5>Исходящие: {{outbounds_5995.length}}</h5>
							<div class="progress">
								<div class="progress-bar" role="progressbar" :style="{width: outGetProg_5995+'%'}" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">{{outTalk_5995.length}}</div>
							</div>
						</div>
						<v-list-item-title class="text-h5 mb-1">
							Успешные: {{outTalk_5995.length}}
						</v-list-item-title>
						<v-list-item-title class="text-h5 mb-1">
							Не дозвонились: {{outbounds_5995.length - outTalk_5995.length}}
						</v-list-item-title>
						<v-list-item-title class="text-h5 mb-1">
							Время разговора: {{calcHMS(outSumTalk_5995)}}
						</v-list-item-title> -->
						<v-simple-table>
							<template v-slot:default>
								<thead>
									<tr>
										<th>
											<h5>Исходящие: {{out_todayData.answered + out_todayData.missed}}</h5>
										</th>
										<th class="text-left">Сегодня</th>
										<th class="text-left">На этой неделе</th>
										<th class="text-left">В этом месяце</th>
										
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Всего:</td>
										<td>{{out_todayData.answered + out_todayData.missed}}</td>
										<td>{{out_weekData.answered + out_weekData.missed}}</td>
										<td>{{out_monthData.answered + out_monthData.missed}}</td>
									</tr>
									<tr>
										<td>Успешные:</td>
										<td>{{out_todayData.answered}}</td>
										<td>{{out_weekData.answered}}</td>
										<td>{{out_monthData.answered}}</td>
									</tr>
									<tr>
										<td>Не дозвонились:</td>
										<td>{{out_todayData.missed}} ({{((out_todayData.missed/(out_todayData.answered + out_todayData.missed))*100).toFixed(0)}} %)</td>
										<td>{{out_weekData.missed}} ({{((out_weekData.missed/(out_weekData.answered + out_weekData.missed))*100).toFixed(0)}} %)</td>
										<td>{{out_monthData.missed}} ({{((out_monthData.missed/(out_monthData.answered + out_monthData.missed))*100).toFixed(0)}} %)</td>
									</tr>
									<tr>
										<td>Среднее время разговора:</td>
										<td>{{calcHMS(out_todayData.talking_time/(out_todayData.answered + out_todayData.missed))}}</td>
										<td>{{calcHMS(out_weekData.talking_time/(out_weekData.answered + out_weekData.missed))}}</td>
										<td>{{calcHMS(out_monthData.talking_time/(out_monthData.answered + out_monthData.missed))}}</td>
									</tr>
									<tr>
										<td>Общее время разговора:</td>
										<td>{{calcHMS(out_todayData.talking_time)}}</td>
										<td>{{calcHMS(out_weekData.talking_time)}}</td>
										<td>{{calcHMS(out_monthData.talking_time)}}</td>
									</tr>
								</tbody>
							</template>
						</v-simple-table>
					
					</v-list-item-content>
				</v-list-item>
			</v-card>
			<v-checkbox
				style="margin-top: 2%; margin-left: 1%; width: 10%"
				v-model="filters"
				label="Фильтры"
				@change="!filters"
				class="filtersCheckbox"
			></v-checkbox>
      	</v-col>
    </v-row>
    <v-row v-show="filters">
    	<v-col>
    		<div class="float-right">
				<div class="d-inline-block">
					<label for="" class="mt-2 font-weight-bold">Фильтр:</label>
					<label class="mr-5 ml-4 mb-2">
						 <select class="form-control mt-4" name="propuw" id="propuw" @change="get_by_filter()">
						 	<option value="0">По умолчанию</option>
							<option value="1">Не дозвон.</option>
							<option value="2">Не перезв.</option>
						</select>
					</label>
				</div>

				<input class="form-control" type="date" id="start_date" name="start_date" style="display: inline;width: auto;">
		   		<input class="form-control" type="date" id="get_date" name="get_date" style="display: inline;width: auto;">
		   		<button class="mb-1 btn btn-primary text-white" :loading="loading" type="button" @click="filter()">Поиск</button>
		   	</div>
		   	<div class="float-right">
		   		<div class="d-inline-block">
		   			<v-row>
		   				<v-col>
		   					<button style="margin-right: 20px; border-radius: 8px; padding: 8px; border: 1px solid white; background-color: #28a745; color: white;" onclick="tableToExcel('exportTable','excel','excel')">EXCEL</button>
            				<a id="dlink"  href="" style="display: none"></a>
		   				</v-col>
		   			</v-row>
				</div>
		   		<div class="d-inline-block">
					<label for="" class="mt-4 font-weight-bold">Со вчера: </label>
					<label class="switch mr-5 ml-4 mb-2">
					  <input type="checkbox" id="yesterday" name="yesterday" value="1" @change="get_with_yesterday()">
					  <span class="slider round"></span>
					</label>
				</div>
				<div class="d-inline-block">
					<label for="" class="mt-4 font-weight-bold">Показать все пропущенные: </label>
					<label class="switch mr-5 ml-4 mb-2">
					  <input type="checkbox" id="all_calls" name="all_calls" value="1" @change="get_by_status()">
					  <span class="slider round"></span>
					</label>
				</div>
		   	</div>
    	</v-col>
	</v-row>

	<template>
	  <div class="text-center">
	    <v-overlay :value="loading">
	    	<div class="text-center">
				<v-progress-circular
					:size="50"
			        indeterminate
			        color="primary"
			    ></v-progress-circular>
			</div>  
	    </v-overlay>
	  </div>
	</template>
    <v-row class="mb-6 mt-4" no-gutters>
      <v-col cols="6">
        <v-simple-table>
		    <template v-slot:default>
		      <thead style="border: solid 1px grey;">
		        <tr>
		          	<th class="text-left" width="100px;">Время</th>
		          	<th class="text-left">Номер</th>
		          	<th class="text-left">Кол. пропущ.</th>
		          	<th class="text-left">Кол. перезв.</th>
		          	<th class="text-left">Статус</th>
		          	<th class="text-left">Инициатор</th>
		          	<th class="text-left">Нач. разг.</th>
		          	<th class="text-left">До разг.</th>
		          	<th class="text-left">Дл.(сек)</th>
		        </tr>
		      </thead>
		      <tbody style="border: solid 1px grey;">
		      	<tr v-for="report in pro_infos_5995">
		          	<td>
		          		<span :class="[report.start_stamp_ymd.getFullYear() === today.getFullYear() && report.start_stamp_ymd.getMonth() === today.getMonth() && report.start_stamp_ymd.getDate() === today.getDate() ? 'date_none' : 'date_ymd']">
		          			<span :class="[report.start_stamp_ymd.getFullYear() === today.getFullYear() ? 'date_none' : 'date_ymd']">
		          				{{report.start_stamp_ymd.getFullYear()}}
		          			</span>
		          			{{report.start_stamp_ymd.getDate()}}-{{months[report.start_stamp_ymd.getMonth()]}}
		          		</span>
		          		
		          		<br>{{report.start_stamp}}
		          	</td>
		          	<td>{{report.number}}</td>
		          	<td>{{report.count_pro}}</td>
		          	<td>{{report.count_nedoz}}</td>
		          	<td :class="[report.status == 'Успешно' ? 'cl-green' : report.status == 'Не перезв.' ? 'cl-red' : 'cl-warn-red']">{{report.status}}</td>
		          	<td>{{report.user_call}}</td>
		          	<td>
		          		<span :class="[report.start_talking_ymd.getFullYear() === today.getFullYear() && report.start_talking_ymd.getMonth() === today.getMonth() && report.start_talking_ymd.getDate() === today.getDate() ? 'date_none' : report.start_talking_ymd == my_year ? 'date_none' : 'date_ymd']">
		          			<span :class="[report.start_talking_ymd.getFullYear() === today.getFullYear() ? 'date_none' : 'date_ymd']">
			          			{{report.start_talking_ymd.getFullYear()}}
			          		</span>
		          			{{report.start_talking_ymd.getDate()}}-{{months[report.start_talking_ymd.getMonth()]}}
		          		</span>
		          		<br>{{report.start_talking}}
		          	</td>
		          	<td>{{report.for_talking}}</td>
		          	<td>{{report.talk_time}}</td>
		        </tr>
		      </tbody>
		    </template>
		</v-simple-table>
      </v-col>
      <v-col cols="6">
        <v-simple-table>
		    <template v-slot:default>
		      <thead style="border: solid 1px grey;">
		        <tr>
		          	<th class="text-left" width="150px">имя</th>
		          	<th class="text-left" width="15px">вход. звон</th>
		          	<th class="text-left">время</th>
		          	<th class="text-left" width="15px">исход. звон</th>
		          	<th class="text-left">время</th>
		          	<th class="text-left">общ. вре.</th>
					<th class="text-left">%</th>
					<th class="text-left">👍</th>
					<th class="text-left">☹️</th>
		        </tr>
		      </thead>
		      <tbody style="border: solid 1px grey;">
		      	<tr v-for="report in users_5995">
		          	<td width="230px"><span :id="'num_'+report.num" class="dot mt-2"></span> {{report.name}}</td>
		          	<td width="15px">{{report.vxod_count}}</td>
		          	<td>{{report.vxod_time}}</td>
		          	<td width="15px">{{report.isxod_count}}</td>
		          	<td>{{report.isxod_time}}</td>
		          	<td>{{report.all_time}}</td>
					<td>{{((report.all_time_s/(inSumTalk_5995+outSumTalk_5995))*100).toFixed(2)}}</td>
					<td>{{ feedbacks.mark3[report.num] ?? 0 }}</td>
					<td>{{ feedbacks.mark0[report.num] ?? 0 }}</td>
		        </tr>
		      </tbody>
		    </template>
		</v-simple-table>
		<div class="ml-2">
			<span class="dot mt-2" style="background: blue"></span> - ringing, &nbsp
			<span class="dot mt-4" style="background: yellow"></span> - hangup, &nbsp
			<span class="dot mt-4" style="background: green"></span> - answering, &nbsp
			<span class="dot mt-2" style="background: red"></span> - offline, &nbsp
			<span class="dot mt-4" style="background: #bbb"></span> - online
		</div>
      </v-col>

      <!-- export excel -->
      <v-col style="display: none">
        <v-simple-table style="border-top: solid 1px grey;" id="exportTable">
		    <template v-slot:default>
		      <thead style="border-left: solid 1px grey;">
		        <tr>
		          	<th class="text-left" width="100px;">Время</th>
		          	<th class="text-left">Номер</th>
		          	<th class="text-left">Кол. пропущ.</th>
		          	<th class="text-left">Кол. перезв.</th>
		          	<th class="text-left">Статус</th>
		          	<th class="text-left">Инициатор</th>
		          	<th class="text-left">Нач. разг.</th>
		          	<th class="text-left">До разг.</th>
		          	<th class="text-left">Дл.(сек)</th>
		        </tr>
		      </thead>
		      <tbody style="border-left: solid 1px grey;">
		      	<tr v-for="report in pro_infos_5995">
		          	<td>
		          		<span :class="[report.start_stamp_ymd.getFullYear() === today.getFullYear() && report.start_stamp_ymd.getMonth() === today.getMonth() && report.start_stamp_ymd.getDate() === today.getDate() ? 'date_none' : 'date_ymd']">
		          			<span :class="[report.start_stamp_ymd.getFullYear() === today.getFullYear() ? 'date_none' : 'date_ymd']">
		          				{{report.start_stamp_ymd.getFullYear()}}
		          			</span>
		          			{{report.start_stamp_ymd.getDate()}}-{{months[report.start_stamp_ymd.getMonth()]}}
		          		</span>
		          		
		          		<br>{{report.start_stamp}}
		          	</td>
		          	<td>{{report.number}}</td>
		          	<td>{{report.count_pro}}</td>
		          	<td>{{report.count_nedoz}}</td>
		          	<td :class="[report.status == 'Успешно' ? 'cl-green' : report.status == 'Не перезв.' ? 'cl-red' : 'cl-warn-red']">{{report.status}}</td>
		          	<td>{{report.user_call}}</td>
		          	<td>
		          		<span :class="[report.start_talking_ymd.getFullYear() === today.getFullYear() && report.start_talking_ymd.getMonth() === today.getMonth() && report.start_talking_ymd.getDate() === today.getDate() ? 'date_none' : report.start_talking_ymd == my_year ? 'date_none' : 'date_ymd']">
		          			<span :class="[report.start_talking_ymd.getFullYear() === today.getFullYear() ? 'date_none' : 'date_ymd']">
			          			{{report.start_talking_ymd.getFullYear()}}
			          		</span>
		          			{{report.start_talking_ymd.getDate()}}-{{months[report.start_talking_ymd.getMonth()]}}
		          		</span>
		          		<br>{{report.start_talking}}
		          	</td>
		          	<td>{{report.for_talking}}</td>
		          	<td>{{report.talk_time_Excel}}</td>
		        </tr>
		      </tbody>
		    </template>
		</v-simple-table>
      </v-col>
      <!-- ------------ -->
    </v-row>
	<v-row>
		<v-col cols="11"></v-col>
		<v-col cols="1">
			<a href="/logout" class="mb-2 btn btn-outline-dark float-right">Выйти</a>
		</v-col>
	</v-row>
  </v-container>
 </template>
	

</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script>

	const statusColors = {
		registered: '#bbb',
		unregistered: 'black',
		ringing: 'blue',
		hangup: 'yellow',
		answered: 'green',
		register: '#bbb',
		unregister: 'red',
		pre_register: 'brown',
		register_attempt: 'brown',
	}
	
	const exampleSocket = new WebSocket("wss://pbx12127.onpbx.ru:3342/?key=<?= $auth_key ?>");

	exampleSocket.onopen = function (e) {
		let mes = {
			"command": "subscribe",
			"reqId": "123123",
			"data": {
				"eventGroups": [
					"user_blf",
					"user_registration"
				]
			}
		}
		exampleSocket.send(JSON.stringify(mes))
	}

	exampleSocket.onmessage = function (e) {
		var data = JSON.parse(e.data)
		if (data.event == 'user_blf') {
			let id = "num_"+data.data.uid;
			document.getElementById(id).style.background=statusColors[data.data.status];
		}
		if (data.event == 'user_registration') {
			let id = "num_"+data.data.uid;
			document.getElementById(id).style.background=statusColors[data.data.state];
		}
	}

	var tableToExcel = (function () {
        var uri = 'data:application/vnd.ms-excel;base64,';
        var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>';
        var base64 = function (s) {
            return window.btoa(unescape(encodeURIComponent(s)))
        };
        var format = function (s, c) {
            return s.replace(/{(\w+)}/g, function (m, p) {
                return c[p];
            })
        };
        return function (table, name) {
            if (!table.nodeType) table = document.getElementById(table);
            var ctx = {worksheet: 'Monitoring' || 'Worksheet', table: table.innerHTML};
            document.getElementById("dlink").href = uri + base64(format(template, ctx));
            var filename = new Date().toLocaleDateString("en-GB");
            document.getElementById("dlink").download = 'Monitoring (' + filename + ')';
            document.getElementById("dlink").click();
        }
    })();
	const statuses = {
		SUCCESS: "Успешно",
		NO_ATTEMPT: "Не перезв.",
		DIDNT_ANSWER: "Не дозв."
	};
	new Vue({
	  	el: '#app',
	  	vuetify: new Vuetify(),
	  	data: {
			filters: false,
	  		loading: false,
	  		today: new Date(),
	  		my_year: new Date(1019502000 * 1000),
	  		months: [
	  			"январь",
	  			"февраль",
	  			"март",
	  			"апрель",
	  			"май",
	  			"июнь",
	  			"июль",
	  			"август",
	  			"сентябрь",
	  			"октябрь",
	  			"ноябрь",
	  			"декабрь"
	  		],
	  		interval:null,
	  		calls: [],
	  		users: [],
	  		inbounds_5995: [],
	  		inTalk_5995: [], 
	  		inSumTalk_5995: null,
	  		inGetProg_5995: null,
	  		outbounds_5995: [],
	  		outTalk_5995: [],
	  		outSumTalk_5995:null,
	  		outGetProg_5995: null,
	  		inreports_5995: [],
	  		outreports_5995: [],
	  		real_reports_5995: [],
	  		fifos: [],
	  		users_5995: [],
	  		notTalk_5995: [],
	  		notAnswer_5995: [],
	  		pro_infos_5995: [],
	  		pro_all_infos_5995: [],
	  		data_by_date: [],
			feedbacks: {
				mark0: {},
				mark3: {}
			},
			bigData: [],
			todayData: {},
			weekData: {},
			monthData: {},
			out_todayData: {},
			out_weekData: {},
			out_monthData: {},
	  	},
	  	async mounted () {
			var day = ("0" + this.today.getDate()).slice(-2);
			var month = ("0" + (this.today.getMonth() + 1)).slice(-2);
			var today = this.today.getFullYear()+"-"+(month)+"-"+(day);

			$('#get_date').val(today);
			$('#start_date').val(today);

			this.loading = true;

			await this.get_date();
		    await this.getUsers();
			await this.get_users_feedbacks();
		    this.getInfos_5995();
		    this.getReport_5995();
		    await this.getFifo();
			this.fifoToReport();
			this.set_data_from_date();

			await this.getBigData();
			this.setTable()

			this.loading = false;
	  	},
	  	created(){	

			this.interval = setInterval(() =>{
		      	this.get_date()},30000)

			this.interval = setInterval(() =>{
		      	this.get_users_feedbacks()},600000)

		    this.interval = setInterval(() =>{
		      	this.getInfos_5995()},30000)

		    this.interval = setInterval(() =>{
		      	this.getReport_5995()},30000)

			this.interval = setInterval(() =>{
		      	this.fifoToReport()},30000)

			this.interval = setInterval(() =>{
		      	this.set_data_from_date()},30000)
			
		    if ($("#propuw").val() == 1 || $("#propuw").val() == 2) {
		    	this.interval = setInterval(() =>{
		      		this.get_by_filter()},30000)
		    }
		},
		destroyed(){
		    clearInterval(this.interval)
		},
	  	methods: {
			async filter(){
				await this.get_date();
				await this.get_users_feedbacks();
				this.getInfos_5995();
		    	this.getReport_5995();
				this.fifoToReport();
				this.set_data_from_date();
			},
			async getBigData(){
				await axios.get('monitoring/bigData', {params: {date: this.today.toISOString().split('T')[0]}}).then(response => {
					if (response.status == 200) {
						this.bigData = response.data;
					}
				});	
			},
			setTable(){
				const date = this.getMonday(this.today.toISOString().split('T')[0])
				const mondayTimestamp = Math.floor(date.getTime() / 1000)
				
				this.monthData = {
					answered: 0,
					missed: 0,
					missed_in: 0,
					talking_time: 0
				}
				this.weekData = {
					answered: 0,
					missed: 0,
					missed_in: 0,
					talking_time: 0
				}
				this.out_monthData = {
					answered: 0,
					missed: 0,
					talking_time: 0
				}
				this.out_weekData = {
					answered: 0,
					missed: 0,
					talking_time: 0
				}
				

				for (const datum of this.bigData) {
					if (datum.accountcode == 'inbound') {

						this.monthData.talking_time += datum.user_talk_time
						if (datum.user_talk_time > 0) {
							this.monthData.answered += 1;
						}else{
							this.monthData.missed += 1;
							this.monthData.missed_in += this.checkDateHours(datum.start_stamp)
						}

						if (mondayTimestamp < datum.start_stamp) {
							this.weekData.talking_time += datum.user_talk_time
							if (datum.user_talk_time > 0) {
								this.weekData.answered += 1;
							}else{
								this.weekData.missed += 1;
								this.weekData.missed_in += this.checkDateHours(datum.start_stamp)
							}
						}

					}else{
						this.out_monthData.talking_time += datum.user_talk_time
						if (datum.user_talk_time > 0) {
							this.out_monthData.answered += 1;
						}else{
							this.out_monthData.missed += 1;
						}

						if (mondayTimestamp < datum.start_stamp) {
							this.out_weekData.talking_time += datum.user_talk_time
							if (datum.user_talk_time > 0) {
								this.out_weekData.answered += 1;
							}else{
								this.out_weekData.missed += 1;
							}
						}
					}
					
				}
			},
			checkDateHours(timestamp){
				let date = new Date(timestamp * 1000),
					hours = date.getHours();
				if (hours >= 9 && hours <= 20) {
					return 1
				}else{
					return 0
				}
			},
			getMonday(d) {
				d = new Date(d);
				var day = d.getDay(),
					diff = d.getDate() - day + (day == 0 ? -6 : 1);
				return new Date(d.setDate(diff));
			},
			async get_users_feedbacks(){
				this.feedbacks = {
					mark0: {},
					mark3: {}
				}
				await axios.get('monitoring/usersFeedbacks', {params: {from: $('#start_date').val(), to: $('#get_date').val()}}).then(response => {
					if (response.status == 200) {
						for (const datum of response.data) {
							if (!this.feedbacks.mark0[datum.phone]) {
								this.feedbacks.mark0[datum.phone] = datum.mark0
							}
							if (!this.feedbacks.mark3[datum.phone]) {
								this.feedbacks.mark3[datum.phone] = datum.mark3
							}
						}
					}
				});		
			},
		  	async getUsers(){
				await axios.get('monitoring/users').then(response => {
					if (response.status == 200) {
						this.users = response.data
					}
				});
		  	},
		  	getInfos_5995: function(){
		  		let calls = this.calls;

				// -------------------------------- inbounds_5995 -----------------------------------------
		  		let inbounds_5995 = [];
		  		let inreports_5995 = [];
				for (var j = 0; j < calls.length; j++) {
					if (calls[j].gateway == '712075995' && calls[j].accountcode == 'inbound') {
						inbounds_5995.push(calls[j]);
						let infos = { 
							num: calls[j].destination_number,
							vxod_time: calls[j].user_talk_time
						}
						inreports_5995.push(infos);
					}
				}
				this.inbounds_5995 = inbounds_5995;

				// ===========================================================

				const resultArr = [];

				const groupByNum = inreports_5995.reduce((group, item) => {
					const { num } = item;
					group[num] = group[num] ?? [];
					group[num].push(item.vxod_time);
					return group;
				}, {});

				Object.keys(groupByNum).forEach((num) => {
					let counter = groupByNum[num].length;
					groupByNum[num] = groupByNum[num].reduce((a, b) => a + b);
					resultArr.push({
						'num': num,
						'vxod_time': this.calcHMS(groupByNum[num]),
						'for_all_time': groupByNum[num],
						'vxod_count': counter
					})
				})
				this.inreports_5995 = resultArr;
				
				// ===========================================================

				let countTalk = [];
				let notTalk = [];
				for (var i = 0; i < inbounds_5995.length; i++) {
					if (inbounds_5995[i].user_talk_time != 0) {
						countTalk.push(inbounds_5995[i]);
					}else if (inbounds_5995[i].user_talk_time == 0) {
						notTalk.push(inbounds_5995[i]);
					}	
				}
				this.inTalk_5995 = countTalk;
				this.notTalk_5995 = notTalk;

				let sum = 0;
				for (var k = 0; k < countTalk.length; k++) {
					sum += countTalk[k].user_talk_time;
				}
				this.inSumTalk_5995 = sum

				let inGetProg = ((this.inTalk_5995.length / this.inbounds_5995.length) * 100);
				this.inGetProg_5995 = inGetProg;

				// -------------------------------- outbounds_5995 -----------------------------------------
				
				let outbounds_5995 = [];
				let outreports_5995 = [];
				for (var j = 0; j < calls.length; j++) {
					if (calls[j].gateway == '712075995' && calls[j].accountcode == 'outbound') {
						outbounds_5995.push(calls[j]);
						let infos = { 
							num: calls[j].caller_id_number,
							isxod_time: calls[j].user_talk_time
						}
						outreports_5995.push(infos);
					}
				}
				this.outbounds_5995 = outbounds_5995

				// ===========================================================

				const resultArr1 = [];

				const groupByNum1 = outreports_5995.reduce((group, item) => {
					const { num } = item;
					group[num] = group[num] ?? [];
					group[num].push(item.isxod_time);
					return group;
				}, {});

				Object.keys(groupByNum1).forEach((num) => {
					let counter = groupByNum1[num].length;
					groupByNum1[num] = groupByNum1[num].reduce((a, b) => a + b);
					resultArr1.push({
						'num': num,
						'isxod_time': this.calcHMS(groupByNum1[num]),
						'for_all_time': groupByNum1[num],
						'isxod_count': counter
					})
				})
				this.outreports_5995 = resultArr1;

				// ===========================================================

				let ocountTalk = [];
				let notAnswer = [];
				for (var i = 0; i < outbounds_5995.length; i++) {
					if (outbounds_5995[i].user_talk_time != 0) {
						ocountTalk.push(outbounds_5995[i]);
					}else if (outbounds_5995[i].user_talk_time == 0) {
						notAnswer.push(outbounds_5995[i])
					}
				}
				this.outTalk_5995 = ocountTalk;
				this.notAnswer_5995 = notAnswer;

				let osum = 0;
				for (var k = 0; k < ocountTalk.length; k++) {
					osum += ocountTalk[k].user_talk_time;
				}
				this.outSumTalk_5995 = osum

				let outGetProg = ((this.outTalk_5995.length / this.outbounds_5995.length) * 100);
				this.outGetProg_5995 = outGetProg;
		  	},
		  	getReport_5995: function(){
		  		let vxods1 = this.inreports_5995;
		  		let isxods1 = this.outreports_5995;

				//--------------- bir birida yo'q elementlarni 0 bilan to'ldirish num bo'yicha ---------------
		  		let isxods = this.getMerge(isxods1, vxods1);
			    let vxods = this.getMerge2(vxods1, isxods1);
				//--------------------------------- end -------------------------------------------------------
			    let users = this.users;
			    let reports = [];
			    
				for (var m = 0; m < isxods.length; m++) {
			    	let num = isxods[m].num;
			    	for (var n = 0; n < vxods.length; n++) {
			    		if (num == vxods[n].num) {
			    			let infoss = {
		      					id: num,
		      					vxod_count: vxods[n].vxod_count,
		      					vxod_time: vxods[n].vxod_time,
		      					isxod_count: isxods[m].isxod_count,
		      					isxod_time: isxods[m].isxod_time,
		      					all_time: this.calcHMS(isxods[m].for_all_time+vxods[n].for_all_time),
								all_time_s: isxods[m].for_all_time+vxods[n].for_all_time,
			      			}
			      			reports.push(infoss);
			    		}
			    	}
			    }
			   
			    let reps = [];
			    for (var i = 0; i < users.length; i++) {
			    	let infoss = {
	    				num: users[i].num,
      					name: users[i].name,
      					vxod_count: 0,
      					vxod_time: 0,
      					isxod_count: 0,
      					isxod_time: 0,
      					all_time: 0,
						all_time_s: 0
	      			};
			    	for (var j = 0; j < reports.length; j++) {
			    		if (users[i].num === reports[j].id) {
			    			infoss = {
			    				num: users[i].num,
		      					name: users[i].name,
		      					vxod_count: reports[j].vxod_count,
		      					vxod_time: reports[j].vxod_time,
		      					isxod_count: reports[j].isxod_count,
		      					isxod_time: reports[j].isxod_time,
		      					all_time: reports[j].all_time,
								all_time_s: reports[j].all_time_s
			      			}	
			    		}
			    	}
			    	reps.push(infoss)
			    }
			    var byVxod_count = reps.slice(0);
				byVxod_count.sort(function(a,b) {
				    return b.vxod_count - a.vxod_count;
				});
			    this.real_reports_5995 = byVxod_count
		  	},
		  	calcHMS: function(d){
		  		if (d == 0) {
	      			return 0;
	      		}else {
	      			d = Number(d);
				    var h = Math.floor(d / 3600);
				    var m = Math.floor(d % 3600 / 60);
				    var s = Math.floor(d % 3600 % 60);

				    var hDisplay = h > 0 ? h + (h == 1 ? " час, " : " час, ") : "";
				    var mDisplay = m > 0 ? m + (m == 1 ? " мин, " : " мин, ") : "";
				    var sDisplay = s > 0 ? s + (s == 1 ? " с" : " с") : "";
				    return hDisplay + mDisplay + sDisplay;
	      		}
		  	},
		  	calcHMSexcel: function(d){
		  		if (d == 0) {
	      			return "00:00:00";
	      		}else {
	      			d = Number(d);
				    var h = Math.floor(d / 3600);
				    var m = Math.floor(d % 3600 / 60);
				    var s = Math.floor(d % 3600 % 60);

				    var hDisplay = h == 0 ? "00" : (h > 0 && h < 10) ? "0" + h : h;
				    var mDisplay = m == 0 ? "00" : (m > 0 && m < 10) ? "0" + m : m;
				    var sDisplay = s == 0 ? "00" : (s > 0 && s < 10) ? "0" + s : s;
				    return hDisplay + ":" + mDisplay + ":" + sDisplay;
	      		}
		  	},
		  	getMerge(arr1, arr2) {
		  		let ids = arr1.map((elem) => elem.num)
		  		Object.values(arr2).forEach((el, index) => {
		  			if (!ids.includes(el.num)) {
			  			arr1.push({
			  				num: el.num,
			  				isxod_count: 0,
			  				isxod_time: "0",
			  				for_all_time: 0
			  			})
		  			}
		  		});
		  		return arr1
		  	},
		  	getMerge2(arr1, arr2) {
		  		let ids = arr1.map((elem) => elem.num)
		  		Object.values(arr2).forEach((el, index) => {
		  			if (!ids.includes(el.num)) {
			  			arr1.push({
			  				num: el.num,
			  				vxod_count: 0,
			  				vxod_time: "0",
			  				for_all_time: 0
			  			})
		  			}
		  		});
		  		return arr1
		  	},
		  	async getFifo(){
		  	    let response = await axios({
				    method: 'post',
				    url: "https://api2.onlinepbx.ru/pbx12127.onpbx.ru/fifo/get.json",
				    data: {
				    	asd: 'asdad'
				    },
				    headers: {
				        "x-pbx-authentication": "<?= $key_and_id ?>"
				    }
				});
				this.fifos = response.data.data;		 		
		  	},
			fifoToReport(){
				let user_5995;

				for (var i = 0; i < this.fifos.length; i++) {
					if (this.fifos[i].num == "5201") {
						user_5995 = this.fifos[i].users;
					}
				}
				
				var myArray_5995 = user_5995.split(";");				
				let reports_support = this.real_reports_5995;
		  		let set_support = [];

		  		for (var a = 0; a < reports_support.length; a++) {
		  			for (var b = 0; b < myArray_5995.length; b++) {
		  				if (reports_support[a].num == myArray_5995[b]) {
		  					set_support.push(reports_support[a])
		  				}
		  			}
		  		}
		  		this.users_5995 = set_support;
			},
			async get_date(){
				let startDate = $('#start_date').val();
				let endDate = $('#get_date').val();

				let fromDate = Math.floor(new Date(startDate).getTime() / 1000);
				let toDate = Math.floor((new Date(endDate).getTime() / 1000)+86400);

				await axios.get('monitoring/data', {params: {from: fromDate, to: toDate}}).then(response => {
					if (response.status == 200) {
						this.calls = response.data
					}
				});		
			},
		  	set_data_from_date() {
				let calls = this.calls	

				let inbounds_5995 = [];
		  		let outbounds_5995 = [];
		      	for (var j = 0; j < calls.length; j++) {
		      		if (calls[j].gateway == '712075995' && calls[j].accountcode == 'inbound') {
		      			inbounds_5995.push(calls[j]);
		      		}else if (calls[j].gateway == '712075995' && calls[j].accountcode == 'outbound') {
		      			outbounds_5995.push(calls[j]);
		      		}
		      	}

				// ----------------------- get_report_by_date 5995 ---------------------------------------------------------

				const todayDate = new Date(this.today.toISOString().split('T')[0])
				const todayTimestamp = Math.floor(todayDate.getTime() / 1000)

				this.todayData = {
					answered: 0,
					missed: 0,
					missed_in: 0,
					talking_time: 0
				}

				this.out_todayData = {
					answered: 0,
					missed: 0,
					talking_time: 0
				}

		      	let inTalk_5995 = [];
		      	let notTalk_5995 = [];
		  		for (var i = 0; i < inbounds_5995.length; i++) {
		  			if (inbounds_5995[i].user_talk_time != 0) {
		  				inTalk_5995.push(inbounds_5995[i]);
		  			}else if (inbounds_5995[i].user_talk_time == 0) {
		  				notTalk_5995.push(inbounds_5995[i]);
		  			}	

					if (todayTimestamp < inbounds_5995[i].start_stamp) {
						this.todayData.talking_time += inbounds_5995[i].user_talk_time
						if (inbounds_5995[i].user_talk_time > 0) {
							this.todayData.answered += 1;
						}else{
							this.todayData.missed += 1;
							this.todayData.missed_in += this.checkDateHours(inbounds_5995[i].start_stamp)
						}
					}
		  		}

		  		let outTalk_5995 = [];
		      	let notAnswer_5995 = [];
		  		for (var i = 0; i < outbounds_5995.length; i++) {
		  			if (outbounds_5995[i].user_talk_time != 0) {
		  				outTalk_5995.push(outbounds_5995[i]);
		  			}else if (outbounds_5995[i].user_talk_time == 0) {
		  				notAnswer_5995.push(outbounds_5995[i])
		  			}

					if (todayTimestamp < outbounds_5995[i].start_stamp) {
						this.out_todayData.talking_time += outbounds_5995[i].user_talk_time
						if (outbounds_5995[i].user_talk_time > 0) {
							this.out_todayData.answered += 1;
						}else{
							this.out_todayData.missed += 1;
						}
					}
		  		}

		  		let all_missed = [];

		  		for (var i = 0; i < notTalk_5995.length; i++) {
		  			all_missed.push(notTalk_5995[i]);
		  		}

		  		let result = inTalk_5995.filter(o1 => notTalk_5995.some(o2 => o1.caller_id_number === o2.caller_id_number));

		  		for (var i = 0; i < result.length; i++) {
					all_missed.push(result[i]);
				}

				let data = [];

				for (var i = 0; i < all_missed.length; i++) {
					let info;
					if (all_missed[i].user_talk_time == 0) {
						info = {
							'start_stamp': all_missed[i].start_stamp,
							'end_stamp': all_missed[i].end_stamp,
							'number': all_missed[i].caller_id_number,
							'user': all_missed[i].destination_number,
							'type': 'Вход',
							'status': 'Пропущенный',
							'talk_time': all_missed[i].user_talk_time
						}
					}else{
						info = {
							'start_stamp': all_missed[i].start_stamp,
							'end_stamp': all_missed[i].end_stamp,
							'number': all_missed[i].caller_id_number,
							'user': all_missed[i].destination_number,
							'type': 'Вход',
							'status': 'Ответили',
							'talk_time': all_missed[i].user_talk_time
						}
					}
					data.push(info);
				}

				let resulto = outbounds_5995.filter(o1 => data.some(o2 => o1.destination_number === o2.number));

				for (var i = 0; i < resulto.length; i++) {
					let info;
					if (resulto[i].user_talk_time == 0) {
						info = {
							'start_stamp': resulto[i].start_stamp,
							'end_stamp': resulto[i].end_stamp,
							'number': resulto[i].destination_number,
							'user': resulto[i].caller_id_number,
							'type': 'Исход',
							'status': 'Не дозвонились',
							'talk_time': resulto[i].user_talk_time
						}
					}else{
						info = {
							'start_stamp': resulto[i].start_stamp,
							'end_stamp': resulto[i].end_stamp,
							'number': resulto[i].destination_number,
							'user': resulto[i].caller_id_number,
							'type': 'Исход',
							'status': 'Дозвонились',
							'talk_time': resulto[i].user_talk_time
						}
					}
					data.push(info);
				}
				function groupBy(collection, property) {
				    var i = 0, val, index,
				        values = [], result = [];
				    for (; i < collection.length; i++) {
				        val = collection[i][property];
				        index = values.indexOf(val);
				        if (index > -1)
				            result[index].push(collection[i]);
				        else {
				            values.push(val);
				            result.push([collection[i]]);
				        }
				    }
				    return result;
				}
				var obj = groupBy(data, "number");

				let order = [];

				for (var i = 0; i < obj.length; i++) {
					order.push(obj[i].sort(function(a, b){return a.start_stamp - b.start_stamp}));
				}

				let infos = [];

				for (var j = 0; j < order.length; j++) {
					let count_pro = 0;
					let count_nedoz = 0;
					let first_calling = [];
					let info;
					let arr = order[j];
					for (var i = 0; i < arr.length; i++) {
					
						if (arr[i].status == "Пропущенный") {
							first_calling.push(arr[i].start_stamp);
							count_pro += 1;
							if (i == arr.length-1) {
								info = {
									'time_for_sort': first_calling[0],
									'start_stamp': new Date(first_calling[0] * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
									'start_stamp_ymd': new Date(first_calling[0] * 1000),
									'number': arr[i].number,
									'count_pro': count_pro,
									'count_nedoz': count_nedoz,
									'status': count_nedoz == 0 ? statuses.NO_ATTEMPT : statuses.DIDNT_ANSWER,
									'user_call': '-',
									'start_talking': '-',
									'start_talking_ymd': this.my_year,
									'for_talking': '-',
									'talk_time': this.calcHMS(arr[i].talk_time),
									'talk_time_Excel': arr[i].talk_time
								}
								infos.push(info);
							}
						}else if (arr[i].status == "Не дозвонились") {
							first_calling.push(arr[i].start_stamp);
							count_nedoz += 1;
							if (i == arr.length-1) {
								info = {
									'time_for_sort': first_calling[0],
									'start_stamp': new Date(first_calling[0] * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
									'start_stamp_ymd': new Date(first_calling[0] * 1000),
									'number': arr[i].number,
									'count_pro': count_pro,
									'count_nedoz': count_nedoz,
									'status': count_nedoz == 0 ? statuses.NO_ATTEMPT : statuses.DIDNT_ANSWER,
									'user_call': '-',
									'start_talking': '-',
									'start_talking_ymd': this.my_year,
									'for_talking': '-',
									'talk_time': this.calcHMS(arr[i].talk_time),
									'talk_time_Excel': arr[i].talk_time
								}
								infos.push(info);
							}
						}else if (arr[i].status == "Ответили") {

							if (!first_calling[0]) {
								info = {
									'time_for_sort': arr[i].start_stamp,
									'start_stamp': '-',
									'start_stamp_ymd': '-',
									'number': arr[i].number,
									'count_pro': count_pro,
									'count_nedoz': count_nedoz,
									'status': arr[i].talk_time < 0 ? statuses.NO_ATTEMPT : statuses.SUCCESS,
									'user_call': 'Клиент',
									'start_talking': new Date(arr[i].start_stamp * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
									'start_talking_ymd': new Date(arr[i].start_stamp * 1000),
									'for_talking': '-',
									'talk_time': this.calcHMS(arr[i].talk_time),
									'talk_time_Excel': arr[i].talk_time
								}
							}else{
								info = {
									'time_for_sort': first_calling[0],
									'start_stamp': new Date(first_calling[0] * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
									'start_stamp_ymd': new Date(first_calling[0] * 1000),
									'number': arr[i].number,
									'count_pro': count_pro,
									'count_nedoz': count_nedoz,
									'status': arr[i].talk_time < 0 ? statuses.NO_ATTEMPT : statuses.SUCCESS,
									'user_call': 'Клиент',
									'start_talking': new Date(first_calling[first_calling.length-1] * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
									'start_talking_ymd': new Date(first_calling[first_calling.length-1] * 1000),
									'for_talking': this.calcHMSexcel(first_calling[first_calling.length-1] - first_calling[0]),
									'talk_time': this.calcHMS(arr[i].talk_time),
									'talk_time_Excel': arr[i].talk_time
								}
							}
							infos.push(info);
							count_nedoz -= count_nedoz;
							count_pro -= count_pro;
							first_calling = [];
						}else if (arr[i].status == "Дозвонились") {
							count_nedoz += 1
							info = {
								'time_for_sort': first_calling[0],
								'start_stamp': new Date(first_calling[0] * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
								'start_stamp_ymd': new Date(first_calling[0] * 1000),
								'number': arr[i].number,
								'count_pro': count_pro,
								'count_nedoz': count_nedoz,
								'status': arr[i].talk_time < 0 ? statuses.NO_ATTEMPT : statuses.SUCCESS,
								'user_call': arr[i].user,
								'start_talking': new Date(arr[i].start_stamp * 1000).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
								'start_talking_ymd': new Date(arr[i].start_stamp * 1000),
								'for_talking': this.calcHMSexcel(arr[i].start_stamp - first_calling[0]),
								'talk_time': this.calcHMS(arr[i].talk_time),
								'talk_time_Excel': arr[i].talk_time
							}
							infos.push(info);
							count_nedoz -= count_nedoz;
							count_pro -= count_pro;
							first_calling = [];
						}
					}
				}

				let comp_info = [];
				for (var i = 0; i < infos.length; i++) {
					if (infos[i].count_pro == 0) {
						continue;
					}
					comp_info.push(infos[i]);
				}
				let users = this.users;
				let datas = [];
				for (var i = 0; i < comp_info.length; i++) {
					let info;
					for (var j = 0; j < users.length; j++) {
						if (comp_info[i].user_call === users[j].num) {
							info = {
								'time_for_sort': comp_info[i].time_for_sort,
								'start_stamp': comp_info[i].start_stamp,
								'start_stamp_ymd': comp_info[i].start_stamp_ymd,
								'number': comp_info[i].number,
								'count_pro': comp_info[i].count_pro,
								'count_nedoz': comp_info[i].count_nedoz,
								'status': comp_info[i].status,
								'user_call': users[j].name,
								'start_talking': comp_info[i].start_talking,
								'start_talking_ymd': comp_info[i].start_talking_ymd,
								'for_talking': comp_info[i].for_talking,
								'talk_time': comp_info[i].talk_time,
								'talk_time_Excel': comp_info[i].talk_time_Excel
							}
						}else if (comp_info[i].user_call === "Клиент" || comp_info[i].user_call === "-") {
							info = {
								'time_for_sort': comp_info[i].time_for_sort,
								'start_stamp': comp_info[i].start_stamp,
								'start_stamp_ymd': comp_info[i].start_stamp_ymd,
								'number': comp_info[i].number,
								'count_pro': comp_info[i].count_pro,
								'count_nedoz': comp_info[i].count_nedoz,
								'status': comp_info[i].status,
								'user_call': comp_info[i].user_call,
								'start_talking': comp_info[i].start_talking,
								'start_talking_ymd': comp_info[i].start_talking_ymd,
								'for_talking': comp_info[i].for_talking,
								'talk_time': comp_info[i].talk_time,
								'talk_time_Excel': comp_info[i].talk_time_Excel
							}
						}
					}
					if(info){
						datas.push(info)
					}
					
				}
				let pro_infos_5995 = datas.sort(function(a, b){return a.time_for_sort - b.time_for_sort});

				this.pro_all_infos_5995 = pro_infos_5995;

				if (document.getElementById("all_calls").checked) {
		  			this.pro_infos_5995 = pro_infos_5995
		  		}else{
		  			let real_datas = [];
		  			for (var i = 0; i < pro_infos_5995.length; i++) {
		  				if (pro_infos_5995[i].status === statuses.SUCCESS) {
		  					continue;
		  				}
		  				real_datas.push(pro_infos_5995[i]);
		  			}
		  			this.pro_infos_5995 = real_datas
		  		}  
				
		  	},
		  	get_by_status: function() {
		  		if (document.getElementById("all_calls").checked) {
		  			let all_5995 = this.pro_all_infos_5995
		  			this.pro_infos_5995 = all_5995;
		  		}else{
		  			let all_5995 = this.pro_all_infos_5995
		  			let pro_infos_5995 = [];
		  			for (var i = 0; i < all_5995.length; i++) {
		  				if (all_5995[i].status === statuses.SUCCESS) {
		  					continue;
		  				}
		  				pro_infos_5995.push(all_5995[i]);
		  			}
		  			this.pro_infos_5995 = pro_infos_5995;
		  		}  
		  	},
		  	async get_with_yesterday() {
		  		var now = new Date();
				var day = ("0" + now.getDate()).slice(-2);
				var last_day = ("0" + (now.getDate() - 1)).slice(-2);
				var month = ("0" + (now.getMonth() + 1)).slice(-2);

				var today = now.getFullYear()+"-"+(month)+"-"+(day);
				var yesterday = now.getFullYear()+"-"+(month)+"-"+(last_day);
		  		if (document.getElementById("yesterday").checked) {
		  			$('#start_date').val(yesterday);
		  			$('#get_date').val(today);
		  		}else{
		  			$('#start_date').val(today);
		  			$('#get_date').val(today);
		  		}  
	  			await this.get_date()
				this.set_data_from_date();
		  	},
		  	get_by_filter: function() {
		  		let filter_by_5995 = [];
		  		if (document.getElementById("propuw").value == 1) {
		  			for(let i in this.pro_all_infos_5995){
		  				if (this.pro_all_infos_5995[i].status === statuses.DIDNT_ANSWER) {
		  					filter_by_5995.push(this.pro_all_infos_5995[i]);
		  				}
		  			}	
		  			this.pro_infos_5995 = filter_by_5995;
		  		}else if (document.getElementById("propuw").value == 2) {
		  			for(let i in this.pro_all_infos_5995){
		  				if (this.pro_all_infos_5995[i].status === statuses.NO_ATTEMPT) {
		  					filter_by_5995.push(this.pro_all_infos_5995[i]);
		  				}
		  			}	
		  			this.pro_infos_5995 = filter_by_5995;
		  		}else{
		  			let all_5995 = this.pro_all_infos_5995
		  			let pro_infos_5995 = [];
		  			for (var i = 0; i < all_5995.length; i++) {
		  				if (all_5995[i].status === statuses.SUCCESS) {
		  					continue;
		  				}
		  				pro_infos_5995.push(all_5995[i]);
		  			}
		  			this.pro_infos_5995 = pro_infos_5995;
		  		}
		  	},
		  	exportData: () => tableToExcel("#exportTable")
	  	}
	})
</script>	
</body>
</html>