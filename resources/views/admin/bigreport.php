<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="robots" content="noindex">
	<link href="/assets/other/materialdesignicons.css" rel="stylesheet">
  <link href="/assets/other/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/other/vuetify.min.css" rel="stylesheet">
	<meta charset="UTF-8">
	<link rel="icon" href="../assets/logo.png">
	<title>Big report | Sales Doctor</title>
	<style scoped>
		.online_text {
			background: chartreuse;
			padding: 5px;
			border-radius: 10px;
			color: black;
		}

		.dot {
			height: 15px;
			width: 15px;
			background-color: red;
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

    .link:hover{
      cursor: pointer;
      text-decoration: underline;
    }
    table{
      font-weight: 700;
    }
	</style>
</head>
<body>
	
<div id="app" class="content-wrapper">
<section class="content-header">
</section>
<template class="content">
  <v-container fluid class="grey lighten-5">
    <v-row>
    	<v-col>
    		<div class="float-right">
            <select class="form-control" v-model="operator_id" style="display: inline;width: auto;">
              <option selected value="">Все операторы</option>
              <option v-for="item in users" :value="item.num">{{item.name}}</option>
            </select>
            <input class="form-control" type="date" v-model="from_date" style="display: inline;width: auto;">
            <input class="form-control" type="date" v-model="to_date" style="display: inline;width: auto;">
            <button class="mb-1 btn btn-success text-white" :loading="loading" type="button" @click="filter()">Фильтр</button>
          </div>
          <div class="float-left">
            <div class="d-inline-block">
              <v-row>
                <v-col>
                  <h4>Дашборд</h4>
                </v-col>
              </v-row>
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
      <v-col cols="12">
        <v-simple-table>
          <template v-slot:default>
          <thead style="border: solid 1px grey;">
            <tr>
              <th class="text-center" width="220px">Имя</th>
              <th class="text-center">⏰ (вовремя)</th>
              <th class="text-center">⏰ (поздно)</th>
              <th class="text-center">Перс. пропущ. звон</th>
              <th class="text-center">Пропущ. в раб. время</th>
              <th class="text-center">Вход. звон</th>
              <th class="text-center">Незарег. вход. клиенты</th>
              <th class="text-center">Total feedback</th>
              <th class="text-center">👍 feedback</th>
              <th class="text-center">Like</th>
              <th class="text-center">Punishment</th>
              <th class="text-center">Script</th>
              <th class="text-center">Product</th>
              <th class="text-center">Решения</th>
              <th class="text-center" width="160px">Онлайн-время</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody style="border: solid 1px grey;">
            <tr v-for="(report, index) in users_5995.filter((u) => u.num != '')" v-show="operator_id == '' || operator_id == report.num">
              <td class="link text-left" :style="{backgroundColor: colors[index]}" @click="toStatistics"><span v-if="report.field == '1'" style="color: #646161">{{ report.name }}</span><span v-else>{{ report.name }}</span></td>
              <td class="text-center link" :style="{backgroundColor: colors[index]}" @click="toReportTable('workly')">{{ report.ontime }}</td>
              <td class="text-center link" :style="{backgroundColor: colors[index]}" @click="toReportTable('workly')">{{ report.outtime }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('personal_missed')">{{ report.personal_missed }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toStatistics">{{ report.missed }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toStatistics">{{ report.inbound }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('unreg_calls')">{{ report.unregs }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('marks_count')">{{ report.total_feedback }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('marks3')">{{ report.mark3_feedback }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('like')">{{ report.like }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('punishment')">{{ report.punishment }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('script')">{{ report.script }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('product')">{{ report.product }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('product')">{{ report.solution }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('online_times')">{{ report.online_time }}</td>
              <td class="text-center" :style="{backgroundColor: colors[index]}">{{ report.total_point.toFixed(1) }}</td>
            </tr>
          </tbody>
          </template>
        </v-simple-table>
      </v-col>

		  <!-- export excel -->
      <v-col style="display: none">
        <v-simple-table style="border-top: solid 1px grey;" id="exportTable2">
        <template v-slot:default>
          <thead style="border: solid 1px grey;">
            <tr>
              <th class="text-center" width="220px">Имя</th>
              <th class="text-center">⏰ (вовремя)</th>
              <th class="text-center">⏰ (поздно)</th>
              <th class="text-center">Перс. пропущ. звон</th>
              <th class="text-center">Пропущ. в раб. время</th>
              <th class="text-center">Вход. звон</th>
              <th class="text-center">Незарег. вход. клиенты</th>
              <th class="text-center">Total feedback</th>
              <th class="text-center">👍 feedback</th>
              <th class="text-center">Like</th>
              <th class="text-center">Punishment</th>
              <th class="text-center">Script</th>
              <th class="text-center">Product</th>
              <th class="text-center">Решения</th>
              <th class="text-center" width="160px">Онлайн-время</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody style="border: solid 1px grey;">
            <tr v-for="(report, index) in users_5995.filter((u) => u.num != '')" v-show="operator_id == '' || operator_id == report.num">
              <td class="link text-left" :style="{backgroundColor: colors[index]}" @click="toStatistics"><span v-if="report.field == '1'" style="color: #646161">{{ report.name }}</span><span v-else>{{ report.name }}</span></td>
              <td class="text-center link" :style="{backgroundColor: colors[index]}" @click="toReportTable('workly')">{{ report.ontime }}</td>
              <td class="text-center link" :style="{backgroundColor: colors[index]}" @click="toReportTable('workly')">{{ report.outtime }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('personal_missed')">{{ report.personal_missed }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toStatistics">{{ report.missed }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toStatistics">{{ report.inbound }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('unreg_calls')">{{ report.unregs }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('marks_count')">{{ report.total_feedback }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('marks3')">{{ report.mark3_feedback }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('like')">{{ report.like }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('punishment')">{{ report.punishment }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('script')">{{ report.script }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('product')">{{ report.product }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('product')">{{ report.solution }}</td>
              <td class="link text-center" :style="{backgroundColor: colors[index]}" @click="toReportTable('online_times')">{{ report.online_time }}</td>
              <td class="text-center" :style="{backgroundColor: colors[index]}">{{ report.total_point.toFixed(1) }}</td>
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
        <button class="float-right btn-primary" style="border-radius: 4px; padding: 8px; border: 1px solid white; color: white;" onclick="tableToExcel('exportTable2','excel','excel')">EXCEL</button>
        <a id="dlink"  href="" style="display: none"></a>
      </v-col>
    </v-row>
  </v-container>
</template>
	
</div>

<script src="/assets/other/axios.min.js"></script>
<script src="/assets/other/vue.js"></script>
<script src="/assets/other/vuetify.js"></script>

<script>

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
        var ctx = {worksheet: 'Big report' || 'Worksheet', table: table.innerHTML};
        document.getElementById("dlink").href = uri + base64(format(template, ctx));
        var filename = new Date().toLocaleDateString("en-GB");
        document.getElementById("dlink").download = 'Big report (' + filename + ')';
        document.getElementById("dlink").click();
    }
  })();

	new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    data: {
      operator_id: '',
      day: '',
      loading: false,
      today: new Date(),
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
      feedbacks: {
        mark0: {},
        mark3: {}
      },
      bigData: [],
      bigDataPeriod: [],
      oper_times: {},
      availableOperators: [],
      oper_misseds: {},
      from_date: "",
      to_date: "",
      unknownClients: {
        inbound: {},
        outbound: {}
      },
      extra: {
        likes: {},
        products: {}
      },
      worklyData: {},
      worklySchedule: {},
      worklyOperators: {},
      scores: {},
      colors: [
        '#6add6a',
        '#6add6a',
        '#6add6a',
        '#f1dc48',
        '#f1dc48',
        '#f1dc48',
        '#f19648',
        '#f19648',
        '#f19648',
        '#f16363',
        '#f16363',
        '#f16363',
        '#f16363',
        '#f16363',
        '#f16363',
        '#f16363',
      ]
    },
    async mounted () {
      var day = ("0" + this.today.getDate()).slice(-2);
      var month = ("0" + (this.today.getMonth() + 1)).slice(-2);
      var today = this.today.getFullYear()+"-"+(month)+"-"+(day);
      this.day = today 

      this.from_date = today
      this.to_date = today

      await this.getWorklyData();
      await this.get_date();

      this.loading = true; 

      await this.getScores();
      await this.getWorklyOperators();
      await this.getWorklySchedule();

      await this.getUsers();
      await this.get_users_feedbacks();
      await this.getOperatorTime();
      
      await this.getUnknownClients();
      await this.getExtra();

      this.getInfos_5995();
      this.getReport_5995();
      await this.getFifo();
      await this.fifoToReport();

      this.loading = false;
    },
    created(){	

      this.interval = setInterval(() =>{
        if (this.from_date == this.day && this.to_date == this.day) {
          this.get_date();
          this.getOperatorTime();
          this.getInfos_5995();
          this.getReport_5995();
          this.fifoToReport();
          this.get_users_feedbacks();
        }
      },30000)

    },
    destroyed(){
      clearInterval(this.interval)
    },
    methods: {
      async getScores(){
        await axios.get('score').then(response => {
          if (response.status == 200) {
            this.scores = response.data;
          }          
        });	
      }, 
      async getWorklyOperators(){
        await axios.get('monitoring/worklyOperators').then(response => {
          if (response.status == 200) {
            this.worklyOperators = response.data;
          }
        });	
      }, 
      async getWorklySchedule(){
        await axios.get('monitoring/worklySchedule').then(response => {
          if (response.status == 200) {
            for (const datum of response.data) {
              if (Object.values(this.worklyOperators).includes(datum.id) > 0) {
                this.worklySchedule[datum.id] = datum.schedule
              }
            }
          }          
        });	
      }, 
      async getWorklyData(){
        this.loading = true;
        await axios.get('monitoring/worklyData', {params: {from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {
            this.loading = false;
            this.worklyData = {}
            for (const datum of response.data) {
              let day = datum.date.slice(0,10)
              if (!this.worklyData[datum.id]) {
                this.worklyData[datum.id] = []
              }
              if (!this.worklyData[datum.id][day]) {
                this.worklyData[datum.id][day] = []
              }
              this.worklyData[datum.id][day].push(datum.date.slice(11, -3))
            }
          }
        });	
      }, 
      async getExtra(){
        await axios.get('bigreport/extra', {params: {from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {
            this.extra = {
              likes: {},
              products: {}
            }
            
            if (response.data.likes.length > 0) {
              for (const like of response.data.likes) {
                this.extra.likes[like.operator] = like
              }
            } 
            if (response.data.products.length > 0) {
              for (const product of response.data.products) {
                this.extra.products[product.operator] = product
              }
            }           
          }
        });	
      }, 
      async personalMissed(){	
        await axios.get('monitoring/personalMissed', {params: {from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {	
            this.oper_misseds = []		
            for (const res of response.data) {
              var create = new Date(res.create_timestamp);
              var destroy = new Date(res.destroy_timestamp);
              if ( 
                this.availableOperators.includes(res.destination_number) && 
                !this.availableOperators.includes(res.caller_number) && 
                res.caller_number.search(".onpbx.ru") < 0 && 
                Math.abs(destroy - create) > 4000
              ) {		
                if (!this.oper_misseds[res.destination_number]) {
                  this.oper_misseds[res.destination_number] = 0;
                }
                this.oper_misseds[res.destination_number] += 1;								
              }
            }			
          }
        });									
      },
      async getOperatorTime(){
        await axios.get('monitoring/operatorTime', {params: {from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {
            this.oper_times = response.data.oper_times			
          }
        });	
      }, 
      async getUnknownClients(){
        await axios.get('monitoring/unknownClients', {params: {from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {
            for (const datum of response.data) {
              if (datum.direction == 'inbound') {
                if (!this.unknownClients.inbound[datum.operator]) {
                  this.unknownClients.inbound[datum.operator] = 0
                }
                this.unknownClients.inbound[datum.operator] = datum.count
              }else{
                if (!this.unknownClients.outbound[datum.operator]) {
                  this.unknownClients.outbound[datum.operator] = 0
                }
                this.unknownClients.outbound[datum.operator] = datum.count
              }
              
            }
          }
        });	
      }, 
      async filter(){
        
        await this.getWorklyData();
        await this.get_date();

        this.loading = true;

        await this.get_users_feedbacks();
        await this.getOperatorTime();
        await this.getUnknownClients();
        await this.getExtra();

        this.getInfos_5995();
        this.getReport_5995();
        await this.fifoToReport();

        this.loading = false;
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
      async get_users_feedbacks(){
        this.feedbacks = {
          mark0: {},
          mark3: {}
        }
        await axios.get('monitoring/usersFeedbacks', {params: {from: this.from_date, to: this.to_date}}).then(response => {
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
            'vxod_time': groupByNum[num],
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
            'isxod_time': groupByNum1[num],
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
            field: users[i].field
          };
          for (var j = 0; j < reports.length; j++) {
            if (users[i].num === reports[j].id) {
              infoss = {
                num: users[i].num,
                name: users[i].name,
                vxod_count: reports[j].vxod_count,
                field: users[i].field
              }	
            }
          }
          reps.push(infoss)
        }
        this.real_reports_5995 = reps.slice(0)
      },
      calcHMS: function(d, format = '0'){
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
        if (format == '1') {
          mDisplay = m > 0 ? m + (m == 1 ? " мин" : " мин") : "";
          sDisplay = "";
        }
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
      async fifoToReport(){
        let user_5995;

        for (var i = 0; i < this.fifos.length; i++) {
          if (this.fifos[i].num == "5201") {
            user_5995 = this.fifos[i].users;
          }
        }
        
        var myArray_5995 = user_5995.split(":1;");	
        for (const el of ['115', '110', '114', '119', '117', '118', '122', '120', '112', '111', '123', '125']) {
          myArray_5995.push(el)
        }
        this.availableOperators = myArray_5995;
        await this.personalMissed();

        let reports_support = this.real_reports_5995;
        let set_support = [];

        for (var a = 0; a < reports_support.length; a++) {
          // if (reports_support[a].num == '120') {
          //   continue;
          // }
          for (var b = 0; b < myArray_5995.length; b++) {
            if (reports_support[a].num == myArray_5995[b]) {
              
              reports_support[a].personal_missed = this.calcPoints(this.oper_misseds[myArray_5995[b]] ?? 0, 'personal_missed')
              reports_support[a].missed = this.calcPoints(this.bigDataPeriod.missed_in ?? 0, 'missed')
              reports_support[a].inbound = this.calcPoints(reports_support[a].vxod_count, 'inbound')
              reports_support[a].total_feedback = this.calcPoints(parseFloat(this.feedbacks.mark3[reports_support[a].num] ?? 0) + parseFloat(this.feedbacks.mark0[reports_support[a].num] ?? 0), 'total_feedback')
              reports_support[a].mark3_feedback = this.calcPoints(parseFloat(this.feedbacks.mark3[reports_support[a].num] ?? 0), 'mark3_feedback')
              reports_support[a].like = this.calcPoints(this.extra.likes[reports_support[a].num] ? this.extra.likes[reports_support[a].num].likes : 0, 'like')
              reports_support[a].punishment = this.calcPoints(this.extra.likes[reports_support[a].num] ? this.extra.likes[reports_support[a].num].punishments : 0, 'punishment')
              reports_support[a].unregs = this.calcPoints(this.unknownClients.inbound[reports_support[a].num] ? this.unknownClients.inbound[reports_support[a].num] : 0, 'unreg_client_inbound')
              reports_support[a].script = this.extra.products[reports_support[a].num] ? parseFloat(parseFloat(this.extra.products[reports_support[a].num].avg_script).toFixed(1)) : 0
              reports_support[a].product = this.extra.products[reports_support[a].num] ? parseFloat(parseFloat(this.extra.products[reports_support[a].num].avg_product).toFixed(1)) : 0
              reports_support[a].solution = this.extra.products[reports_support[a].num] ? parseFloat(parseFloat(this.extra.products[reports_support[a].num].avg_solution).toFixed(1)) : 0
              reports_support[a].online_time = this.calcPoints((this.oper_times[reports_support[a].num] ?? 0)/3600, 'online_time')

              let times = this.calcWorkly(reports_support[a].num);
              reports_support[a].ontime = times.ontime
              reports_support[a].outtime = times.outtime

              reports_support[a].total_point = 
                reports_support[a].personal_missed + reports_support[a].missed + reports_support[a].inbound + reports_support[a].total_feedback 
                + reports_support[a].mark3_feedback + reports_support[a].like + reports_support[a].punishment + reports_support[a].unregs + 
                + reports_support[a].script + reports_support[a].product + reports_support[a].solution + reports_support[a].online_time + reports_support[a].ontime + reports_support[a].outtime;
              
              set_support.push(reports_support[a])
            }
          }
        }     
        set_support.sort(function(a,b) {
          return b.total_point - a.total_point
        });     
        this.users_5995 = set_support;
      },
      async get_date(){
        let startDate = this.from_date;
        let endDate = this.to_date;

        let fromDate = Math.floor(new Date(startDate).getTime() / 1000);
        let toDate = Math.floor((new Date(endDate).getTime() / 1000)+86400);

        this.loading = true;
        await axios.get('monitoring/data', {params: {from: fromDate, to: toDate}}).then(response => {
          if (response.status == 200) {
            this.calls = response.data

            this.bigDataPeriod = {
              answered: 0,
              missed: 0,
              missed_in: 0,
              talking_time: 0
            }

            for (const datum of response.data) {
              if (datum.accountcode == 'inbound') {

                this.bigDataPeriod.talking_time += datum.user_talk_time
                if (datum.user_talk_time > 0) {
                  this.bigDataPeriod.answered += 1;
                }else{
                  this.bigDataPeriod.missed += 1;
                  this.bigDataPeriod.missed_in += this.checkDateHours(datum.start_stamp)
                }

              }
            }
            this.today = new Date()
            this.loading = false
          }
        });		
      },
      calcWorkly(oper_id){  
        try {
          let workly_id = this.worklyOperators[oper_id]
          let data = this.worklyData[workly_id]
          let schedule = this.worklySchedule[workly_id].toString().split('-')
          var scoress = this.scores        

          let ontime = 0;
          let outtime = 0;
          if (data) {
            for (const date in data) {

              var came_date = new Date('2002-04-23 '+data[date][0]+':00')
              var sched_date = new Date('2002-04-23 '+schedule[0]+':00')
              var diff = (came_date.getTime() - sched_date.getTime()) / 60000;

              if (diff > 0) {
                for (const score of scoress['workly_late']) {            
                  if (parseFloat(score.from) <= diff && (score.to == null || parseFloat(score.to) >= diff)) {
                    outtime += parseFloat(score.value) 
                    break;
                  }
                }
              }else{
                for (const score of scoress['workly_ontime']) {            
                  if (parseFloat(score.from) <= Math.abs(diff) && (score.to == null || parseFloat(score.to) >= Math.abs(diff))) {
                    ontime += parseFloat(score.value) 
                    break;
                  }
                }
              }

            }
          }
          return {ontime: ontime, outtime: outtime}       
        }
        catch(err) {
          return {ontime: 0, outtime: 0}   
        } 
      },
      calcPoints(value, key){
        var scoress = this.scores[key]
        if (scoress.value) {
          return parseFloat((value * parseFloat(scoress.value)).toFixed(1));
        }else{
          var point = 0
          for (const score of scoress) {            
            if (parseFloat(score.from) <= value && parseFloat(score.to) >= value) {
              point = parseFloat((value * parseFloat(score.value)).toFixed(1))
              break;
            }
          }
          return point;
        }
      },
      toStatistics(){
        window.open('/admin/piece?from_date='+this.from_date+'&to_date='+this.to_date)
      },
      toReportTable(script){
        window.open('/admin/tablereport?from_date='+this.from_date+'&to_date='+this.to_date+"#"+script)
      }
    }
	})
</script>	
</body>
</html>