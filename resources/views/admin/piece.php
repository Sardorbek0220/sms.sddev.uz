<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="robots" content="noindex">
  <link href="/assets/other/materialdesignicons.css" rel="stylesheet">
  <link href="/assets/other/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/other/vuetify.min.css" rel="stylesheet">
	<meta charset="UTF-8">
	<link rel="icon" href="/assets/logo.png">
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
	
<div id="app" class="content-wrapper">
    <section class="content-header">
    </section>
    <template class="content">
        <v-container fluid class="grey lighten-5">
            <v-row>
              <v-col>
                  <div class="float-right">
                    <select style="display:inline-block; width: auto;" class="form-control" v-model="company" @change="set_company()">
                      <option value="1">Sales Doctor</option>
                      <option value="2">Ibox</option>
                      <option value="3">Ido'kon</option>
                    </select>
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
                                <h4>Статистика</h4>
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
                      <th class="text-left" width="220px">Имя</th>
                      <th class="text-center">Workly <br><span v-show="period" style="color:gainsboro">(вовремя) (поздно)</span></th>
                      <th class="text-left">Перс. пропущ. звон</th>
                      <th class="text-left">Все входящие</th>
                      <th class="text-left">Пропущенные в раб. время</th>
                      <th class="text-left">Вход. звон</th>
                      <th class="text-left">Незарег. вход. клиенты</th>
                      <th class="text-left">Total feedback %</th>
                      <th class="text-left">Feedback 👍 %</th>
                      <th class="text-left">👍</th>
                      <th class="text-left">☹️</th>
                      <th class="text-left" width="160px"><span>онлайн-время</span></th>
                    </tr>
                  </thead>
                  <tbody style="border: solid 1px grey;">
                    <tr v-for="report in users_5995" v-show="operator_id == '' || operator_id == report.num">
                      <td><strong v-if="report.field == '1'">{{ report.name }}</strong><span v-else>{{ report.name }}</span></td>
                      <td class="text-center" v-html="calcWorkly(report.num)"></td>
                      <td>{{ report.personal_missed }}</td>
                      <td>{{ report.inbound }}</td>
                      <td>{{ report.missed_in }}</td>
                      <td>{{ report.vxod_count }}</td>
                      <td>{{ report.unregs }}</td>
                      <td>{{ report.total_feedback }} %</td>
                      <td>{{ report.mark3_feedback }} %</td>
                      <td>{{ report.mark3 }}</td>
                      <td>{{ report.mark0 }}</td>
                      <td>{{ report.online_time }}</td>
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
                      <th class="text-left" width="220px">Имя</th>
                      <th class="text-center">Workly <br><span v-show="period" style="color:gainsboro">(вовремя) (поздно)</span></th>
                      <th class="text-left">Перс. пропущ. звон</th>
                      <th class="text-left">Все входящие</th>
                      <th class="text-left">Пропущенные в раб. время</th>
                      <th class="text-left">Вход. звон</th>
                      <th class="text-left">Незарег. вход. клиенты</th>
                      <th class="text-left">Total feedback %</th>
                      <th class="text-left">Feedback 👍 %</th>
                      <th class="text-left">👍</th>
                      <th class="text-left">☹️</th>
                      <th class="text-left" width="160px"><span>онлайн-время</span></th>
                    </tr>
                  </thead>
                  <tbody style="border: solid 1px grey;">
                    <tr v-for="report in users_5995">
                      <td><strong v-if="report.field == '1'">{{ report.name }}</strong><span v-else>{{ report.name }}</span></td>
                      <td class="text-center" v-html="calcWorkly(report.num)"></td>
                      <td>{{ report.personal_missed }}</td>
                      <td>{{ report.inbound }}</td>
                      <td>{{ report.missed_in }}</td>
                      <td>{{ report.vxod_count }}</td>
                      <td>{{ report.unregs }}</td>
                      <td>{{ report.total_feedback }} %</td>
                      <td>{{ report.mark3_feedback }} %</td>
                      <td>{{ report.mark3 }}</td>
                      <td>{{ report.mark0 }}</td>
                      <td>{{ report.online_time }}</td>
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
        var ctx = {worksheet: 'Piece' || 'Worksheet', table: table.innerHTML};
        document.getElementById("dlink").href = uri + base64(format(template, ctx));
        var filename = new Date().toLocaleDateString("en-GB");
        document.getElementById("dlink").download = 'Piece (' + filename + ')';
        document.getElementById("dlink").click();
    }
})();

new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    data: {
      company: 1,
      fifo_num: "5201",
	    tel_num: "712075995",
      operator_id: '',
      period: false,
      loading: false,
      today: new Date(),
      day: '',
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
      holidays: [],
      availableOperators: [],
      oper_misseds: {},
      from_date: "",
      to_date: "",
      unknownClients: {
        inbound: {},
        outbound: {}
      },
      worklyData: {},
      worklySchedule: {},
      worklyOperators: {}
    },
    async mounted () {
      var day = ("0" + this.today.getDate()).slice(-2);
      var month = ("0" + (this.today.getMonth() + 1)).slice(-2);
      var today = this.today.getFullYear()+"-"+(month)+"-"+(day);
      this.day = today 

      var url = new URL(window.location.href);
      var from_date = url.searchParams.get("from_date");
      var to_date = url.searchParams.get("to_date");

      if (from_date && to_date) {
        this.from_date = from_date
        this.to_date = to_date
      }else{
        this.from_date = today
        this.to_date = today
      }

      await this.TRIGGER();
    },
    created(){	

      this.interval = setInterval(async () =>{
        if (this.from_date == this.day && this.to_date == this.day) {
          await this.get_date();
          await this.getOperatorTime();
          await this.get_users_feedbacks();
          this.getInfos_5995();
          this.getReport_5995();
          await this.fifoToReport();
        }
      },30000)

    },
    destroyed(){
      clearInterval(this.interval)
    },
    methods: {
      async TRIGGER(){
        await this.getWorklyData();
        await this.get_date();
        
        this.loading = true; 

        await this.getWorklyOperators();
        await this.getWorklySchedule();

        await this.getUsers();
        await this.get_users_feedbacks();
        await this.getOperatorTime();
        await this.getBigDataPeriod();
        await this.getUnknownClients();

        this.getInfos_5995();
        this.getReport_5995();
        await this.getFifo();
        await this.fifoToReport();
        
        this.loading = false;
      },
      async set_company(){
        if (this.company == 2) {
          this.fifo_num = "5202";
          this.tel_num = "781138585";
        }else if (this.company == 3) {
					this.fifo_num = "5200";
					this.tel_num = "781136022";
				}else{
          this.fifo_num = "5201";
          this.tel_num = "712075995";
        }
        await this.TRIGGER();
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
            this.holidays = response.data.holidays							
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
        await this.getBigDataPeriod();
        await this.getUnknownClients();
        this.getInfos_5995();
        this.getReport_5995();
        await this.fifoToReport();

        this.loading = false;
      },
      async getBigDataPeriod(){
        await axios.get('monitoring/bigData', {params: {gateway: this.tel_num, from: this.from_date, to: this.to_date}}).then(response => {
          if (response.status == 200) {
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
          }
        });	
      },
      checkDateHours(timestamp){
        let date = new Date(timestamp * 1000)	
        let formattedDate = date.toLocaleDateString('en-CA')							
        let	hours = date.getHours()
        let day = date.getDay()
        
        if (this.holidays.includes(formattedDate)) {
            
          if (hours >= 9 && hours < 18) {
            return 1
          }else{
            return 0
          }
            
        }else{

          if ( hours >= 9 && ( (hours < 20 && ![0,6].includes(day)) || (hours < 18 && [0,6].includes(day)) ) ) {
            return 1
          }else{
            return 0
          }

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
      getInfos_5995(){
        let calls = this.calls;

        // -------------------------------- inbounds_5995 -----------------------------------------
        let inbounds_5995 = [];
        let inreports_5995 = [];
        for (var j = 0; j < calls.length; j++) {
          if (calls[j].gateway == this.tel_num && calls[j].accountcode == 'inbound') {
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
          if (calls[j].gateway == this.tel_num && calls[j].accountcode == 'outbound') {
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
      getReport_5995(){
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
        var byVxod_count = reps.slice(0);
        byVxod_count.sort(function(a,b) {
          return a.name.localeCompare(b.name)
        });
        this.real_reports_5995 = byVxod_count
      },
      calcHMS(d, format = '0'){
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
      calcHMSexcel(d){
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
          if (this.fifos[i].num == this.fifo_num) {
            user_5995 = this.fifos[i].users;
          }
        }
        
        var myArray_5995 = user_5995;	
        this.availableOperators = myArray_5995;
        await this.personalMissed();

        let reports_support = this.real_reports_5995;
        let set_support = [];

        for (var a = 0; a < reports_support.length; a++) {
          if (myArray_5995.includes(reports_support[a].num)) {
            reports_support[a].personal_missed = this.oper_misseds[reports_support[a].num] ?? 0
            reports_support[a].inbound = this.bigDataPeriod.missed+this.bigDataPeriod.answered
            reports_support[a].missed_in = this.bigDataPeriod.missed_in
            reports_support[a].total_feedback = reports_support[a].vxod_count > 0 ? ( ((parseFloat(this.feedbacks.mark3[reports_support[a].num] ?? 0) + parseFloat(this.feedbacks.mark0[reports_support[a].num] ?? 0))/reports_support[a].vxod_count) * 100 ).toFixed(1) : 0
            reports_support[a].mark3_feedback = reports_support[a].vxod_count > 0 ? ( (parseFloat(this.feedbacks.mark3[reports_support[a].num] ?? 0)/reports_support[a].vxod_count) * 100 ).toFixed(1) : 0
            reports_support[a].unregs = this.unknownClients.inbound[reports_support[a].num] ?? 0
            reports_support[a].mark3 = this.feedbacks.mark3[reports_support[a].num] ?? 0
            reports_support[a].mark0 = this.feedbacks.mark0[reports_support[a].num] ?? 0
            reports_support[a].online_time = this.calcHMS(this.oper_times[reports_support[a].num], '1')
            set_support.push(reports_support[a])
          }
        }
        this.users_5995 = set_support;
      },
      async get_date(){
        let startDate = this.from_date;
        let endDate = this.to_date;

        let fromDate = Math.floor(new Date(startDate).getTime() / 1000);
        let toDate = Math.floor((new Date(endDate).getTime() / 1000)+86400);

        this.loading = true;
        await axios.get('monitoring/data', {params: {gateway: this.tel_num, from: fromDate, to: toDate}}).then(response => {
          if (response.status == 200) {
            this.calls = response.data
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
          
          if (data) {
            
            if (this.day != this.to_date || this.day != this.from_date) {
              this.period = true;
              let ontime = 0;
              let outtime = 0;
              for (const date in data) {
                if (new Date('2002-04-23 '+data[date][0]+':00') <= new Date('2002-04-23 '+schedule[0]+':00')) {
                  ontime++;
                }else{
                  outtime++;
                }

              }
              return "<strong style='color:#2de12d'>"+ontime+"</strong>&nbsp&nbsp&nbsp&nbsp&nbsp<strong style='color:red'>"+outtime+"</strong>"

            }else{

              if (new Date('2002-04-23 '+data[this.day][0]+':00') <= new Date('2002-04-23 '+schedule[0]+':00')) {
                return "<strong style='color:#2de12d'>"+data[this.day][0]+"</strong>"
              }else{
                return "<strong style='color:red'>"+data[this.day][0]+"</strong>"
              }

            }

          }else{
            return '-'
          }        
        } catch (error) {
          return '-'
        }
        
      }
    }
})
</script>	
</body>
</html>